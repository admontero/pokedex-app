<x-app-layout>
    <div class="card py-5 px-2 mb-3 shadow-sm">
        <form action="{{ route('pokemons.search') }}" method="GET">
            <h1 class="text-center display-4 fw-bold">Bienvenid@ a la Laravel Pokédex</h1>

            <p class="text-center lead">En esta aplicación podrás visualizar la información de cualquier pokémon. Lets do it!</p>

            <div class="row g-2 justify-content-center">
                <div class="col-lg-4 mb-1 mb-lg-0">
                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        type="search"
                        name="name"
                        id="name"
                        placeholder="Nombre de pokémon o cualquier término..."
                        value="{{ old('name') }}"
                    />

                    @error('name')
                        <div class="invalid-feedback d-block small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-3 mb-1 mb-lg-0">
                    <select name="type" class="form-select" aria-label="Default select example">
                        <option selected value="">Selecciona un tipo</option>
                        <option value="normal" @selected(old('type') == 'normal')>Normal</option>
                        <option value="fighting" @selected(old('type') == 'fighting')>Fighting</option>
                        <option value="flying" @selected(old('type') == 'flying')>Flying</option>
                        <option value="poison" @selected(old('type') == 'poison')>Poison</option>
                        <option value="ground" @selected(old('type') == 'ground')>Ground</option>
                        <option value="rock" @selected(old('type') == 'rock')>Rock</option>
                        <option value="bug" @selected(old('type') == 'bug')>Bug</option>
                        <option value="ghost" @selected(old('type') == 'ghost')>Ghost</option>
                        <option value="steel" @selected(old('type') == 'steel')>Steel</option>
                        <option value="fire" @selected(old('type') == 'fire')>Fire</option>
                        <option value="water" @selected(old('type') == 'water')>Water</option>
                        <option value="grass" @selected(old('type') == 'grass')>Grass</option>
                        <option value="electric" @selected(old('type') == 'electric')>Electric</option>
                        <option value="psychic" @selected(old('type') == 'psychic')>Psychic</option>
                        <option value="ice" @selected(old('type') == 'ice')>Ice</option>
                        <option value="dragon" @selected(old('type') == 'dragon')>Dragon</option>
                        <option value="dark" @selected(old('type') == 'dark')>Dark</option>
                        <option value="fairy" @selected(old('type') == 'fairy')>Fairy</option>
                    </select>

                    @error('type')
                        <div class="invalid-feedback d-block small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-1">
                    <div class="d-grid">
                        <button class="btn btn-pokemon-red" type="submit">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>
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
