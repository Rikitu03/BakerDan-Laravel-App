@props([
    'quote',
    'name',
    'role' => 'Client',
])

<article class="rounded-[2rem] border p-6 transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_55px_-36px_rgba(38,24,15,0.45)]" style="border-color: rgba(38, 24, 15, 0.1); background: linear-gradient(180deg, rgba(255,255,255,0.75), rgba(243,227,198,0.55));">
    <div class="mb-5 flex gap-1" style="color: #c86f38;" aria-label="5 star rating">
        @for ($i = 0; $i < 5; $i++)
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="m12 2.7 2.6 5.2 5.7.8-4.1 4 1 5.7L12 15.8 6.8 18.4l1-5.7-4.1-4 5.7-.8L12 2.7Z" />
            </svg>
        @endfor
    </div>

    <p class="min-h-32 text-base leading-relaxed" style="color: #3f2d22;">{{ $quote }}</p>

    <div class="mt-6 flex items-center gap-3 border-t pt-5" style="border-color: rgba(38, 24, 15, 0.08);">
        <div class="grid h-12 w-12 place-items-center rounded-full text-sm font-semibold text-white" style="background: linear-gradient(135deg, #8f4723, #c86f38);">
            {{ strtoupper(substr($name, 0, 1)) }}
        </div>
        <div>
            <p class="font-semibold" style="color: #26180f;">{{ $name }}</p>
            <p class="text-xs uppercase tracking-[0.16em]" style="color: #8b705e;">{{ $role }}</p>
        </div>
    </div>
</article>
