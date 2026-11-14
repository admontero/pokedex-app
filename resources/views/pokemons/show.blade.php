@php
    $firstType = $pokemon->types[0] ?? 'normal';
    $number = str_pad((string) $pokemon->id, 3, '0', STR_PAD_LEFT);
    $fromIsSearch = is_string($from) && str_contains($from, '/pokemon/search');

    $breadcrumb = [
        ['label' => 'Inicio', 'url' => route('pokemons.index')],
    ];

    if ($fromIsSearch) {
        $breadcrumb[] = ['label' => 'Resultados', 'url' => $from];
    }

    $breadcrumb[] = ['label' => ucfirst($pokemon->name)];

    $previousUrl = $previous
        ? route('pokemons.show', array_filter(['name' => $previous, 'from' => $from]))
        : null;

    $nextUrl = $next
        ? route('pokemons.show', array_filter(['name' => $next, 'from' => $from]))
        : null;
@endphp

<x-app-layout
    :title="ucfirst($pokemon->name)"
    :description="'Información, estadísticas y sprites de '.$pokemon->name.'.'"
>
    <x-breadcrumb :items="$breadcrumb" />

    <div class="row align-items-center g-3 mb-4">
        <div class="col-md-4">
            <a
                class="btn btn-sm btn-secondary text-white d-inline-flex align-items-center"
                href="{{ $backUrl ?? route('pokemons.index') }}"
            >
                <x-icon name="arrow-left" class="me-2" />
                Volver
            </a>
        </div>
        <div class="col-md-8">
            <x-pokemon.form-search />
        </div>
    </div>

    <header class="pokemon-detail-header type-tint--{{ $firstType }} p-3 p-md-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <p class="pokemon-detail-header__number mb-1">#{{ $number }}</p>
                <h1 class="pokemon-detail-title">{{ $pokemon->name }}</h1>

                <div class="d-flex flex-wrap gap-2">
                    @foreach ($pokemon->types as $type)
                        <x-pokemon.type-badge :type="$type" />
                    @endforeach
                </div>
            </div>

            <div class="col-md-5">
                <div class="d-flex justify-content-md-end gap-2">
                    @if ($previousUrl)
                        <a
                            class="btn btn-sm btn-pokemon-blue text-white d-inline-flex align-items-center prev-next-btn"
                            href="{{ $previousUrl }}"
                            rel="prev"
                            aria-label="Anterior: {{ $previous }}"
                        >
                            <x-icon name="arrow-left-long" class="me-2" />
                            <span>Anterior</span>
                            <span class="prev-next-btn__name ms-1">{{ $previous }}</span>
                        </a>
                    @else
                        <span class="btn btn-sm btn-pokemon-blue text-white d-inline-flex align-items-center prev-next-btn disabled" aria-disabled="true">
                            <x-icon name="arrow-left-long" class="me-2" />
                            <span>Anterior</span>
                        </span>
                    @endif

                    @if ($nextUrl)
                        <a
                            class="btn btn-sm btn-pokemon-blue text-white d-inline-flex align-items-center prev-next-btn"
                            href="{{ $nextUrl }}"
                            rel="next"
                            aria-label="Siguiente: {{ $next }}"
                        >
                            <span>Siguiente</span>
                            <span class="prev-next-btn__name ms-1">{{ $next }}</span>
                            <x-icon name="arrow-right-long" class="ms-2" />
                        </a>
                    @else
                        <span class="btn btn-sm btn-pokemon-blue text-white d-inline-flex align-items-center prev-next-btn disabled" aria-disabled="true">
                            <span>Siguiente</span>
                            <x-icon name="arrow-right-long" class="ms-2" />
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <img
                    src="{{ $pokemon->image }}"
                    class="card-img-top pokemon-detail-image px-4 pt-3"
                    alt="Ilustración oficial de {{ $pokemon->name }}"
                    width="475"
                    height="475"
                    fetchpriority="high"
                    decoding="async"
                />

                <div class="card-body p-0 text-center">
                    <div class="mb-2">
                        <h2 class="h6 bg-body-secondary py-2 fw-bold mb-0">Tipos</h2>
                        <div class="d-flex flex-wrap justify-content-center gap-2 py-2">
                            @foreach ($pokemon->types as $type)
                                <x-pokemon.type-badge :type="$type" />
                            @endforeach
                        </div>
                    </div>
                    <div class="mb-2">
                        <h2 class="h6 bg-body-secondary py-2 fw-bold mb-0">Peso</h2>
                        <div>
                            <span>{{ $pokemon->weight }}</span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h2 class="h6 bg-body-secondary py-2 fw-bold mb-0">Altura</h2>
                        <div>
                            <span>{{ $pokemon->height }}</span>
                        </div>
                    </div>
                    <div class="mb-2">
                        <h2 class="h6 bg-body-secondary py-2 fw-bold mb-0">Experiencia base</h2>
                        <div>
                            <span>{{ $pokemon->base_experience }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card rounded shadow-sm mb-0 mb-md-3">
                <div class="card-body p-0">
                    <h2 class="h6 bg-body-secondary fw-bold py-2 text-center rounded-top mb-0">
                        Estadísticas
                    </h2>
                    <div class="p-3">
                        @foreach ($pokemon->stats as $stat)
                            @php
                                $statId = 'stat-'.$loop->index;
                            @endphp

                            <div class="stat-row">
                                <p class="stat-row__label text-body" id="{{ $statId }}-label">{{ $stat->name }}</p>
                                <div
                                    class="progress stat-row__bar"
                                    role="progressbar"
                                    aria-labelledby="{{ $statId }}-label"
                                    aria-valuenow="{{ $stat->value }}"
                                    aria-valuemin="0"
                                    aria-valuemax="255"
                                    aria-valuetext="{{ $stat->value }} de 255 ({{ (int) round($stat->percentage) }}%)"
                                >
                                    <div class="progress-bar bg-pokemon-red" style="width: {{ round($stat->percentage, 1) }}%"></div>
                                </div>
                                <span class="stat-row__value">{{ $stat->value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card rounded shadow-sm h-100">
                        <div class="card-body p-0">
                            <h2 class="h6 bg-body-secondary fw-bold py-2 text-center rounded-top mb-0">
                                Sprites
                            </h2>
                            @foreach ($pokemon->sprites->chunk(2) as $spriteRow)
                                <div class="d-flex justify-content-around">
                                    @foreach ($spriteRow as $sprite)
                                        <div>
                                            <img
                                                src="{{ $sprite->image }}"
                                                alt="{{ $sprite->name }} de {{ $pokemon->name }}"
                                                class="d-block mx-auto"
                                                width="96"
                                                height="96"
                                                loading="lazy"
                                                decoding="async"
                                            >

                                            <p class="text-center">{{ $sprite->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card rounded shadow-sm h-100">
                        <h2 class="h6 bg-body-secondary fw-bold py-2 text-center rounded-top mb-0">
                            Sprites de hembra
                        </h2>
                        <div class="card-body d-flex flex-column justify-content-center flex-grow-1 p-0">
                            @if ($pokemon->female_sprites->count())
                                @foreach ($pokemon->female_sprites->chunk(2) as $spriteRow)
                                    <div class="d-flex justify-content-around">
                                        @foreach ($spriteRow as $sprite)
                                            <div>
                                                <img
                                                    class="d-block mx-auto"
                                                    src="{{ $sprite->image }}"
                                                    alt="{{ $sprite->name }} de {{ $pokemon->name }}"
                                                    width="96"
                                                    height="96"
                                                    loading="lazy"
                                                    decoding="async"
                                                >

                                                <p class="text-center">{{ $sprite->name }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            @else
                                <p class="text-center fst-italic mb-0">
                                    Sin diferencias de género
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
