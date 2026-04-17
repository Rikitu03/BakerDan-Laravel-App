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

        $existingOtp = Otp::where('email', $email)
            ->where('purpose', 'password')
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

        Mail::to($email)->send(new OtpMail($otpValue));

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

        $otpRecord = Otp::where('email', $email)
            ->where('otp', $request->otp)
            ->where('purpose', 'password')
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if ($otpRecord->expire_at <= Carbon::now()) {
            return back()->withErrors(['otp' => 'OTP has expired']);
        }

        $otpRecord->delete();
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
        
        $detail = UserDetail::where('email', $email)->first();
        if ($detail) {
            $detail->update([
                'password' => Hash::make($request->password)
            ]);
        }

        Session::forget(['forgot_password_email', 'password_otp_verified']);

        return redirect()->route('login')->with('password_changed', true);
    }
}
