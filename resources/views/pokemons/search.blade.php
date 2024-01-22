<x-app-layout>
    <div class="row mb-3">
        <div class="col-md-4">
            <a
                class="btn btn-sm bg-pokemon-blue text-white d-inline-flex align-items-center"
                onclick="window.history.back()"
                role="button"
            >
                <img src="https://img.icons8.com/office/30/000000/pokeball.png" class="me-2"/>
                Volver
            </a>
            <hr class="d-md-none">
        </div>
        <div class="col-md-8">
            <form action="{{ route('pokemons.search') }}" method="GET">
                <div class="input-group shadow-sm">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fa fa-magnifying-glass"></i>
                    </span>

                    <input
                        class="form-control @error('name') is-invalid @enderror"
                        type="search"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="Buscar pokémon. Ej: bulbasaur"
                    />
                </div>
                @error('name')
                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                @enderror
            </form>
        </div>
    </div>

    <hr class="d-none d-md-block">

    <h3>
        <span class="text-pokemon-red">{{ $pokemons->count() }}</span>
        resultados con el término
        <span class="text-pokemon-red">{{ request()->name }}</span>
    </h3>

    <hr>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-2">
        @foreach ($pokemons as $pokemon)
            <x-pokemon-card :$pokemon />
        @endforeach
    </div>

    @push('scripts')
        <script>
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
