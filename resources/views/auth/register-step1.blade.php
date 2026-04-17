@extends('layouts.auth')

@section('content')
<h2 class="font-display text-2xl font-bold mb-6">Sign Up</h2>
<p class="mb-6 text-sm" style="color: var(--ink-soft);">Enter your email to receive a verification code.</p>

<form action="{{ route('register.step1') }}" method="POST">
    @csrf
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" id="email" required
            class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
            style="border-color: var(--line);" placeholder="your@email.com">
        @error('email')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
    <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
        style="background: var(--ink);">
        Continue
    </button>
</form>
@endsection
