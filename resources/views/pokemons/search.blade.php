<x-app-layout
    :title="request()->filled('term') ? 'Resultados para «'.request()->term.'»' : 'Resultados de búsqueda'"
    description="Resultados de búsqueda de Pokémon por nombre, número o tipo."
>
    @php
        $term = request()->get('term');
        $type = request()->get('type');
        $typeLabel = $type ? config('pokemon.type_labels.'.$type, $type) : null;

        $withoutTerm = $type
            ? route('pokemons.search', ['type' => $type])
            : route('pokemons.index');

        $withoutType = $term
            ? route('pokemons.search', ['term' => $term])
            : route('pokemons.index');
    @endphp

    <x-breadcrumb :items="[
        ['label' => 'Inicio', 'url' => route('pokemons.index')],
        ['label' => 'Búsqueda'],
    ]" />

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-1">
                @if ($term)
                    Resultados para «{{ $term }}»
                @else
                    Resultados de búsqueda
                @endif
            </h1>

            <p class="text-muted-pk mb-0">
                Mostrando <strong class="text-pokemon-red-dark" data-shown-count>{{ $shown }}</strong> de {{ $total }} Pokémon
                @if ($typeLabel)
                    de tipo <strong>{{ $typeLabel }}</strong>
                @endif
            </p>
        </div>

        <a class="btn btn-sm btn-secondary text-white" href="{{ route('pokemons.index') }}">
            <i class="fa fa-arrow-left me-2" aria-hidden="true"></i>
            Volver al inicio
        </a>
    </div>

    <div class="card border-0 shadow-sm p-3 p-md-4 mb-4">
        <x-pokemon.form-search />
    </div>

    @if ($term || $type)
        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
            <span class="small text-muted-pk">Filtros activos:</span>

            @if ($term)
                <span class="filter-chip">
                    <span>Término: <strong>{{ $term }}</strong></span>
                    <a class="filter-chip__remove" href="{{ $withoutTerm }}" aria-label="Quitar el filtro de término «{{ $term }}»">
                        <i class="fa fa-xmark" aria-hidden="true"></i>
                    </a>
                </span>
            @endif

            @if ($type)
                <span class="filter-chip">
                    <span>Tipo: <strong>{{ $typeLabel }}</strong></span>
                    <a class="filter-chip__remove" href="{{ $withoutType }}" aria-label="Quitar el filtro de tipo {{ $typeLabel }}">
                        <i class="fa fa-xmark" aria-hidden="true"></i>
                    </a>
                </span>
            @endif

            <a class="btn btn-sm btn-outline-secondary" href="{{ route('pokemons.index') }}">
                Limpiar filtros
            </a>
        </div>
    @endif

    @if (count($pokemons))
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-3" id="main-content">
            @foreach ($pokemons as $pokemon)
                <x-pokemon.card
                    :$pokemon
                    :from="request()->fullUrl()"
                    :priority="$loop->index < 8"
                />
            @endforeach
        </div>
    @else
        <x-empty-state
            title="Sin resultados"
            :description="$term
                ? 'No encontramos Pokémon que coincidan con «'.$term.'». Prueba con otro nombre o elimina los filtros.'
                : 'No encontramos Pokémon para los filtros seleccionados. Prueba con otro tipo o elimina los filtros.'"
            :image="asset('vendor/images/pikachu.jpg')"
        >
            <a class="btn btn-pokemon-red text-white" href="{{ route('pokemons.index') }}">
                Ver todos los Pokémon
            </a>
        </x-empty-state>
    @endif

    @php
        $searchPath = str_replace(
            '__PAGE__',
            '{{#}}',
            route('pokemons.search', array_filter([
                'page' => '__PAGE__',
                'term' => $term,
                'type' => $type,
            ]))
        );
    @endphp

    <div class="my-5">
        <x-infinite-scroller
            :path="$searchPath"
            :has-more="$hasMore"
            :next-page-url="$hasMore
                ? route('pokemons.search', array_filter(['page' => $page + 1, 'term' => $term, 'type' => $type]))
                : null"
        />
    </div>
</x-app-layout>
