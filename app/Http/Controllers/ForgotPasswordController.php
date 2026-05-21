<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Otp;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function showStep1()
    {
        return view('auth.forgot-password-step1');
    }

    public function handleStep1(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:user_details,email']);
        $email = $request->email;

        $existingOtp = Otp::query()
            ->where('email', '=', $email, 'and')
            ->where('purpose', '=', 'password', 'and')
            ->first();

        if ($existingOtp && $existingOtp->expire_at > Carbon::now()) {
            $otpValue = $existingOtp->otp;
        } else {
            $otpValue = rand(100000, 999999);
            Otp::updateOrCreate(
                ['email' => $email, 'purpose' => 'password'],
                [
                    'otp' => $otpValue,
                    'created_at' => Carbon::now(),
                    'expire_at' => Carbon::now()->addMinutes(10)
                ]
            );
        }

        try {
            Mail::to($email)->send(new OtpMail($otpValue));
        } catch (Throwable $e) {
            Log::error('Forgot Password Mail Error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send OTP. Please try again later or contact support.']);
        }


        Session::put('forgot_password_email', $email);
        return redirect()->route('password.otp');
    }

    public function showStep2()
    {
        if (!Session::has('forgot_password_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.forgot-password-step2');
    }

    public function handleStep2(Request $request)
    {
        $request->validate(['otp' => 'required']);
        $email = Session::get('forgot_password_email');

        $otpRecord = Otp::query()
            ->where('email', '=', $email, 'and')
            ->where('otp', '=', $request->otp, 'and')
            ->where('purpose', '=', 'password', 'and')
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if ($otpRecord->expire_at <= Carbon::now()) {
            return back()->withErrors(['otp' => 'OTP has expired']);
        }

        Otp::destroy($otpRecord->otp_id);
        Session::put('password_otp_verified', true);

        return redirect()->route('password.reset');
    }

    public function showStep3()
    {
        if (!Session::get('password_otp_verified')) {
            return redirect()->route('password.otp');
        }
        return view('auth.forgot-password-step3');
    }

    public function handleStep3(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $email = Session::get('forgot_password_email');
        
        $detail = UserDetail::query()->where('email', '=', $email, 'and')->first();
        if ($detail) {
            $detail->update([
                'password' => Hash::make($request->password)
            ]);
        }

        Session::forget(['forgot_password_email', 'password_otp_verified']);

        return redirect()->route('login')->with('password_changed', true);
    }
}
