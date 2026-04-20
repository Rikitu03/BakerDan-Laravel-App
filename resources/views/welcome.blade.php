<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>BakerDan | Bread and Pastries</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800|outfit:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --paper: #fbf4e8;
            --surface: rgba(255, 252, 247, 0.74);
            --ink: #26180f;
            --ink-soft: #6d5949;
            --line: rgba(38, 24, 15, 0.1);
            --brand: #c86f38;
            --brand-deep: #8f4723;
            --olive: #626b3c;
            --shadow: 0 28px 80px -42px rgba(38, 24, 15, 0.45);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.72), transparent 24%),
                radial-gradient(circle at 85% 18%, rgba(200, 111, 56, 0.12), transparent 18%),
                linear-gradient(180deg, #f1ece5 0, #fbf4e8 88px, #fbf4e8 100%);
            color: var(--ink);
        }

        .font-display { font-family: 'Sora', sans-serif; }
        .font-body { font-family: 'Outfit', sans-serif; }

        .page-shell {
            position: relative;
            overflow: hidden;
        }

        .page-shell::before,
        .page-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .page-shell::before {
            width: 32rem;
            height: 32rem;
            top: 8rem;
            right: -14rem;
            background: radial-gradient(circle, rgba(200, 111, 56, 0.14), transparent 65%);
        }

        .page-shell::after {
            width: 24rem;
            height: 24rem;
            left: -10rem;
            bottom: 12rem;
            background: radial-gradient(circle, rgba(143, 71, 35, 0.08), transparent 70%);
        }

        .entrance {
            animation: rise 700ms ease-out both;
        }

        .entrance-delay-1 { animation-delay: 100ms; }
        .entrance-delay-2 { animation-delay: 200ms; }
        .entrance-delay-3 { animation-delay: 300ms; }
        .entrance-delay-4 { animation-delay: 400ms; }

        .mesh-card {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.9), transparent 58%),
                linear-gradient(160deg, rgba(255, 250, 241, 0.92), rgba(243, 227, 198, 0.72));
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .soft-card {
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.58);
            box-shadow: 0 18px 40px -34px rgba(38, 24, 15, 0.5);
        }

        .image-card {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
        }

        .image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 10%, rgba(15, 20, 30, 0.14) 100%);
            pointer-events: none;
        }

        .hero-grid {
            display: grid;
            gap: 1rem;
        }

        .hero-stack {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .photo-fallback {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.65), transparent 32%),
                linear-gradient(155deg, #d8b58f, #b67646 62%, #8f4723);
        }

        .photo-fallback::before,
        .photo-fallback::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255, 248, 236, 0.22);
        }

        .photo-fallback::before {
            width: 9rem;
            height: 9rem;
            right: -1.5rem;
            top: -1.5rem;
        }

        .photo-fallback::after {
            width: 6rem;
            height: 6rem;
            left: 1.25rem;
            bottom: 1rem;
        }

        .section-ring {
            position: absolute;
            right: -12rem;
            top: 50%;
            width: 24rem;
            height: 24rem;
            transform: translateY(-50%);
            border-radius: 999px;
            border: 3rem solid rgba(38, 24, 15, 0.07);
        }

        @keyframes rise {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body class="font-body antialiased">
    @php
        $featured = [
            'name' => 'Korean Garlic Cream Cheese Bun',
            'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
            'price' => 395.00,
            'imagePath' => 'images/bakerdan/Creme_Cheese_Garlic.png',
        ];

        $bestSellers = [
            [
                'title' => 'Wallnut Brownies ',
                'description' => 'Our signature walnut brownies, rich and fudgy with a deep chocolate flavor, packed with crunchy walnuts and a perfectly moist, chewy texture.',
                'price' => 320.00,
                'imagePath' => 'images/bakerdan/Brownies.png',
            ],
            [
                'title' => 'Creme Puffs',
                'description' => 'A light and airy choux pastry filled with smooth, luscious cream and finished with a delicate golden crust.',
                'price' => 310.00,
                'imagePath' => 'images/bakerdan/Creme_Puffs.png',
            ],
            [
                'title' => 'Korean Garlic Cream Cheese Bun',
                'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
                'price' => 395.00,
                'imagePath' => 'images/bakerdan/Creme_Cheese_Garlic.png',
            ],
        ];

        $miniPicks = [
            ['label' => 'Sourdough', 'tone' => 'from-[#e8d8ba] to-[#d7b28a]'],
            ['label' => 'Pastries', 'tone' => 'from-[#f0d5be] to-[#c9875c]'],
            ['label' => 'Cakes', 'tone' => 'from-[#ead9ce] to-[#c8a58f]'],
            ['label' => 'Tarts', 'tone' => 'from-[#f3dfc0] to-[#dab98d]'],
            ['label' => 'Seasonal', 'tone' => 'from-[#d9ddc2] to-[#a9b07b]'],
        ];

        $galleryShots = [
            ['path' => 'images/bakerdan/Creme_Puffs.png', 'title' => 'Cream puffs'],
            ['path' => 'images/bakerdan/Customized_Cookies.png', 'title' => 'Customized cookies'],
            ['path' => 'images/bakerdan/Bread.png', 'title' => 'Fresh bread'],
            ['path' => 'images/bakerdan/Brownies.png', 'title' => 'Brownies'],
        ];

        $testimonials = [
            [
                'quote' => 'Super sarap promise. Recommended for anyone looking for pastries that feel premium but still generous.',
                'name' => 'Elena Vasquez',
                'role' => 'Retail customer',
            ],
            [
                'quote' => 'Cravings satisfied every time. Fresh products, clean presentation, and mabilis kausap for orders.',
                'name' => 'Margaux Erutuoc',
                'role' => 'Repeat client',
            ],
            [
                'quote' => 'Best baked bread in town. Consistent enough for cafe supply and events, which is rare.',
                'name' => 'Belinda Layug',
                'role' => 'Business client',
            ],
        ];

        $highlights = [
            ['value' => '72 hr', 'label' => 'slow fermentation'],
            ['value' => '150+', 'label' => 'bulk orders fulfilled'],
            ['value' => 'QASIA', 'label' => 'award-winning recipes'],
        ];

        $orderSteps = [
            ['title' => 'Browse the menu', 'text' => 'Review breads, pastries, cakes, and seasonal products.'],
            ['title' => 'Send your order', 'text' => 'Message BakerDan with your selected items and target date.'],
            ['title' => 'Confirm details', 'text' => 'Finalize quantity, pickup or delivery, and payment arrangement.'],
            ['title' => 'Receive fresh bakes', 'text' => 'Your order is prepared and packed for pickup, retail, or events.'],
        ];

        $assistantPrompts = [
            'Recommend pastries for office meetings',
            'Suggest a bread bundle for a cafe',
            'Help me choose items for a birthday order',
        ];
    @endphp

    <div class="page-shell min-h-screen">
        <header class="mx-auto max-w-7xl px-6 pb-6 pt-8 md:px-10">
            <div class="mesh-card rounded-full px-5 py-3">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <a href="#top" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo/BAKERDAN LOGO.jpg') }}" alt="BakerDan logo" class="h-10 w-10 rounded-full object-cover">
                        <span class="font-display text-lg font-extrabold tracking-[0.18em] md:text-xl">BAKERDAN</span>
                    </a>
                    <nav class="hidden items-center gap-5 text-sm font-medium md:flex" style="color: var(--ink-soft);">
                        <a href="#about" class="transition hover:opacity-70">About</a>
                        <a href="#menu" class="transition hover:opacity-70">Menu</a>
                        <a href="#how-to-order" class="transition hover:opacity-70">How to Order</a>
                        <a href="#assistant" class="transition hover:opacity-70">AI Assistant</a>
                    </nav>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="grid h-10 w-10 place-items-center rounded-full border transition hover:bg-white/60" style="border-color: var(--line);" aria-label="Account">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 21a8 8 0 0 0-16 0"></path>
                                <circle cx="12" cy="8" r="4"></circle>
                            </svg>
                        </a>
                        <a href="#order-now" class="rounded-full px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5" style="background: var(--ink);">Order now</a>
                    </div>
                </div>
            </div>
        </header>

        <main id="top" class="mx-auto max-w-7xl px-6 pb-20 md:px-10">
            <section class="grid items-center gap-10 pb-20 pt-6 lg:grid-cols-[1.05fr_0.95fr] lg:pt-10">
                <div class="max-w-2xl">
                    <p class="entrance inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.25em]" style="border-color: var(--line); color: var(--ink-soft); background: rgba(255,255,255,0.4);">
                        <span class="h-2 w-2 rounded-full" style="background: var(--brand);"></span>
                        Wholesale bakery
                    </p>

                    <h1 class="font-display entrance entrance-delay-1 mt-6 text-5xl font-extrabold leading-[0.92] sm:text-7xl xl:text-[6.25rem]">
                        BREAD AND
                        <span class="block" style="color: var(--brand-deep);">PASTRIES</span>
                    </h1>

                    <p class="entrance entrance-delay-2 mt-6 max-w-xl text-lg leading-relaxed md:text-xl" style="color: var(--ink-soft);">
                        We also serve as a reliable bread and pastry provider for coffee shops, catering services, and corporate events, delivering consistent quality and freshness you can trust.
                    </p>

                    <div class="entrance entrance-delay-3 mt-8 flex flex-wrap items-center gap-4">
                        <a href="#menu" class="rounded-full px-7 py-3.5 text-sm font-semibold uppercase tracking-[0.14em] text-white transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, var(--brand-deep), var(--brand));">See full menu</a>
                        <a href="#how-to-order" class="rounded-full border px-7 py-3.5 text-sm font-semibold uppercase tracking-[0.14em] transition hover:bg-white/60" style="border-color: rgba(38, 24, 15, 0.18);">How to order</a>
                    </div>

                    <div class="entrance entrance-delay-4 mt-10 grid gap-3 sm:grid-cols-3">
                        @foreach ($highlights as $item)
                            <div class="soft-card rounded-[1.5rem] px-4 py-4">
                                <p class="font-display text-2xl font-extrabold">{{ $item['value'] }}</p>
                                <p class="mt-1 text-sm" style="color: var(--ink-soft);">{{ $item['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="relative lg:pl-6">
                    <div class="absolute -left-4 top-12 hidden h-32 w-32 rounded-[2rem] lg:block" style="background: rgba(98, 107, 60, 0.12);"></div>
                    <div class="absolute right-0 top-0 hidden h-24 w-24 rounded-full lg:block" style="background: rgba(200, 111, 56, 0.14);"></div>

                    <div class="hero-grid relative">
                        <div class="mesh-card entrance entrance-delay-2 rounded-[2.25rem] p-4 sm:p-5">
                            <div class="image-card min-h-[24rem]">
                                <img src="{{ asset('images/bakerdan/Creme_Cheese_Garlic.png') }}" alt="Featured bread and pastries">
                                <div class="absolute left-4 top-4 rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white" style="border-color: rgba(255,255,255,0.22); background: rgba(15, 20, 30, 0.38);">
                                    House favorite
                                </div>
                                <div class="absolute inset-x-4 bottom-4 rounded-[1.25rem] border p-4 backdrop-blur" style="border-color: rgba(255,255,255,0.18); background: rgba(255, 250, 241, 0.92);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em]" style="color: var(--brand-deep);">Featured bake</p>
                                    <p class="font-display mt-1 text-2xl font-bold">Korean Garlic Cream Cheese Bun</p>
                                    <p class="mt-1 text-sm leading-relaxed" style="color: var(--ink-soft);">Soft enriched dough with a rich cream cheese filling and glossy golden top.</p>
                                </div>
                            </div>
                        </div>

                       
                    </div>
                </div>
            </section>

            <section id="about" class="pb-20">
                <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="mesh-card rounded-[2.25rem] p-8">
                        <p class="text-sm font-medium" style="color: var(--ink-soft);">About BakerDan</p>
                        <h2 class="font-display mt-3 text-4xl font-extrabold sm:text-5xl">Bakerdan Bread and Pastries</h2>
                        <p class="mt-4 max-w-2xl text-lg leading-relaxed" style="color: var(--ink-soft);">Bakerdan Bread and Pastries is a Filipino bakery dedicated to bringing fresh, affordable, and delicious bread and pastries to everyday communities. From classic favorites to sweet and savory treats, we take pride in baking products that are made with care and enjoyed by families, students, and busy individuals alike.</p>
                        <p class="mt-4 max-w-2xl text-lg leading-relaxed" style="color: var(--ink-soft);">Our goal is simple: to make quality baked goods accessible to everyone. Whether you're grabbing a quick snack or celebrating a special moment, Bakerdan is here to serve warmth and goodness in every bite.</p>
                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="soft-card rounded-[1.5rem] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em]" style="color: var(--ink-soft);">Bread</p>
                                <p class="mt-2 text-xl font-semibold">Daily baked essentials</p>
                            </div>
                            <div class="soft-card rounded-[1.5rem] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em]" style="color: var(--ink-soft);">Pastries</p>
                                <p class="mt-2 text-xl font-semibold">Cafe and merienda picks</p>
                            </div>
                            <div class="soft-card rounded-[1.5rem] p-5">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em]" style="color: var(--ink-soft);">Events</p>
                                <p class="mt-2 text-xl font-semibold">Bulk and custom orders</p>
                            </div>
                        </div>
                    </div>

                    <div id="account" class="rounded-[2.25rem] border p-6" style="border-color: var(--line); background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(243,227,198,0.75)); box-shadow: var(--shadow);">
                        <div class="flex items-center gap-3">
                            <div class="grid h-12 w-12 place-items-center rounded-full text-white" style="background: linear-gradient(135deg, var(--brand-deep), var(--brand));">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="8" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium" style="color: var(--ink-soft);">Account</p>
                                <h3 class="font-display text-2xl font-bold">Personal order space</h3>
                            </div>
                        </div>
                        <p class="mt-4 text-base leading-relaxed" style="color: var(--ink-soft);">The account path from your flow now has a destination for order history, saved favorites, and quick reorders for regular buyers.</p>
                        <div class="mt-6 space-y-3">
                            <div class="soft-card rounded-2xl px-4 py-3">Track recent orders</div>
                            <div class="soft-card rounded-2xl px-4 py-3">Save favorite items</div>
                            <div class="soft-card rounded-2xl px-4 py-3">Repeat business orders</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="featured" class="relative overflow-hidden rounded-[2.5rem] border px-6 py-8 sm:px-8 md:px-10 md:py-10" style="border-color: var(--line); background: linear-gradient(135deg, rgba(255,255,255,0.58), rgba(243,227,198,0.7));">
                <div class="section-ring hidden lg:block"></div>
                <div class="relative grid gap-8 lg:grid-cols-[1fr_1.02fr] lg:items-center">
                    <div class="max-w-xl">
                        <p class="text-sm font-medium" style="color: var(--ink-soft);">Checkout today's choice</p>
                        <h2 class="font-display mt-4 text-4xl font-extrabold sm:text-5xl">{{ $featured['name'] }}</h2>
                        <p class="mt-4 text-xl leading-relaxed" style="color: var(--ink-soft);">{{ $featured['description'] }}</p>

                        <div class="mt-8 flex items-center gap-5">
                            <p class="font-display text-4xl font-bold">&#8369; {{ number_format($featured['price'], 2) }}</p>
                            <div class="flex gap-3">
                                <button type="button" class="grid h-12 w-12 place-items-center rounded-full border transition hover:-translate-y-0.5" style="border-color: var(--line); background: rgba(255,255,255,0.65);" aria-label="Save item">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10Z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="grid h-12 w-12 place-items-center rounded-full text-white transition hover:-translate-y-0.5" style="background: var(--ink);" aria-label="Add to cart">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="9" cy="20" r="1.25"></circle>
                                        <circle cx="18" cy="20" r="1.25"></circle>
                                        <path d="M3 4h2l2.2 10.2a2 2 0 0 0 2 1.6h8.5a2 2 0 0 0 1.9-1.4L22 7H7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="mt-12">
                            <p class="mb-4 text-sm font-medium" style="color: var(--ink-soft);">Take your pick</p>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                                @foreach ($miniPicks as $pick)
                                    <div class="rounded-[1.25rem] border p-3 text-center text-sm font-semibold" style="border-color: var(--line); background: rgba(255,255,255,0.52);">
                                        <div class="mb-3 h-16 rounded-2xl bg-gradient-to-br {{ $pick['tone'] }}"></div>
                                        {{ $pick['label'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="lg:justify-self-end">
                        <div class="relative mx-auto max-w-2xl">
                            <div class="absolute -left-5 top-10 hidden h-44 w-44 rounded-[2rem] md:block" style="background: rgba(200, 111, 56, 0.12);"></div>
                            <div class="absolute right-2 top-0 hidden h-36 w-36 rounded-[2rem] md:block" style="background: rgba(38, 24, 15, 0.08);"></div>
                            <div class="absolute bottom-4 right-12 hidden h-56 w-56 rounded-[2rem] md:block" style="background: rgba(98, 107, 60, 0.1);"></div>

                            <div class="relative ml-auto h-[26rem] max-w-xl rounded-[2.25rem] border p-4 shadow-[0_28px_70px_-35px_rgba(38,24,15,0.45)]" style="border-color: rgba(38, 24, 15, 0.12); background: rgba(255,255,255,0.6);">
                                <div class="h-full overflow-hidden rounded-[1.75rem]">
                                    @if (file_exists(public_path($featured['imagePath'])))
                                        <img src="{{ asset($featured['imagePath']) }}" alt="{{ $featured['name'] }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="photo-fallback grid h-full place-items-center">
                                            <div class="rounded-[1.75rem] border px-5 py-4 text-center" style="border-color: rgba(255,255,255,0.18); background: rgba(38, 24, 15, 0.36); color: white;">
                                                <p class="text-xs font-semibold uppercase tracking-[0.2em]">Featured bake</p>
                                                <p class="font-display mt-2 text-3xl font-bold">{{ $featured['name'] }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="menu" class="py-20">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="font-display mt-2 text-4xl font-extrabold sm:text-5xl">Menu</h2>
                    </div>
                    <a href="#order-now" class="rounded-full border px-5 py-2.5 text-sm font-semibold transition hover:bg-white/60" style="border-color: var(--line);">Request catalog</a>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach ($bestSellers as $item)
                        <x-product-card
                            :title="$item['title']"
                            :description="$item['description']"
                            :price="$item['price']"
                            :image-path="$item['imagePath']"
                        />
                    @endforeach
                </div>
            </section>

            <section id="how-to-order" class="pb-20">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="font-display mt-2 text-4xl font-extrabold sm:text-5xl">How to order</h2>
                    </div>
                </div>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    @foreach ($orderSteps as $index => $step)
                        <article class="rounded-[2rem] border p-6" style="border-color: var(--line); background: rgba(255,255,255,0.58); box-shadow: var(--shadow);">
                            <div class="grid h-12 w-12 place-items-center rounded-full text-lg font-bold text-white" style="background: linear-gradient(135deg, var(--brand-deep), var(--brand));">{{ $index + 1 }}</div>
                            <h3 class="font-display mt-5 text-2xl font-bold">{{ $step['title'] }}</h3>
                            <p class="mt-3 text-sm leading-relaxed" style="color: var(--ink-soft);">{{ $step['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="gallery" class="pb-20">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium" style="color: var(--ink-soft);">From our kitchen</p>
                        <h2 class="font-display mt-2 text-4xl font-extrabold sm:text-5xl">Our simple gallery</h2>
                    </div>
                    <p class="max-w-md text-sm leading-relaxed" style="color: var(--ink-soft);">Explore our freshly baked breads and pastries—crafted daily with care, using quality ingredients to bring you comforting, delicious treats.</p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($galleryShots as $shot)
                        <article class="overflow-hidden rounded-[2rem] border transition duration-300 hover:-translate-y-1" style="border-color: var(--line); background: rgba(255,255,255,0.5); box-shadow: var(--shadow);">
                            <div class="aspect-[4/3]">
                                @if (file_exists(public_path($shot['path'])))
                                    <img src="{{ asset($shot['path']) }}" alt="{{ $shot['title'] }}" class="h-full w-full object-cover">
                                @else
                                    <div class="photo-fallback h-full w-full"></div>
                                @endif
                            </div>

                            <div class="border-t px-4 py-4" style="border-color: var(--line); background: rgba(255,255,255,0.76);">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--ink-soft);">BakerDan</p>
                                <p class="mt-1 font-display text-lg font-bold">{{ $shot['title'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="assistant" class="pb-20">
                <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                    <div class="mesh-card rounded-[2.25rem] p-8">
                        <p class="text-sm font-medium" style="color: var(--ink-soft);">AI Assistant via LLM</p>
                        <h2 class="font-display mt-3 text-4xl font-extrabold sm:text-5xl">Add a guided assistant entry point</h2>
                        <p class="mt-4 text-lg leading-relaxed" style="color: var(--ink-soft);">This section introduces an AI assistant that can help users choose products, estimate bundles, and move them toward the right order path.</p>
                        <div class="mt-8 space-y-3">
                            @foreach ($assistantPrompts as $prompt)
                                <div class="rounded-2xl border px-4 py-3 text-sm font-medium" style="border-color: var(--line); background: rgba(255,255,255,0.52);">{{ $prompt }}</div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[2.25rem] border p-6" style="border-color: var(--line); background: linear-gradient(180deg, rgba(255,255,255,0.86), rgba(243,227,198,0.7)); box-shadow: var(--shadow);">
                        <div class="flex items-center justify-between border-b pb-4" style="border-color: var(--line);">
                            <div>
                                <p class="text-sm font-medium" style="color: var(--ink-soft);">BakerDan Assistant</p>
                                <p class="font-display text-2xl font-bold">Smart help panel</p>
                            </div>
                            <div class="grid h-11 w-11 place-items-center rounded-full text-white" style="background: var(--ink);">AI</div>
                        </div>
                        <div class="mt-5 space-y-4">
                            <div class="max-w-[85%] rounded-[1.5rem] px-4 py-3 text-sm" style="background: rgba(38,24,15,0.08); color: var(--ink);">Hi! I can suggest pastries for cafes, parties, and bulk business orders.</div>
                            <div class="ml-auto max-w-[85%] rounded-[1.5rem] px-4 py-3 text-sm text-white" style="background: linear-gradient(135deg, var(--brand-deep), var(--brand));">Help me choose a package for 30 people.</div>
                            <div class="max-w-[85%] rounded-[1.5rem] px-4 py-3 text-sm" style="background: rgba(38,24,15,0.08); color: var(--ink);">I recommend a mixed pastry tray, two loaf options, and a custom cake inquiry.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="reviews" class="pb-20">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm font-medium" style="color: var(--ink-soft);">What people say</p>
                    <h2 class="font-display mt-2 text-4xl font-extrabold sm:text-5xl">Read words from real customers</h2>
                </div>

                <div class="mt-10 grid gap-6 lg:grid-cols-3">
                    @foreach ($testimonials as $testimonial)
                        <x-testimonial-card
                            :quote="$testimonial['quote']"
                            :name="$testimonial['name']"
                            :role="$testimonial['role']"
                        />
                    @endforeach
                </div>
            </section>

            <section id="order-now" class="overflow-hidden rounded-[2.5rem] border px-6 py-8 sm:px-8 md:px-10 md:py-10" style="border-color: var(--line); background: linear-gradient(135deg, #2f1d12, #5d331d 55%, #8f4723); color: white;">
                <div class="grid gap-8 lg:grid-cols-[1fr_0.9fr] lg:items-center">
                    <div>
                        <p class="text-sm font-medium uppercase tracking-[0.18em]" style="color: rgba(255,255,255,0.72);">Ready for your next order?</p>
                        <h2 class="font-display mt-3 text-4xl font-extrabold sm:text-5xl">Let's bake for your business</h2>
                        <p class="mt-4 max-w-xl text-base leading-relaxed" style="color: rgba(255,255,255,0.76);"></p>After exploring, this section gives you a clear final step to order now with BakerDan and submit complete order details.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="mailto:hello@bakerdan.com" class="rounded-full bg-white px-7 py-3 text-sm font-semibold uppercase tracking-[0.14em] transition hover:-translate-y-0.5" style="color: var(--ink);">Email BakerDan</a>
                            <a href="tel:+639000000000" class="rounded-full border px-7 py-3 text-sm font-semibold uppercase tracking-[0.14em] transition hover:bg-white/10" style="border-color: rgba(255,255,255,0.18);">Call for orders</a>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.5rem] border p-5" style="border-color: rgba(255,255,255,0.14); background: rgba(255,255,255,0.08);">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: rgba(255,255,255,0.64);">Service focus</p>
                            <p class="mt-2 text-2xl font-semibold">Wholesale and custom events</p>
                        </div>
                        <div class="rounded-[1.5rem] border p-5" style="border-color: rgba(255,255,255,0.14); background: rgba(255,255,255,0.08);">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: rgba(255,255,255,0.64);">Signature edge</p>
                            <p class="mt-2 text-2xl font-semibold">Warm, handcrafted presentation</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="mx-auto max-w-7xl px-6 pb-10 md:px-10">
            <div class="mesh-card rounded-[2rem] px-6 py-6 md:px-8">
                <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr_0.9fr] lg:items-start">
                    <div>
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/logo/BAKERDAN LOGO.jpg') }}" alt="BakerDan logo" class="h-10 w-10 rounded-full object-cover">
                            <p class="font-display text-2xl font-extrabold">BAKERDAN</p>
                        </div>
                        <p class="mt-3 max-w-xl text-sm leading-relaxed" style="color: var(--ink-soft);">
                            Fresh bread, pastries, and custom orders for homes, cafes, and events. Bringing warmth and quality to every bite.
                        <div class="mt-5 flex flex-wrap gap-3 text-sm font-medium" style="color: var(--ink-soft);">
                            <a href="#about">About</a>
                            <a href="#menu">Menu</a>
                            <a href="#account">Account</a>
                            <a href="#how-to-order">How to Order</a>
                            <a href="#assistant">AI Assistant</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--ink-soft);">Quick links</p>
                        <div class="mt-4 grid gap-3 text-sm font-medium">
                            <a href="#order-now" class="transition hover:opacity-70">Order now</a>
                            <a href="#featured" class="transition hover:opacity-70">Featured item</a>
                            <a href="#gallery" class="transition hover:opacity-70">Gallery</a>
                            <a href="#reviews" class="transition hover:opacity-70">Customer reviews</a>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--ink-soft);">Order now</p>
                        <div class="mt-4 space-y-2 text-sm" style="color: var(--ink-soft);">
                            <p>Bakerdan.bp@gmail.com</p>
                            <p>Open daily for orders and inquiries</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 border-t pt-4 text-xs" style="border-color: var(--line); color: var(--ink-soft);">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p>&copy; {{ date('Y') }} BakerDan. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div id="toast" style="position: fixed; top: 2rem; right: 2rem; padding: 1rem 2rem; border-radius: 1rem; background: var(--ink); color: white; box-shadow: var(--shadow); transform: translateY(-100%); opacity: 0; transition: all 0.5s ease; z-index: 50;" class="font-display font-bold">
        <span id="toast-message"></span>
    </div>

    <script>
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
            setTimeout(() => {
                toast.style.transform = 'translateY(-100%)';
                toast.style.opacity = '0';
            }, 3000);
        }

        @if(session('account_created'))
            showToast('Account created successfully!');
        @endif
    </script>
</body>
</html>
