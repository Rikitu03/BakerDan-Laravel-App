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

class RegisterController extends Controller
{
    public function showStep1()
    {
        return view('auth.register-step1');
    }

    public function handleStep1(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        $existingOtp = Otp::where('email', $email)
            ->where('purpose', 'registration')
            ->first();

        if ($existingOtp && $existingOtp->expire_at > Carbon::now()) {
            $otpValue = $existingOtp->otp;
        } else {
            $otpValue = rand(100000, 999999);
            Otp::updateOrCreate(
                ['email' => $email, 'purpose' => 'registration'],
                [
                    'otp' => $otpValue,
                    'created_at' => Carbon::now(),
                    'expire_at' => Carbon::now()->addMinutes(10)
                ]
            );
        }

        Mail::to($email)->send(new OtpMail($otpValue));

        Session::put('registration_email', $email);
        return redirect()->route('register.step2');
    }

    public function showStep2()
    {
        if (!Session::has('registration_email')) {
            return redirect()->route('register.step1');
        }
        return view('auth.register-step2');
    }

    public function handleStep2(Request $request)
    {
        $request->validate(['otp' => 'required']);
        $email = Session::get('registration_email');

        $otpRecord = Otp::where('email', $email)
            ->where('otp', $request->otp)
            ->where('purpose', 'registration')
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if ($otpRecord->expire_at <= Carbon::now()) {
            return back()->withErrors(['otp' => 'OTP has expired']);
        }

        $otpRecord->delete();
        Session::put('otp_verified', true);

        return redirect()->route('register.step3');
    }

    public function resendOtp()
    {
        $email = Session::get('registration_email');
        if (!$email) return redirect()->route('register.step1');

        $otpValue = rand(100000, 999999);
        Otp::updateOrCreate(
            ['email' => $email, 'purpose' => 'registration'],
            [
                'otp' => $otpValue,
                'created_at' => Carbon::now(),
                'expire_at' => Carbon::now()->addMinutes(10)
            ]
        );

        Mail::to($email)->send(new OtpMail($otpValue));

        return back()->with('status', 'OTP has been resent');
    }

    public function showStep3()
    {
        if (!Session::get('otp_verified')) {
            return redirect()->route('register.step2');
        }
        return view('auth.register-step3');
    }

    public function handleStep3(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:user_details',
            'password' => 'required|min:8',
            'name' => 'required',
            'address' => 'required',
            'contact' => 'required',
            'age' => 'required|integer',
        ]);

        $email = Session::get('registration_email');

        $user = User::create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        UserDetail::create([
            'user_id' => $user->user_id,
            'name' => $request->name,
            'username' => $request->username,
            'age' => $request->age,
            'email' => $email,
            'contact' => $request->contact,
            'address' => $request->address,
            'password' => Hash::make($request->password),
        ]);

        Session::forget(['registration_email', 'otp_verified']);

        return redirect('/')->with('account_created', true);
    }
}
