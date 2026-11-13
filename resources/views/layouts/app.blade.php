<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' — Pokédex' : 'Pokédex' }}</title>

        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        <meta name="theme-color" content="#E60013">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800&amp;display=swap">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 p-2 bg-white text-dark rounded" href="#main">
            Saltar al contenido
        </a>

        <nav class="navbar bg-pokemon-red shadow-sm py-2 sticky-top" aria-label="Navegación principal">
            <a
                class="navbar-brand mx-auto"
                href="{{ route('pokemons.index') }}"
                aria-label="Pokédex, ir al inicio"
            >
                <img src="{{ asset('vendor/images/logo.png') }}" alt="" width="250" height="45"/>
            </a>
        </nav>

        <main id="main" class="container py-4" tabindex="-1">
            {{ $slot }}
        </main>

        <footer class="site-footer container pb-4 text-center">
            <p class="mb-0">
                Datos de <a href="https://pokeapi.co/" rel="noopener noreferrer" target="_blank">PokéAPI</a>.
                Pokémon y los nombres de los Pokémon son marcas de Nintendo, Game Freak y Creatures.
            </p>
        </footer>

        @stack('scripts')
    </body>
</html>
