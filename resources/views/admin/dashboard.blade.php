<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BakerDan Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800|outfit:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/admin-dashboard.js'])
    <style>
        :root {
            --paper: #fbf4e8;
            --surface: rgba(255, 252, 247, 0.74);
            --line: rgba(38, 24, 15, 0.1);
            --ink: #26180f;
            --ink-soft: #6d5949;
            --brand: #c86f38;
            --brand-deep: #8f4723;
            --olive: #626b3c;
            --success: #107a4b;
            --danger: #b42318;
            --shadow: 0 28px 80px -42px rgba(38, 24, 15, 0.45);
        }

        body {
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.72), transparent 24%),
                radial-gradient(circle at 85% 18%, rgba(200, 111, 56, 0.12), transparent 18%),
                linear-gradient(180deg, #f1ece5 0, #fbf4e8 88px, #fbf4e8 100%);
        }

        .font-display {
            font-family: 'Sora', sans-serif;
        }

        .page-shell {
            position: relative;
            overflow-x: hidden;
            overflow-y: visible;
        }

        .page-shell::before,
        .page-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
        }

        .page-shell::before {
            width: 30rem;
            height: 30rem;
            top: 4rem;
            right: -12rem;
            background: radial-gradient(circle, rgba(200, 111, 56, 0.14), transparent 65%);
        }

        .page-shell::after {
            width: 22rem;
            height: 22rem;
            left: -9rem;
            bottom: 6rem;
            background: radial-gradient(circle, rgba(143, 71, 35, 0.08), transparent 70%);
        }

        [hidden] {
            display: none !important;
        }

        .glass-panel {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.9), transparent 58%),
                linear-gradient(160deg, rgba(255, 250, 241, 0.92), rgba(243, 227, 198, 0.72));
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .soft-panel {
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid var(--line);
            box-shadow: 0 18px 40px -34px rgba(38, 24, 15, 0.5);
        }

        .panel-lift {
            transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
        }

        .panel-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 28px 54px -38px rgba(38, 24, 15, 0.55);
            border-color: rgba(200, 111, 56, 0.3);
        }

        .metric-card {
            position: relative;
            overflow: hidden;
            isolation: isolate;
        }

        .metric-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(200, 111, 56, 0.1), transparent 55%);
            opacity: 0;
            transition: opacity 220ms ease;
            z-index: -1;
        }

        .metric-card:hover::after {
            opacity: 1;
        }

        .section-title {
            letter-spacing: -0.03em;
        }

        .brand-fill {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            color: #fff;
        }

        .dark-pill {
            background: rgba(38, 24, 15, 0.88);
            color: #fff;
        }

        .brand-logo-wrap {
            height: 3.25rem;
            width: 3.25rem;
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.32);
            box-shadow: 0 12px 24px -16px rgba(38, 24, 15, 0.68);
            background: rgba(255, 255, 255, 0.92);
        }

        .brand-logo-wrap img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            display: block;
        }

        .logout-card {
            border-radius: 1rem;
            padding: 0.65rem;
        }

        .logout-button {
            width: 100%;
            border-radius: 0.9rem;
            border: 1px solid rgba(143, 71, 35, 0.18);
            background: linear-gradient(135deg, rgba(143, 71, 35, 0.08), rgba(200, 111, 56, 0.14));
            color: var(--brand-deep);
            padding: 0.72rem 0.95rem;
            font-size: 0.9rem;
            font-weight: 700;
            transition: transform 170ms ease, box-shadow 170ms ease, background-color 170ms ease;
        }

        .logout-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 16px -12px rgba(38, 24, 15, 0.55);
            background: linear-gradient(135deg, rgba(143, 71, 35, 0.12), rgba(200, 111, 56, 0.2));
        }

        .nav-item[data-active="true"] {
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.16), rgba(255, 255, 255, 0.9));
            color: var(--brand-deep);
            border-color: rgba(200, 111, 56, 0.24);
        }

        .nav-item {
            position: relative;
            transition: transform 180ms ease, border-color 180ms ease, background-color 180ms ease;
        }

        @media (min-width: 1024px) {
            [data-admin-dashboard].is-sidebar-compact [data-sidebar] {
                width: 6.5rem;
            }

            [data-admin-dashboard].is-sidebar-compact [data-sidebar-text],
            [data-admin-dashboard].is-sidebar-compact [data-sidebar-footer],
            [data-admin-dashboard].is-sidebar-compact .nav-item span:first-child {
                display: none;
            }

            [data-admin-dashboard].is-sidebar-compact .nav-item {
                justify-content: center;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            [data-admin-dashboard].is-sidebar-compact .nav-item::before {
                left: 0.55rem;
            }
        }

        .nav-item::before {
            content: '';
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 999px;
            background: rgba(109, 89, 73, 0.28);
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            transition: background-color 180ms ease, box-shadow 180ms ease;
        }

        .nav-item[data-active="true"]::before {
            background: var(--brand);
            box-shadow: 0 0 0 6px rgba(200, 111, 56, 0.14);
        }

        .nav-item:hover {
            transform: translateX(2px);
        }

        .nav-item span:first-child {
            padding-left: 0.9rem;
        }

        .tab-item[data-active="true"] {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            color: #fff;
        }

        [data-pagination-controls] button:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        [data-pagination-controls] button,
        [data-pagination-controls] select {
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        [data-pagination-controls] button:hover,
        [data-pagination-controls] select:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px -12px rgba(38, 24, 15, 0.55);
        }

        [data-section="dashboard"] > .grid > .soft-panel {
            border-top: 3px solid rgba(200, 111, 56, 0.38);
        }

        .chart-surface {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(251, 244, 232, 0.65));
            border: 1px dashed rgba(200, 111, 56, 0.28);
        }

        .chart-grid-line {
            stroke: rgba(109, 89, 73, 0.2);
            stroke-width: 1;
            stroke-dasharray: 3 5;
        }

        .chart-line {
            fill: none;
            stroke: var(--brand);
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .chart-point {
            fill: #fff;
            stroke: var(--brand-deep);
            stroke-width: 2;
        }

        .export-button {
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .export-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px -12px rgba(38, 24, 15, 0.55);
        }

        [data-admin-dashboard] .text-slate-900 { color: var(--ink); }
        [data-admin-dashboard] .text-slate-600,
        [data-admin-dashboard] .text-slate-500,
        [data-admin-dashboard] .text-slate-700 { color: var(--ink-soft); }
        [data-admin-dashboard] .bg-slate-50 { background: rgba(255, 255, 255, 0.65); }
        [data-admin-dashboard] .bg-slate-100 { background: rgba(255, 250, 241, 0.88); }
        [data-admin-dashboard] .divide-slate-100,
        [data-admin-dashboard] .divide-slate-200 { border-color: var(--line); }
        [data-admin-dashboard] .ring-slate-200 { --tw-ring-color: var(--line); }

        [data-admin-dashboard] .bg-slate-900 {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
        }

        [data-admin-dashboard] .shadow-slate-900\/10,
        [data-admin-dashboard] .shadow-orange-900\/20 {
            --tw-shadow-color: rgba(143, 71, 35, 0.24);
            --tw-shadow: 0 16px 32px -20px var(--tw-shadow-color);
            box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
        }

        .fade-in {
            animation: fadeUp 420ms ease-out both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="antialiased">
@php
    $metrics = [
        ['label' => 'Total products ordered', 'value' => '1,284', 'detail' => 'Count from order items'],
        ['label' => 'Completed orders', 'value' => '428', 'detail' => 'All time completed'],
        ['label' => 'Orders completed this week', 'value' => '76', 'detail' => 'Current 7-day window'],
        ['label' => 'Orders completed this month', 'value' => '214', 'detail' => 'Current month total'],
    ];

    $products = [
        ['id' => 1, 'name' => 'Classic Pandesal', 'description' => 'Soft everyday bread for retail and bulk pickup.', 'type' => 'bread', 'image' => 'images/bakerdan/Bread.png'],
        ['id' => 2, 'name' => 'Chocolate Moist Cake', 'description' => 'Rich layered cake with smooth cocoa finish.', 'type' => 'cake', 'image' => 'images/bakerdan/Cake.png'],
        ['id' => 3, 'name' => 'Cheese Roll', 'description' => 'Buttery roll with a savory cheese center.', 'type' => 'bread', 'image' => 'images/bakerdan/Creme_Puffs.png'],
        ['id' => 4, 'name' => 'Brownies Tray', 'description' => 'Dense, fudgy brownies for special orders.', 'type' => 'cake', 'image' => 'images/bakerdan/Brownies.png'],
    ];

    $orders = [
        ['id' => 'ORD-1024', 'customer' => 'Lara Gomez', 'items' => '12 pcs pandesal, 2 cakes', 'status' => 'accepted', 'payment_status' => 'unpaid', 'amount' => '₱1,420'],
        ['id' => 'ORD-1025', 'customer' => 'Mika Reyes', 'items' => '6 brownies trays, 1 loaf set', 'status' => 'preparing', 'payment_status' => 'unpaid', 'amount' => '₱2,980'],
        ['id' => 'ORD-1026', 'customer' => 'Jonah Cruz', 'items' => '3 celebration cakes', 'status' => 'ready', 'payment_status' => 'paid', 'amount' => '₱4,500'],
    ];

    $customers = [
        ['id' => 1, 'role' => 'customer', 'name' => 'Anika Santos', 'username' => 'anikabakes', 'age' => 27, 'email' => 'anika@example.com', 'contact' => '0917 111 2233', 'address' => 'Quezon City', 'status' => 'active'],
        ['id' => 2, 'role' => 'customer', 'name' => 'Paolo Diaz', 'username' => 'paolod', 'age' => 33, 'email' => 'paolo@example.com', 'contact' => '0918 222 3344', 'address' => 'Makati City', 'status' => 'suspended'],
        ['id' => 3, 'role' => 'customer', 'name' => 'Sarah Lim', 'username' => 'sarahlim', 'age' => 24, 'email' => 'sarah@example.com', 'contact' => '0920 333 4455', 'address' => 'Pasig City', 'status' => 'active'],
    ];

    $admins = [
        ['id' => 11, 'role' => 'admin', 'name' => 'Admin Baker', 'username' => 'adminbaker', 'age' => 35, 'email' => 'admin@example.com', 'contact' => '0917 555 6677', 'address' => 'Main Branch', 'status' => 'active'],
        ['id' => 12, 'role' => 'admin', 'name' => 'Supervisor May', 'username' => 'supermay', 'age' => 31, 'email' => 'may@example.com', 'contact' => '0918 666 7788', 'address' => 'Production Unit', 'status' => 'active'],
    ];

    $notifications = [
        ['id' => 1, 'customer_name' => 'Lara Gomez', 'message' => 'New bulk order requested for birthday pastries.', 'date' => '2026-04-22 08:30'],
        ['id' => 2, 'customer_name' => 'Mika Reyes', 'message' => 'Payment follow-up waiting for confirmation.', 'date' => '2026-04-21 16:45'],
        ['id' => 3, 'customer_name' => 'Jonah Cruz', 'message' => 'Requested pickup schedule changed to 3 PM.', 'date' => '2026-04-20 13:12'],
    ];

    $weeklyCompletions = [
        ['label' => 'Mon', 'value' => 9],
        ['label' => 'Tue', 'value' => 11],
        ['label' => 'Wed', 'value' => 13],
        ['label' => 'Thu', 'value' => 10],
        ['label' => 'Fri', 'value' => 15],
        ['label' => 'Sat', 'value' => 12],
        ['label' => 'Sun', 'value' => 6],
    ];

    $productTypeBreakdown = [
        ['label' => 'Bread', 'value' => collect($products)->where('type', 'bread')->count()],
        ['label' => 'Cake', 'value' => collect($products)->where('type', 'cake')->count()],
    ];

    $reportPayload = [
        'metrics' => $metrics,
        'products' => $products,
        'orders' => $orders,
        'customers' => $customers,
        'admins' => $admins,
        'notifications' => $notifications,
        'weeklyCompletions' => $weeklyCompletions,
        'productTypeBreakdown' => $productTypeBreakdown,
    ];

    $sidebarCounts = [
        'dashboard' => '•',
        'inventory' => count($products),
        'orders' => count($orders),
        'customers' => count($customers),
        'notifications' => count($notifications),
    ];
@endphp

<script id="admin-report-data" type="application/json">@json($reportPayload)</script>

<div data-admin-dashboard data-default-section="dashboard" class="page-shell min-h-screen lg:flex lg:h-screen lg:overflow-hidden">
    <aside data-sidebar class="glass-panel sticky top-0 z-20 flex w-full flex-col gap-6 border-b lg:h-screen lg:w-80 lg:shrink-0 lg:self-start lg:border-b-0 lg:border-r">
        <div class="flex items-center justify-between px-6 pt-6 lg:block lg:px-7">
            <div class="flex items-center gap-3">
                <div class="brand-logo-wrap">
                    <img src="{{ asset('images/logo/BAKERDAN LOGO.jpg') }}" alt="BakerDan logo">
                </div>
                <div data-sidebar-text>
                    <p class="font-display text-xl font-bold">BakerDan</p>
                    <p class="text-sm text-slate-500">Admin Control Center</p>
                </div>
            </div>
            <button type="button" data-sidebar-compact class="hidden lg:grid h-9 w-9 place-items-center rounded-full bg-white/70 text-slate-500" title="Toggle sidebar size" aria-label="Toggle sidebar size">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M9 5v14"></path>
                </svg>
            </button>
        </div>

        <nav class="grid gap-2 px-4 lg:px-5">
            @foreach ([['dashboard', 'Dashboard'], ['inventory', 'Inventory'], ['orders', 'Orders'], ['customers', 'Customers'], ['notifications', 'Notifications']] as [$key, $label])
                <button type="button" data-nav="{{ $key }}" class="nav-item flex items-center justify-between rounded-2xl border border-transparent px-4 py-3 text-left text-sm font-semibold text-slate-600 transition hover:border-slate-200 hover:bg-white/70">
                    <span data-sidebar-text>{{ $label }}</span>
                    <span data-nav-count="{{ $key }}" class="rounded-full bg-white/70 px-2 py-1 text-xs font-semibold text-slate-500">{{ $sidebarCounts[$key] }}</span>
                </button>
            @endforeach
        </nav>

        <div class="mx-4 mt-auto space-y-3 lg:mx-5 lg:mb-5" data-sidebar-footer>
            <div class="rounded-3xl dark-pill px-5 py-5">
                <p class="text-xs uppercase tracking-[0.24em] text-white/60">Today</p>
                <p class="mt-2 font-display text-2xl font-bold">{{ now()->format('M d') }}</p>
                <p class="mt-2 text-sm text-white/70">Bakery operations are live and ready for review.</p>
            </div>

            <div class="logout-card">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="relative z-10 flex-1 px-4 py-4 sm:px-6 lg:h-screen lg:overflow-y-auto lg:px-8 lg:py-6">
        <header class="glass-panel panel-lift mb-6 flex flex-col gap-4 rounded-[2rem] px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Bakery management system</p>
                <h1 class="font-display section-title mt-1 text-3xl font-bold text-slate-900 sm:text-4xl">Modern Admin Dashboard</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div data-current-section-label class="rounded-full bg-white/70 px-4 py-2 text-sm font-semibold text-slate-600">Dashboard</div>
                <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600">Active products: {{ count($products) }}</div>
                <div class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">Open orders: {{ count($orders) }}</div>
            </div>
        </header>

        <section data-section="dashboard" class="fade-in space-y-6">
            <div class="grid gap-4 xl:grid-cols-4">
                @foreach ($metrics as $metric)
                    <article class="soft-panel panel-lift metric-card rounded-[1.75rem] p-5">
                        <p class="text-sm font-medium text-slate-500">{{ $metric['label'] }}</p>
                        <p class="font-display mt-3 text-3xl font-bold text-slate-900">{{ $metric['value'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $metric['detail'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <article class="soft-panel panel-lift rounded-[2rem] p-6">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Operations snapshot</p>
                            <h2 class="font-display mt-1 text-2xl font-bold">Bake flow overview</h2>
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Live</span>
                    </div>
                    <div class="mt-6 grid gap-4 md:grid-cols-3">
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Inventory readiness</p>
                            <p class="mt-2 font-display text-2xl font-bold">94%</p>
                        </div>
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Orders moving</p>
                            <p class="mt-2 font-display text-2xl font-bold">31</p>
                        </div>
                        <div class="rounded-3xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Customers active</p>
                            <p class="mt-2 font-display text-2xl font-bold">218</p>
                        </div>
                    </div>
                </article>

                <article class="soft-panel panel-lift rounded-[2rem] p-6">
                    <p class="text-sm font-medium text-slate-500">Quick actions</p>
                    <h2 class="font-display mt-1 text-2xl font-bold">Admin shortcuts</h2>
                    <div class="mt-5 grid gap-3">
                        <button data-nav="inventory" type="button" class="rounded-2xl bg-slate-900 px-4 py-3 text-left text-sm font-semibold text-white">Manage product catalog</button>
                        <button data-nav="orders" type="button" class="rounded-2xl bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-700">Review active orders</button>
                        <button data-nav="customers" type="button" class="rounded-2xl bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-700">Review customer accounts</button>
                    </div>
                </article>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <article class="soft-panel panel-lift rounded-[2rem] p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Weekly report graph</p>
                            <h3 class="font-display mt-1 text-2xl font-bold">Completed orders trend</h3>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">7 days</span>
                    </div>
                    <div class="chart-surface mt-5 rounded-3xl p-4">
                        <svg data-line-chart viewBox="0 0 680 240" class="h-56 w-full">
                            <line x1="54" y1="190" x2="640" y2="190" class="chart-grid-line"></line>
                            <line x1="54" y1="145" x2="640" y2="145" class="chart-grid-line"></line>
                            <line x1="54" y1="100" x2="640" y2="100" class="chart-grid-line"></line>
                            <line x1="54" y1="55" x2="640" y2="55" class="chart-grid-line"></line>
                            <path data-line-path class="chart-line" d=""></path>
                            <g data-line-points></g>
                        </svg>
                        <div data-line-labels class="mt-2 grid grid-cols-7 text-center text-xs font-semibold text-slate-500"></div>
                    </div>
                </article>

                <article class="soft-panel panel-lift rounded-[2rem] p-6">
                    <p class="text-sm font-medium text-slate-500">Product mix graph</p>
                    <h3 class="font-display mt-1 text-2xl font-bold">Category distribution</h3>
                    <div data-type-bars class="mt-5 space-y-4"></div>
                </article>
            </div>

            <article class="soft-panel panel-lift rounded-[2rem] p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Report export center</p>
                        <h3 class="font-display mt-1 text-2xl font-bold">Generate report data</h3>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500">Download bakery report data in CSV or JSON format for reporting, sharing, and archival.</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    <label for="export-target" class="text-sm font-medium text-slate-600">Dataset</label>
                    <select id="export-target" data-export-target class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                        <option value="summary">Summary</option>
                        <option value="products">Products</option>
                        <option value="orders">Orders</option>
                        <option value="customers">Customers</option>
                        <option value="admins">Admins</option>
                        <option value="notifications">Notifications</option>
                    </select>
                    <button type="button" data-export-format="csv" class="export-button rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Export CSV</button>
                    <button type="button" data-export-format="json" class="export-button rounded-full bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700">Export JSON</button>
                    <p data-export-feedback class="text-sm font-medium text-emerald-700"></p>
                </div>
            </article>
        </section>

        <section data-section="inventory" hidden class="fade-in space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Inventory section</p>
                    <h2 class="font-display section-title mt-1 text-3xl font-bold">Active products</h2>
                </div>
                <button type="button" data-open-add-product class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10">+ Add Product</button>
            </div>

            <div data-inventory-feedback class="min-h-6 text-sm font-medium text-emerald-700"></div>

            <article class="soft-panel panel-lift overflow-hidden rounded-[2rem]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-medium">Name</th>
                                <th class="px-5 py-4 font-medium">Description</th>
                                <th class="px-5 py-4 font-medium">Type</th>
                                <th class="px-5 py-4 font-medium">Image</th>
                                <th class="px-5 py-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($products as $product)
                                <tr data-product-row data-page-item="inventory" data-product-id="{{ $product['id'] }}" data-product-name="{{ $product['name'] }}" data-product-description="{{ $product['description'] }}" data-product-type="{{ $product['type'] }}" class="align-top">
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $product['name'] }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $product['description'] }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">{{ $product['type'] }}</span></td>
                                    <td class="px-5 py-4">
                                        @if (file_exists(public_path($product['image'])))
                                            <img src="{{ asset($product['image']) }}" alt="{{ $product['name'] }}" class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200">
                                        @else
                                            <div class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xs font-semibold text-slate-500">IMG</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" data-edit-product class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Edit</button>
                                            <button type="button" data-remove-product class="rounded-full bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Remove</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div data-inventory-empty hidden class="border-t border-dashed border-slate-200 px-5 py-10 text-center text-sm text-slate-500">No active products found.</div>
            </article>

            <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="inventory">
                <p class="text-sm text-slate-500" data-page-info="inventory"></p>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-500" for="page-size-inventory">Items</label>
                    <select id="page-size-inventory" data-page-size="inventory" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                    </select>
                    <button type="button" data-page-prev="inventory" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                    <button type="button" data-page-next="inventory" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                </div>
            </div>
        </section>

        <section data-section="orders" hidden class="fade-in space-y-6">
            <div>
                <p class="text-sm font-medium text-slate-500">Orders section</p>
                <h2 class="font-display section-title mt-1 text-3xl font-bold">Orders in progress</h2>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($orders as $order)
                    <article data-order-card data-page-item="orders" data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}" data-payment-status="{{ $order['payment_status'] }}" class="soft-panel panel-lift rounded-[1.75rem] p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h3 class="font-display text-2xl font-bold text-slate-900">{{ $order['id'] }}</h3>
                                    <span data-payment-status class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($order['payment_status']) }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500">Customer: {{ $order['customer'] }}</p>
                            </div>
                            <div class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">{{ $order['status'] }}</div>
                        </div>

                        <div class="mt-4 rounded-3xl bg-slate-50 p-4 text-sm text-slate-600" data-order-body>
                            <p>{{ $order['items'] }}</p>
                            <p class="mt-2 font-semibold text-slate-900">{{ $order['amount'] }}</p>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <button data-order-action="accept" @if ($order['status'] !== 'accepted') hidden @endif type="button" class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Accept</button>
                            <button data-order-action="preparing" @if ($order['status'] !== 'preparing') hidden @endif type="button" class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Preparing</button>
                            <button data-order-action="ready" @if ($order['status'] !== 'ready') hidden @endif type="button" class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Ready</button>
                            <button data-order-action="mark-paid" @if ($order['payment_status'] === 'paid') hidden @endif type="button" class="rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Mark as Paid</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="orders">
                <p class="text-sm text-slate-500" data-page-info="orders"></p>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-500" for="page-size-orders">Items</label>
                    <select id="page-size-orders" data-page-size="orders" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                    </select>
                    <button type="button" data-page-prev="orders" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                    <button type="button" data-page-next="orders" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                </div>
            </div>
        </section>

        <section data-section="customers" hidden class="fade-in space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Customers section</p>
                    <h2 class="font-display section-title mt-1 text-3xl font-bold">Accounts and access</h2>
                </div>
                <div class="flex rounded-full bg-slate-100 p-1">
                    <button type="button" data-person-tab="customers" class="tab-item rounded-full px-4 py-2 text-sm font-semibold text-slate-600" data-active="true">Customers</button>
                    <button type="button" data-person-tab="admins" class="tab-item rounded-full px-4 py-2 text-sm font-semibold text-slate-600">Admins</button>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.38fr]">
                <article class="soft-panel panel-lift rounded-[2rem] p-4 sm:p-5">
                    <div data-person-panel="customers">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Name</th>
                                        <th class="px-4 py-3 font-medium">Username</th>
                                        <th class="px-4 py-3 font-medium">Age</th>
                                        <th class="px-4 py-3 font-medium">Email</th>
                                        <th class="px-4 py-3 font-medium">Contact</th>
                                        <th class="px-4 py-3 font-medium">Address</th>
                                        <th class="px-4 py-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($customers as $person)
                                        <tr data-person-card data-page-item="customers" data-person-id="{{ $person['id'] }}" data-person-role="Customer" data-person-name="{{ $person['name'] }}" data-person-email="{{ $person['email'] }}" data-person-status="{{ $person['status'] }}" class="align-top">
                                            <td class="px-4 py-4 font-semibold text-slate-900">{{ $person['name'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['username'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['age'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['email'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['contact'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['address'] }}</td>
                                            <td class="px-4 py-4">
                                                <div class="flex flex-wrap gap-2">
                                                    <button type="button" data-view-person class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">View</button>
                                                    <button type="button" data-toggle-person class="rounded-full {{ $person['status'] === 'active' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-2 text-xs font-semibold">{{ $person['status'] === 'active' ? 'Suspend' : 'Unsuspend' }}</button>
                                                </div>
                                                <template data-person-details>
                                                    <div class="grid gap-3 text-sm text-slate-600">
                                                        <p><span class="font-semibold text-slate-900">Name:</span> {{ $person['name'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Username:</span> {{ $person['username'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Age:</span> {{ $person['age'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Email:</span> {{ $person['email'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Contact:</span> {{ $person['contact'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Address:</span> {{ $person['address'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Status:</span> <span data-person-status>{{ ucfirst($person['status']) }}</span></p>
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3" data-pagination-controls="customers">
                            <p class="text-sm text-slate-500" data-page-info="customers"></p>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-slate-500" for="page-size-customers">Items</label>
                                <select id="page-size-customers" data-page-size="customers" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                                    <option value="10" selected>10</option>
                                    <option value="20">20</option>
                                </select>
                                <button type="button" data-page-prev="customers" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                                <button type="button" data-page-next="customers" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                            </div>
                        </div>
                    </div>

                    <div data-person-panel="admins" hidden>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-slate-500">
                                    <tr>
                                        <th class="px-4 py-3 font-medium">Name</th>
                                        <th class="px-4 py-3 font-medium">Username</th>
                                        <th class="px-4 py-3 font-medium">Age</th>
                                        <th class="px-4 py-3 font-medium">Email</th>
                                        <th class="px-4 py-3 font-medium">Contact</th>
                                        <th class="px-4 py-3 font-medium">Address</th>
                                        <th class="px-4 py-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($admins as $person)
                                        <tr data-person-card data-page-item="admins" data-person-id="{{ $person['id'] }}" data-person-role="Admin" data-person-name="{{ $person['name'] }}" data-person-email="{{ $person['email'] }}" data-person-status="{{ $person['status'] }}" class="align-top">
                                            <td class="px-4 py-4 font-semibold text-slate-900">{{ $person['name'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['username'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['age'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['email'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['contact'] }}</td>
                                            <td class="px-4 py-4 text-slate-600">{{ $person['address'] }}</td>
                                            <td class="px-4 py-4">
                                                <div class="flex flex-wrap gap-2">
                                                    <button type="button" data-view-person class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">View</button>
                                                    <button type="button" data-toggle-person class="rounded-full bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">Suspend</button>
                                                </div>
                                                <template data-person-details>
                                                    <div class="grid gap-3 text-sm text-slate-600">
                                                        <p><span class="font-semibold text-slate-900">Name:</span> {{ $person['name'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Username:</span> {{ $person['username'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Age:</span> {{ $person['age'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Email:</span> {{ $person['email'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Contact:</span> {{ $person['contact'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Address:</span> {{ $person['address'] }}</p>
                                                        <p><span class="font-semibold text-slate-900">Status:</span> <span data-person-status>{{ ucfirst($person['status']) }}</span></p>
                                                    </div>
                                                </template>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3" data-pagination-controls="admins" hidden>
                            <p class="text-sm text-slate-500" data-page-info="admins"></p>
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-slate-500" for="page-size-admins">Items</label>
                                <select id="page-size-admins" data-page-size="admins" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                                    <option value="10" selected>10</option>
                                    <option value="20">20</option>
                                </select>
                                <button type="button" data-page-prev="admins" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                                <button type="button" data-page-next="admins" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                            </div>
                        </div>
                    </div>
                </article>

                <aside data-customer-panel hidden class="soft-panel panel-lift rounded-[2rem] p-5">
                    <p class="text-sm font-medium text-slate-500">Detail panel</p>
                    <h3 data-customer-panel-title class="font-display mt-1 text-2xl font-bold">Account details</h3>
                    <p data-customer-panel-meta class="mt-1 text-sm text-slate-500"></p>
                    <div data-customer-panel-body class="mt-5 space-y-3 text-sm text-slate-600"></div>
                </aside>
            </div>
        </section>

        <section data-section="notifications" hidden class="fade-in space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Notifications section</p>
                    <h2 class="font-display section-title mt-1 text-3xl font-bold">Active notifications</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span data-notification-count class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">{{ count($notifications) }}</span>
                    <button data-clear-notifications type="button" class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Clear All</button>
                </div>
            </div>

            <div class="grid gap-4">
                @foreach ($notifications as $notification)
                    <article data-notification-item data-page-item="notifications" class="soft-panel panel-lift flex flex-col gap-4 rounded-[1.5rem] p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $notification['customer_name'] }}</p>
                            <h3 class="mt-1 font-display text-xl font-bold">{{ $notification['message'] }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $notification['date'] }}</p>
                        </div>
                        <button data-remove-notification type="button" class="rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700">Remove</button>
                    </article>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="notifications">
                <p class="text-sm text-slate-500" data-page-info="notifications"></p>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-500" for="page-size-notifications">Items</label>
                    <select id="page-size-notifications" data-page-size="notifications" class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                    </select>
                    <button type="button" data-page-prev="notifications" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                    <button type="button" data-page-next="notifications" class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                </div>
            </div>
        </section>
    </main>

    <div data-inventory-drawer hidden class="fixed inset-0 z-40 bg-slate-950/35 p-4 backdrop-blur-sm">
        <div class="ml-auto flex h-full max-w-xl items-stretch">
            <div class="soft-panel panel-lift flex w-full flex-col rounded-[2rem] bg-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Product form</p>
                        <h3 data-inventory-title class="font-display mt-1 text-2xl font-bold">Add Product</h3>
                        <p data-inventory-subtitle class="mt-1 text-sm text-slate-500">Create a new active product for the bakery catalog.</p>
                    </div>
                    <button type="button" data-inventory-close class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600">✕</button>
                </div>

                <form data-inventory-form class="mt-6 flex flex-1 flex-col gap-4" data-mode="add">
                    <input data-inventory-id type="hidden" value="">
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Name
                        <input data-inventory-name type="text" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400" placeholder="Product name">
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Description
                        <textarea data-inventory-description rows="4" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400" placeholder="Short product description"></textarea>
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Image upload
                        <input type="file" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-slate-400">
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Type
                        <select data-inventory-type class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400">
                            <option value="bread">Bread</option>
                            <option value="cake">Cake</option>
                        </select>
                    </label>
                    <button type="submit" class="mt-auto rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Save Product</button>
                </form>
            </div>
        </div>
    </div>

    <div data-modal-shell hidden class="fixed inset-0 z-50 grid place-items-center bg-slate-950/40 p-4 backdrop-blur-sm">
        <div class="soft-panel panel-lift w-full max-w-md rounded-[2rem] p-6">
            <p class="text-sm font-medium text-slate-500">Confirmation</p>
            <h3 data-modal-title class="font-display mt-1 text-2xl font-bold">Title</h3>
            <p data-modal-message class="mt-3 text-sm leading-6 text-slate-600">Message</p>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button data-modal-cancel type="button" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">Cancel</button>
                <button data-modal-confirm type="button" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Confirm</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>