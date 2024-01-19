@props(['pokemon'])

<div class="col">
    <div class="pokemon-card card shadow bg-white mb-4">
        <img
            class="card-img-top mx-auto"
            style="width: 250px;"
            src="{{ $pokemon->image }}"
            alt="{{ $pokemon->name }} image"
        >

        <div class="card-body bg-dark rounded-bottom">
            <h5 class="card-title text-capitalize pokemon-solid text-center">
                <a
                    class="text-white text-decoration-none"
                    href="{{ route('pokemons.show', $pokemon->name) }}"
                >
                    {{ $pokemon->name }}
                </a>
            </h5>

            <div class="text-center mt-3">
                @foreach ($pokemon->types as $type)
                    <span class="badge {{ $type }} fw-semibold px-2 py-1 text-capitalize shadow-lg">
                        {{ $type }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</div>
