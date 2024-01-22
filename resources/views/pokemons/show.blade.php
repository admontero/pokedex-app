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

    <div class="row mb-0 mb-md-2">
        <div class="col-md-4">
            <h5 class="text-capitalize text-center text-white pokemon-solid bg-dark rounded pt-1 pb-3 mb-0">
                {{ $pokemon->name }}
            </h5>
        </div>
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center h-100">
                <a
                    class="btn btn-sm bg-pokemon-red text-white d-inline-flex align-items-center {{ $pokemon->id === 1 ? 'disabled' : '' }}"
                    href="{{ route('pokemons.show', $pokemon->id - 1) }}"
                    role="button"
                >
                    <i class="fa fa-arrow-left-long me-2"></i>
                    Anterior
                </a>

                <a
                    class="btn btn-sm bg-pokemon-red text-white d-inline-flex align-items-center {{ $pokemon->id === $total ? 'disabled' : '' }}"
                    href="{{ route('pokemons.show', $pokemon->id + 1) }}"
                    role="button"
                >
                    Siguiente
                    <i class="fa fa-arrow-right-long ms-2"></i>
                </a>
            </div>
            <hr class="d-md-none">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow">
                <img
                    src="{{ $pokemon->image }}"
                    class="card-img-top px-4"
                    alt="{{ $pokemon->name }} image"
                />

                <div class="card-body p-0 text-center">
                    <div class="mb-2">
                        <h6 class="bg-dark bg-opacity-10 py-2 fw-bold">Tipos</h6>
                        <div>
                            @foreach ($pokemon->types as $type)
                                <span class="badge {{ $type }} px-2 py-1 text-capitalize">
                                    {{ $type }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-2">
                        <h6 class="bg-dark bg-opacity-10 py-2 fw-bold">Peso</h6>
                        <div>
                            <span>{{ $pokemon->weight }}</span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h6 class="bg-dark bg-opacity-10 py-2 fw-bold">Altura</h6>
                        <div>
                            <span>{{ $pokemon->height }}</span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h6 class="bg-dark bg-opacity-10 py-2 fw-bold">Experiencia Base</h6>
                        <div>
                            <span>{{ $pokemon->base_experience }}</span>
                        </div>
                    </div>
                    {{-- <div class="mb-2">
                        <h6 class="bg-dark bg-opacity-10 py-2 fw-bold">Habilidades</h6>
                        <div>
                            {{ $pokemon->abilities->implode(', ') }}
                        </div>
                    </div>
                    <div class="mb-2">
                        <h6 class="bg-dark bg-opacity-10 py-2 fw-bold">Items</h6>
                        <div>
                            {{ $pokemon->items->implode(', ') }}
                        </div>
                    </div> --}}
                </div>
            </div>

            <hr class="d-md-none">
        </div>

        <div class="col-md-8">
            <div class="card rounded shadow mb-0 mb-md-3">
                <div class="card-body p-0">
                    <h6 class="bg-dark bg-opacity-10 fw-bold py-2 text-center rounded-top mb-0">
                        Estadísticas
                    </h6>
                    <div class="p-3">
                        @foreach ($pokemon->stats as $stat)
                            <div class="d-flex">
                                <p class="text-dark text-capitalize font-weight-bold" style="width: 200px;">{{ $stat->name }}</p>
                                <div class="progress font-weight-bold text-capitalize" style="height: 20px; width:100%;">
                                    <div class="progress-bar bg-pokemon-red" role="progressbar" style="width: {{ $stat->percentage }}%"
                                        aria-valuenow="{{ $stat->percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $stat->value }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr class="d-md-none">

            <div class="row">
                <div class="col-md-6">
                    <div class="card rounded shadow">
                        <div class="card-body p-0">
                            <h6 class="bg-dark bg-opacity-10 fw-bold py-2 text-center rounded-top mb-0">
                                Sprites
                            </h6>
                            @for ($i = 0; $i < $pokemon->sprites->count() / 2; $i++)
                                <div class="d-flex justify-content-around">
                                    @foreach ($pokemon->sprites->skip($i * 2)->take(2) as $sprite)
                                        <div>
                                            <img src="{{ $sprite->image }}" alt="{{ $pokemon->name  }}'s sprite"
                                                class="d-block mx-auto">

                                            <p class="text-center">{{ $sprite->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endfor
                        </div>
                    </div>

                    <hr class="d-md-none">
                </div>

                <div class="col-md-6">
                    <div class="card rounded shadow h-100">
                        <h6 class="bg-dark bg-opacity-10 fw-bold py-2 text-center rounded-top mb-0">
                            Female Sprites
                        </h6>
                        <div class="card-body d-flex flex-column justify-content-center flex-grow-1 p-0">
                            @if ($pokemon->female_sprites->count())
                                @for ($i = 0; $i < $pokemon->female_sprites->count() / 2; $i++)
                                    <div class="d-flex justify-content-around">
                                        @foreach ($pokemon->female_sprites->skip($i * 2)->take(2) as $sprite)
                                            <div>
                                                <img
                                                    class="d-block mx-auto"
                                                    src="{{ $sprite->image }}"
                                                    alt="{{ $pokemon->name  }}'s sprite"
                                                >

                                                <p class="text-center">{{ $sprite->name }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endfor
                            @else
                                <p class="text-center fst-italic mb-0">
                                    Sin diferencia de género
                                </p>
                            @endif

                        </div>
                    </div>

                    <hr class="d-md-none">
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
