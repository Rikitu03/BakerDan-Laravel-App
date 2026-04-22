<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BAKERDAN - Artisan Bakery</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Urbanist:wght@600;700;800&display=swap" rel="stylesheet">
    
    @if(app()->environment('local'))
        <!-- Development: Vite HMR -->
        @vite(['resources/js/app.js'])
    @else
        <!-- Production: Built assets -->
        <link rel="stylesheet" href="{{ asset('dist/assets/main.css') }}">
    @endif
</head>
<body>
    <div id="app"></div>
    
    <!-- Pass Laravel data to Vue -->
    <script>
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            user: @json($customer ?? [
                'name' => 'Jason Jay Recto',
                'email' => 'recto_jasonjay@plpasig.edu.ph',
                'avatar' => 'JR'
            ])
        };
    </script>
    
    @if(app()->environment('local'))
        <!-- Development mode already loaded via @vite -->
    @else
        <!-- Production: Load built JS -->
        <script type="module" src="{{ asset('dist/assets/main.js') }}"></script>
    @endif
</body>
</html>
