<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' — Pokédex' : 'Pokédex' }}</title>

        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif

        <meta name="theme-color" content="#E60013">

        {{-- Apply the stored theme before the CSS loads to avoid a flash. --}}
        <script>
            (function () {
                var theme = 'light';

                try {
                    var stored = window.localStorage.getItem('pk-theme');

                    if (stored === 'dark' || stored === 'light') {
                        theme = stored;
                    } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        theme = 'dark';
                    }
                } catch (error) {
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        theme = 'dark';
                    }
                }

                document.documentElement.setAttribute('data-bs-theme', theme);
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=nunito:400,600,700,800&amp;display=swap">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 p-2 bg-body text-body rounded" href="#main">
            Saltar al contenido
        </a>

        <nav class="navbar bg-pokemon-red shadow-sm py-2 sticky-top" aria-label="Navegación principal">
            <div class="container-fluid align-items-center">
                <span class="theme-toggle-spacer" aria-hidden="true"></span>

                <a
                    class="navbar-brand mx-auto"
                    href="{{ route('pokemons.index') }}"
                    aria-label="Pokédex, ir al inicio"
                >
                    <x-picture
                        base="vendor/images/logo"
                        fallback="vendor/images/logo.png"
                        :width="250"
                        :height="45"
                        loading="eager"
                        fetchpriority="high"
                    />
                </a>

                <button
                    type="button"
                    class="theme-toggle"
                    data-theme-toggle
                    aria-label="Modo oscuro"
                    aria-pressed="false"
                    title="Activar modo oscuro"
                >
                    <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.42-1.42"/>
                    </svg>
                </button>
            </div>
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
