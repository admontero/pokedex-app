<x-app-layout
    title="Inicio"
    description="Explora la Pokédex: busca Pokémon por nombre o número y consulta sus estadísticas, medidas y sprites."
>
    <section class="hero card border-0 shadow-sm text-center mb-3">
        <h1 class="hero-title">Bienvenido a la Pokédex</h1>

        <p class="hero-subtitle lead">
            Consulta la información, las estadísticas y los sprites de cualquier Pokémon.
        </p>
    </section>

    <div class="row align-items-end g-3 py-2">
        <div class="col-md-4">
            <p class="mb-0 text-muted-pk">
                Mostrando <strong class="text-pokemon-red-dark" data-shown-count>{{ $shown }}</strong> de {{ $total }} Pokémon
            </p>
        </div>

        <div class="col-md-8">
            <x-pokemon.form-search />
        </div>
    </div>

    @php
        $path = '?page={{#}}';
    @endphp

    @if (count($pokemons))
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-3 mt-2" id="main-content">
            @foreach ($pokemons as $pokemon)
                <x-pokemon.card
                    :$pokemon
                    :from="request()->fullUrl()"
                    :priority="$loop->index < 8"
                />
            @endforeach
        </div>
    @else
        <div class="mt-4">
            <x-empty-state
                title="No hay Pokémon para mostrar"
                description="No encontramos registros disponibles en este momento. Inténtalo de nuevo en unos segundos."
                :image="asset('vendor/images/pikachu.jpg')"
            >
                <a class="btn btn-pokemon-red text-white" href="{{ route('pokemons.index') }}">
                    Volver al inicio
                </a>
            </x-empty-state>
        </div>
    @endif

    <div class="my-5">
        <x-infinite-scroller
            :path="$path"
            :has-more="$hasMore"
            :next-page-url="$hasMore ? route('pokemons.index', ['page' => $page + 1]) : null"
        />
    </div>
</x-app-layout>
