<x-app-layout>
    <div class="card py-5 px-2 shadow-sm">
        <h1 class="text-center display-4 fw-bold">Bienvenido a la Laravel Pokédex</h1>

        <p class="text-center lead">En esta aplicación podrás visualizar la información de cualquier pokémon. Lets do it!
        </p>
    </div>

    <div class="row align-items-center py-3">
        <div class="col-md-4 mb-2 mb-md-0">
            <p class="d-inline"><span class="text-danger fw-bold">{{ $total }}</span> Resultados</p>
        </div>

        <div class="col-md-8">
            <x-pokemon.form-search />
        </div>
    </div>

    @if (count($pokemons))
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-2" id="main-content">
            @foreach ($pokemons as $pokemon)
                <x-pokemon.card :$pokemon />
            @endforeach
        </div>
    @else
        <p class="text-center fst-italic mt-4">
            Actualmente no existen más registros de pokémon, seguiremos actualizando...
        </p>
    @endif

    <div class="my-5">
        <x-infinite-scroller />
    </div>

    @push('scripts')
        <script>
            let elem = document.querySelector('#main-content');

            let infScroll = new InfiniteScroll(elem, {
                path: '?page=@{{#}}',
                append: '.col',
                status: '.page-load-status',
            });

            infScroll.on('append', function(body, path, items, response) {
                initElements(items)
            })

            const initElements = function(items) {
                items.forEach(function(el) {
                    el.addEventListener('click', function() {
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
