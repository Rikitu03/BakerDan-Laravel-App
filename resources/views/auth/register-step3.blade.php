@extends('layouts.auth')

@section('content')
@php
    $contactCountries = [
        'PH' => ['label' => 'Philippines', 'dialCode' => '63', 'nationalDigits' => 10, 'groups' => [3, 3, 4], 'localPrefix' => '0', 'placeholder' => '+63 939 165 4377', 'nationalPlaceholder' => '939 165 4377'],
        'US' => ['label' => 'United States', 'dialCode' => '1', 'nationalDigits' => 10, 'groups' => [3, 3, 4], 'placeholder' => '+1 415 555 2671', 'nationalPlaceholder' => '415 555 2671'],
        'CA' => ['label' => 'Canada', 'dialCode' => '1', 'nationalDigits' => 10, 'groups' => [3, 3, 4], 'placeholder' => '+1 416 555 2671', 'nationalPlaceholder' => '416 555 2671'],
        'GB' => ['label' => 'United Kingdom', 'dialCode' => '44', 'nationalDigits' => 10, 'groups' => [4, 3, 3], 'localPrefix' => '0', 'placeholder' => '+44 7911 123 456', 'nationalPlaceholder' => '7911 123 456'],
        'AU' => ['label' => 'Australia', 'dialCode' => '61', 'nationalDigits' => 9, 'groups' => [1, 4, 4], 'localPrefix' => '0', 'placeholder' => '+61 4 1234 5678', 'nationalPlaceholder' => '4 1234 5678'],
        'SG' => ['label' => 'Singapore', 'dialCode' => '65', 'nationalDigits' => 8, 'groups' => [4, 4], 'placeholder' => '+65 9123 4567', 'nationalPlaceholder' => '9123 4567'],
        'JP' => ['label' => 'Japan', 'dialCode' => '81', 'nationalDigits' => 10, 'groups' => [2, 4, 4], 'localPrefix' => '0', 'placeholder' => '+81 90 1234 5678', 'nationalPlaceholder' => '90 1234 5678'],
    ];
    $selectedContactCountry = array_key_exists(old('contact_country', 'PH'), $contactCountries) ? old('contact_country', 'PH') : 'PH';
@endphp

<h2 class="font-display text-2xl font-bold mb-6">Complete Profile</h2>
<p class="mb-6 text-sm" style="color: var(--ink-soft);">Fill in your details to finish registration.</p>

