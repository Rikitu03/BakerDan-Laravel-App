<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BAKERDAN - Artisan Bakery</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Urbanist:wght@600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/main.js'])
</head>
<body>
    <div id="app"></div>
    
    <script>
        window.Laravel = {
            csrfToken: @json(csrf_token()),
            user: @json(array_merge($customer, ['role' => 'customer'])),
            customer: @json($customer),
        };
    </script>
</body>
</html>
