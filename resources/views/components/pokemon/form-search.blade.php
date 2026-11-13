@php
    $searchErrors = collect($errors->getBag('default')->getMessages())
        ->only(['term', 'type'])
        ->flatten();
@endphp

<form
    action="{{ route('pokemons.search') }}"
    method="GET"
    role="search"
    aria-label="Buscar Pokémon"
    data-search-form
>
    @if ($searchErrors->isNotEmpty())
        <div class="alert alert-danger" role="alert">
            <p class="fw-semibold mb-1">Revisa los datos de búsqueda:</p>
            <ul class="mb-0 ps-3">
                @foreach ($searchErrors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-2 align-items-end justify-content-center">
        <div class="col-lg-7 mb-1 mb-lg-0">
            <label class="form-label" for="term">Nombre o número</label>

            <input
                class="form-control @error('term') is-invalid @enderror"
                type="search"
                name="term"
                id="term"
                placeholder="Ej. pikachu o 25"
                value="{{ old('term', request()->term) }}"
                aria-describedby="term-help @error('term') term-error @enderror"
                @error('term') aria-invalid="true" autofocus @enderror
            />

            <div id="term-help" class="form-text">Busca por el nombre del Pokémon.</div>

            @error('term')
                <div id="term-error" class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 mb-1 mb-lg-0">
            <label class="form-label" for="type">Tipo</label>

            <select
                name="type"
                id="type"
                class="form-select text-capitalize @error('type') is-invalid @enderror"
                @error('type') aria-invalid="true" aria-describedby="type-error" autofocus @enderror
            >
                <option value="">Todos los tipos</option>
                @foreach (config('pokemon.types') as $type)
                    <option class="text-capitalize" value="{{ $type }}" @selected(old('type', request()->type) == $type)>
                        {{ config('pokemon.type_labels.'.$type, $type) }}
                    </option>
                @endforeach
            </select>

            @error('type')
                <div id="type-error" class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-2">
            <div class="d-grid">
                <button class="btn btn-pokemon-red d-inline-flex align-items-center justify-content-center" type="submit" data-search-submit>
                    <span class="spinner-border spinner-border-sm me-2 d-none" data-search-spinner aria-hidden="true"></span>
                    <span data-search-label>Buscar</span>
                </button>
            </div>
        </div>
    </div>
</form>
