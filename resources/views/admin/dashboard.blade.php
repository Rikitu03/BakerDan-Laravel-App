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

        .nav-item[data-active="true"] {
            background: linear-gradient(135deg, rgba(200, 111, 56, 0.16), rgba(255, 255, 255, 0.9));
            color: var(--brand-deep);
            border-color: rgba(200, 111, 56, 0.24);
        }

        .tab-item[data-active="true"] {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            color: #fff;
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
@endphp

<div data-admin-dashboard data-default-section="dashboard" class="page-shell min-h-screen lg:flex">
    <aside class="glass-panel sticky top-0 z-20 flex w-full flex-col gap-6 border-b lg:h-screen lg:w-80 lg:border-b-0 lg:border-r">
        <div class="flex items-center justify-between px-6 pt-6 lg:block lg:px-7">
            <div class="flex items-center gap-3">
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-[#b35c35] to-[#7f4122] text-white shadow-lg shadow-orange-900/20">
                    <span class="font-display text-lg font-bold">B</span>
                </div>
                <div>
                    <p class="font-display text-xl font-bold">BakerDan</p>
                    <p class="text-sm text-slate-500">Admin Control Center</p>
                </div>
            </div>
        </div>

        <nav class="grid gap-2 px-4 lg:px-5">
            @foreach ([['dashboard', 'Dashboard'], ['inventory', 'Inventory'], ['orders', 'Orders'], ['customers', 'Customers'], ['notifications', 'Notifications']] as [$key, $label])
                <button type="button" data-nav="{{ $key }}" class="nav-item flex items-center justify-between rounded-2xl border border-transparent px-4 py-3 text-left text-sm font-semibold text-slate-600 transition hover:border-slate-200 hover:bg-white/70">
                    <span>{{ $label }}</span>
                    <span class="text-xs text-slate-400">View</span>
                </button>
            @endforeach
        </nav>

        <div class="mx-4 mt-auto rounded-3xl dark-pill px-5 py-5 lg:mx-5 lg:mb-5">
            <p class="text-xs uppercase tracking-[0.24em] text-white/60">Today</p>
            <p class="mt-2 font-display text-2xl font-bold">{{ now()->format('M d') }}</p>
            <p class="mt-2 text-sm text-white/70">Bakery operations are live and ready for review.</p>
        </div>
    </aside>

    <main class="flex-1 px-4 py-4 sm:px-6 lg:px-8 lg:py-6">
        <header class="glass-panel mb-6 flex flex-col gap-4 rounded-[2rem] px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Bakery management system</p>
                <h1 class="font-display section-title mt-1 text-3xl font-bold text-slate-900 sm:text-4xl">Modern Admin Dashboard</h1>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600">Active products: {{ count($products) }}</div>
                <div class="rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">Open orders: {{ count($orders) }}</div>
            </div>
        </header>

        <section data-section="dashboard" class="fade-in space-y-6">
            <div class="grid gap-4 xl:grid-cols-4">
                @foreach ($metrics as $metric)
                    <article class="soft-panel rounded-[1.75rem] p-5">
                        <p class="text-sm font-medium text-slate-500">{{ $metric['label'] }}</p>
                        <p class="font-display mt-3 text-3xl font-bold text-slate-900">{{ $metric['value'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $metric['detail'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <article class="soft-panel rounded-[2rem] p-6">
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

                <article class="soft-panel rounded-[2rem] p-6">
                    <p class="text-sm font-medium text-slate-500">Quick actions</p>
                    <h2 class="font-display mt-1 text-2xl font-bold">Admin shortcuts</h2>
                    <div class="mt-5 grid gap-3">
                        <button data-nav="inventory" type="button" class="rounded-2xl bg-slate-900 px-4 py-3 text-left text-sm font-semibold text-white">Manage product catalog</button>
                        <button data-nav="orders" type="button" class="rounded-2xl bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-700">Review active orders</button>
                        <button data-nav="customers" type="button" class="rounded-2xl bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-700">Review customer accounts</button>
                    </div>
                </article>
            </div>
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

            <article class="soft-panel overflow-hidden rounded-[2rem]">
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
                                <tr data-product-row data-product-id="{{ $product['id'] }}" data-product-name="{{ $product['name'] }}" data-product-description="{{ $product['description'] }}" data-product-type="{{ $product['type'] }}" class="align-top">
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
        </section>

        <section data-section="orders" hidden class="fade-in space-y-6">
            <div>
                <p class="text-sm font-medium text-slate-500">Orders section</p>
                <h2 class="font-display section-title mt-1 text-3xl font-bold">Orders in progress</h2>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($orders as $order)
                    <article data-order-card data-order-id="{{ $order['id'] }}" data-order-status="{{ $order['status'] }}" data-payment-status="{{ $order['payment_status'] }}" class="soft-panel rounded-[1.75rem] p-5">
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
                <article class="soft-panel rounded-[2rem] p-4 sm:p-5">
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
                                        <tr data-person-card data-person-id="{{ $person['id'] }}" data-person-role="Customer" data-person-name="{{ $person['name'] }}" data-person-email="{{ $person['email'] }}" data-person-status="{{ $person['status'] }}" class="align-top">
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
                                        <tr data-person-card data-person-id="{{ $person['id'] }}" data-person-role="Admin" data-person-name="{{ $person['name'] }}" data-person-email="{{ $person['email'] }}" data-person-status="{{ $person['status'] }}" class="align-top">
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
                    </div>
                </article>

                <aside data-customer-panel hidden class="soft-panel rounded-[2rem] p-5">
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
                    <article data-notification-item class="soft-panel flex flex-col gap-4 rounded-[1.5rem] p-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $notification['customer_name'] }}</p>
                            <h3 class="mt-1 font-display text-xl font-bold">{{ $notification['message'] }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $notification['date'] }}</p>
                        </div>
                        <button data-remove-notification type="button" class="rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700">Remove</button>
                    </article>
                @endforeach
            </div>
        </section>
    </main>

    <div data-inventory-drawer hidden class="fixed inset-0 z-40 bg-slate-950/35 p-4 backdrop-blur-sm">
        <div class="ml-auto flex h-full max-w-xl items-stretch">
            <div class="soft-panel flex w-full flex-col rounded-[2rem] bg-white p-6">
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
        <div class="soft-panel w-full max-w-md rounded-[2rem] p-6">
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