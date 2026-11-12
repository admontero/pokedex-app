@props(['pokemon'])

<div class="col">
    <a
        class="pokemon-card card shadow text-decoration-none"
        href="{{ route('pokemons.show', $pokemon->name) }}"
        id="{{ $pokemon->name }}"
    >
        <img
            class="pokemon-card-image card-img-top mx-auto"
            src="{{ $pokemon->image }}"
            alt="{{ $pokemon->name }}"
            width="300"
            height="300"
            loading="lazy"
            decoding="async"
        >

        <div class="card-body bg-dark rounded-bottom">
            <h5 class="card-title text-capitalize pokemon-solid text-center text-white">
                {{ $pokemon->name }}
            </h5>

            <div class="text-center mt-3">
                @foreach ($pokemon->types as $type)
                    <span class="badge {{ $type }} fw-semibold px-2 py-1 text-capitalize shadow-lg">
                        {{ $type }}
                    </span>
                @endforeach
            </div>
        </div>
    </a>
</div>
