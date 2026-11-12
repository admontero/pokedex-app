<?php

namespace App\Services;

use App\DTOs\PokemonSearchDTO;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class PokemonService extends ClientService
{
    private const CACHE_PREFIX = 'pokemon:';

    private const LIST_CACHE_KEY = self::CACHE_PREFIX.'list';

    private const LOCK_SECONDS = 60;

    private const LOCK_WAIT_SECONDS = 15;

    public function __construct(?callable $handler = null)
    {
        parent::__construct(
            baseUrl: config('services.pokemon.base_url'),
            headers: [
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
            ],
            withErrorHandler: false,
            handler: $handler,
        );
    }

    public function getAllPokemon(): array
    {
        return $this->remember(self::LIST_CACHE_KEY, function (): array {
            $response = $this->fetch('pokemon?limit=100000');

            return $response['results'] ?? [];
        });
    }

    public function getAllBySearch(PokemonSearchDTO $dto): array
    {
        return $this->remember(
            $this->searchCacheKey($dto),
            fn (): array => match (true) {
                ! empty($dto->type) => $this->filterByType($dto),
                default => $this->filterByTerm($dto->term),
            },
        );
    }

    public function paginate(array $data = [], int $page = 1, ?int $perPage = null): array
    {
        $perPage ??= (int) config('pokemon.per_page', 16);

        $paginatedPokemons = collect($data)
            ->skip($perPage * ($page - 1))
            ->take($perPage);

        $pokemonData = [];
        $pokemonUrls = [];

        foreach ($paginatedPokemons as $pokemon) {
            $cached = Cache::get($this->pokemonCacheKey($pokemon['name']));

            if ($cached !== null) {
                $pokemonData[] = $cached;
            } else {
                $pokemonUrls[] = $pokemon['url'];
            }
        }

        if ($pokemonUrls !== []) {
            foreach ($this->fetchMultiple($pokemonUrls) as $pokemon) {
                Cache::put($this->pokemonCacheKey($pokemon['name']), $pokemon, $this->cacheTtl());

                $pokemonData[] = $pokemon;
            }
        }

        usort($pokemonData, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $pokemonData;
    }

    public function getPokemon(string $name): array
    {
        return $this->remember(
            $this->pokemonCacheKey($name),
            fn (): array => $this->fetch("pokemon/{$name}"),
        );
    }

    public function getNextPokemonName(string $name): ?string
    {
        $pokemonList = $this->getAllPokemon();

        $index = collect($pokemonList)
            ->search(fn ($pokemon) => $pokemon['name'] === $name);

        $nextPokemon = collect($pokemonList)
            ->get($index + 1);

        return $nextPokemon['name'] ?? null;
    }

    public function getPreviousPokemonName(string $name): ?string
    {
        $pokemonList = $this->getAllPokemon();

        $index = collect($pokemonList)
            ->search(fn ($pokemon) => $pokemon['name'] === $name);

        $previousPokemon = collect($pokemonList)
            ->get($index - 1);

        return $previousPokemon['name'] ?? null;
    }

    private function filterByType(PokemonSearchDTO $dto): array
    {
        $url = "type/{$dto->type}";

        ['pokemon' => $pokemons] = $this->fetch($url);

        $results = collect($pokemons)
            ->map(fn ($pokemon) => $pokemon['pokemon'])
            ->all();

        if ($dto->term) {
            $results = array_filter($results, fn ($pokemon) => str_contains($pokemon['name'], $dto->term));
        }

        return array_values($results);
    }

    private function filterByTerm(?string $term): array
    {
        $pokemonList = $this->getAllPokemon();

        $results = array_filter(
            $pokemonList,
            fn ($pokemon) => str_contains($pokemon['name'], (string) $term),
        );

        return array_values($results);
    }

    /**
     * Cache the callback result, computing it only once even if several
     * requests miss the cache at the same time (cache stampede).
     */
    private function remember(string $key, callable $callback): array
    {
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

    private function cacheTtl(): int
    {
        return (int) config('pokemon.cache_ttl', 3600);
    }

    private function searchCacheKey(PokemonSearchDTO $dto): string
    {
        return sprintf('%ssearch:%s:%s', self::CACHE_PREFIX, $dto->type ?? '-', $dto->term ?? '-');
    }

    private function pokemonCacheKey(string $name): string
    {
        return self::CACHE_PREFIX."name:{$name}";
    }
}
