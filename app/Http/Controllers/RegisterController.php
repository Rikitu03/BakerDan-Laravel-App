<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    private const OTP_PURPOSE = 'registration';
    private const OTP_TTL_MINUTES = 10;
    private const CONTACT_COUNTRIES = [
        'PH' => [
            'dial_code' => '63',
            'national_digits' => 10,
            'groups' => [3, 3, 4],
            'local_prefix' => '0',
            'pattern' => '/^9\d{9}$/',
            'example' => '+63 939 165 4377',
        ],
        'US' => [
            'dial_code' => '1',
            'national_digits' => 10,
            'groups' => [3, 3, 4],
            'pattern' => '/^[2-9]\d{9}$/',
            'example' => '+1 415 555 2671',
        ],
        'CA' => [
            'dial_code' => '1',
            'national_digits' => 10,
            'groups' => [3, 3, 4],
            'pattern' => '/^[2-9]\d{9}$/',
            'example' => '+1 416 555 2671',
        ],
        'GB' => [
            'dial_code' => '44',
            'national_digits' => 10,
            'groups' => [4, 3, 3],
            'local_prefix' => '0',
            'pattern' => '/^[1-9]\d{9}$/',
            'example' => '+44 7911 123 456',
        ],
        'AU' => [
            'dial_code' => '61',
            'national_digits' => 9,
            'groups' => [1, 4, 4],
            'local_prefix' => '0',
            'pattern' => '/^[2-9]\d{8}$/',
            'example' => '+61 4 1234 5678',
        ],
        'SG' => [
            'dial_code' => '65',
            'national_digits' => 8,
            'groups' => [4, 4],
            'pattern' => '/^[689]\d{7}$/',
            'example' => '+65 9123 4567',
        ],
        'JP' => [
            'dial_code' => '81',
            'national_digits' => 10,
            'groups' => [2, 4, 4],
            'local_prefix' => '0',
            'pattern' => '/^[1-9]\d{9}$/',
            'example' => '+81 90 1234 5678',
        ],
    ];

    public function showStep1()
    {
        return view('auth.register-step1');
    }

    public function handleStep1(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => $this->normalizeEmail((string) $request->input('email')),
        ]);

        $validated = $request->validate([
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('user_details', 'email'),
            ],
        ], [
            'email.email' => 'Please enter a valid email address with a domain that can receive mail.',
            'email.unique' => 'An account already exists for this email. Please log in instead.',
        ]);

        $email = $this->normalizeEmail($validated['email']);
        $otpValue = $this->issueRegistrationOtp($email);

        Session::put('registration_email', $email);
        Session::forget(['otp_verified', 'registration_otp_verified_at']);

        if (! $this->sendRegistrationOtp($email, $otpValue, 'Registration Mail Error')) {
            Session::forget(['registration_email', 'otp_verified', 'registration_otp_verified_at']);
            $this->deleteRegistrationOtp($email);

            return back()->withErrors(['email' => 'Unable to send OTP. Please try again later or contact support.']);
        }

        return redirect()->route('register.step2');
    }

    public function showStep2()
    {
        if (!Session::has('registration_email')) {
            return redirect()->route('register.step1');
        }
        return view('auth.register-step2');
    }

    public function handleStep2(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Session::get('registration_email');

        if (! $email) {
            return redirect()->route('register.step1');
        }

        $otpRecord = Otp::query()
            ->where('email', '=', $email, 'and')
            ->where('purpose', '=', self::OTP_PURPOSE, 'and')
            ->first();

        if (! $otpRecord || ! hash_equals((string) $otpRecord->otp, (string) $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid OTP. Please check the code and try again.']);
        }

        if ($otpRecord->expire_at <= now()) {
            $otpRecord->delete();

            return back()->withErrors(['otp' => 'OTP has expired. Please request a new code.']);
        }

        Otp::query()
            ->where('email', '=', $email, 'and')
            ->where('purpose', '=', self::OTP_PURPOSE, 'and')
            ->delete();

        Session::put('otp_verified', true);
        Session::put('registration_otp_verified_at', now()->toIso8601String());

        return redirect()->route('register.step3');
    }

    public function resendOtp(): RedirectResponse
    {
        $email = Session::get('registration_email');

        if (! $email) {
            return redirect()->route('register.step1');
        }

        if (UserDetail::query()->where('email', '=', $email, 'and')->exists()) {
            Session::forget(['registration_email', 'otp_verified', 'registration_otp_verified_at']);

            return redirect()
                ->route('register.step1')
                ->withErrors(['email' => 'An account already exists for this email. Please log in instead.']);
        }

        $otpValue = $this->issueRegistrationOtp($email, forceNew: true);

        Session::forget(['otp_verified', 'registration_otp_verified_at']);

        if (! $this->sendRegistrationOtp($email, $otpValue, 'Resend OTP Mail Error')) {
            $this->deleteRegistrationOtp($email);

            return back()->withErrors(['otp' => 'Unable to resend OTP. Please try again later.']);
        }

        return back()->with('status', 'A new OTP has been sent to your email.');
    }

    public function showStep3()
    {
        if (!Session::get('otp_verified')) {
            return redirect()->route('register.step2');
        }
        return view('auth.register-step3');
    }

    public function handleStep3(Request $request): RedirectResponse
    {
        if (! Session::get('otp_verified') || ! Session::has('registration_email')) {
            return redirect()->route('register.step2');
        }

        $email = $this->normalizeEmail((string) Session::get('registration_email'));

        $request->merge([
            'name' => $this->cleanNameInput((string) $request->input('name')),
            'username' => $this->cleanUsernameInput((string) $request->input('username')),
            'address' => $this->cleanAddressInput((string) $request->input('address')),
            'contact_country' => Str::upper(trim((string) $request->input('contact_country', 'PH'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[\pL]+(?: [\pL]+)*$/u'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9]+$/', Rule::unique('user_details', 'username')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'address' => ['required', 'string', 'min:5', 'max:500', 'not_regex:/[<>]/'],
            'contact_country' => ['required', Rule::in(array_keys(self::CONTACT_COUNTRIES))],
            'contact' => [
                'required',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $country = Str::upper((string) $request->input('contact_country', 'PH'));
                    if ($this->normalizedContactDigits($country, (string) $value) !== null) {
                        return;
                    }

                    $example = self::CONTACT_COUNTRIES[$country]['example'] ?? self::CONTACT_COUNTRIES['PH']['example'];
                    $fail("The contact number must match the selected country format, like {$example}.");
                },
            ],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
        ], [
            'name.regex' => 'Full name may only contain letters and spaces.',
            'name.max' => 'Full name may not be greater than 100 characters.',
            'username.regex' => 'Username may only contain letters and numbers.',
            'username.max' => 'Username may not be greater than 50 characters.',
            'address.not_regex' => 'Address cannot contain angle brackets.',
        ]);

        $formattedContact = $this->formatContactNumber($validated['contact_country'], $validated['contact']);

        if (UserDetail::query()->where('email', '=', $email, 'and')->exists()) {
            Session::forget(['registration_email', 'otp_verified', 'registration_otp_verified_at']);

            return redirect()
                ->route('register.step1')
                ->withErrors(['email' => 'An account already exists for this email. Please log in instead.']);
        }

        DB::transaction(function () use ($validated, $email, $formattedContact): void {
            $user = User::query()->create([
                'role' => 'customer',
                'is_active' => true,
            ]);

            UserDetail::query()->create([
                'user_id' => $user->user_id,
                'name' => $validated['name'],
                'username' => $validated['username'],
                'age' => (int) $validated['age'],
                'email' => $email,
                'contact' => $formattedContact,
                'address' => $validated['address'],
                'password' => Hash::make((string) $validated['password']),
            ]);
        });

        Session::forget(['registration_email', 'otp_verified', 'registration_otp_verified_at']);

        return redirect('/')->with('account_created', true);
    }

    private function issueRegistrationOtp(string $email, bool $forceNew = false): string
    {
        $email = $this->normalizeEmail($email);
        $existingOtp = Otp::query()
            ->where('email', '=', $email, 'and')
            ->where('purpose', '=', self::OTP_PURPOSE, 'and')
            ->first();

        if (! $forceNew && $existingOtp && $existingOtp->expire_at && $existingOtp->expire_at->isFuture()) {
            return (string) $existingOtp->otp;
        }

        $otpValue = (string) random_int(100000, 999999);

        Otp::query()->updateOrCreate(
            ['email' => $email, 'purpose' => self::OTP_PURPOSE],
            [
                'otp' => $otpValue,
                'created_at' => Carbon::now(),
                'expire_at' => Carbon::now()->addMinutes(self::OTP_TTL_MINUTES),
            ]
        );

        return $otpValue;
    }

    private function sendRegistrationOtp(string $email, string $otpValue, string $logPrefix): bool
    {
        try {
            Mail::to($email)->send(new OtpMail($otpValue));

            return true;
        } catch (Throwable $e) {
            Log::error($logPrefix . ': ' . $e->getMessage());

            return false;
        }
    }

    private function deleteRegistrationOtp(string $email): void
    {
        Otp::query()
            ->where('email', '=', $this->normalizeEmail($email), 'and')
            ->where('purpose', '=', self::OTP_PURPOSE, 'and')
            ->delete();
    }

    private function cleanNameInput(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    private function cleanUsernameInput(string $value): string
    {
        return Str::lower(trim($value));
    }

    private function cleanAddressInput(string $value): string
    {
        $value = strip_tags($value);
        $value = (string) preg_replace('/[\x00-\x1F\x7F]/u', ' ', $value);
        $value = (string) preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    private function normalizedContactDigits(string $country, string $contact): ?string
    {
        $country = Str::upper($country);
        $metadata = self::CONTACT_COUNTRIES[$country] ?? null;

        if ($metadata === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $contact) ?? '';
        $dialCode = $metadata['dial_code'];
        $nationalLength = (int) $metadata['national_digits'];
        $internationalPrefix = '00' . $dialCode;

        if (str_starts_with($digits, $internationalPrefix)) {
            $digits = substr($digits, strlen($internationalPrefix));
        }

        if (strlen($digits) > $nationalLength && str_starts_with($digits, $dialCode)) {
            $digits = substr($digits, strlen($dialCode));
        }

        $localPrefix = $metadata['local_prefix'] ?? null;
        if ($localPrefix !== null && $localPrefix !== '' && strlen($digits) > $nationalLength && str_starts_with($digits, $localPrefix)) {
            $digits = substr($digits, strlen($localPrefix));
        }

        if (strlen($digits) !== $nationalLength) {
            return null;
        }

        if (! preg_match($metadata['pattern'], $digits)) {
            return null;
        }

        return $digits;
    }

    private function formatContactNumber(string $country, string $contact): string
    {
        $country = Str::upper($country);
        $metadata = self::CONTACT_COUNTRIES[$country] ?? self::CONTACT_COUNTRIES['PH'];
        $digits = $this->normalizedContactDigits($country, $contact) ?? '';
        $groups = [];
        $offset = 0;

        foreach ($metadata['groups'] as $size) {
            $part = substr($digits, $offset, $size);
            if ($part === '') {
                continue;
            }

            $groups[] = $part;
            $offset += $size;
        }

        if ($offset < strlen($digits)) {
            $groups[] = substr($digits, $offset);
        }

        return '+' . $metadata['dial_code'] . ' ' . implode(' ', $groups);
    }

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
