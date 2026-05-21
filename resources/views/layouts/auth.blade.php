<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BakerDan | Authentication</title>
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
        body {
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.72), transparent 24%),
                radial-gradient(circle at 85% 18%, rgba(200, 111, 56, 0.12), transparent 18%),
                linear-gradient(180deg, #f1ece5 0, #fbf4e8 88px, #fbf4e8 100%);
            color: var(--ink);
            font-family: 'Outfit', sans-serif;
        }
        .font-display { font-family: 'Sora', sans-serif; }
        .mesh-card {
            background:
                radial-gradient(circle at top, rgba(255, 255, 255, 0.9), transparent 58%),
                linear-gradient(160deg, rgba(255, 250, 241, 0.92), rgba(243, 227, 198, 0.72));
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }
        .toast {
            position: fixed;
            top: 2rem;
            right: 2rem;
            padding: 1rem 2rem;
            border-radius: 1rem;
            background: var(--ink);
            color: white;
            box-shadow: var(--shadow);
            transform: translateY(-100%);
            opacity: 0;
            transition: all 0.5s ease;
            z-index: 50;
        }
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3">
                <img src="{{ asset('images/logo/BAKERDAN LOGO.jpg') }}" alt="BakerDan logo" class="h-12 w-12 rounded-full object-cover">
                <span class="font-display text-2xl font-extrabold tracking-[0.18em]">BAKERDAN</span>
            </a>
        </div>
        <div class="mesh-card rounded-[2rem] p-8">
            @yield('content')
        </div>
    </div>

    <div id="toast" class="toast font-display font-bold">
        <span id="toast-message"></span>
    </div>

    <script>
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        @if(session('account_created'))
            showToast('Account created successfully!');
        @endif

        @if(session('password_changed'))
            showToast('Password changed successfully!');
        @endif
    </script>
</body>
</html>
