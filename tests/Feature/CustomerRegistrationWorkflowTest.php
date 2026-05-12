<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
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

    public function test_registration_reuses_an_unexpired_registration_otp_and_sends_email(): void
    {
        Mail::fake();

        Otp::query()->create([
            'email' => 'new@example.com',
            'otp' => '123456',
            'purpose' => 'registration',
            'created_at' => now()->subMinute(),
            'expire_at' => now()->addMinutes(9),
        ]);

        $this->post('/register/step-1', [
            'email' => 'NEW@example.com',
        ])
            ->assertRedirect(route('register.step2'))
            ->assertSessionHas('registration_email', 'new@example.com');

        $this->assertDatabaseCount('otps', 1);
        $this->assertDatabaseHas('otps', [
            'email' => 'new@example.com',
            'otp' => '123456',
            'purpose' => 'registration',
        ]);

        Mail::assertSent(OtpMail::class, function (OtpMail $mail): bool {
            return (string) $mail->otp === '123456';
        });
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
                'username' => 'fresh_customer',
                'password' => 'password123',
                'age' => 28,
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
            'username' => 'fresh_customer',
            'name' => 'Fresh Customer',
            'contact' => '09991234567',
            'address' => 'San Nicolas, Pasig City',
        ]);
    }
}
