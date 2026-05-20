@extends('layouts.auth')

@section('content')
<h2 class="font-display text-2xl font-bold mb-6">Login</h2>

@php
    $authRedirect = old('redirect', $redirectTo ?? null);
@endphp

<form action="{{ route('login') }}" method="POST">
    @csrf
    @if ($authRedirect)
        <input type="hidden" name="redirect" value="{{ $authRedirect }}">
    @endif
    <div class="space-y-4 mb-6">
        <div>
            <label for="username" class="block text-sm font-medium mb-1">Username</label>
            <input type="text" name="username" id="username" required value="{{ old('username') }}"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);" placeholder="Enter your username">
            @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex justify-between items-center mb-1">
                <label for="password" class="block text-sm font-medium">Password</label>
                <a href="{{ route('password.request') }}" class="text-xs font-medium hover:underline" style="color: var(--brand-deep);">Forgot Password?</a>
            </div>
            <input type="password" name="password" id="password" required
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 mb-4"
        style="background: var(--ink);">
        Login
    </button>
</form>

<form action="{{ route('login.guest') }}" method="POST" class="mt-3">
    @csrf
    @if ($authRedirect)
        <input type="hidden" name="redirect" value="{{ $authRedirect }}">
    @endif
    <button type="submit" class="w-full rounded-xl border py-3.5 text-sm font-semibold transition hover:-translate-y-0.5 hover:bg-white/70"
        style="border-color: var(--line); color: var(--brand-deep);">
        Continue as guest
    </button>
</form>

<p class="mt-4 text-center text-sm" style="color: var(--ink-soft);">
    Don't have an account?
    <a href="{{ route('register.step1', $authRedirect ? ['redirect' => $authRedirect] : []) }}" class="font-bold hover:underline" style="color: var(--brand-deep);">Sign Up</a>
</p>
@endsection
