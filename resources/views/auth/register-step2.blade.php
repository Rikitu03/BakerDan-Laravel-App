@extends('layouts.auth')

@section('content')
<h2 class="font-display text-2xl font-bold mb-6">Verify OTP</h2>
<p class="mb-6 text-sm" style="color: var(--ink-soft);">We sent a verification code to <strong>{{ session('registration_email') }}</strong>.</p>

<form action="{{ route('register.step2') }}" method="POST">
    @csrf
    <div class="mb-6">
        <label for="otp" class="block text-sm font-medium mb-1">One-Time Password</label>
        <input type="text" name="otp" id="otp" required
            class="w-full rounded-xl border p-3 text-center text-2xl tracking-[0.5em] font-bold bg-white/50 focus:bg-white transition"
            style="border-color: var(--line);" placeholder="000000" maxlength="6">
        @error('otp')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    
    <div class="space-y-3">
        <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
            style="background: var(--ink);">
            Verify
        </button>
    </div>
</form>

<form action="{{ route('register.resend-otp') }}" method="POST" class="mt-4 text-center">
    @csrf
    <button type="submit" class="text-sm font-medium hover:underline" style="color: var(--brand-deep);">
        Resend OTP
    </button>
</form>

@if (session('status'))
    <p class="text-green-600 text-xs mt-4 text-center">{{ session('status') }}</p>
@endif
@endsection
