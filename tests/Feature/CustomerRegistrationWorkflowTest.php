<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class CustomerRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_registration_rejects_an_email_that_already_has_an_account(): void
    {
        Mail::fake();

        $user = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        UserDetail::query()->create([
            'user_id' => $user->user_id,
            'name' => 'Existing Customer',
            'username' => 'existing_customer',
            'age' => 25,
            'email' => 'taken@example.com',
            'contact' => '09123456789',
            'address' => 'Pasig City',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/register/step-1', [
            'email' => 'taken@example.com',
        ])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();

        $this->assertDatabaseMissing('otps', [
            'email' => 'taken@example.com',
            'purpose' => 'registration',
        ]);
    }

    public function test_registration_rejects_an_email_domain_that_cannot_receive_mail(): void
    {
        Mail::fake();

        $this->post('/register/step-1', [
            'email' => 'customer@nonexistent-bakerdan.invalid',
        ])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();

        $this->assertDatabaseMissing('otps', [
            'email' => 'customer@nonexistent-bakerdan.invalid',
            'purpose' => 'registration',
        ]);
    }

    public function test_registration_reuses_an_unexpired_registration_otp_and_sends_email(): void
    {
        Mail::fake();

        Otp::query()->create([
            'email' => 'new@gmail.com',
            'otp' => '123456',
            'purpose' => 'registration',
            'created_at' => now()->subMinute(),
            'expire_at' => now()->addMinutes(9),
        ]);

        $this->post('/register/step-1', [
            'email' => 'NEW@gmail.com',
        ])
            ->assertRedirect(route('register.step2'))
            ->assertSessionHas('registration_email', 'new@gmail.com');

        $this->assertDatabaseCount('otps', 1);
        $this->assertDatabaseHas('otps', [
            'email' => 'new@gmail.com',
            'otp' => '123456',
            'purpose' => 'registration',
        ]);

        Mail::assertSent(OtpMail::class, function (OtpMail $mail): bool {
            return (string) $mail->otp === '123456';
        });
    }

    public function test_registration_stops_when_otp_mail_delivery_fails(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->with('fallback@gmail.com')
            ->andThrow(new RuntimeException('SMTP recipient limit reached'));

        $this
            ->from(route('register.step1'))
            ->post('/register/step-1', [
                'email' => 'Fallback@Gmail.com',
            ])
            ->assertRedirect(route('register.step1'))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('registration_email');

        $this->assertDatabaseMissing('otps', [
            'email' => 'fallback@gmail.com',
            'purpose' => 'registration',
        ]);
    }

    public function test_profile_completion_form_renders_country_contact_and_current_address_controls(): void
    {
        $this
            ->withSession([
                'registration_email' => 'profile@example.com',
                'otp_verified' => true,
            ])
            ->get('/register/step-3')
            ->assertOk()
            ->assertSee('name="contact_country"', false)
            ->assertSee('id="country-trigger"', false)
            ->assertSee('data-country-code="PH"', false)
            ->assertSee('+63')
            ->assertSee('Use current address');
    }

    public function test_customer_can_verify_otp_and_finish_registration(): void
    {
        Mail::fake();

        Otp::query()->create([
            'email' => 'fresh@example.com',
            'otp' => '654321',
            'purpose' => 'registration',
            'created_at' => now(),
            'expire_at' => now()->addMinutes(10),
        ]);

        $this
            ->withSession(['registration_email' => 'fresh@example.com'])
            ->post('/register/step-2', [
                'otp' => '654321',
            ])
            ->assertRedirect(route('register.step3'))
            ->assertSessionHas('otp_verified', true);

        $this->assertDatabaseMissing('otps', [
            'email' => 'fresh@example.com',
            'purpose' => 'registration',
        ]);

        $this
            ->post('/register/step-3', [
                'name' => 'Fresh Customer',
                'username' => 'freshcustomer',
                'password' => 'password123',
                'age' => 28,
                'contact_country' => 'PH',
                'contact' => '09991234567',
                'address' => 'San Nicolas, Pasig City',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('account_created', true);

        $this->assertDatabaseHas('users', [
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('user_details', [
            'email' => 'fresh@example.com',
            'username' => 'freshcustomer',
            'name' => 'Fresh Customer',
            'contact' => '+63 999 123 4567',
            'address' => 'San Nicolas, Pasig City',
        ]);
    }

    public function test_profile_completion_rejects_special_characters_and_invalid_country_contact(): void
    {
        $this
            ->withSession([
                'registration_email' => 'secure@example.com',
                'otp_verified' => true,
            ])
            ->post('/register/step-3', [
                'name' => 'Robert; Drop',
                'username' => 'secure_user',
                'password' => 'password123',
                'age' => 31,
                'contact_country' => 'PH',
                'contact' => '12345',
                'address' => '<script>Pasig City</script>',
            ])
            ->assertSessionHasErrors(['name', 'username', 'contact']);

        $this->assertDatabaseMissing('user_details', [
            'email' => 'secure@example.com',
        ]);
    }

    public function test_profile_completion_formats_selected_country_contact_number(): void
    {
        $this
            ->withSession([
                'registration_email' => 'phone@example.com',
                'otp_verified' => true,
            ])
            ->post('/register/step-3', [
                'name' => 'Phone Customer',
                'username' => 'phonecustomer',
                'password' => 'password123',
                'age' => 34,
                'contact_country' => 'SG',
                'contact' => '91234567',
                'address' => 'Singapore Central Area',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('account_created', true);

        $this->assertDatabaseHas('user_details', [
            'email' => 'phone@example.com',
            'username' => 'phonecustomer',
            'contact' => '+65 9123 4567',
            'address' => 'Singapore Central Area',
        ]);
    }
}
