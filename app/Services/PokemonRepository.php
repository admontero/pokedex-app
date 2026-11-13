<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class PokemonRepository
{
    private const CACHE_PREFIX = 'pokemon:';

    private const LOCK_SECONDS = 60;

    private const LOCK_WAIT_SECONDS = 15;

    public function __construct(private PokeApiClient $client) {}

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function all(): array
    {
        return $this->remember('list', fn (): array => $this->client->listPokemons());
    }

    /**
     * @return array<string, mixed>
     */
    public function find(string $name): array
    {
        return $this->remember($this->pokemonKey($name), fn (): array => $this->client->getPokemon($name));
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

        $pokemonData = [];
        $pokemonUrls = [];

        foreach ($paginatedPokemons as $pokemon) {
            $cached = Cache::get($this->cacheKey($this->pokemonKey($pokemon['name'])));

            if ($cached !== null) {
                $pokemonData[] = $cached;
            } else {
                $pokemonUrls[] = $pokemon['url'];
            }
        }

        if ($pokemonUrls !== []) {
            foreach ($this->client->getPokemonsByUrl($pokemonUrls) as $pokemon) {
                Cache::put($this->cacheKey($this->pokemonKey($pokemon['name'])), $pokemon, $this->cacheTtl());

                $pokemonData[] = $pokemon;
            }
        }

        usort($pokemonData, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $pokemonData;
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
