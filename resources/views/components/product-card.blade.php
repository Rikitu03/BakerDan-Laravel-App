@props([
    'title',
    'description',
    'price',
    'imagePath' => null,
])

<article class="group overflow-hidden rounded-[2rem] border transition duration-300 hover:-translate-y-1.5 hover:shadow-[0_28px_70px_-36px_rgba(38,24,15,0.48)]" style="border-color: rgba(38, 24, 15, 0.1); background: rgba(255, 252, 247, 0.72);">
    <div class="relative overflow-hidden">
        <div class="h-72 overflow-hidden">
            @if ($imagePath && file_exists(public_path($imagePath)))
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
            <span class="rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em]" style="border-color: rgba(255,255,255,0.18); background: rgba(255,255,255,0.72); color: #8f4723;">Fresh bake</span>
            <div class="flex gap-2">
                @auth
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-full border transition hover:-translate-y-0.5" style="border-color: rgba(255,255,255,0.18); background: rgba(255,255,255,0.72);" aria-label="Add to cart" data-add-to-cart>
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="9" cy="20" r="1.25"></circle>
                            <circle cx="18" cy="20" r="1.25"></circle>
                            <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L22 7H7"></path>
                        </svg>
                    </button>
                @else
                    <button type="button" class="grid h-10 w-10 place-items-center rounded-full border transition hover:-translate-y-0.5 cursor-help" style="border-color: rgba(255,255,255,0.18); background: rgba(255,255,255,0.72); opacity: 0.6;" aria-label="Sign in to add to cart" title="Sign in to add to cart" data-requires-auth="true">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="9" cy="20" r="1.25"></circle>
                            <circle cx="18" cy="20" r="1.25"></circle>
                            <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L22 7H7"></path>
                        </svg>
                    </button>
                @endauth
                <button type="button" class="grid h-10 w-10 place-items-center rounded-full text-white transition hover:-translate-y-0.5" style="background: rgba(38, 24, 15, 0.78);" aria-label="Add to favorites">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"></path>
                    </svg>
                </button>
            </div>
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