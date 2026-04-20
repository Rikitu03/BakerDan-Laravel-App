@extends('layouts.auth')

@section('content')
<h2 class="font-display text-2xl font-bold mb-6">Verify Code</h2>
<p class="mb-6 text-sm" style="color: var(--ink-soft);">We sent a reset code to <strong>{{ session('forgot_password_email') }}</strong>.</p>

<form action="{{ route('password.otp') }}" method="POST">
    @csrf
    <div class="mb-6">
        <label for="otp" class="block text-sm font-medium mb-1">Enter Code</label>
        <input type="text" name="otp" id="otp" required
            class="w-full rounded-xl border p-3 text-center text-2xl tracking-[0.5em] font-bold bg-white/50 focus:bg-white transition"
            style="border-color: var(--line);" placeholder="000000" maxlength="6">
        @error('otp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
        style="background: var(--ink);">
        Verify
    </button>
</form>
@endsection
