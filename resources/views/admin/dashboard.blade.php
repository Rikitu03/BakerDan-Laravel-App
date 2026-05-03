<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BakerDan Admin Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700,800|outfit:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/main.js'])
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
                radial-gradient(circle at top, rgba(255, 255, 255, 0.92), transparent 58%),
                linear-gradient(160deg, rgba(255, 250, 241, 0.95), rgba(243, 227, 198, 0.78));
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
        }

        [data-sidebar] {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.94), transparent 50%),
                linear-gradient(160deg, rgba(255, 252, 247, 0.96), rgba(245, 230, 205, 0.82));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), var(--shadow);
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
            background: linear-gradient(135deg, rgba(143, 71, 35, 0.95), rgba(38, 24, 15, 0.92));
            color: #fff;
            border: 1px solid rgba(200, 111, 56, 0.3);
            box-shadow: 0 12px 28px -12px rgba(143, 71, 35, 0.45);
        }

        .brand-logo-wrap {
            height: 3.5rem;
            width: 3.5rem;
            border-radius: 1.2rem;
            overflow: hidden;
            border: 2px solid rgba(200, 111, 56, 0.2);
            box-shadow: 0 8px 24px -12px rgba(143, 71, 35, 0.4);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(255, 248, 240, 0.95));
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 200ms ease;
        }

        .brand-logo-wrap:hover {
            border-color: rgba(200, 111, 56, 0.4);
            box-shadow: 0 12px 28px -8px rgba(143, 71, 35, 0.5);
            transform: scale(1.03);
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
            border-radius: 0.95rem;
            border: 1.5px solid rgba(200, 111, 56, 0.3);
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.1), rgba(255, 255, 255, 0.15));
            /* color: rgba(255, 255, 255, 0.9); */
            padding: 0.8rem 1.1rem;
            font-size: 0.9rem;
            font-weight: 700;
            transition: all 200ms ease;
            cursor: pointer;
            backdrop-filter: blur(4px);
        }

        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -12px rgba(143, 71, 35, 0.6);
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.2), rgba(255, 255, 255, 0.22));
            border-color: rgba(200, 111, 56, 0.5);
            color: rgba(255, 255, 255, 1);
        }

        .logout-button:active {
            transform: translateY(-1px);
        }

        .nav-item[data-active="true"] {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            color: #fff;
            border-color: var(--brand);
            box-shadow: 0 8px 16px -4px rgba(143, 71, 35, 0.35);
        }

        .nav-item {
            position: relative;
            transition: all 200ms ease;
            cursor: pointer;
        }

        .nav-item:hover:not([data-active="true"]) {
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.1), rgba(255, 255, 255, 0.95));
            border-color: rgba(200, 111, 56, 0.3);
            transform: translateX(2px);
            box-shadow: 0 4px 12px -4px rgba(143, 71, 35, 0.2);
        }

        .nav-item:active {
            transform: translateX(0px);
        }

        [data-nav-count] {
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.12), rgba(255, 255, 255, 0.88)) !important;
            border: 1px solid rgba(200, 111, 56, 0.15);
            color: var(--brand-deep) !important;
            font-weight: 700;
            transition: all 150ms ease;
        }

        .nav-item:hover [data-nav-count]:not([data-active="true"]) {
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.18), rgba(255, 255, 255, 0.95)) !important;
            border-color: rgba(200, 111, 56, 0.25);
            transform: scale(1.05);
        }

        .nav-item[data-active="true"] [data-nav-count] {
            background: rgba(255, 255, 255, 0.25) !important;
            color: #fff !important;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .hero-panel {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.82), transparent 30%),
                linear-gradient(135deg, rgba(66, 45, 33, 0.98), rgba(122, 72, 41, 0.96), rgba(199, 132, 87, 0.9));
            color: #fff;
        }

        .hero-panel::after {
            content: '';
            position: absolute;
            right: -4rem;
            top: -3rem;
            height: 14rem;
            width: 14rem;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2), transparent 68%);
            pointer-events: none;
        }

        .section-shell {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            padding: 1.1rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.52), rgba(255, 248, 240, 0.62));
            border: 1px solid rgba(38, 24, 15, 0.08);
        }

        .message-thread-button[data-active="true"] {
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 36px -30px rgba(122, 82, 54, 0.48);
            border-color: rgba(234, 218, 205, 1);
        }

        @media (min-width: 1024px) {
            [data-admin-dashboard].is-sidebar-compact [data-sidebar] {
                width: 6.8rem;
            }

            [data-admin-dashboard].is-sidebar-compact [data-sidebar-text],
            [data-admin-dashboard].is-sidebar-compact [data-sidebar-footer],
            [data-admin-dashboard].is-sidebar-compact .nav-item-label {
                display: none;
            }

            [data-admin-dashboard].is-sidebar-compact .nav-item {
                justify-content: center;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
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

        .filter-chip[data-active="true"] {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            color: #fff;
            box-shadow: 0 12px 24px -18px rgba(143, 71, 35, 0.6);
        }

        .filter-chip {
            transition: transform 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
        }

        .filter-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px -14px rgba(38, 24, 15, 0.35);
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

        [data-sidebar] [data-sidebar-text] p:first-child {
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        [data-sidebar] [data-sidebar-text] p:last-child {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--ink-soft);
            opacity: 0.85;
        }

        .nav-item-label {
            font-weight: 600;
            transition: all 150ms ease;
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
    $metrics = $metrics ?? [];
    $products = $products ?? [];
    $orders = $orders ?? [];
    $customers = $customers ?? [];
    $admins = $admins ?? [];
    $notifications = $notifications ?? [];
    $messages = $messages ?? [];
    $weeklyCompletions = $weeklyCompletions ?? [];
    $productTypeBreakdown = $productTypeBreakdown ?? [];
    $reportPayload = $reportPayload ?? [
        'metrics' => $metrics,
        'products' => $products,
        'orders' => $orders,
        'customers' => $customers,
        'admins' => $admins,
        'notifications' => $notifications,
        'messages' => $messages,
        'weeklyCompletions' => $weeklyCompletions,
        'productTypeBreakdown' => $productTypeBreakdown,
    ];
    $sidebarCounts = $sidebarCounts ?? [
        'dashboard' => '•',
        'inventory' => count($products),
        'orders' => count($orders),
        'customers' => count($customers),
        'notifications' => count($notifications),
        'messages' => collect($messages)->where('unread', true)->count(),
    ];
    $adminProductOptions = collect($products)->map(function ($product) {
        return [
            'id' => $product['id'],
            'name' => $product['product_name'] ?? $product['name'],
            'price' => $product['formatted_price'] ?? ('PHP ' . number_format((float) $product['price'], 2)),
        ];
    })->values();
@endphp

<script id="admin-report-data" type="application/json">@json($reportPayload)</script>
<script id="admin-product-options" type="application/json">@json($adminProductOptions)</script>
<script id="admin-messages-data" type="application/json">@json($messages)</script>

@php
    $defaultSection = session('admin_section')
        ?? ($errors->walkinOrder->any() ? 'orders' : ($errors->bulkUpload->any() ? 'inventory' : 'dashboard'));
    $modalToOpen = $errors->walkinOrder->any() ? 'walkin' : ($errors->bulkUpload->any() ? 'bulk-upload' : '');
    $walkinItems = old('items', [['product_id' => '', 'quantity' => 1]]);
@endphp

<div data-admin-dashboard data-default-section="{{ $defaultSection }}" data-open-modal="{{ $modalToOpen }}" class="page-shell min-h-screen lg:flex lg:h-screen lg:overflow-hidden">
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
            @foreach ([['dashboard', 'Dashboard'], ['inventory', 'Inventory'], ['orders', 'Orders'], ['customers', 'Customers'], ['notifications', 'Notifications'], ['messages', 'Messages']] as [$key, $label])
                <button type="button" data-nav="{{ $key }}" class="nav-item flex items-center justify-between rounded-2xl border border-transparent px-4 py-3 text-left text-sm font-semibold text-slate-600 transition hover:border-slate-200 hover:bg-white/70">
                    <span class="nav-item-label" data-sidebar-text>{{ $label }}</span>
                    <span data-nav-count="{{ $key }}" class="rounded-full bg-white/70 px-2 py-1 text-xs font-semibold text-slate-500">{{ $sidebarCounts[$key] ?? 0 }}</span>
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
        <header class="hero-panel panel-lift mb-6 flex flex-col gap-5 rounded-[2rem] px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/65">Admin operations</p>
                <h1 class="font-display section-title mt-2 text-3xl font-bold text-white sm:text-4xl">Bakerdan Command Center</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-white/76">Track orders, customer activity, inbox requests, and bakery alerts from one warmer and more focused workspace.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div data-current-section-label class="rounded-full bg-white/18 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/18">Dashboard</div>
                <div class="rounded-full bg-white/12 px-4 py-2 text-sm font-medium text-white/86 ring-1 ring-white/16">Active products: {{ count($products) }}</div>
                <div class="rounded-full bg-emerald-400/18 px-4 py-2 text-sm font-medium text-emerald-50 ring-1 ring-emerald-200/18">Open orders: {{ count($orders) }}</div>
                <div class="rounded-full bg-amber-300/18 px-4 py-2 text-sm font-medium text-amber-50 ring-1 ring-amber-100/20">Unread inbox: {{ collect($messages)->where('unread', true)->count() }}</div>
            </div>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div id="walkin-order-modal" hidden class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-[2rem] bg-white p-6 shadow-2xl sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl font-bold">New Walk-in Order</h2>
                        <p class="mt-1 text-sm text-slate-500">Build a counter order using live inventory items and save it directly to the order queue.</p>
                    </div>
                    <button type="button" data-close-walkin-order class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Close</button>
                </div>

                @if ($errors->walkinOrder->any())
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        <p class="font-semibold">Please fix the walk-in order form.</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->walkinOrder->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="walkin-order-form" method="POST" action="{{ route('admin.orders.walkin') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="_admin_section" value="orders">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-medium text-slate-700">
                            Customer name
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Walk-in Customer">
                        </label>
                        <label class="grid gap-2 text-sm font-medium text-slate-700">
                            Registered customer
                            <select name="linked_customer_user_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option value="">Guest / no linked account</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer['id'] }}" @selected((string) old('linked_customer_user_id') === (string) $customer['id'])>
                                        {{ $customer['name'] }}{{ $customer['email'] ? ' - ' . $customer['email'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-medium text-slate-700">
                            Payment status
                            <select name="payment_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                                <option value="unpaid" @selected(old('payment_status', 'unpaid') === 'unpaid')>Unpaid</option>
                                <option value="paid" @selected(old('payment_status') === 'paid')>Paid</option>
                            </select>
                        </label>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            Link a registered customer if you want the walk-in order to appear in their in-app notifications.
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Order items</p>
                                <p class="text-xs text-slate-500">Add one or more catalog products with their quantities.</p>
                            </div>
                            <button type="button" data-add-walkin-item class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Add item</button>
                        </div>

                        <div data-walkin-items class="space-y-3">
                            @foreach ($walkinItems as $index => $item)
                                <div data-walkin-item class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-[minmax(0,1fr)_120px_auto]">
                                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                                        Product
                                        <select name="items[{{ $index }}][product_id]" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                            <option value="">Select a product</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product['id'] }}" @selected((string) ($item['product_id'] ?? '') === (string) $product['id'])>
                                                    {{ $product['product_name'] ?? $product['name'] }} - {{ $product['formatted_price'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                                        Quantity
                                        <input type="number" min="1" max="999" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    </label>
                                    <div class="flex items-end">
                                        <button type="button" data-remove-walkin-item class="w-full rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-700">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Notes
                        <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3" placeholder="Optional counter notes or customer instructions">{{ old('notes') }}</textarea>
                    </label>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white">Create walk-in order</button>
                        <button type="button" data-close-walkin-order class="rounded-full bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <template data-walkin-item-template>
            <div data-walkin-item class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-[minmax(0,1fr)_120px_auto]">
                <label class="grid gap-2 text-sm font-medium text-slate-700">
                    Product
                    <select data-walkin-product class="rounded-2xl border border-slate-200 bg-white px-4 py-3"></select>
                </label>
                <label class="grid gap-2 text-sm font-medium text-slate-700">
                    Quantity
                    <input data-walkin-quantity type="number" min="1" max="999" value="1" class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                </label>
                <div class="flex items-end">
                    <button type="button" data-remove-walkin-item class="w-full rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-700">Remove</button>
                </div>
            </div>
        </template>

        <div id="bulk-upload-modal" hidden class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-xl rounded-[2rem] bg-white p-6 shadow-2xl sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl font-bold">Bulk Product Upload</h2>
                        <p class="mt-1 text-sm text-slate-500">Import inventory rows from CSV using the bakery catalog fields already used in the app.</p>
                    </div>
                    <button type="button" data-close-bulk-upload class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Close</button>
                </div>

                @if ($errors->bulkUpload->any())
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        @foreach ($errors->bulkUpload->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form id="bulk-upload-form" method="POST" action="{{ route('admin.inventory.bulk') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_admin_section" value="inventory">
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        CSV file
                        <input type="file" name="csv" accept=".csv,text/csv" class="w-full rounded-2xl border border-slate-200 px-4 py-3">
                    </label>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Required columns: <span class="font-semibold">name</span> or <span class="font-semibold">product_name</span>, <span class="font-semibold">price</span>, <span class="font-semibold">category</span>.
                        Optional columns: <span class="font-semibold">description</span>, <span class="font-semibold">image_url</span>, <span class="font-semibold">is_active</span>.
                    </div>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white">Upload products</button>
                        <button type="button" data-download-bulk-template class="rounded-full bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700">Download template</button>
                        <button type="button" data-close-bulk-upload class="rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-700">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

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
                        <button data-nav="messages" type="button" class="rounded-2xl bg-[#FFF4EB] px-4 py-3 text-left text-sm font-semibold text-[#8F4723]">Open admin inbox</button>
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
                <div class="flex gap-2">
                    <button type="button" data-open-add-product class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10">+ Add Product</button>
                    <button type="button" data-open-bulk-upload class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/10">Bulk Upload</button>
                </div>
            </div>
            
            <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <label class="relative block w-full lg:max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z"></path>
                            </svg>
                        </span>
                        <input data-search-input="inventory" type="search" placeholder="Search by product name, description, or category" class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-filter-button="inventory" data-filter-value="all" data-active="true" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                        <button type="button" data-filter-button="inventory" data-filter-value="Bread" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Bread</button>
                        <button type="button" data-filter-button="inventory" data-filter-value="Pastries" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Pastries</button>
                        <button type="button" data-filter-button="inventory" data-filter-value="Cakes" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Cakes</button>
                        <button type="button" data-filter-button="inventory" data-filter-value="Customize" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Customize</button>
                    </div>
                </div>
            </article>

            <article class="soft-panel panel-lift overflow-hidden rounded-[2rem]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-5 py-4 font-medium">Name</th>
                                <th class="px-5 py-4 font-medium">Description</th>
                                <th class="px-5 py-4 font-medium">Price</th>
                                <th class="px-5 py-4 font-medium">Category</th>
                                <th class="px-5 py-4 font-medium">Image</th>
                                <th class="px-5 py-4 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($products as $product)
                                <tr data-product-row data-page-item="inventory" data-product-id="{{ $product['id'] }}" data-product-name="{{ $product['name'] }}" data-product-description="{{ $product['description'] }}" data-product-price="{{ $product['price'] }}" data-product-category="{{ $product['category'] }}" data-product-image-url="{{ $product['image_url'] ?? '' }}" data-product-is-active="{{ !empty($product['is_active']) ? 1 : 0 }}" class="align-top">
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $product['name'] }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $product['description'] }}</td>
                                    <td class="px-5 py-4 font-semibold text-slate-900">{{ $product['formatted_price'] ?? $product['price'] }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">{{ $product['category'] }}</span></td>
                                    <td class="px-5 py-4">
                                        @if (!empty($product['image_url']))
                                            <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200">
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
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Orders section</p>
                    <h2 class="font-display section-title mt-1 text-3xl font-bold">Orders in progress</h2>
                </div>
                <button type="button" data-open-walkin-order class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/10">+ New Walk-in Order</button>
            </div>

            <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <label class="relative block w-full lg:max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z"></path>
                            </svg>
                        </span>
                        <input data-search-input="orders" type="search" placeholder="Search by order #, customer, or payment status" class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-filter-button="orders" data-filter-value="all" data-active="true" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                        <button type="button" data-filter-button="orders" data-filter-value="pending" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Pending</button>
                        <button type="button" data-filter-button="orders" data-filter-value="processing" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Processing</button>
                        <button type="button" data-filter-button="orders" data-filter-value="shipped" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Shipped</button>
                        <button type="button" data-filter-button="orders" data-filter-value="paid" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Paid</button>
                        <button type="button" data-filter-button="orders" data-filter-value="custom" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Custom</button>
                    </div>
                </div>
            </article>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($orders as $order)
                    <article data-order-card data-page-item="orders" data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}" data-payment-status="{{ $order['payment_status'] }}" data-next-status="{{ $order['next_status'] ?? '' }}" data-order-customer="{{ $order['customer'] }}" data-order-custom="{{ !empty($order['contains_custom']) ? '1' : '0' }}" class="soft-panel panel-lift rounded-[1.75rem] p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="font-display text-2xl font-bold text-slate-900">Order #{{ $order['id'] }}</h3>
                                    <span data-payment-status class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $order['payment_status_label'] }}</span>
                                    @if (!empty($order['contains_custom']))
                                        <span class="rounded-full bg-[#FFF4EB] px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Custom order</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-slate-500">Customer: {{ $order['customer'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    @if (!empty($order['placed_at']))
                                        Placed {{ $order['placed_at'] }}
                                    @endif
                                    @if (!empty($order['payment_method_label']))
                                        | Payment {{ $order['payment_method_label'] }}
                                    @endif
                                    @if (!empty($order['payment_reference']))
                                        | Ref {{ $order['payment_reference'] }}
                                    @endif
                                </p>
                            </div>
                            <div data-order-status-label class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">{{ $order['status_label'] }}</div>
                        </div>

                        <div class="mt-4 space-y-3 text-sm text-slate-600" data-order-body>
                            @foreach ($order['item_lines'] as $line)
                                <div class="rounded-3xl bg-slate-50 p-4">
                                    <p class="font-semibold text-slate-900">{{ $line['summary'] }}</p>
                                    @if (!empty($line['detail']))
                                        <p class="mt-1 text-xs text-slate-500">{{ $line['detail'] }}</p>
                                    @endif
                                </div>
                            @endforeach

                            @if (!empty($order['contains_custom']))
                                <div class="rounded-3xl border border-[#F0DCCC] bg-[#FFF8F1] p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Customization Brief</p>
                                    @foreach ($order['custom_items'] as $customItem)
                                        <div class="mt-3 flex gap-4">
                                            <img src="{{ $customItem['image_url'] }}" alt="{{ $customItem['name'] }}" class="h-20 w-20 rounded-[1.25rem] object-cover ring-1 ring-[#EACFBC]">
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-slate-900">{{ $customItem['name'] }}</p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    Qty {{ $customItem['quantity'] }}
                                                    @if (!empty($customItem['size']))
                                                        | Size {{ $customItem['size'] }}
                                                    @endif
                                                    @if (!empty($customItem['flavor']))
                                                        | Flavor {{ $customItem['flavor'] }}
                                                    @endif
                                                </p>
                                                @if (!empty($customItem['design_description']))
                                                    <p class="mt-2 text-sm leading-6 text-slate-700">{{ $customItem['design_description'] }}</p>
                                                @endif
                                                @if (!empty($customItem['dedication_message']))
                                                    <p class="mt-2 text-xs font-medium text-[#8E5632]">Dedication: {{ $customItem['dedication_message'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    <p class="mt-3 text-xs text-[#8E5632]">{{ $order['workflow_note'] }}</p>
                                </div>
                            @endif

                            <div class="rounded-3xl bg-white p-4 ring-1 ring-slate-100">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-semibold text-slate-900">{{ $order['amount'] }}</p>
                                    <p class="text-xs text-slate-500">Payment {{ $order['payment_method_label'] }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <button
                                data-order-action="advance"
                                data-next-status="{{ $order['next_status'] ?? '' }}"
                                @if (empty($order['next_status'])) hidden @endif
                                type="button"
                                class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white"
                            >
                                {{ $order['next_status_label'] ?? 'Advance Workflow' }}
                            </button>
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

        <section data-section="messages" hidden class="fade-in space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500">Messages section</p>
                    <h2 class="font-display section-title mt-1 text-3xl font-bold">Admin inbox and replies</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-[#FFF4EB] px-4 py-2 text-sm font-semibold text-[#8F4723]">{{ collect($messages)->where('unread', true)->count() }} unread</span>
                    <button type="button" data-admin-message-mark-read class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Mark thread as read</button>
                </div>
            </div>

            <section class="section-shell">
                <div class="grid gap-4 xl:h-[76vh] xl:grid-cols-[340px_minmax(0,1fr)]">
                    <aside class="overflow-hidden rounded-[28px] border border-[#EEE2D7] bg-[#FBF7F3] xl:flex xl:min-h-0 xl:flex-col">
                        <div class="border-b border-[#EADDD2] px-5 py-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#C19370]">Messages</p>
                                    <h3 class="mt-2 text-3xl font-black text-[#4E3527]" style="font-family: 'Sora', sans-serif;">Inbox</h3>
                                </div>
                                <span data-admin-message-unread class="rounded-full bg-[#FDEBDC] px-3 py-1 text-xs font-semibold text-[#B96D3F]">{{ collect($messages)->where('unread', true)->count() }} unread</span>
                            </div>

                            <label class="relative mt-5 block">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-[#BBA89A]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input data-admin-message-search type="text" placeholder="Search conversations" class="h-12 w-full rounded-full border border-[#E4D6CB] bg-white pr-4 pl-11 text-sm text-[#4F4944] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]">
                            </label>
                        </div>

                        <div data-admin-message-list class="max-h-[28rem] overflow-y-auto px-3 py-3 xl:max-h-none xl:min-h-0 xl:flex-1"></div>
                    </aside>

                    <section data-admin-message-panel class="flex min-h-[60vh] flex-col overflow-hidden rounded-[30px] border border-[#E9DDD3] bg-[#FFFCF9] xl:h-full xl:min-h-0">
                        <div class="shrink-0 border-b border-[#EEE1D7] bg-white px-6 py-5">
                            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div class="flex items-center gap-4">
                                    <div data-admin-message-avatar class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-[#D59A72] to-[#C27A51] text-base font-bold text-white shadow-md">--</div>
                                    <div>
                                        <h3 data-admin-message-name class="text-2xl font-bold text-[#4C3326]" style="font-family: 'Sora', sans-serif;">Select a thread</h3>
                                        <p data-admin-message-subtitle class="mt-1 text-sm text-[#8A7A6E]">Choose a conversation from the inbox.</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <span data-admin-message-label class="rounded-full bg-[#F8EADF] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#B46C42]">Inbox</span>
                                    <span class="rounded-full bg-[#F3EEE8] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#7E736B]">Admin reply demo</span>
                                </div>
                            </div>
                        </div>

                        <div data-admin-message-feed class="flex-1 overflow-y-auto bg-[radial-gradient(circle_at_top,#fffaf5_0%,#f8f1e8_58%,#f3e8dd_100%)] px-4 py-5 md:px-6"></div>

                        <div class="shrink-0 border-t border-[#EEE1D7] bg-white px-4 py-4 md:px-6">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end">
                                <label class="block flex-1">
                                    <span class="sr-only">Type a message</span>
                                    <textarea data-admin-message-draft rows="3" placeholder="Write a message to the customer or operations team" class="w-full rounded-[24px] border border-[#E1D5CA] bg-[#FCFAF8] px-5 py-4 text-sm text-[#4F4944] outline-none transition focus:border-transparent focus:ring-2 focus:ring-[#C9876C]"></textarea>
                                </label>

                                <div class="flex items-center gap-3">
                                    <button type="button" class="flex h-12 w-12 items-center justify-center rounded-full border border-[#E2D6CC] bg-white text-[#786D66] transition hover:border-[#D3B6A0] hover:text-[#B36F46]" aria-label="Attach file">
                                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 6.5l-7.78 7.78a3 3 0 104.24 4.24l8.49-8.48a5 5 0 10-7.07-7.07L5.2 12.15" />
                                        </svg>
                                    </button>

                                    <button type="button" data-admin-message-send class="rounded-full bg-[#4A4541] px-6 py-3 font-semibold text-white shadow-md transition hover:bg-[#383431]">Send</button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </section>
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

            <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <label class="relative block w-full lg:max-w-md">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z"></path>
                            </svg>
                        </span>
                        <input data-search-input="notifications" type="search" placeholder="Search notifications by customer, title, or order" class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" data-filter-button="notifications" data-filter-value="all" data-active="true" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                        <button type="button" data-filter-button="notifications" data-filter-value="orders" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Orders</button>
                        <button type="button" data-filter-button="notifications" data-filter-value="payments" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Payments</button>
                        <button type="button" data-filter-button="notifications" data-filter-value="customers" class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Customers</button>
                    </div>
                </div>
            </article>

            <div class="grid gap-4">
                @foreach ($notifications as $notification)
                    <article data-notification-item data-page-item="notifications" data-notification-category="{{ $notification['category'] ?? 'orders' }}" data-notification-title="{{ $notification['title'] ?? '' }}" data-notification-message="{{ $notification['message'] }}" data-notification-customer="{{ $notification['customer_name'] }}" data-notification-order="{{ $notification['order_id'] ?? '' }}" class="soft-panel panel-lift flex flex-col gap-4 rounded-[1.5rem] border border-transparent p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-[#FFF4EB] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B96C3D]">{{ $notification['category_label'] ?? 'Order' }}</span>
                                @if (!empty($notification['contains_custom']))
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Custom</span>
                                @endif
                            </div>
                            <p class="mt-3 text-sm font-medium text-slate-500">{{ $notification['customer_name'] }}</p>
                            <h3 class="mt-1 font-display text-xl font-bold">{{ $notification['title'] ?? 'Admin update' }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $notification['message'] }}</p>
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
                        <p data-inventory-feedback class="mt-2 text-sm font-medium text-rose-600"></p>
                    </div>
                    <button type="button" data-inventory-close class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600">✕</button>
                </div>

                <form data-inventory-form class="mt-6 flex flex-1 flex-col gap-4" data-mode="add" action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data" data-store-url="{{ route('admin.inventory.store') }}" data-update-url-base="{{ url('/admin/inventory') }}">
                    @csrf
                    <input data-inventory-id name="product_id" type="hidden" value="">
                    <input data-inventory-method type="hidden" name="_method" value="" disabled>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Name
                        <input data-inventory-name name="product_name" type="text" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400" placeholder="Product name">
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Description
                        <textarea data-inventory-description name="description" rows="4" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400" placeholder="Short product description"></textarea>
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Price
                        <input data-inventory-price name="price" type="number" step="0.01" min="0" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400" placeholder="e.g. 850 or 16">
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Image upload
                        <input name="image" type="file" accept="image/*" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-slate-400">
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Category
                        <select data-inventory-type name="category" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400">
                            <option value="Bread">Bread</option>
                            <option value="Pastries">Pastries</option>
                            <option value="Cakes">Cakes</option>
                            <option value="Customize">Customize</option>
                        </select>
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Status
                        <select data-inventory-is-active name="is_active" class="rounded-2xl border border-slate-200 px-4 py-3 outline-none focus:border-slate-400">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
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
