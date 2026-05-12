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
                'email',
                'max:255',
                Rule::unique('user_details', 'email'),
            ],
        ], [
            'email.unique' => 'An account already exists for this email. Please log in instead.',
        ]);

        $email = $this->normalizeEmail($validated['email']);
        $otpValue = $this->issueRegistrationOtp($email);

        try {
            Mail::to($email)->send(new OtpMail($otpValue));
        } catch (Throwable $e) {
            Log::error('Registration Mail Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send OTP. Please try again later or contact support.']);
        }

        Session::put('registration_email', $email);
        Session::forget(['otp_verified', 'registration_otp_verified_at']);

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

        try {
            Mail::to($email)->send(new OtpMail($otpValue));
        } catch (Throwable $e) {
            Log::error('Resend OTP Mail Error: ' . $e->getMessage());
            return back()->withErrors(['otp' => 'Unable to resend OTP. Please try again later.']);
        }

        Session::forget(['otp_verified', 'registration_otp_verified_at']);

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

        $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'max:255', Rule::unique('user_details', 'username')],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'contact' => ['required', 'string', 'max:30'],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        if (UserDetail::query()->where('email', '=', $email, 'and')->exists()) {
            Session::forget(['registration_email', 'otp_verified', 'registration_otp_verified_at']);

            return redirect()
                ->route('register.step1')
                ->withErrors(['email' => 'An account already exists for this email. Please log in instead.']);
        }

        DB::transaction(function () use ($request, $email): void {
            $user = User::query()->create([
                'role' => 'customer',
                'is_active' => true,
            ]);

            UserDetail::query()->create([
                'user_id' => $user->user_id,
                'name' => trim((string) $request->name),
                'username' => trim((string) $request->username),
                'age' => (int) $request->age,
                'email' => $email,
                'contact' => trim((string) $request->contact),
                'address' => trim((string) $request->address),
                'password' => Hash::make((string) $request->password),
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

    private function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }
}
