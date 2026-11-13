<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class PokemonRepository
{
    private const CACHE_PREFIX = 'pokemon:';

    private const LOCK_SECONDS = 60;

    private const LOCK_WAIT_SECONDS = 15;

    /**
     * How many full payloads are fetched at once when a page misses the cache.
     * Deep pages (page 20, 40...) would otherwise hold hundreds of complete
     * PokeAPI payloads in memory at the same time.
     */
    private const FETCH_BATCH = 16;

    public function __construct(private PokeApiClient $client) {}

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function all(): array
    {
        return $this->remember('list', fn (): array => $this->client->listPokemons());
    }

    /**
     * Full detail used by the show page. Only the fields rendered by the
     * view are cached: a raw PokeAPI payload is ~500 KB on disk and several
     * MB once decoded, while this trimmed version is a few KB.
     *
     * @return array<string, mixed>
     */
    public function find(string $name): array
    {
        return $this->remember(
            $this->pokemonKey($name),
            fn (): array => $this->toDetail($this->client->getPokemon($name)),
        );
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function byType(string $type): array
    {
        return $this->client->getPokemonsByType($type);
    }

    /**
     * Slice a page out of the given list.
     *
     * When $accumulate is true, the result contains every item up to the
     * requested page. That keeps "load more" pages shareable: reloading
     * ?page=3 renders the same 48 cards instead of only the last 16.
     *
     * Cards are cached in a compact shape (id, name, image and type names),
     * so rendering 80 cards does not require 80 full PokeAPI payloads.
     *
     * @return array<int, array{id: int|string|null, name: string, image: string, types: array<int, string>}>
     */
    public function paginate(array $data = [], int $page = 1, ?int $perPage = null, bool $accumulate = false): array
    {
        $perPage ??= (int) config('pokemon.per_page', 16);

        $paginatedPokemons = collect($data)
            ->when(
                $accumulate,
                fn ($collection) => $collection->take($perPage * $page),
                fn ($collection) => $collection->skip($perPage * ($page - 1))->take($perPage),
            );

        $cards = [];
        $missingUrls = [];

        foreach ($paginatedPokemons as $pokemon) {
            $card = Cache::get($this->cardKey($pokemon['name']));

            if ($card !== null) {
                $cards[] = $card;

                continue;
            }

            // A previous detail view may already hold the trimmed payload.
            $detail = Cache::get($this->cacheKey($this->pokemonKey($pokemon['name'])));

            if ($detail !== null && isset($detail['id'], $detail['name'])) {
                $card = $this->toCard($detail);
                Cache::put($this->cardKey($card['name']), $card, $this->cacheTtl());
                $cards[] = $card;

                continue;
            }

            $missingUrls[] = $pokemon['url'];
        }

        foreach (array_chunk($missingUrls, self::FETCH_BATCH) as $urls) {
            foreach ($this->client->getPokemonsByUrl($urls) as $payload) {
                $card = $this->toCard($payload);

                Cache::put($this->cardKey($card['name']), $card, $this->cacheTtl());
                Cache::put($this->cacheKey($this->pokemonKey($payload['name'])), $this->toDetail($payload), $this->cacheTtl());

                $cards[] = $card;
            }
        }

        usort($cards, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $cards;
    }

    /**
     * Cache the callback result, computing it only once even if several
     * requests miss the cache at the same time (cache stampede).
     */
    public function remember(string $key, callable $callback): array
    {
        $key = $this->cacheKey($key);

        if (($cached = Cache::get($key)) !== null) {
            return $cached;
        }

        try {
            return Cache::lock("{$key}:lock", self::LOCK_SECONDS)
                ->block(self::LOCK_WAIT_SECONDS, fn () => Cache::remember($key, $this->cacheTtl(), $callback));
        } catch (LockTimeoutException) {
            // Another worker is taking too long; answer with fresh data
            // instead of failing the request.
            return Cache::remember($key, $this->cacheTtl(), $callback);
        }
    }

    /**
     * @param  array<string, mixed>  $pokemon
     * @return array{id: int|string|null, name: string, image: string, types: array<int, string>}
     */
    private function toCard(array $pokemon): array
    {
        return [
            'id' => $pokemon['id'] ?? null,
            'name' => $pokemon['name'] ?? '',
            'image' => $pokemon['sprites']['other']['official-artwork']['front_default']
                ?? config('pokemon.placeholder'),
            'types' => $this->typeNames($pokemon['types'] ?? []),
        ];
    }

    /**
     * Keep only the fields used by PokemonViewModel, preserving the shape the
     * view expects for types, abilities, items, stats and sprites.
     *
     * @param  array<string, mixed>  $pokemon
     * @return array<string, mixed>
     */
    private function toDetail(array $pokemon): array
    {
        $sprites = $pokemon['sprites'] ?? [];

        return [
            'id' => $pokemon['id'] ?? null,
            'name' => $pokemon['name'] ?? null,
            'weight' => $pokemon['weight'] ?? null,
            'height' => $pokemon['height'] ?? null,
            'base_experience' => $pokemon['base_experience'] ?? null,
            'types' => array_map(
                fn (array $type): array => ['type' => ['name' => $type['type']['name'] ?? '']],
                array_values($pokemon['types'] ?? []),
            ),
            'abilities' => array_map(
                fn (array $ability): array => ['ability' => ['name' => $ability['ability']['name'] ?? '']],
                array_values($pokemon['abilities'] ?? []),
            ),
            'held_items' => array_map(
                fn (array $item): array => ['item' => ['name' => $item['item']['name'] ?? '']],
                array_values($pokemon['held_items'] ?? []),
            ),
            'stats' => array_map(
                fn (array $stat): array => [
                    'base_stat' => $stat['base_stat'] ?? 0,
                    'stat' => ['name' => $stat['stat']['name'] ?? ''],
                ],
                array_values($pokemon['stats'] ?? []),
            ),
            'sprites' => [
                'front_default' => $sprites['front_default'] ?? null,
                'front_shiny' => $sprites['front_shiny'] ?? null,
                'back_default' => $sprites['back_default'] ?? null,
                'back_shiny' => $sprites['back_shiny'] ?? null,
                'front_female' => $sprites['front_female'] ?? null,
                'front_shiny_female' => $sprites['front_shiny_female'] ?? null,
                'back_female' => $sprites['back_female'] ?? null,
                'back_shiny_female' => $sprites['back_shiny_female'] ?? null,
                'other' => [
                    'official-artwork' => [
                        'front_default' => $sprites['other']['official-artwork']['front_default'] ?? null,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{type?: array{name?: string}}|string>  $types
     * @return array<int, string>
     */
    private function typeNames(array $types): array
    {
        return array_values(array_map(
            fn ($type): string => is_array($type) ? ($type['type']['name'] ?? '') : (string) $type,
            $types,
        ));
    }

    private function cardKey(string $name): string
    {
        return $this->cacheKey('card:'.$name);
    }

    private function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }

    private function pokemonKey(string $name): string
    {
        return "name:{$name}";
    }

    private function cacheTtl(): int
    {
        return (int) config('pokemon.cache_ttl', 3600);
    }
}
