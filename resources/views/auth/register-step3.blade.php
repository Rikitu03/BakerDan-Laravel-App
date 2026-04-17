@extends('layouts.auth')

@section('content')
<h2 class="font-display text-2xl font-bold mb-6">Complete Profile</h2>
<p class="mb-6 text-sm" style="color: var(--ink-soft);">Fill in your details to finish registration.</p>

<form action="{{ route('register.step3') }}" method="POST">
    @csrf
    <div class="grid gap-4 mb-6">
        <div>
            <label for="name" class="block text-sm font-medium mb-1">Full Name</label>
            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);" placeholder="John Doe">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="username" class="block text-sm font-medium mb-1">Username</label>
            <input type="text" name="username" id="username" required value="{{ old('username') }}"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);" placeholder="johndoe">
            @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" id="password" required
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="age" class="block text-sm font-medium mb-1">Age</label>
                <input type="number" name="age" id="age" required value="{{ old('age') }}"
                    class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                    style="border-color: var(--line);">
                @error('age') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium mb-1">Contact Number</label>
                <input type="text" name="contact" id="contact" required value="{{ old('contact') }}"
                    class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                    style="border-color: var(--line);" placeholder="09123456789">
                @error('contact') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="address" class="block text-sm font-medium mb-1">Address</label>
            <textarea name="address" id="address" required rows="2"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);">{{ old('address') }}</textarea>
            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
        style="background: var(--ink);">
        Finish
    </button>
</form>
@endsection
