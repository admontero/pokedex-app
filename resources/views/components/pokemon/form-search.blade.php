<form action="{{ route('pokemons.search') }}" method="GET">
    <div class="row g-2 justify-content-center">
        <div class="col-lg-8 mb-1 mb-lg-0">
            <input class="form-control @error('term') is-invalid @enderror" type="search" name="term" id="term"
                placeholder="Nombre de pokémon o cualquier término..." value="{{ old('term', request()->term) }}" />

            @error('term')
                <div class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 mb-1 mb-lg-0">
            <select name="type" class="form-select text-capitalize" aria-label="Default select example">
                <option selected value="">tipo</option>
                @foreach (App\Services\PokemonService::TYPES as $type)
                    <option class="text-capitalize" value="{{ $type }}" @selected(old('type', request()->type) == $type)>{{ $type }}</option>
                @endforeach
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
