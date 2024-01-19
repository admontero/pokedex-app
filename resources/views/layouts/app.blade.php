<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

         <!-- Scripts -->
         @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-body-tertiary">
        <nav class="navbar bg-pokemon-red shadow-sm py-2 sticky-top">
            <a class="navbar-brand mx-auto" href="{{ route('pokemons.index') }}">
                <img src="{{ asset('vendor/images/logo.png') }}" width="250"/>
            </a>
        </nav>

        <div class="container-fluid py-4">
            {{ $slot }}
        </div>

        @stack('scripts')
    </body>
</html>
