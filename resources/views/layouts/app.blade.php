<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-body-tertiary">
        <nav class="navbar bg-pokemon-red shadow-sm py-2 sticky-top">
            <a class="navbar-brand mx-auto" href="{{ route('pokemons.index') }}">
                <img src="{{ asset('vendor/images/logo.png') }}" width="250"/>
            </a>
        </nav>

        <div class="container py-4">
            {{ $slot }}
        </div>

        @stack('scripts')
    </body>
</html>
