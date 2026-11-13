@props(['pokemon', 'from' => null, 'priority' => false])

@php
    $firstType = $pokemon->types[0] ?? 'normal';
    $number = str_pad((string) $pokemon->id, 3, '0', STR_PAD_LEFT);
    $href = route('pokemons.show', array_filter([
        'name' => $pokemon->name,
        'from' => $from,
    ]));
@endphp

<div class="col">
    <a
        class="pokemon-card card text-decoration-none type-tint--{{ $firstType }}"
        href="{{ $href }}"
        id="{{ $pokemon->name }}"
    >
        <div class="pokemon-card__media">
            <span class="pokemon-card__number" aria-hidden="true">#{{ $number }}</span>

            <img
                class="pokemon-card-image"
                src="{{ $pokemon->image }}"
                alt=""
                width="300"
                height="300"
                loading="{{ $priority ? 'eager' : 'lazy' }}"
                fetchpriority="{{ $priority ? 'high' : 'auto' }}"
                decoding="async"
            >
        </div>

        <div class="card-body pokemon-card__body">
            <h2 class="card-title pokemon-card__name">
                <span class="visually-hidden">Número {{ $number }}: </span>{{ $pokemon->name }}
            </h2>

            <div class="pokemon-card__types">
                @foreach ($pokemon->types as $type)
                    <x-pokemon.type-badge :type="$type" />
                @endforeach
            </div>
        </div>
    </a>
</div>
