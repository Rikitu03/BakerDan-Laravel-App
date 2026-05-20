<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BakerDan Admin Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Urbanist:wght@600;700;800;900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/main.js'])
    <style>
        :root {
            --paper: #f8f5f1;
            --surface: #ffffff;
            --line: #ece1d8;
            --ink: #473d36;
            --ink-soft: #8a7e76;
            --brand: #c9876c;
            --brand-deep: #b8765b;
            --olive: #8e7868;
            --success: #2e682e;
            --danger: #c84b31;
            --shadow: 0 18px 40px -24px rgba(71, 61, 54, 0.12);
        }

        .panel-open .hide-on-panel {
            display: none !important;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.8), transparent 30%),
                radial-gradient(circle at 85% 18%, rgba(201, 135, 108, 0.1), transparent 20%),
                linear-gradient(180deg, #fcfbf9 0, #f8f5f1 88px, #f8f5f1 100%);
        }

        .font-display {
            font-family: 'Urbanist', sans-serif;
            font-weight: 800;
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
            background: radial-gradient(circle, rgba(201, 135, 108, 0.12), transparent 65%);
        }

        .page-shell::after {
            width: 22rem;
            height: 22rem;
            left: -9rem;
            bottom: 6rem;
            background: radial-gradient(circle, rgba(184, 118, 91, 0.08), transparent 70%);
        }

        [hidden] {
            display: none !important;
        }

        .glass-panel {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.96), transparent 60%),
                linear-gradient(160deg, rgba(255, 255, 255, 0.98), rgba(250, 248, 245, 0.92));
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
        }

        [data-sidebar] {
            background: linear-gradient(180deg, #2b211a 0%, #1e1611 100%);
            border-color: rgba(201, 135, 108, 0.08) !important;
            box-shadow: 10px 0 30px rgba(0, 0, 0, 0.08);
            transition: width 350ms cubic-bezier(0.4, 0, 0.2, 1), background-color 300ms ease;
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
        }
        [data-sidebar]::-webkit-scrollbar {
            display: none; /* Safari/Chrome */
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
            box-shadow: 0 28px 54px -38px rgba(71, 61, 54, 0.25);
            border-color: rgba(201, 135, 108, 0.3);
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
            background: linear-gradient(120deg, rgba(201, 135, 108, 0.1), transparent 55%);
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
            background: linear-gradient(135deg, rgba(184, 118, 91, 0.95), rgba(71, 61, 54, 0.92));
            color: #fff;
            border: 1px solid rgba(201, 135, 108, 0.3);
            box-shadow: 0 12px 28px -12px rgba(184, 118, 91, 0.45);
        }

        .brand-logo-wrap {
            height: 3.5rem;
            width: 3.5rem;
            border-radius: 1.2rem;
            overflow: hidden;
            border: 2px solid rgba(201, 135, 108, 0.2);
            box-shadow: 0 8px 24px -12px rgba(184, 118, 91, 0.25);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 245, 241, 0.95));
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 200ms ease;
        }

        .brand-logo-wrap:hover {
            border-color: rgba(201, 135, 108, 0.4);
            box-shadow: 0 12px 28px -8px rgba(184, 118, 91, 0.35);
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
            border: 1.5px solid rgba(201, 135, 108, 0.3);
            background: linear-gradient(135deg, rgba(201, 135, 108, 0.1), rgba(255, 255, 255, 0.15));
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
            box-shadow: 0 12px 24px -12px rgba(184, 118, 91, 0.4);
            background: linear-gradient(135deg, rgba(201, 135, 108, 0.2), rgba(255, 255, 255, 0.22));
            border-color: rgba(201, 135, 108, 0.5);
            color: rgba(255, 255, 255, 1);
        }

        .logout-button:active {
            transform: translateY(-1px);
        }

        .nav-item[data-active="true"] {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
            color: #fff;
            border-color: var(--brand);
            box-shadow: 0 8px 16px -4px rgba(184, 118, 91, 0.35);
        }

        .nav-item {
            position: relative;
            transition: all 200ms ease;
            cursor: pointer;
        }

        .nav-item:hover:not([data-active="true"]) {
            background: linear-gradient(135deg, rgba(201, 135, 108, 0.1), rgba(255, 255, 255, 0.95));
            border-color: rgba(201, 135, 108, 0.3);
            transform: translateX(2px);
            box-shadow: 0 4px 12px -4px rgba(184, 118, 91, 0.15);
        }

        .nav-item:active {
            transform: translateX(0px);
        }

        [data-nav-count] {
            background: linear-gradient(135deg, rgba(201, 135, 108, 0.12), rgba(255, 255, 255, 0.88)) !important;
            border: 1px solid rgba(201, 135, 108, 0.15);
            color: var(--brand-deep) !important;
            font-weight: 700;
            transition: all 150ms ease;
        }

        .nav-item:hover [data-nav-count]:not([data-active="true"]) {
            background: linear-gradient(135deg, rgba(201, 135, 108, 0.18), rgba(255, 255, 255, 0.95)) !important;
            border-color: rgba(201, 135, 108, 0.25);
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
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.85), transparent 30%),
                linear-gradient(135deg, rgba(71, 61, 54, 0.98), rgba(142, 120, 104, 0.96), rgba(201, 135, 108, 0.9));
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
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.6), rgba(248, 245, 241, 0.65));
            border: 1px solid var(--line);
        }

        .message-thread-button[data-active="true"] {
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 18px 36px -30px rgba(122, 82, 54, 0.48);
            border-color: rgba(234, 218, 205, 1);
        }

        @media (min-width: 1024px) {
            [data-admin-dashboard].is-sidebar-compact [data-sidebar] {
                width: 6.2rem;
            }
            [data-admin-dashboard].is-sidebar-compact [data-sidebar-text],
            [data-admin-dashboard].is-sidebar-compact [data-sidebar-footer] > div:first-child {
                opacity: 0;
                pointer-events: none;
                max-width: 0;
                overflow: hidden;
                margin: 0 !important;
                padding: 0 !important;
                transform: scale(0.9);
            }
            [data-admin-dashboard].is-sidebar-compact .nav-item {
                justify-content: center;
                padding: 0.85rem 0;
                width: 3.2rem;
                margin: 0 auto;
                gap: 0;
            }
            [data-admin-dashboard].is-sidebar-compact .nav-item-label {
                display: none;
            }
            
            /* CSS Tooltips for Compact Sidebar Items */
            [data-admin-dashboard].is-sidebar-compact .nav-item::after {
                content: attr(data-tooltip);
                position: absolute;
                left: 100%;
                margin-left: 1.25rem;
                top: 50%;
                transform: translateY(-50%) translateX(-10px);
                background: #1e1611;
                border: 1px solid rgba(201, 135, 108, 0.15);
                color: #fff;
                font-size: 0.75rem;
                font-weight: 600;
                padding: 0.4rem 0.8rem;
                border-radius: 0.75rem;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
                transition: opacity 200ms ease, transform 200ms ease;
                z-index: 50;
            }
            [data-admin-dashboard].is-sidebar-compact .nav-item:hover::after {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
        }

        /* Sidebar Logo Header */
        .brand-logo-wrap {
            border: 2px solid rgba(201, 135, 108, 0.25);
            padding: 2px;
            border-radius: 999px;
            transition: border-color 300ms ease, transform 300ms ease;
        }
        .brand-logo-wrap:hover {
            border-color: #c9876c;
            transform: rotate(5deg) scale(1.05);
        }

        /* Sidebar Compact Toggle Button */
        [data-sidebar-compact] {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: rgba(255, 255, 255, 0.6);
            transition: all 250ms ease;
        }
        [data-sidebar-compact]:hover {
            background: rgba(201, 135, 108, 0.15);
            border-color: rgba(201, 135, 108, 0.3);
            color: #fff;
            transform: scale(1.05);
        }
        [data-admin-dashboard].is-sidebar-compact [data-sidebar-compact] svg {
            transform: rotate(180deg);
        }
        [data-sidebar-compact] svg {
            transition: transform 300ms ease;
        }

        /* Sidebar text transitions */
        [data-sidebar-text], [data-sidebar-footer] {
            transition: opacity 250ms ease, max-width 300ms ease, transform 250ms ease;
        }

        /* Navigation items */
        .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.85rem 1.15rem;
            border-radius: 1.25rem;
            color: rgba(248, 244, 240, 0.65) !important;
            border: 1px solid transparent;
            background: transparent;
            transition: all 250ms cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: left center;
        }
        .nav-item svg {
            color: rgba(201, 135, 108, 0.65);
            transition: color 250ms ease, transform 250ms ease;
        }
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.05);
            color: #fff !important;
            transform: translateX(3px);
        }
        .nav-item:hover svg {
            color: #c9876c;
            transform: scale(1.1);
        }

        /* Active Navigation Item */
        .nav-item[data-active="true"] {
            background: #c9876c !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            box-shadow: 0 8px 20px -6px rgba(201, 135, 108, 0.4);
        }
        .nav-item[data-active="true"] svg {
            color: #fff !important;
        }

        /* Active indicator glow bar */
        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 25%;
            height: 50%;
            width: 3px;
            background: #c9876c;
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 250ms ease, height 250ms ease;
        }
        .nav-item[data-active="true"]::before {
            opacity: 1;
            height: 50%;
            background: #fff;
        }

        /* Navigation counter badge floated on the icon */
        [data-nav-count] {
            background: linear-gradient(135deg, #c9876c 0%, #b8765b 100%);
            border: 1.5px solid #2b211a !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4), 0 0 0 2px rgba(201, 135, 108, 0.15);
            transition: all 250ms cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 10;
        }

        .nav-item:hover [data-nav-count] {
            background: linear-gradient(135deg, #ffffff 0%, #ece1d8 100%);
            border-color: #1e1611 !important;
            color: #2b211a !important;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.25), 0 0 0 3px rgba(255, 255, 255, 0.1);
            transform: scale(1.15) translate(1px, -1px);
        }

        .nav-item[data-active="true"] [data-nav-count] {
            background: #ffffff !important;
            border-color: #c9876c !important;
            color: #c9876c !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        /* Today card inside premium dark sidebar */
        .dark-pill {
            background: linear-gradient(135deg, rgba(201, 135, 108, 0.1) 0%, rgba(184, 118, 91, 0.05) 100%);
            border: 1px solid rgba(201, 135, 108, 0.15);
            transition: all 300ms ease;
        }
        .dark-pill:hover {
            border-color: rgba(201, 135, 108, 0.35);
            transform: translateY(-2px);
        }

        /* Logout button inside dark sidebar */
        .logout-card {
            margin-top: 1rem;
        }
        .logout-button {
            position: relative;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem;
            font-size: 0.85rem;
            font-weight: 700;
            color: rgba(244, 63, 94, 0.8);
            background: rgba(244, 63, 94, 0.05);
            border: 1px solid rgba(244, 63, 94, 0.15);
            border-radius: 1.25rem;
            transition: all 250ms ease;
            cursor: pointer;
        }
        .logout-button:hover {
            color: #fff;
            background: rgb(244, 63, 94);
            border-color: transparent;
            box-shadow: 0 8px 20px -6px rgba(244, 63, 94, 0.4);
            transform: scale(0.98);
        }
        .logout-button .logout-icon {
            display: none;
        }

        @media (min-width: 1024px) {
            /* When compact, morph the logout button into a perfect circular icon button! */
            [data-admin-dashboard].is-sidebar-compact .logout-button {
                width: 3.2rem;
                height: 3.2rem;
                padding: 0;
                border-radius: 999px;
                margin: 0 auto;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            [data-admin-dashboard].is-sidebar-compact .logout-button .logout-text {
                display: none;
            }
            [data-admin-dashboard].is-sidebar-compact .logout-button .logout-icon {
                display: block;
            }
            [data-admin-dashboard].is-sidebar-compact .logout-card {
                display: flex;
                justify-content: center;
                margin-top: 0.5rem;
            }
            
            /* Add standard compact tooltip to compact logout button */
            [data-admin-dashboard].is-sidebar-compact .logout-button::after {
                content: attr(data-tooltip);
                position: absolute;
                left: 100%;
                margin-left: 1.25rem;
                top: 50%;
                transform: translateY(-50%) translateX(-10px);
                background: #1e1611;
                border: 1px solid rgba(244, 63, 94, 0.3);
                color: #ff8595;
                font-size: 0.75rem;
                font-weight: 600;
                padding: 0.4rem 0.8rem;
                border-radius: 0.75rem;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
                transition: opacity 200ms ease, transform 200ms ease;
                z-index: 50;
            }
            [data-admin-dashboard].is-sidebar-compact .logout-button:hover::after {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
        }

        .tab-item[data-active="true"] {
            background: #2b211a !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px -8px rgba(43, 33, 26, 0.35) !important;
        }

        .filter-chip[data-active="true"] {
            background: #2b211a !important;
            color: #ffffff !important;
            box-shadow: 0 8px 20px -8px rgba(43, 33, 26, 0.35) !important;
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

        [data-section="dashboard"]>.grid>.soft-panel {
            border-top: 3px solid rgba(201, 135, 108, 0.38);
        }

        .chart-surface {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(248, 245, 241, 0.65));
            border: 1px dashed rgba(201, 135, 108, 0.28);
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

        [data-admin-dashboard] .text-slate-900 {
            color: var(--ink);
        }

        [data-admin-dashboard] .text-slate-600,
        [data-admin-dashboard] .text-slate-500,
        [data-admin-dashboard] .text-slate-700 {
            color: var(--ink-soft);
        }

        [data-admin-dashboard] .bg-slate-50 {
            background: rgba(255, 255, 255, 0.65);
        }

        [data-admin-dashboard] .bg-slate-100 {
            background: rgba(255, 250, 241, 0.88);
        }

        [data-admin-dashboard] .divide-slate-100,
        [data-admin-dashboard] .divide-slate-200 {
            border-color: var(--line);
        }

        [data-admin-dashboard] .ring-slate-200 {
            --tw-ring-color: var(--line);
        }

        [data-admin-dashboard] .bg-slate-900 {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
        }

        [data-admin-dashboard] .shadow-slate-900\/10,
        [data-admin-dashboard] .shadow-orange-900\/20 {
            --tw-shadow-color: rgba(184, 118, 91, 0.24);
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
        $promos = $promos ?? [];
        $orders = $orders ?? [];
        $customers = $customers ?? [];
        $admins = $admins ?? [];
        $notifications = $notifications ?? [];
        $messages = $messages ?? [];
        $weeklyCompletions = $weeklyCompletions ?? [];
        $monthlyCompletions = $monthlyCompletions ?? [];
        $yearlyCompletions = $yearlyCompletions ?? [];
        $productSales = $productSales ?? [];
        $usersWeekly = $usersWeekly ?? [];
        $usersMonthly = $usersMonthly ?? [];
        $usersYearly = $usersYearly ?? [];
        $ordersWeekly = $ordersWeekly ?? [];
        $ordersMonthly = $ordersMonthly ?? [];
        $ordersYearly = $ordersYearly ?? [];
        $revenueWeekly = $revenueWeekly ?? [];
        $revenueMonthly = $revenueMonthly ?? [];
        $revenueYearly = $revenueYearly ?? [];
        $productTypeBreakdown = $productTypeBreakdown ?? [];
        $reportPayload = $reportPayload ?? [
            'metrics' => $metrics,
            'products' => $products,
            'promos' => $promos,
            'orders' => $orders,
            'customers' => $customers,
            'admins' => $admins,
            'notifications' => $notifications,
            'messages' => $messages,
            'weeklyCompletions' => $weeklyCompletions,
            'monthlyCompletions' => $monthlyCompletions,
            'yearlyCompletions' => $yearlyCompletions,
            'productSales' => $productSales,
            'usersWeekly' => $usersWeekly,
            'usersMonthly' => $usersMonthly,
            'usersYearly' => $usersYearly,
            'ordersWeekly' => $ordersWeekly,
            'ordersMonthly' => $ordersMonthly,
            'ordersYearly' => $ordersYearly,
            'revenueWeekly' => $revenueWeekly,
            'revenueMonthly' => $revenueMonthly,
            'revenueYearly' => $revenueYearly,
            'productTypeBreakdown' => $productTypeBreakdown,
        ];
        $reportSummary = $reportPayload['summary'] ?? [];
        $topProductRows = collect($reportPayload['productSales'] ?? [])->sortByDesc('quantity_sold')->take(8)->values();
        $usersWeeklyTotal = collect($reportPayload['usersWeekly'] ?? [])->sum('value');
        $usersMonthlyCurrent = data_get(collect($reportPayload['usersMonthly'] ?? [])->last(), 'value', 0);
        $usersYearlyCurrent = data_get(collect($reportPayload['usersYearly'] ?? [])->last(), 'value', 0);
        $ordersWeeklyTotal = collect($reportPayload['ordersWeekly'] ?? [])->sum('value');
        $ordersMonthlyCurrent = data_get(collect($reportPayload['ordersMonthly'] ?? [])->last(), 'value', 0);
        $ordersYearlyCurrent = data_get(collect($reportPayload['ordersYearly'] ?? [])->last(), 'value', 0);
        $revenueWeeklyTotal = collect($reportPayload['revenueWeekly'] ?? [])->sum('value');
        $revenueMonthlyCurrent = data_get(collect($reportPayload['revenueMonthly'] ?? [])->last(), 'value', 0);
        $revenueYearlyCurrent = data_get(collect($reportPayload['revenueYearly'] ?? [])->last(), 'value', 0);
        $sidebarCounts = $sidebarCounts ?? [
            'dashboard' => '•',
            'inventory' => count($products),
            'promos' => count($promos),
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

    <div data-admin-dashboard data-default-section="{{ $defaultSection }}" data-open-modal="{{ $modalToOpen }}"
        class="page-shell min-h-screen lg:flex lg:h-screen lg:overflow-hidden">
        <aside data-sidebar
            class="sticky top-0 z-20 flex w-full flex-col gap-6 lg:h-screen lg:w-80 lg:shrink-0 lg:self-start border-b lg:border-b-0 lg:border-r border-white/5">
            <div class="flex items-center justify-between px-6 pt-6 lg:block lg:px-7">
                <div class="flex items-center gap-3">
                    <div class="brand-logo-wrap overflow-hidden h-10 w-10 shrink-0">
                        <img src="{{ asset('images/logo/BAKERDAN LOGO.jpg') }}" alt="BakerDan logo" class="h-full w-full object-cover rounded-full">
                    </div>
                    <div data-sidebar-text>
                        <p class="font-display text-xl font-bold text-white">BakerDan</p>
                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Admin Center</p>
                    </div>
                </div>
                <button type="button" data-sidebar-compact
                    class="hidden lg:grid h-9 w-9 place-items-center rounded-full bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white transition-all shadow-md mt-4 lg:mt-6 border border-white/10"
                    title="Toggle sidebar size" aria-label="Toggle sidebar size">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                        <path d="M9 5v14"></path>
                    </svg>
                </button>
            </div>

            <nav class="grid gap-2 px-4 lg:px-5">
                @foreach ([
                    ['dashboard', 'Dashboard', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"></rect><rect x="14" y="3" width="7" height="5" rx="1"></rect><rect x="14" y="12" width="7" height="9" rx="1"></rect><rect x="3" y="16" width="7" height="5" rx="1"></rect></svg>'],
                    ['inventory', 'Inventory', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>'],
                    ['promos', 'Promos & Discounts', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82zM7 7h.01"></path></svg>'],
                    ['orders', 'Orders', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0"></path></svg>'],
                    ['customers', 'Customers', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100-8 4 4 0 000 8zm14 14v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"></path></svg>'],
                    ['notifications', 'Notifications', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"></path></svg>'],
                    ['messages', 'Messages', '<svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path></svg>']
                ] as [$key, $label, $iconSvg])
                    <button type="button" data-nav="{{ $key }}" data-tooltip="{{ $label }}"
                        class="nav-item flex items-center rounded-2xl border border-transparent px-4 py-3 text-left text-sm font-semibold transition">
                        <div class="flex items-center gap-3.5">
                            <div class="relative flex items-center justify-center shrink-0">
                                {!! $iconSvg !!}
                                @php
                                    $cnt = $sidebarCounts[$key] ?? 0;
                                @endphp
                                @if($cnt > 0)
                                    <span data-nav-count="{{ $key }}"
                                        class="absolute -top-1.5 -right-2 flex h-4 min-w-[1rem] items-center justify-center rounded-full text-[8px] font-extrabold px-1">
                                        {{ $cnt }}
                                    </span>
                                @endif
                            </div>
                            <span class="nav-item-label" data-sidebar-text>{{ $label }}</span>
                        </div>
                    </button>
                @endforeach
            </nav>

            <div class="mx-4 mt-auto space-y-3 lg:mx-5 lg:mb-5" data-sidebar-footer>
                <div class="rounded-3xl dark-pill px-5 py-5">
                    <p class="text-xs uppercase tracking-[0.24em] text-white/50">Today</p>
                    <p class="mt-2 font-display text-2xl font-bold text-[#e9bc9f]">{{ now()->format('M d') }}</p>
                    <p class="mt-2 text-sm text-slate-300">Bakery operations are live and ready.</p>
                </div>

                <div class="logout-card">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-button" data-tooltip="Log Out">
                            <span class="logout-text" data-sidebar-text>Log Out</span>
                            <span class="logout-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"></path>
                                </svg>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="relative z-10 flex-1 px-4 py-4 sm:px-6 lg:h-screen lg:overflow-y-auto lg:px-8 lg:py-6">
            <header
                class="hero-panel panel-lift mb-6 flex flex-col gap-5 rounded-[2rem] px-5 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-white/65">Admin operations</p>
                    <h1 class="font-display section-title mt-2 text-3xl font-bold text-white sm:text-4xl">Bakerdan
                        Command Center</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/76">Track orders, customer activity, inbox
                        requests, and bakery alerts from one warmer and more focused workspace.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div data-current-section-label
                        class="rounded-full bg-white/18 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/18">
                        Dashboard</div>
                    <div
                        class="rounded-full bg-white/12 px-4 py-2 text-sm font-medium text-white/86 ring-1 ring-white/16">
                        Active products: {{ count($products) }}</div>
                    <div
                        class="rounded-full bg-emerald-400/18 px-4 py-2 text-sm font-medium text-emerald-50 ring-1 ring-emerald-200/18">
                        Open orders: {{ count($orders) }}</div>
                    <div
                        class="rounded-full bg-amber-300/18 px-4 py-2 text-sm font-medium text-amber-50 ring-1 ring-amber-100/20">
                        Unread inbox: {{ collect($messages)->where('unread', true)->count() }}</div>
                </div>
            </header>

            @if (session('status'))
                <div
                    class="mb-6 rounded-[1.5rem] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div id="walkin-order-modal" hidden
                class="fixed inset-0 z-40 flex items-center justify-center bg-[#473D36]/25 p-4 backdrop-blur-md">
                <div class="w-full max-w-2xl animate-fade-in">
                    <div class="soft-panel panel-lift max-h-[90vh] w-full overflow-y-auto rounded-[2.5rem] !bg-white p-6 shadow-2xl md:p-8"
                        style="background-color: #ffffff !important;">
                        <!-- Modal Header -->
                        <div class="flex items-start justify-between gap-4 border-b border-[#ece1d8] pb-5">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Counter Terminal</p>
                                <h3 class="font-display mt-1 text-2xl font-bold text-slate-850">New Walk-in Order</h3>
                                <p class="mt-1 text-sm text-slate-500">Build a counter order using live inventory items and save it directly to the order queue.</p>
                            </div>
                            <button type="button" data-close-walkin-order
                                class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">✕</button>
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

                        <form id="walkin-order-form" method="POST" action="{{ route('admin.orders.walkin') }}"
                            class="mt-6 flex flex-col gap-6">
                            @csrf
                            <input type="hidden" name="_admin_section" value="orders">

                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="grid gap-2 text-sm font-medium text-slate-700">
                                    Customer name
                                    <input type="text" name="customer_name" value="{{ old('customer_name') }}"
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all"
                                        placeholder="Walk-in Customer">
                                </label>
                                <label class="grid gap-2 text-sm font-medium text-slate-700">
                                    Registered customer
                                    <select name="linked_customer_user_id"
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all">
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
                                    <select name="payment_status"
                                        class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all">
                                        <option value="unpaid" @selected(old('payment_status', 'unpaid') === 'unpaid')>Unpaid</option>
                                        <option value="paid" @selected(old('payment_status') === 'paid')>Paid</option>
                                    </select>
                                </label>
                                <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4 text-xs leading-relaxed text-slate-500 flex items-center">
                                    Link a registered customer if you want the walk-in order to appear in their in-app notifications queue.
                                </div>
                            </div>

                            <div class="border-t border-[#ece1d8] pt-5">
                                <div class="flex items-center justify-between gap-4 mb-4">
                                    <div>
                                        <h4 class="text-base font-bold text-slate-800">Order Items</h4>
                                        <p class="text-xs text-slate-500">Add one or more catalog products with their quantities.</p>
                                    </div>
                                    <button type="button" data-add-walkin-item
                                        class="rounded-full bg-slate-100 hover:bg-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 active:scale-95 transition-all">+ Add Item</button>
                                </div>
                                <div data-walkin-items class="space-y-3">
                                    @foreach ($walkinItems as $index => $item)
                                        <div data-walkin-item
                                            class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-[minmax(0,1fr)_120px_auto]">
                                            <label class="grid gap-2 text-sm font-medium text-slate-700">
                                                Product
                                                <select name="items[{{ $index }}][product_id]"
                                                    class="w-full max-w-xs rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all">
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
                                                <input type="number" min="1" max="999" name="items[{{ $index }}][quantity]"
                                                    value="{{ $item['quantity'] ?? 1 }}"
                                                    class="rounded-2xl border border-slate-200 bg-white px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all">
                                            </label>
                                            <div class="flex items-end">
                                                <button type="button" data-remove-walkin-item
                                                    class="w-full rounded-full bg-white hover:bg-slate-100 border border-slate-200 px-4 py-3 text-sm font-semibold text-rose-600 active:scale-95 transition-all">✕ Remove</button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <label class="grid gap-2 text-sm font-medium text-slate-700">
                                Notes
                                <textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all"
                                    placeholder="Optional counter notes or customer instructions">{{ old('notes') }}</textarea>
                            </label>

                            <div class="flex flex-wrap gap-3 pt-4 border-t border-[#ece1d8]">
                                <button type="submit"
                                    class="rounded-full bg-emerald-700 hover:bg-emerald-800 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-700/20 active:scale-95 transition-all">Create Walk-in Order</button>
                                <button type="button" data-close-walkin-order
                                    class="rounded-full border border-slate-200 hover:bg-slate-50 px-6 py-3.5 text-sm font-semibold text-slate-700 active:scale-95 transition-all">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <template data-walkin-item-template>
                <div data-walkin-item
                    class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-[minmax(0,1fr)_120px_auto]">
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Product
                        <select data-walkin-product
                            class="w-full max-w-xs rounded-2xl border border-slate-200 bg-white px-4 py-3"></select>
                    </label>
                    <label class="grid gap-2 text-sm font-medium text-slate-700">
                        Quantity
                        <input data-walkin-quantity type="number" min="1" max="999" value="1"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                    </label>
                    <div class="flex items-end">
                        <button type="button" data-remove-walkin-item
                            class="w-full rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-700">Remove</button>
                    </div>
                </div>
            </template>

            <div id="bulk-upload-modal" hidden
                class="fixed inset-0 z-40 flex items-center justify-center bg-[#473D36]/25 p-4 backdrop-blur-md">
                <div class="w-full max-w-xl animate-fade-in">
                    <div class="soft-panel panel-lift max-h-[90vh] w-full overflow-y-auto rounded-[2.5rem] !bg-white p-6 shadow-2xl md:p-8"
                        style="background-color: #ffffff !important;">
                        <!-- Modal Header -->
                        <div class="flex items-start justify-between gap-4 border-b border-[#ece1d8] pb-5">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Bulk Import</p>
                                <h3 class="font-display mt-1 text-2xl font-bold text-slate-850">Bulk Product Upload</h3>
                                <p class="mt-1 text-sm text-slate-500">Import inventory rows from CSV using the bakery catalog fields already used in the app.</p>
                            </div>
                            <button type="button" data-close-bulk-upload
                                class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">✕</button>
                        </div>

                        @if ($errors->bulkUpload->any())
                            <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                @foreach ($errors->bulkUpload->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form id="bulk-upload-form" method="POST" action="{{ route('admin.inventory.bulk') }}"
                            enctype="multipart/form-data" class="mt-6 flex flex-col gap-6">
                            @csrf
                            <input type="hidden" name="_admin_section" value="inventory">
                            
                            <label class="grid gap-2 text-sm font-medium text-slate-700">
                                CSV File
                                <input type="file" name="csv" accept=".csv,text/csv"
                                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 focus:outline-none focus:border-[#c9876c] focus:ring-2 focus:ring-[#c9876c]/20 transition-all">
                            </label>

                            <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-4 text-xs leading-relaxed text-slate-600 space-y-1">
                                <p class="font-semibold text-slate-800 text-sm mb-1.5">Template Format Instructions</p>
                                <p>• <span class="font-semibold">Required Columns</span>: <code class="bg-slate-200/60 px-1 rounded">name</code> (or <code class="bg-slate-200/60 px-1 rounded">product_name</code>), <code class="bg-slate-200/60 px-1 rounded">price</code>, <code class="bg-slate-200/60 px-1 rounded">category</code>.</p>
                                <p>• <span class="font-semibold">Optional Columns</span>: <code class="bg-slate-200/60 px-1 rounded">description</code>, <code class="bg-slate-200/60 px-1 rounded">image_url</code>, <code class="bg-slate-200/60 px-1 rounded">is_active</code>.</p>
                            </div>

                            <div class="flex flex-wrap gap-3 pt-2 border-t border-[#ece1d8]">
                                <button type="submit"
                                    class="rounded-full bg-emerald-700 hover:bg-emerald-800 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-700/20 active:scale-95 transition-all">Upload Products</button>
                                <button type="button" data-download-bulk-template
                                    class="rounded-full bg-slate-100 hover:bg-slate-200 px-6 py-3.5 text-sm font-semibold text-slate-700 active:scale-95 transition-all">Download Template</button>
                                <button type="button" data-close-bulk-upload
                                    class="rounded-full border border-slate-200 hover:bg-slate-50 px-6 py-3.5 text-sm font-semibold text-slate-700 active:scale-95 transition-all">Cancel</button>
                            </div>
                        </form>
                    </div>
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
                            <span
                                class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Live</span>
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
                            <button data-nav="inventory" type="button"
                                class="rounded-2xl bg-slate-900 px-4 py-3 text-left text-sm font-semibold text-white">Manage
                                product catalog</button>
                            <button data-nav="orders" type="button"
                                class="rounded-2xl bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-700">Review
                                active orders</button>
                            <button data-nav="customers" type="button"
                                class="rounded-2xl bg-slate-100 px-4 py-3 text-left text-sm font-semibold text-slate-700">Review
                                customer accounts</button>
                            <button data-nav="messages" type="button"
                                class="rounded-2xl bg-[#FFF4EB] px-4 py-3 text-left text-sm font-semibold text-[#8F4723]">Open
                                admin inbox</button>
                        </div>
                    </article>
                </div>

                <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                    <article class="soft-panel panel-lift rounded-[2rem] p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p data-report-kicker class="text-sm font-medium text-slate-500">Weekly report graph</p>
                                <h3 data-report-title class="font-display mt-1 text-2xl font-bold">Completed orders
                                    trend</h3>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <label for="report-range" class="text-sm font-medium text-slate-600">Range</label>
                                <select id="report-range" data-report-range
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                <span data-report-badge
                                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">7
                                    days</span>
                            </div>
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
                            <div data-line-labels class="mt-2 grid text-center text-xs font-semibold text-slate-500">
                            </div>
                        </div>
                    </article>

                    <article class="soft-panel panel-lift rounded-[2rem] p-6">
                        <p class="text-sm font-medium text-slate-500">Product mix graph</p>
                        <h3 class="font-display mt-1 text-2xl font-bold">Category distribution</h3>
                        <div data-type-bars class="mt-5 space-y-4"></div>
                    </article>
                </div>

                <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <article class="soft-panel panel-lift rounded-[2rem] p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Report summary</p>
                                <h3 class="font-display mt-1 text-2xl font-bold">Sales and activity overview</h3>
                            </div>
                            <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                                {{ number_format((float) ($reportSummary['total_items_sold'] ?? 0)) }} items sold
                            </span>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Products sold</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format((float) ($reportSummary['total_items_sold'] ?? 0)) }}</p>
                                <p class="mt-1 text-sm text-slate-500">All ordered item quantities</p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Total revenue</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">PHP {{ number_format((float) ($reportSummary['total_revenue'] ?? 0), 2) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Paid orders only</p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Total orders</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format((float) ($reportSummary['total_orders'] ?? 0)) }}</p>
                                <p class="mt-1 text-sm text-slate-500">All recorded orders</p>
                            </div>
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Total users</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format((float) ($reportSummary['total_customers'] ?? 0)) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Registered customers</p>
                            </div>
                        </div>

                        <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200">
                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-sm font-semibold text-slate-700">Users, orders, and revenue snapshot</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-white text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold">Metric</th>
                                            <th class="px-4 py-3 text-left font-semibold">Weekly</th>
                                            <th class="px-4 py-3 text-left font-semibold">Monthly</th>
                                            <th class="px-4 py-3 text-left font-semibold">Yearly</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                        <tr>
                                            <td class="px-4 py-3 font-semibold">Users</td>
                                            <td class="px-4 py-3">{{ number_format((float) $usersWeeklyTotal) }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $usersMonthlyCurrent) }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $usersYearlyCurrent) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-semibold">Orders</td>
                                            <td class="px-4 py-3">{{ number_format((float) $ordersWeeklyTotal) }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $ordersMonthlyCurrent) }}</td>
                                            <td class="px-4 py-3">{{ number_format((float) $ordersYearlyCurrent) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-semibold">Revenue</td>
                                            <td class="px-4 py-3">PHP {{ number_format((float) $revenueWeeklyTotal, 2) }}</td>
                                            <td class="px-4 py-3">PHP {{ number_format((float) $revenueMonthlyCurrent, 2) }}</td>
                                            <td class="px-4 py-3">PHP {{ number_format((float) $revenueYearlyCurrent, 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>

                    <article class="soft-panel panel-lift rounded-[2rem] p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Product sales report</p>
                                <h3 class="font-display mt-1 text-2xl font-bold">Top products and buyers</h3>
                            </div>
                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                {{ $topProductRows->count() }} products
                            </span>
                        </div>

                        <div class="mt-5 overflow-hidden rounded-3xl border border-slate-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="bg-slate-50 text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold">Product</th>
                                            <th class="px-4 py-3 text-left font-semibold">Qty Sold</th>
                                            <th class="px-4 py-3 text-left font-semibold">Buyers</th>
                                            <th class="px-4 py-3 text-left font-semibold">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                                        @forelse ($topProductRows as $productRow)
                                            <tr>
                                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $productRow['name'] ?? 'Unknown product' }}</td>
                                                <td class="px-4 py-3">{{ number_format((float) ($productRow['quantity_sold'] ?? 0)) }}</td>
                                                <td class="px-4 py-3">{{ number_format((float) ($productRow['buyers_count'] ?? 0)) }}</td>
                                                <td class="px-4 py-3">PHP {{ number_format((float) ($productRow['revenue'] ?? 0), 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-slate-500">No product sales data available yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>
                </div>

                <article class="soft-panel panel-lift rounded-[2rem] p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Report export center</p>
                            <h3 class="font-display mt-1 text-2xl font-bold">Generate report data</h3>
                            <p class="mt-2 max-w-2xl text-sm text-slate-500">Download bakery report data in formatted Excel
                                or PDF format for reporting, sharing, and archival.</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <label for="export-target" class="text-sm font-medium text-slate-600">Dataset</label>
                        <select id="export-target" data-export-target
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600">
                            <option value="summary">Summary</option>
                            <option value="weeklyCompletions">Weekly report</option>
                            <option value="monthlyCompletions">Monthly report</option>
                            <option value="yearlyCompletions">Yearly report</option>
                            <option value="productSales">Product sales</option>
                            <option value="usersWeekly">User signups (weekly)</option>
                            <option value="usersMonthly">User signups (monthly)</option>
                            <option value="usersYearly">User signups (yearly)</option>
                            <option value="ordersWeekly">Orders (weekly)</option>
                            <option value="ordersMonthly">Orders (monthly)</option>
                            <option value="ordersYearly">Orders (yearly)</option>
                            <option value="revenueWeekly">Revenue (weekly)</option>
                            <option value="revenueMonthly">Revenue (monthly)</option>
                            <option value="revenueYearly">Revenue (yearly)</option>
                            <option value="products">Products</option>
                            <option value="orders">Orders</option>
                            <option value="customers">Customers</option>
                            <option value="admins">Admins</option>
                            <option value="notifications">Notifications</option>
                        </select>
                        <button type="button" data-export-format="excel"
                            class="export-button rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Export
                            Excel</button>
                        <button type="button" data-export-format="pdf"
                            class="export-button rounded-full bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700">Export
                            PDF</button>
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
                        <button type="button" data-open-add-product
                            class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10">+
                            Add Product</button>
                        <button type="button" data-open-bulk-upload
                            class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/10">Bulk
                            Upload</button>
                    </div>
                </div>

                <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <label class="relative block w-full lg:max-w-md">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z">
                                    </path>
                                </svg>
                            </span>
                            <input data-search-input="inventory" type="search"
                                placeholder="Search by product name, description, or category"
                                class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-filter-button="inventory" data-filter-value="all"
                                data-active="true"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                            <button type="button" data-filter-button="inventory" data-filter-value="Bread"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Bread</button>
                            <button type="button" data-filter-button="inventory" data-filter-value="Pastries"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Pastries</button>
                            <button type="button" data-filter-button="inventory" data-filter-value="Brazos and Cakes"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Brazos and Cakes</button>
                            <button type="button" data-filter-button="inventory" data-filter-value="Tarts"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Tarts</button>
                            <button type="button" data-filter-button="inventory" data-filter-value="Sugar Cookies"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Sugar Cookies</button>
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
                                    <tr data-product-row data-page-item="inventory" data-product-id="{{ $product['id'] }}"
                                        data-product-name="{{ $product['name'] }}"
                                        data-product-description="{{ $product['description'] }}"
                                        data-product-price="{{ $product['price'] }}"
                                        data-product-category="{{ $product['category'] }}"
                                        data-product-image-url="{{ $product['image_url'] ?? '' }}"
                                        data-product-is-active="{{ !empty($product['is_active']) ? 1 : 0 }}"
                                        class="align-top">
                                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $product['name'] }}</td>
                                        <td class="px-5 py-4 text-slate-600">{{ $product['description'] }}</td>
                                        <td class="px-5 py-4 font-semibold text-slate-900">
                                            {{ $product['formatted_price'] ?? $product['price'] }}
                                        </td>
                                        <td class="px-5 py-4"><span
                                                class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">{{ $product['category'] }}</span>
                                        </td>
                                        <td class="px-5 py-4">
                                            @if (!empty($product['image_url']))
                                                <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}"
                                                    class="h-14 w-14 rounded-2xl object-cover ring-1 ring-slate-200">
                                            @else
                                                <div
                                                    class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-xs font-semibold text-slate-500">
                                                    IMG</div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" data-edit-product
                                                    class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Edit</button>
                                                <button type="button" data-remove-product
                                                    class="rounded-full bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Remove</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div data-inventory-empty hidden
                        class="border-t border-dashed border-slate-200 px-5 py-10 text-center text-sm text-slate-500">No
                        active products found.</div>
                </article>

                <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="inventory">
                    <p class="text-sm text-slate-500" data-page-info="inventory"></p>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-500" for="page-size-inventory">Items</label>
                        <select id="page-size-inventory" data-page-size="inventory"
                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </select>
                        <button type="button" data-page-prev="inventory"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                        <button type="button" data-page-next="inventory"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                    </div>
                </div>
            </section>


            <section data-section="promos" hidden class="fade-in space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Promos & Discounts section</p>
                        <h2 class="font-display section-title mt-1 text-3xl font-bold">Promos & Discounts</h2>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-open-add-promo
                            class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/10">+
                            Create Promo</button>
                    </div>
                </div>

                <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <label class="relative block w-full lg:max-w-md">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z">
                                    </path>
                                </svg>
                            </span>
                            <input data-search-input="promos" type="search"
                                placeholder="Search by promo code or description"
                                class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-filter-button="promos" data-filter-value="all" data-active="true"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                            <button type="button" data-filter-button="promos" data-filter-value="active"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Active</button>
                            <button type="button" data-filter-button="promos" data-filter-value="inactive"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Inactive</button>
                        </div>
                    </div>
                </article>

                <article class="soft-panel panel-lift overflow-hidden rounded-[2rem]">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-5 py-4 font-medium">Code / Info</th>
                                    <th class="px-5 py-4 font-medium">Discount</th>
                                    <th class="px-5 py-4 font-medium">Constraints</th>
                                    <th class="px-5 py-4 font-medium">Validity Dates</th>
                                    <th class="px-5 py-4 font-medium">Usage</th>
                                    <th class="px-5 py-4 font-medium">Status</th>
                                    <th class="px-5 py-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($promos as $promo)
                                    <tr data-promo-row data-page-item="promos" data-promo-id="{{ $promo['id'] }}"
                                        data-promo-code="{{ $promo['code'] }}"
                                        data-promo-description="{{ $promo['description'] }}"
                                        data-promo-discount-type="{{ $promo['discount_type'] }}"
                                        data-promo-discount-value="{{ $promo['discount_value'] }}"
                                        data-promo-min-purchase="{{ $promo['min_purchase'] ?? '' }}"
                                        data-promo-max-discount="{{ $promo['max_discount'] ?? '' }}"
                                        data-promo-usage-limit="{{ $promo['usage_limit'] ?? '' }}"
                                        data-promo-limit-per-user="{{ $promo['limit_per_user'] ?? '' }}"
                                        data-promo-starts-at="{{ $promo['starts_at'] ?? '' }}"
                                        data-promo-expires-at="{{ $promo['expires_at'] ?? '' }}"
                                        data-promo-applicable-products="{{ json_encode($promo['applicable_products']) }}"
                                        data-promo-is-active="{{ !empty($promo['is_active']) ? 1 : 0 }}" class="align-top">
                                        <td class="px-5 py-4 font-semibold text-slate-900">
                                            <div
                                                class="font-bold text-slate-900 uppercase tracking-wide bg-orange-50 text-orange-850 rounded-lg px-2.5 py-1 inline-block border border-orange-100 mb-1">
                                                {{ $promo['code'] }}
                                            </div>
                                            <div class="text-xs text-slate-500 max-w-[200px] line-clamp-2 mt-1">
                                                {{ $promo['description'] ?? 'No description.' }}
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span
                                                class="font-semibold text-slate-900">{{ $promo['formatted_discount'] }}</span>
                                            <div class="text-xs text-slate-500 capitalize">{{ $promo['discount_type'] }}
                                                discount</div>
                                        </td>
                                        <td class="px-5 py-4 space-y-1">
                                            <div class="text-xs"><span class="text-slate-500">Min Purchase:</span> <span
                                                    class="font-semibold">{{ $promo['formatted_min_purchase'] }}</span>
                                            </div>
                                            <div class="text-xs"><span class="text-slate-500">Max Discount:</span> <span
                                                    class="font-semibold">{{ $promo['formatted_max_discount'] }}</span>
                                            </div>
                                            <div class="text-xs">
                                                <span class="text-slate-500">Products:</span>
                                                @if (!empty($promo['applicable_products']))
                                                    <span
                                                        class="rounded bg-slate-100 px-1.5 py-0.5 font-medium text-slate-700 text-[10px]">{{ count($promo['applicable_products']) }}
                                                        specific</span>
                                                @else
                                                    <span
                                                        class="rounded bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700 text-[10px]">All
                                                        Catalog</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 space-y-1">
                                            <div class="text-xs"><span class="text-slate-500">Starts:</span> <span
                                                    class="font-semibold text-slate-700">{{ $promo['formatted_starts_at'] }}</span>
                                            </div>
                                            <div class="text-xs"><span class="text-slate-500">Expires:</span> <span
                                                    class="font-semibold text-slate-700">{{ $promo['formatted_expires_at'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-slate-900">{{ $promo['usage_count'] }} used</div>
                                            @if ($promo['usage_limit'])
                                                <div class="text-[11px] text-slate-400">Limit: {{ $promo['usage_limit'] }} total
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <span
                                                class="rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wider {{ $promo['is_active'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                                {{ $promo['is_active'] ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" data-edit-promo
                                                    class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Edit</button>
                                                <button type="button" data-remove-promo
                                                    class="rounded-full bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700">Remove</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div data-promos-empty hidden
                        class="border-t border-dashed border-slate-200 px-5 py-10 text-center text-sm text-slate-500">No
                        promo codes found.</div>
                </article>

                <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="promos">
                    <p class="text-sm text-slate-500" data-page-info="promos"></p>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-500" for="page-size-promos">Items</label>
                        <select id="page-size-promos" data-page-size="promos"
                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </select>
                        <button type="button" data-page-prev="promos"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                        <button type="button" data-page-next="promos"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                    </div>
                </div>
            </section>


            <section data-section="orders" hidden class="fade-in space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Orders section</p>
                        <h2 class="font-display section-title mt-1 text-3xl font-bold">Orders in progress</h2>
                    </div>
                    <button type="button" data-open-walkin-order
                        class="rounded-full bg-emerald-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-700/10">+
                        New Walk-in Order</button>
                </div>

                <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <label class="relative block w-full lg:max-w-md">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z">
                                    </path>
                                </svg>
                            </span>
                            <input data-search-input="orders" type="search"
                                placeholder="Search by order #, customer, or payment status"
                                class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-filter-button="orders" data-filter-value="all" data-active="true"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                            <button type="button" data-filter-button="orders" data-filter-value="pending"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Pending</button>
                            <button type="button" data-filter-button="orders" data-filter-value="processing"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Processing</button>
                            <button type="button" data-filter-button="orders" data-filter-value="shipped"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Shipped</button>
                            <button type="button" data-filter-button="orders" data-filter-value="paid"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Paid</button>
                            <button type="button" data-filter-button="orders" data-filter-value="custom"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Custom</button>
                        </div>
                    </div>
                </article>

                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($orders as $order)
                        <article data-order-card data-page-item="orders" data-order-id="{{ $order['id'] }}"
                            data-order-status="{{ $order['status'] }}" data-payment-status="{{ $order['payment_status'] }}"
                            data-next-status="{{ $order['next_status'] ?? '' }}"
                            data-order-customer="{{ $order['customer'] }}"
                            data-order-custom="{{ !empty($order['contains_custom']) ? '1' : '0' }}"
                            class="soft-panel panel-lift rounded-[1.75rem] p-5">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="font-display text-2xl font-bold text-slate-900">Order #{{ $order['id'] }}
                                        </h3>
                                        <span data-payment-status
                                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $order['payment_status_label'] }}</span>
                                        @if (!empty($order['contains_custom']))
                                            <span
                                                class="rounded-full bg-[#FFF4EB] px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">Custom
                                                order</span>
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
                                <div data-order-status-label
                                    class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">
                                    {{ $order['status_label'] }}
                                </div>
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
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#C9876C]">
                                            Customization Brief</p>
                                        @foreach ($order['custom_items'] as $customItem)
                                            <div class="mt-3 flex gap-4">
                                                <img src="{{ $customItem['image_url'] }}" alt="{{ $customItem['name'] }}"
                                                    class="h-20 w-20 rounded-[1.25rem] object-cover ring-1 ring-[#EACFBC]">
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
                                                        <p class="mt-2 text-sm leading-6 text-slate-700">
                                                            {{ $customItem['design_description'] }}
                                                        </p>
                                                    @endif
                                                    @if (!empty($customItem['dedication_message']))
                                                        <p class="mt-2 text-xs font-medium text-[#8E5632]">Dedication:
                                                            {{ $customItem['dedication_message'] }}
                                                        </p>
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
                                <button data-order-action="advance" data-next-status="{{ $order['next_status'] ?? '' }}" @if (empty($order['next_status'])) hidden @endif type="button"
                                    class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">
                                    {{ $order['next_status_label'] ?? 'Advance Workflow' }}
                                </button>
                                <button data-order-action="mark-paid" @if ($order['payment_status'] === 'paid') hidden @endif
                                    type="button"
                                    class="rounded-full bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Mark
                                    as Paid</button>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="orders">
                    <p class="text-sm text-slate-500" data-page-info="orders"></p>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-500" for="page-size-orders">Items</label>
                        <select id="page-size-orders" data-page-size="orders"
                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </select>
                        <button type="button" data-page-prev="orders"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                        <button type="button" data-page-next="orders"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
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
                        <button type="button" data-person-tab="customers"
                            class="tab-item rounded-full px-4 py-2 text-sm font-semibold text-slate-600"
                            data-active="true">Customers</button>
                        <button type="button" data-person-tab="admins"
                            class="tab-item rounded-full px-4 py-2 text-sm font-semibold text-slate-600">Admins</button>
                    </div>
                </div>

                <div class="flex flex-col xl:flex-row gap-6 items-start">
                    <article class="soft-panel panel-lift rounded-[2rem] p-4 sm:p-5 flex-1 min-w-0">
                        <div data-person-panel="customers">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3 font-medium">Name</th>
                                            <th class="px-4 py-3 font-medium">Username</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Age</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Email</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Contact</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Address</th>
                                            <th class="px-4 py-3 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($customers as $person)
                                            <tr data-person-card data-page-item="customers"
                                                data-person-id="{{ $person['id'] }}" data-person-role="Customer"
                                                data-person-name="{{ $person['name'] }}"
                                                data-person-email="{{ $person['email'] }}"
                                                data-person-status="{{ $person['status'] }}" class="align-top">
                                                <td class="px-4 py-4 font-semibold text-slate-900">{{ $person['name'] }}
                                                </td>
                                                <td class="px-4 py-4 text-slate-600">{{ $person['username'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['age'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['email'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['contact'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['address'] }}</td>
                                                <td class="px-4 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        <button type="button" data-view-person
                                                            class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">View</button>
                                                        <button type="button" data-toggle-person
                                                            class="rounded-full {{ $person['status'] === 'active' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-2 text-xs font-semibold">{{ $person['status'] === 'active' ? 'Suspend' : 'Unsuspend' }}</button>
                                                    </div>
                                                    <template data-person-details>
                                                        <div class="grid gap-3 text-sm text-slate-600">
                                                            <p><span class="font-semibold text-slate-900">Name:</span>
                                                                {{ $person['name'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Username:</span>
                                                                {{ $person['username'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Age:</span>
                                                                {{ $person['age'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Email:</span>
                                                                {{ $person['email'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Contact:</span>
                                                                {{ $person['contact'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Address:</span>
                                                                {{ $person['address'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Status:</span>
                                                                <span
                                                                    data-person-status>{{ ucfirst($person['status']) }}</span>
                                                            </p>
                                                        
                                                             <div class="mt-4 pt-4 border-t border-[#ece1d8] flex flex-col gap-2">
                                                                 <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Actions</p>
                                                                 <button type="button" data-toggle-person data-person-id="{{ $person['id'] }}" data-person-status="{{ $person['status'] }}"
                                                                     class="w-full text-center rounded-full {{ $person['status'] === 'active' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} px-4 py-2.5 text-xs font-semibold active:scale-95 transition-all">
                                                                     {{ $person['status'] === 'active' ? 'Suspend Account' : 'Unsuspend Account' }}
                                                                 </button>
                                                             </div>
                                                         </div>
                                                    </template>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3"
                                data-pagination-controls="customers">
                                <p class="text-sm text-slate-500" data-page-info="customers"></p>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-slate-500" for="page-size-customers">Items</label>
                                    <select id="page-size-customers" data-page-size="customers"
                                        class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                    </select>
                                    <button type="button" data-page-prev="customers"
                                        class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                                    <button type="button" data-page-next="customers"
                                        class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
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
                                            <th class="px-4 py-3 font-medium hide-on-panel">Age</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Email</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Contact</th>
                                            <th class="px-4 py-3 font-medium hide-on-panel">Address</th>
                                            <th class="px-4 py-3 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($admins as $person)
                                            <tr data-person-card data-page-item="admins"
                                                data-person-id="{{ $person['id'] }}" data-person-role="Admin"
                                                data-person-name="{{ $person['name'] }}"
                                                data-person-email="{{ $person['email'] }}"
                                                data-person-status="{{ $person['status'] }}" class="align-top">
                                                <td class="px-4 py-4 font-semibold text-slate-900">{{ $person['name'] }}
                                                </td>
                                                <td class="px-4 py-4 text-slate-600">{{ $person['username'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['age'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['email'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['contact'] }}</td>
                                                <td class="px-4 py-4 text-slate-600 hide-on-panel">{{ $person['address'] }}</td>
                                                <td class="px-4 py-4">
                                                    <div class="flex flex-wrap gap-2">
                                                        <button type="button" data-view-person
                                                            class="rounded-full bg-slate-900 px-3 py-2 text-xs font-semibold text-white">View</button>
                                                        <button type="button" data-toggle-person
                                                            class="rounded-full {{ $person['status'] === 'active' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-2 text-xs font-semibold">{{ $person['status'] === 'active' ? 'Suspend' : 'Unsuspend' }}</button>
                                                    </div>
                                                    <template data-person-details>
                                                        <div class="grid gap-3 text-sm text-slate-600">
                                                            <p><span class="font-semibold text-slate-900">Name:</span>
                                                                {{ $person['name'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Username:</span>
                                                                {{ $person['username'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Age:</span>
                                                                {{ $person['age'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Email:</span>
                                                                {{ $person['email'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Contact:</span>
                                                                {{ $person['contact'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Address:</span>
                                                                {{ $person['address'] }}</p>
                                                            <p><span class="font-semibold text-slate-900">Status:</span>
                                                                <span
                                                                    data-person-status>{{ ucfirst($person['status']) }}</span>
                                                            </p>
                                                        
                                                             <div class="mt-4 pt-4 border-t border-[#ece1d8] flex flex-col gap-2">
                                                                 <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Actions</p>
                                                                 <button type="button" data-toggle-person data-person-id="{{ $person['id'] }}" data-person-status="{{ $person['status'] }}"
                                                                     class="w-full text-center rounded-full {{ $person['status'] === 'active' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }} px-4 py-2.5 text-xs font-semibold active:scale-95 transition-all">
                                                                     {{ $person['status'] === 'active' ? 'Suspend Account' : 'Unsuspend Account' }}
                                                                 </button>
                                                             </div>
                                                         </div>
                                                    </template>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3"
                                data-pagination-controls="admins" hidden>
                                <p class="text-sm text-slate-500" data-page-info="admins"></p>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-slate-500" for="page-size-admins">Items</label>
                                    <select id="page-size-admins" data-page-size="admins"
                                        class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                                        <option value="10" selected>10</option>
                                        <option value="20">20</option>
                                    </select>
                                    <button type="button" data-page-prev="admins"
                                        class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                                    <button type="button" data-page-next="admins"
                                        class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                                </div>
                            </div>
                        </div>
                    </article>

                    <aside data-customer-panel hidden class="soft-panel panel-lift rounded-[2rem] p-5 w-full xl:w-[360px] shrink-0 min-w-0 overflow-hidden break-words">
                        <p class="text-sm font-medium text-slate-500">Detail panel</p>
                        <h3 data-customer-panel-title class="font-display mt-1 text-2xl font-bold">Account details</h3>
                        <p data-customer-panel-meta class="mt-1 text-sm text-slate-500"></p>
                        <div data-customer-panel-body class="mt-5 space-y-3 text-sm text-slate-600 break-words min-w-0"></div>
                    </aside>
                </div>
            </section>

            <section data-section="messages" hidden class="fade-in space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Communications</p>
                        <h2 class="font-display section-title mt-1 text-3xl font-bold">Inbox</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span
                            class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">{{ collect($messages)->where('unread', true)->count() }}
                            unread</span>
                        <button type="button" data-admin-message-mark-read
                            class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 active:scale-95">Mark as read</button>
                    </div>
                </div>

                <div class="grid gap-6 xl:h-[calc(100vh-220px)] xl:grid-cols-[360px_1fr]">
                    <!-- Sidebar: Conversations List -->
                    <aside class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 p-5">
                            <h3 class="font-display text-xl font-bold text-slate-900">Conversations</h3>
                            <div class="relative mt-4">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input data-admin-message-search type="text" placeholder="Search by customer name..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pr-4 pl-11 text-sm text-slate-700 outline-none transition focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]">
                            </div>
                        </div>

                        <div data-admin-message-list class="flex-1 overflow-y-auto p-3 space-y-1">
                            <!-- Populated by JS -->
                        </div>
                    </aside>

                    <!-- Main: Chat Interface -->
                    <section data-admin-message-panel class="flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <!-- Chat Header -->
                        <div class="shrink-0 border-b border-slate-100 bg-white px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div data-admin-message-avatar class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--brand)] text-sm font-bold text-white">
                                        --
                                    </div>
                                    <div>
                                        <h3 data-admin-message-name class="font-display text-lg font-bold text-slate-900 leading-tight">Select a conversation</h3>
                                        <p data-admin-message-subtitle class="text-xs text-slate-500">Pick a thread from the list to start chatting.</p>
                                    </div>
                                </div>
                                <div class="hidden sm:block">
                                    <span data-admin-message-label class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">Inbound</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Feed -->
                        <div data-admin-message-feed class="flex-1 overflow-y-auto bg-slate-50/50 p-6">
                            <!-- Populated by JS -->
                        </div>

                        <!-- Chat Input -->
                        <div class="shrink-0 border-t border-slate-100 bg-white p-4">
                            <div class="flex items-end gap-3">
                                <div class="relative flex-1">
                                    <textarea data-admin-message-draft rows="1" 
                                        placeholder="Type your message here..."
                                        class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 py-3 px-4 pr-12 text-sm text-slate-700 outline-none transition focus:border-[var(--brand)] focus:ring-1 focus:ring-[var(--brand)]"
                                        style="min-height: 46px; max-height: 120px;"></textarea>
                                </div>
                                <button type="button" data-admin-message-send
                                    class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-2xl bg-[var(--brand)] text-white shadow-md transition hover:bg-[var(--brand-deep)] active:scale-90 disabled:opacity-50 disabled:pointer-events-none">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </section>

            <section data-section="notifications" hidden class="fade-in space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Notifications section</p>
                        <h2 class="font-display section-title mt-1 text-3xl font-bold">Active notifications</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span data-notification-count
                            class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-600">{{ count($notifications) }}</span>
                        <button data-clear-notifications type="button"
                            class="rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white">Clear
                            All</button>
                    </div>
                </div>

                <article class="soft-panel rounded-[1.75rem] p-4 sm:p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <label class="relative block w-full lg:max-w-md">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35m1.6-5.15a6.75 6.75 0 11-13.5 0 6.75 6.75 0 0113.5 0z">
                                    </path>
                                </svg>
                            </span>
                            <input data-search-input="notifications" type="search"
                                placeholder="Search notifications by customer, title, or order"
                                class="w-full rounded-full border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none focus:border-slate-300 focus:ring-2 focus:ring-[rgba(200,111,56,0.18)]">
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-filter-button="notifications" data-filter-value="all"
                                data-active="true"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">All</button>
                            <button type="button" data-filter-button="notifications" data-filter-value="orders"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Orders</button>
                            <button type="button" data-filter-button="notifications" data-filter-value="payments"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Payments</button>
                            <button type="button" data-filter-button="notifications" data-filter-value="customers"
                                class="filter-chip rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-600">Customers</button>
                        </div>
                    </div>
                </article>

                <div class="grid gap-4">
                    @foreach ($notifications as $notification)
                        <article data-notification-item data-page-item="notifications"
                            data-notification-id="{{ $notification['id'] }}"
                            data-notification-category="{{ $notification['category'] ?? 'orders' }}"
                            data-notification-title="{{ $notification['title'] ?? '' }}"
                            data-notification-message="{{ $notification['message'] }}"
                            data-notification-customer="{{ $notification['customer_name'] }}"
                            data-notification-order="{{ $notification['order_id'] ?? '' }}"
                            class="soft-panel panel-lift flex flex-col gap-4 rounded-[1.5rem] border border-transparent p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded-full bg-[#FFF4EB] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#B96C3D]">{{ $notification['category_label'] ?? 'Order' }}</span>
                                    @if (!empty($notification['contains_custom']))
                                        <span
                                            class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-600">Custom</span>
                                    @endif
                                </div>
                                <p class="mt-3 text-sm font-medium text-slate-500">{{ $notification['customer_name'] }}</p>
                                <h3 class="mt-1 font-display text-xl font-bold">
                                    {{ $notification['title'] ?? 'Admin update' }}
                                </h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $notification['message'] }}</p>
                                <p class="mt-2 text-sm text-slate-500">{{ $notification['date'] }}</p>
                            </div>
                            <button data-remove-notification type="button"
                                class="rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700">Remove</button>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3" data-pagination-controls="notifications">
                    <p class="text-sm text-slate-500" data-page-info="notifications"></p>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-500" for="page-size-notifications">Items</label>
                        <select id="page-size-notifications" data-page-size="notifications"
                            class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-sm text-slate-600">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                        </select>
                        <button type="button" data-page-prev="notifications"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Prev</button>
                        <button type="button" data-page-next="notifications"
                            class="rounded-xl bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">Next</button>
                    </div>
                </div>
            </section>
        </main>

        <div data-promo-drawer hidden
            class="fixed inset-0 z-40 flex items-center justify-center bg-[#473D36]/25 p-4 backdrop-blur-md">
            <div class="w-full max-w-5xl animate-fade-in">
                <div class="soft-panel panel-lift max-h-[95vh] w-full overflow-hidden rounded-[2.5rem] !bg-white p-6 shadow-2xl md:p-8"
                    style="background-color: #ffffff !important;">
                    <!-- Drawer Header -->
                    <div class="flex items-start justify-between gap-4 border-b border-[#ece1d8] pb-5">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-brand">Campaign Studio</p>
                            <h3 data-promo-title class="font-display mt-1 text-2xl font-bold text-slate-850">Add Promo
                            </h3>
                            <p data-promo-subtitle class="mt-1 text-sm text-slate-500">Create a new discount campaign
                                with target rules.</p>
                            <p data-promo-feedback class="mt-2 text-sm font-semibold text-rose-600"></p>
                        </div>
                        <button type="button" data-promo-close
                            class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">✕</button>
                    </div>

                    <form data-promo-form class="flex flex-col gap-6 mt-6 overflow-hidden" data-mode="add"
                        action="{{ route('admin.promos.store') }}" method="POST"
                        data-store-url="{{ route('admin.promos.store') }}"
                        data-update-url-base="{{ url('/admin/promos') }}">
                        @csrf
                        <input data-promo-id name="promo_id" type="hidden" value="">
                        <input data-promo-method type="hidden" name="_method" value="" disabled>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start overflow-y-auto max-h-[58vh] pr-2 overflow-x-hidden">

                            <!-- LEFT COLUMN: Physical ticket preview and basic campaign fields (col-span-5) -->
                            <div class="lg:col-span-5 flex flex-col gap-6">

                                <!-- Premium Live Preview Ticket -->
                                <div
                                    class="relative bg-gradient-to-br from-[#c9876c] to-[#b07359] text-white rounded-3xl p-6 shadow-xl overflow-hidden border border-[#e8b59e]/30 select-none">
                                    <!-- Ticket notches on sides -->
                                    <div
                                        class="absolute top-1/2 -left-3 w-6 h-6 bg-white rounded-full -translate-y-1/2">
                                    </div>
                                    <div
                                        class="absolute top-1/2 -right-3 w-6 h-6 bg-white rounded-full -translate-y-1/2">
                                    </div>

                                    <div class="flex justify-between items-start border-b border-white/20 pb-4 mb-4">
                                        <div>
                                            <p class="text-[9px] font-bold tracking-widest uppercase opacity-75">
                                                BakerDan Official Campaign</p>
                                            <h4 id="preview-code"
                                                class="font-display text-2xl font-black tracking-wide mt-1 uppercase">
                                                YOURCODE</h4>
                                        </div>
                                        <span id="preview-status"
                                            class="rounded-full bg-white/20 px-3 py-1 text-[9px] font-bold uppercase tracking-wider transition-all">Active</span>
                                    </div>

                                    <div class="my-4">
                                        <h1 id="preview-value"
                                            class="font-display text-4xl font-extrabold tracking-tight">15% OFF</h1>
                                        <p id="preview-desc"
                                            class="text-xs mt-2 opacity-90 line-clamp-2 leading-relaxed">BakerDan
                                            freshly baked discounts and promotions</p>
                                    </div>

                                    <div
                                        class="flex justify-between items-center text-[9px] font-medium opacity-80 pt-3 border-t border-dashed border-white/20">
                                        <span>EXCLUSIVE BAKERDAN OFFER</span>
                                        <span>SELECT / REDEEM</span>
                                    </div>
                                </div>

                                <!-- General Fields -->
                                <div class="space-y-4">
                                    <!-- Promo Code with generate button -->
                                    <div class="grid gap-2">
                                        <label class="text-sm font-semibold text-slate-700">Promo Code</label>
                                        <div class="flex gap-2">
                                            <input data-promo-code-input name="code" type="text"
                                                class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand uppercase font-bold text-slate-850 placeholder-slate-400"
                                                placeholder="e.g. BAKERFRESH20" required>
                                            <button type="button" onclick="generatePromoCode()"
                                                class="rounded-xl bg-[#c9876c]/10 border border-[#c9876c]/20 px-4 text-brand font-bold text-xs hover:bg-[#c9876c]/20 transition-all active:scale-95">
                                                Generate
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <label class="grid gap-2 text-sm font-semibold text-slate-700">
                                        Description
                                        <textarea data-promo-description name="description" rows="3"
                                            class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand text-slate-705 placeholder-slate-400 resize-none"
                                            placeholder="Short description of benefits and limits"></textarea>
                                    </label>
                                </div>

                            </div>

                            <div class="lg:col-span-7 flex flex-col gap-6">

                                <!-- Group 1: Value Settings -->
                                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                                    <h4
                                        class="text-xs font-bold uppercase tracking-wider text-slate-450 border-b border-slate-200 pb-2">
                                        Reward Settings</h4>

                                    <div
                                        class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                        <span class="text-xs font-bold text-slate-700">Discount Type</span>
                                        <select data-promo-discount-type name="discount_type"
                                            class="w-48 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700">
                                            <option value="percentage">Percentage (%)</option>
                                            <option value="fixed">Fixed Amount (PHP)</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-1.5">
                                        <span class="text-xs font-bold text-slate-700">Discount Value</span>
                                        <input data-promo-discount-value name="discount_value" type="number" step="0.01"
                                            min="0"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white"
                                            placeholder="e.g. 15 or 150" required>
                                    </div>
                                </div>

                                <!-- Group 2: Constraints -->
                                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                                    <h4
                                        class="text-xs font-bold uppercase tracking-wider text-slate-450 border-b border-slate-200 pb-2">
                                        Purchase & Usage Limits</h4>

                                    <div
                                        class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                        <span class="text-xs font-bold text-slate-700">Min Purchase (PHP)</span>
                                        <input data-promo-min-purchase name="min_purchase" type="number" step="0.01"
                                            min="0"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white"
                                            placeholder="e.g. 500 (Optional)">
                                    </div>

                                    <div
                                        class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                        <span class="text-xs font-bold text-slate-700">Max Discount (PHP)</span>
                                        <input data-promo-max-discount name="max_discount" type="number" step="0.01"
                                            min="0"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white"
                                            placeholder="e.g. 200 (Optional)">
                                    </div>

                                    <div
                                        class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                        <span class="text-xs font-bold text-slate-700">Global Usage Limit</span>
                                        <input data-promo-usage-limit name="usage_limit" type="number" min="1"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white"
                                            placeholder="e.g. 100 uses (Optional)">
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-1.5">
                                        <span class="text-xs font-bold text-slate-700">Limit Per User</span>
                                        <input data-promo-limit-per-user name="limit_per_user" type="number" min="1"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white"
                                            placeholder="e.g. 1 use (Optional)">
                                    </div>
                                </div>

                                <!-- Group 3: Campaign Validity -->
                                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                                    <h4
                                        class="text-xs font-bold uppercase tracking-wider text-slate-450 border-b border-slate-200 pb-2">
                                        Campaign Validity</h4>

                                    <div
                                        class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                        <span class="text-xs font-bold text-slate-700">Starts At</span>
                                        <input data-promo-starts-at name="starts_at" type="datetime-local"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white">
                                    </div>

                                    <div
                                        class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                        <span class="text-xs font-bold text-slate-700">Expires At</span>
                                        <input data-promo-expires-at name="expires_at" type="datetime-local"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white">
                                    </div>

                                    <div class="flex items-center justify-between gap-4 py-1.5">
                                        <span class="text-xs font-bold text-slate-700">Campaign Status</span>
                                        <select data-promo-is-active name="is_active"
                                            class="w-48 rounded-xl border border-slate-200 px-3 py-1.5 text-xs outline-none focus:border-brand text-slate-700 bg-white">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Group 4: Target Products -->
                                <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50/50 space-y-4">
                                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-450">Target
                                            Products</h4>
                                        <span class="text-[10px] text-slate-500 font-medium">(Leave empty for all
                                            items)</span>
                                    </div>

                                    <div class="flex items-center justify-between gap-2">
                                        <input type="text" oninput="filterPromoProducts(this.value)"
                                            placeholder="Search catalog..."
                                            class="flex-1 rounded-2xl border border-slate-200 px-4 py-2 text-xs outline-none focus:border-brand bg-white">
                                        <div class="flex gap-1">
                                            <button type="button" onclick="toggleAllPromoProducts(true)"
                                                class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 text-[10px] font-bold transition-all">All</button>
                                            <button type="button" onclick="toggleAllPromoProducts(false)"
                                                class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 text-[10px] font-bold transition-all">Clear</button>
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-2xl border border-[#ece1d8] p-3 max-h-[220px] overflow-y-auto overflow-x-hidden gap-2 grid grid-cols-1 bg-white">
                                        @foreach ($products as $p)
                                            <label data-promo-product-item
                                                class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white p-3 text-sm text-slate-600 font-semibold cursor-pointer hover:bg-slate-50 transition-colors">
                                                <input type="checkbox" name="applicable_products[]" value="{{ $p['id'] }}"
                                                    data-promo-product-checkbox
                                                    class="rounded text-[#c9876c] focus:ring-[#c9876c] h-5 w-5 border-slate-300">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-slate-800">{{ $p['name'] }}</span>
                                                    <span
                                                        class="text-xs text-slate-500 font-medium">{{ $p['formatted_price'] }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                            </div>

                        </div>

                        <!-- Submit Footer -->
                        <div class="border-t border-[#ece1d8] pt-4 mt-2 flex justify-end gap-3">
                            <button type="button" data-promo-close
                                class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit"
                                class="rounded-2xl bg-[#c9876c] text-white font-bold px-8 py-3 hover:bg-[#b8765b] transition-all shadow-lg shadow-[#c9876c]/20 active:scale-[0.98]">
                                Save Discount Campaign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function updatePromoTicketPreview() {
                const form = document.querySelector('[data-promo-form]');
                const codeInput = form ? form.querySelector('[data-promo-code-input]') : null;
                const descInput = form ? form.querySelector('[data-promo-description]') : null;
                const typeSelect = form ? form.querySelector('[data-promo-discount-type]') : null;
                const valueInput = form ? form.querySelector('[data-promo-discount-value]') : null;
                const statusSelect = form ? form.querySelector('[data-promo-is-active]') : null;

                const previewCode = document.getElementById('preview-code');
                const previewValue = document.getElementById('preview-value');
                const previewDesc = document.getElementById('preview-desc');
                const previewStatus = document.getElementById('preview-status');

                if (previewCode && codeInput) {
                    previewCode.textContent = codeInput.value.toUpperCase() || 'YOURCODE';
                }
                if (previewDesc && descInput) {
                    previewDesc.textContent = descInput.value || 'BakerDan freshly baked discounts and promotions';
                }
                if (previewValue && valueInput && typeSelect) {
                    const val = parseFloat(valueInput.value) || 0;
                    if (typeSelect.value === 'percentage') {
                        previewValue.textContent = val + '% OFF';
                    } else {
                        previewValue.textContent = '₱' + val.toLocaleString() + ' OFF';
                    }
                }
                if (previewStatus && statusSelect) {
                    const isActive = statusSelect.value === '1';
                    if (isActive) {
                        previewStatus.textContent = 'Active';
                        previewStatus.className = 'rounded-full bg-white/20 px-3 py-1 text-[9px] font-bold uppercase tracking-wider transition-all';
                    } else {
                        previewStatus.textContent = 'Inactive';
                        previewStatus.className = 'rounded-full bg-black/30 text-white/50 border border-white/10 px-3 py-1 text-[9px] font-bold uppercase tracking-wider transition-all';
                    }
                }
            }

            document.addEventListener('input', (e) => {
                if (e.target.matches('[data-promo-code-input], [data-promo-description], [data-promo-discount-value]')) {
                    updatePromoTicketPreview();
                }
            });

            document.addEventListener('change', (e) => {
                if (e.target.matches('[data-promo-discount-type], [data-promo-is-active]')) {
                    updatePromoTicketPreview();
                }
            });

            const previewObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'hidden') {
                        updatePromoTicketPreview();
                        setTimeout(updatePromoTicketPreview, 50);
                        setTimeout(updatePromoTicketPreview, 150);
                        setTimeout(updatePromoTicketPreview, 300);
                        setTimeout(updatePromoTicketPreview, 600);
                    }
                });
            });

            const targetPromoDrawer = document.querySelector('[data-promo-drawer]');
            if (targetPromoDrawer) {
                previewObserver.observe(targetPromoDrawer, { attributes: true });
            }

            function generatePromoCode() {
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let code = 'DAN-';
                for (let i = 0; i < 6; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                const input = document.querySelector('[data-promo-code-input]');
                if (input) {
                    input.value = code;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            function toggleAllPromoProducts(select) {
                document.querySelectorAll('[data-promo-product-checkbox]').forEach(cb => {
                    cb.checked = select;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            function filterPromoProducts(query) {
                const q = query.toLowerCase();
                document.querySelectorAll('[data-promo-product-item]').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (text.includes(q)) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                    }
                });
            }
            
            // Initialize on load
            updatePromoTicketPreview();
        </script>

        <div data-inventory-drawer hidden
            class="fixed inset-0 z-40 flex items-center justify-center bg-[#473D36]/25 p-4 backdrop-blur-md">
            <div class="w-full max-w-xl animate-fade-in">
                <div class="soft-panel panel-lift max-h-[90vh] w-full overflow-y-auto rounded-[2.5rem] !bg-white p-6 shadow-2xl md:p-8"
                    style="background-color: #ffffff !important;">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between gap-4 border-b border-[#ece1d8] pb-5">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Product Form</p>
                            <h3 data-inventory-title class="font-display mt-1 text-2xl font-bold text-slate-850">Add Product</h3>
                            <p data-inventory-subtitle class="mt-1 text-sm text-slate-500">Create a new active product for the bakery catalog.</p>
                            <p data-inventory-feedback class="mt-2 text-sm font-semibold text-rose-600"></p>
                        </div>
                        <button type="button" data-inventory-close
                            class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">✕</button>
                    </div>

                    <form data-inventory-form class="mt-6 flex flex-col gap-6" data-mode="add"
                        action="{{ route('admin.inventory.store') }}" method="POST" enctype="multipart/form-data"
                        data-store-url="{{ route('admin.inventory.store') }}"
                        data-update-url-base="{{ url('/admin/inventory') }}">
                        @csrf
                        <input data-inventory-id name="product_id" type="hidden" value="">
                        <input data-inventory-method type="hidden" name="_method" value="" disabled>
                        
                        <!-- Compact Settings Group -->
                        <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50/50 space-y-3">
                            <!-- Product Name -->
                            <div class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                <span class="text-xs font-bold text-slate-700">Product Name</span>
                                <input data-inventory-name name="product_name" type="text"
                                    class="w-64 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-[#c9876c] text-slate-700"
                                    placeholder="Product name" required>
                            </div>

                            <!-- Description -->
                            <div class="flex items-start justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                <span class="text-xs font-bold text-slate-700 mt-1">Description</span>
                                <textarea data-inventory-description name="description" rows="2"
                                    class="w-64 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-[#c9876c] text-slate-705 placeholder-slate-400 resize-none"
                                    placeholder="Short description of product features"></textarea>
                            </div>

                            <!-- Price -->
                            <div class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                <span class="text-xs font-bold text-slate-700">Price (PHP)</span>
                                <input data-inventory-price name="price" type="number" step="0.01" min="0"
                                    class="w-32 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-[#c9876c] text-slate-700 text-right"
                                    placeholder="e.g. 850" required>
                            </div>

                            <!-- Category -->
                            <div class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                <span class="text-xs font-bold text-slate-700">Category</span>
                                <select data-inventory-type name="category"
                                    class="w-48 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-[#c9876c] text-slate-700">
                                    <option value="Bread">Bread</option>
                                    <option value="Pastries">Pastries</option>
                                    <option value="Brazos and Cakes">Brazos and Cakes</option>
                                    <option value="Tarts">Tarts</option>
                                    <option value="Sugar Cookies">Sugar Cookies</option>
                                </select>
                            </div>

                            <!-- Image Upload -->
                            <div class="flex items-center justify-between gap-4 py-1.5 border-b border-slate-100/80">
                                <span class="text-xs font-bold text-slate-700">Product Image</span>
                                <input name="image" type="file" accept="image/*"
                                    class="w-64 rounded-xl border border-slate-200 bg-white px-3 py-1 text-[10px] outline-none focus:border-[#c9876c] text-slate-700">
                            </div>

                            <!-- Status -->
                            <div class="flex items-center justify-between gap-4 py-1.5">
                                <span class="text-xs font-bold text-slate-700">Status</span>
                                <select data-inventory-is-active name="is_active"
                                    class="w-32 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs outline-none focus:border-[#c9876c] text-slate-700">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="border-t border-[#ece1d8] pt-4 mt-2 flex justify-end gap-3">
                            <button type="button" data-inventory-close
                                class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Cancel</button>
                            <button type="submit"
                                class="rounded-2xl bg-[#c9876c] text-white font-bold px-8 py-3 hover:bg-[#b8765b] transition-all shadow-lg shadow-[#c9876c]/20 active:scale-[0.98]">
                                Save Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div data-modal-shell hidden
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#473D36]/25 p-4 backdrop-blur-md">
            <div class="w-full max-w-md animate-fade-in">
                <div class="soft-panel panel-lift max-h-[90vh] w-full overflow-y-auto rounded-[2.5rem] !bg-white p-6 shadow-2xl md:p-8"
                    style="background-color: #ffffff !important;">
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between gap-4 border-b border-[#ece1d8] pb-5">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Security & Access</p>
                            <h3 data-modal-title class="font-display mt-1 text-2xl font-bold text-slate-850">Title</h3>
                        </div>
                        <button type="button" data-modal-cancel
                            class="grid h-10 w-10 place-items-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">✕</button>
                    </div>

                    <div class="mt-6">
                        <p data-modal-message class="text-sm leading-relaxed text-slate-600">Message</p>
                    </div>

                    <!-- Footer Actions -->
                    <div class="border-t border-[#ece1d8] pt-5 mt-6 flex justify-end gap-3">
                        <button data-modal-cancel type="button"
                            class="rounded-full border border-slate-200 hover:bg-slate-50 px-6 py-3.5 text-sm font-semibold text-slate-700 active:scale-95 transition-all">Cancel</button>
                        <button data-modal-confirm type="button"
                            class="rounded-full bg-slate-900 hover:bg-slate-850 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/10 active:scale-95 transition-all">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
