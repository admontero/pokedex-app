<x-app-layout>
    <div class="bg-white text-center py-5 px-2 mb-3">
        <form action="{{ route('pokemons.search') }}" method="GET">
            <h1 class="display-4 fw-bold">Bienvenid@ a la Laravel Pokédex</h1>

            <p class="lead">En esta aplicación podrás visualizar la información de cualquier pokémon. Lets do it!</p>

            <div class="row">
                <div class="col-md-8 col-lg-6 mx-auto">
                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        type="search"
                        name="name"
                        id="name"
                        placeholder="Nombre del Pokémon. Ej: bulbasaur"
                    />
                </div>
            </div>

            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </form>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-2" id="main-content">
        @foreach ($pokemons as $pokemon)
            <x-pokemon-card :$pokemon />
        @endforeach
    </div>

    <div class="my-5">
        <div class="page-load-status">
            <div class="d-flex justify-content-center">
                <div class="infinite-scroll-request spinner-border text-pokemon-red" role="status">
                    <span class="sr-only"></span>
                </div>
                <div class="infinite-scroll-last">
                    <img
                        src="{{ asset('/vendor/images/pikachu.jpg') }}"
                        class="d-block mx-auto rounded rounded-3 shadow border border-light"
                        width="200"
                        height="200"
                        alt="pikachu llorando"
                    />
                    <p class="fst-italic mt-4">
                        ¡Ooops...! Estos son todos los registros hasta ahora.
                    </p>
                </div>
                <div class="infinite-scroll-error">
                    <img
                        class="d-block mx-auto rounded rounded-3 shadow border border-light"
                        src="{{ asset('/vendor/images/rocket.png') }}"
                        width="200"
                        height="200"
                        alt="equipo rocket"
                    />
                    <p class="fst-italic mt-4">
                        ¡Houston tenemos un problema!
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.min.js"></script>

        <script>
            let elem = document.querySelector('#main-content');

            let infScroll = new InfiniteScroll(elem, {
                path: '/?page=@{{#}}',
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
