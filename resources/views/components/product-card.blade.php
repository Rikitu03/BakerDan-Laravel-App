@props([
    'title',
    'description',
    'price',
    'imagePath' => null,
    'salesCount' => null,
])

@php
    $salesLabel = is_null($salesCount)
        ? 'Fresh bake'
        : 'Sold ' . number_format((int) $salesCount) . ' pcs';
@endphp

<article class="group overflow-hidden rounded-[2rem] border transition duration-300 hover:-translate-y-1.5 hover:shadow-[0_28px_70px_-36px_rgba(38,24,15,0.48)]" style="border-color: rgba(38, 24, 15, 0.1); background: rgba(255, 252, 247, 0.72);">
    <div class="relative overflow-hidden">
        <div class="h-72 overflow-hidden">
            @if ($imagePath && (str_starts_with($imagePath, 'http') || file_exists(public_path($imagePath))))
                <img
                    src="{{ asset($imagePath) }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                >
            @else
                <div class="relative h-full w-full overflow-hidden" style="background: linear-gradient(155deg, #d7b08a, #c37b44 58%, #8f4723);">
                    <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full" style="background: rgba(255,255,255,0.18);"></div>
                    <div class="absolute bottom-5 left-5 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em]" style="border-color: rgba(255,255,255,0.18); background: rgba(38,24,15,0.22); color: white;">
                        Best seller
                    </div>
                </div>
            @endif
        </div>

        <div class="absolute inset-x-4 top-4 flex items-center justify-between">
        </div>
    </div>

    <div class="space-y-4 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: #8f4723;">Signature pick</p>
                <h3 class="font-display mt-2 text-2xl font-bold" style="color: #26180f;">{{ $title }}</h3>
            </div>
            <p class="font-display text-2xl font-bold" style="color: #26180f;">&#8369; {{ number_format((float) $price, 2) }}</p>
        </div>

        <p class="text-sm leading-relaxed" style="color: #6d5949;">{{ $description }}</p>
    </div>
</article>