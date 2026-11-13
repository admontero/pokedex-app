@props(['path', 'hasMore' => true, 'nextPageUrl' => null, 'target' => 'main-content'])

@php
    $nextPage = (int) request()->query('page', 1) + 1;
    $nextPageUrl ??= request()->fullUrlWithQuery(['page' => $nextPage]);
@endphp

<div
    @if ($hasMore)
        data-pk-infinite-scroll
        data-path="{{ $path }}"
        data-target="#{{ $target }}"
    @endif
>
    <div class="page-load-status">
        <div class="d-flex justify-content-center">
            <div class="infinite-scroll-request spinner-border text-pokemon-red" role="status">
                <span class="visually-hidden">Cargando más Pokémon…</span>
            </div>
            <div class="infinite-scroll-last">
                <img src="{{ asset('/vendor/images/pikachu.jpg') }}"
                    class="d-block mx-auto rounded rounded-3 shadow border border-light" width="200"
                    height="200" alt="" loading="lazy" />
                <p class="fst-italic mt-4 text-center">
                    ¡Ooops...! Estos son todos los registros hasta ahora.
                </p>
            </div>
            <div class="infinite-scroll-error">
                <img class="d-block mx-auto rounded rounded-3 shadow border border-light"
                    src="{{ asset('/vendor/images/rocket.png') }}" width="200" height="200"
                    alt="" loading="lazy" />
                <p class="fst-italic mt-4 text-center">
                    ¡Oh no, el Equipo Rocket! Tenemos un problema...
                </p>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-pokemon-blue text-white" data-retry>
                        Reintentar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="visually-hidden" role="status" aria-live="polite" data-infinite-scroll-status></div>

    @if ($hasMore)
        <div class="text-center mt-3">
            <a
                class="btn btn-outline-primary"
                href="{{ $nextPageUrl }}"
                rel="next"
                data-load-more
                aria-controls="{{ $target }}"
            >
                Cargar más Pokémon
            </a>
        </div>

        <a class="pagination__next visually-hidden" href="{{ $nextPageUrl }}" rel="next" tabindex="-1" aria-hidden="true">
            Página siguiente
        </a>
    @else
        <p class="text-center text-muted-pk mt-4 mb-0">Has llegado al final de la lista.</p>
    @endif
</div>
