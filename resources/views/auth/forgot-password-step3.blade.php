@extends('layouts.auth')

@section('content')
<h2 class="font-display text-2xl font-bold mb-6">New Password</h2>
<p class="mb-6 text-sm" style="color: var(--ink-soft);">Create a new strong password for your account.</p>

<form action="{{ route('password.reset') }}" method="POST">
    @csrf
    <div class="space-y-4 mb-6">
        <div>
            <label for="password" class="block text-sm font-medium mb-1">New Password</label>
            <input type="password" name="password" id="password" required
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);">
        </div>
    </div>

    <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
        style="background: var(--ink);">
        Update Password
    </button>
</form>
@endsection
