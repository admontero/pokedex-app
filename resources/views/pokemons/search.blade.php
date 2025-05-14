<x-app-layout>
    <div class="row align-items-center mb-3">
        <div class="col-md-4">
            <a
                class="btn btn-sm btn-secondary text-white d-inline-flex align-items-center"
                href="{{ route('pokemons.index') }}"
                role="button"
            >
                <i class="fa fa-arrow-left me-2"></i>
                Volver
            </a>
            <hr class="d-md-none">
        </div>
        <div class="col-md-8">
            <x-pokemon.form-search />
        </div>
    </div>

    <div class="d-inline-block align-items-center gap-2 mb-3">
        <h4 class="d-inline">
            <span class="text-pokemon-red">{{ $total }}</span>
            Resultados de la búsqueda:
        </h4>

        @if (request()->term)
            <h5 class="d-inline"><span class="badge bg-white text-black px-2.5 py-1.5 shadow-lg border ms-2">{{ request()->term }}</span></h5>
        @endif

        @if (request()->type)
            <h5 class="d-inline ms-1">
                <span class="badge {{ request()->type }} fw-semibold px-2.5 py-1.5 text-capitalize shadow-lg">
                    {{ request()->type }}
                </span>
            </h5>
        @endif
    </div>

    @if (count($pokemons))
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-2" id="main-content">
            @foreach ($pokemons as $pokemon)
                <x-pokemon.card :$pokemon />
            @endforeach
        </div>
    @else
        <p class="text-center fst-italic mt-4">
            No existen pokémon según sus criterios de búsqueda. Inténtelo con otros.
        </p>
    @endif


    <div class="my-5">
        <x-infinite-scroller />
    </div>

    @push('scripts')
        <script>
            let elem = document.querySelector('#main-content');

            let infScroll = new InfiniteScroll(elem, {
                path: "/pokemon/search?page=@{{#}}&term={{ request()->get('term') ?? '' }}&type={{ request()->get('type') ?? '' }}",
                append: '.col',
                status: '.page-load-status',
            });

            infScroll.on('append', function(body, path, items, response) {
                initElements(items)
            })

            const initElements = function (items) {
                items.forEach(function (el) {
                    el.addEventListener('click', function () {
                        el.querySelector('a').click()
                    })
                })
            }

            document.addEventListener('DOMContentLoaded', function() {
                initElements(document.querySelectorAll('.card'))
            })
        </script>
    @endpush
</x-app-layout>