<form action="{{ route('register.step3') }}" method="POST" id="complete-profile-form" novalidate>
    @csrf
    <div class="grid gap-4 mb-6">
        <div>
            <label for="name" class="block text-sm font-medium mb-1">Full Name</label>
            <input
                type="text"
                name="name"
                id="name"
                required
                maxlength="100"
                autocomplete="name"
                pattern="[\p{L} ]{1,100}"
                value="{{ old('name') }}"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);"
                placeholder="John Doe"
            >
            <p class="mt-1 text-[11px]" style="color: var(--ink-soft);">Letters and spaces only. Maximum 100 characters.</p>
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="username" class="block text-sm font-medium mb-1">Username</label>
            <input
                type="text"
                name="username"
                id="username"
                required
                maxlength="50"
                autocomplete="username"
                pattern="[A-Za-z0-9]{1,50}"
                value="{{ old('username') }}"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);"
                placeholder="johndoe"
            >
            <p class="mt-1 text-[11px]" style="color: var(--ink-soft);">Letters and numbers only. Maximum 50 characters.</p>
            @error('username') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium mb-1">Password</label>
            <input
                type="password"
                name="password"
                id="password"
                required
                minlength="8"
                maxlength="255"
                autocomplete="new-password"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);"
            >
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="age" class="block text-sm font-medium mb-1">Age</label>
                <input
                    type="number"
                    name="age"
                    id="age"
                    required
                    min="1"
                    max="120"
                    value="{{ old('age') }}"
                    class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                    style="border-color: var(--line);"
                >
                @error('age') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium mb-1">Contact Number</label>
                <div class="relative" id="contact-country-picker">
                    <input
                        type="hidden"
                        name="contact_country"
                        id="contact_country"
                        value="{{ $selectedContactCountry }}"
                    >
                    <div
                        class="flex items-center overflow-hidden rounded-xl border bg-white/50 transition focus-within:bg-white focus-within:ring-2 focus-within:ring-[#26180f]/10"
                        style="border-color: var(--line);"
                    >
                        <button
                            type="button"
                            id="country-trigger"
                            class="flex shrink-0 items-center gap-1 px-3 py-3 text-sm font-semibold transition hover:bg-white/70"
                            style="color: var(--brand-deep);"
                            aria-label="Choose phone country code"
                            aria-expanded="false"
                            aria-controls="country-menu"
                        >
                            <span id="country-dial-label">+{{ $contactCountries[$selectedContactCountry]['dialCode'] }}</span>
                            <span class="text-[10px]" aria-hidden="true">v</span>
                        </button>
                        <span class="h-8 w-px shrink-0 bg-[rgba(38,24,15,0.14)]" aria-hidden="true"></span>
                        <input
                            type="tel"
                            name="contact"
                            id="contact"
                            required
                            inputmode="tel"
                            autocomplete="tel-national"
                            maxlength="20"
                            value="{{ old('contact') }}"
                            class="min-w-0 flex-1 bg-transparent px-3 py-3 outline-none"
                            placeholder="{{ $contactCountries[$selectedContactCountry]['nationalPlaceholder'] }}"
                        >
                    </div>
                    <div
                        id="country-menu"
                        class="absolute left-0 right-0 top-full z-20 mt-2 hidden overflow-hidden rounded-xl border bg-white shadow-xl"
                        style="border-color: var(--line);"
                    >
                        <div class="max-h-56 overflow-y-auto py-1">
                            @foreach ($contactCountries as $code => $country)
                                <button
                                    type="button"
                                    class="country-option flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm transition hover:bg-[#FBF4EA]"
                                    data-country-code="{{ $code }}"
                                >
                                    <span class="font-medium">{{ $country['label'] }}</span>
                                    <span class="shrink-0 font-semibold" style="color: var(--brand-deep);">+{{ $country['dialCode'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
                <p id="contact_hint" class="mt-1 text-[11px]" style="color: var(--ink-soft);"></p>
                @error('contact_country') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @error('contact') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                <label for="address" class="block text-sm font-medium">Address</label>
                <button
                    type="button"
                    id="use-current-address"
                    class="rounded-full border px-3 py-1.5 text-xs font-semibold transition hover:bg-white/60 disabled:cursor-wait disabled:opacity-60"
                    style="border-color: var(--line); color: var(--brand-deep);"
                >
                    Use current address
                </button>
            </div>
            <textarea
                name="address"
                id="address"
                required
                rows="3"
                maxlength="500"
                autocomplete="street-address"
                class="w-full rounded-xl border p-3 bg-white/50 focus:bg-white transition"
                style="border-color: var(--line);"
                placeholder="House number, street, barangay, city"
            >{{ old('address') }}</textarea>
            <p id="address-status" class="mt-1 text-[11px]" style="color: var(--ink-soft);">You can type manually or use your device location.</p>
            @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <button type="submit" class="w-full rounded-xl py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5"
        style="background: var(--ink);">
        Finish
    </button>
</form>

<script>
    (() => {
        const contactCountries = @json($contactCountries);
        const form = document.getElementById('complete-profile-form');
        const nameInput = document.getElementById('name');
        const usernameInput = document.getElementById('username');
        const countryInput = document.getElementById('contact_country');
        const countryTrigger = document.getElementById('country-trigger');
        const countryMenu = document.getElementById('country-menu');
        const countryDialLabel = document.getElementById('country-dial-label');
        const countryOptions = Array.from(document.querySelectorAll('.country-option'));
        const contactInput = document.getElementById('contact');
        const contactHint = document.getElementById('contact_hint');
        const addressInput = document.getElementById('address');
        const addressButton = document.getElementById('use-current-address');
        const addressStatus = document.getElementById('address-status');
        let activeCountryCode = contactCountries[countryInput?.value] ? countryInput.value : 'PH';

        const cleanSpaces = (value) => value.replace(/\s+/g, ' ').trimStart();
        const sanitizeName = (value) => Array.from(value)
            .filter((character) => /^[\p{L} ]$/u.test(character))
            .join('')
            .replace(/\s+/g, ' ')
            .slice(0, 100);
        const sanitizeUsername = (value) => value.replace(/[^a-z0-9]/gi, '').toLowerCase().slice(0, 50);

        const selectedCountry = () => contactCountries[activeCountryCode] || contactCountries.PH;

        const normalizeNationalDigits = (countryCode, value) => {
            const country = contactCountries[countryCode] || contactCountries.PH;
            let digits = String(value || '').replace(/\D/g, '');
            const dialCode = String(country.dialCode);
            const nationalLength = Number(country.nationalDigits);
            const internationalPrefix = `00${dialCode}`;

            if (digits.startsWith(internationalPrefix)) {
                digits = digits.slice(internationalPrefix.length);
            }

            if (digits.length > nationalLength && digits.startsWith(dialCode)) {
                digits = digits.slice(dialCode.length);
            }

            if (country.localPrefix && digits.length > nationalLength && digits.startsWith(country.localPrefix)) {
                digits = digits.slice(String(country.localPrefix).length);
            }

            return digits.slice(0, nationalLength);
        };

        const groupedNationalNumber = (country, digits) => {
            const groups = [];
            let offset = 0;

            country.groups.forEach((size) => {
                const part = digits.slice(offset, offset + Number(size));
                if (part) {
                    groups.push(part);
                }
                offset += Number(size);
            });

            if (offset < digits.length) {
                groups.push(digits.slice(offset));
            }

            return groups.join(' ');
        };

        const renderCountry = () => {
            const country = selectedCountry();

            if (countryInput) {
                countryInput.value = activeCountryCode;
            }

            if (countryDialLabel) {
                countryDialLabel.textContent = `+${country.dialCode}`;
            }

            if (contactInput) {
                contactInput.placeholder = country.nationalPlaceholder || country.placeholder;
            }

            if (contactHint) {
                contactHint.textContent = `Example: ${country.placeholder}`;
            }

            countryOptions.forEach((option) => {
                const isSelected = option.dataset.countryCode === activeCountryCode;
                option.classList.toggle('bg-[#FBF4EA]', isSelected);
                option.setAttribute('aria-selected', isSelected ? 'true' : 'false');
            });
        };

        const formatContact = () => {
            const country = selectedCountry();
            const digits = normalizeNationalDigits(activeCountryCode, contactInput.value);

            contactInput.value = groupedNationalNumber(country, digits);
        };

        const openCountryMenu = () => {
            countryMenu?.classList.remove('hidden');
            countryTrigger?.setAttribute('aria-expanded', 'true');
        };

        const closeCountryMenu = () => {
            countryMenu?.classList.add('hidden');
            countryTrigger?.setAttribute('aria-expanded', 'false');
        };

        const toggleCountryMenu = () => {
            if (countryMenu?.classList.contains('hidden')) {
                openCountryMenu();
                return;
            }

            closeCountryMenu();
        };

        nameInput?.addEventListener('input', () => {
            nameInput.value = sanitizeName(nameInput.value);
        });
        nameInput?.addEventListener('blur', () => {
            nameInput.value = cleanSpaces(nameInput.value).trim();
        });

        usernameInput?.addEventListener('input', () => {
            usernameInput.value = sanitizeUsername(usernameInput.value);
        });

        countryTrigger?.addEventListener('click', (event) => {
            event.stopPropagation();
            toggleCountryMenu();
        });

        countryOptions.forEach((option) => {
            option.addEventListener('click', () => {
                activeCountryCode = option.dataset.countryCode || 'PH';
                renderCountry();
                formatContact();
                closeCountryMenu();
                contactInput?.focus();
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('#contact-country-picker')) {
                closeCountryMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeCountryMenu();
            }
        });

        contactInput?.addEventListener('input', formatContact);
        contactInput?.addEventListener('blur', formatContact);

        addressInput?.addEventListener('blur', () => {
            addressInput.value = cleanSpaces(addressInput.value).replace(/[<>]/g, '').trim().slice(0, 500);
        });

        addressButton?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                addressStatus.textContent = 'Location is not available on this device.';
                return;
            }

            addressButton.disabled = true;
            addressStatus.textContent = 'Getting your current location...';

            navigator.geolocation.getCurrentPosition(async (position) => {
                const latitude = Number(position.coords.latitude).toFixed(6);
                const longitude = Number(position.coords.longitude).toFixed(6);
                const fallbackAddress = `Current location: ${latitude}, ${longitude} | Map: https://www.google.com/maps?q=${latitude},${longitude}`;

                try {
                    addressStatus.textContent = 'Looking up your address...';
                    const controller = new AbortController();
                    const timeout = window.setTimeout(() => controller.abort(), 8000);
                    const url = new URL('https://nominatim.openstreetmap.org/reverse');
                    url.searchParams.set('format', 'jsonv2');
                    url.searchParams.set('lat', latitude);
                    url.searchParams.set('lon', longitude);

                    const response = await fetch(url.toString(), {
                        headers: { Accept: 'application/json' },
                        signal: controller.signal,
                    });
                    window.clearTimeout(timeout);

                    if (!response.ok) {
                        throw new Error('Address lookup failed');
                    }

                    const payload = await response.json();
                    addressInput.value = String(payload.display_name || fallbackAddress).slice(0, 500);
                    addressStatus.textContent = 'Address filled from your current location.';
                } catch (error) {
                    addressInput.value = fallbackAddress.slice(0, 500);
                    addressStatus.textContent = 'Exact address lookup failed, so map coordinates were added.';
                } finally {
                    addressButton.disabled = false;
                }
            }, () => {
                addressStatus.textContent = 'Location permission was denied or unavailable.';
                addressButton.disabled = false;
            }, {
                enableHighAccuracy: true,
                maximumAge: 60000,
                timeout: 10000,
            });
        });

        form?.addEventListener('submit', () => {
            if (nameInput) {
                nameInput.value = cleanSpaces(nameInput.value).trim();
            }
            if (usernameInput) {
                usernameInput.value = sanitizeUsername(usernameInput.value);
            }
            if (contactInput) {
                formatContact();
            }
            if (addressInput) {
                addressInput.value = cleanSpaces(addressInput.value).replace(/[<>]/g, '').trim().slice(0, 500);
            }
        });

        renderCountry();
        formatContact();
    })();
</script>
@endsection
