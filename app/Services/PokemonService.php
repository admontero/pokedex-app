<?php

namespace App\Services;

use App\DTOs\PokemonSearchDTO;
use Illuminate\Support\Facades\Cache;

class PokemonService extends ClientService
{
    public const TYPES = [
        'normal',
        'fighting',
        'flying',
        'poison',
        'ground',
        'rock',
        'bug',
        'ghost',
        'steel',
        'fire',
        'water',
        'grass',
        'electric',
        'psychic',
        'ice',
        'dragon',
        'dark',
        'fairy',
    ];

    public function __construct()
    {
        parent::__construct(
            baseUrl: config('services.pokemon.base_url'),
            headers: [
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
            ],
            withErrorHandler: false,
        );
    }

    public function getAllPokemon(): array
    {
        $url = 'pokemon?limit=-1';

        $results = Cache::remember('POKEMON_LIST', 60 * 60, function () use ($url) {
            ['results' => $results] = $this->fetch($url);

            return $results;
        });

        return $results;
    }

    public function getAllBySearch(PokemonSearchDTO $dto): array
    {
        $cacheKey = 'POKEMON_LIST=';

        $cacheKey .= collect(get_object_vars($dto))
            ->each(fn ($value, $key) => "{$key}:{$value},");

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = match (true) {
            ! empty($dto->type) => $this->filterByType($dto),
            empty($dto->type) => $this->filterByTerm($dto->term),
            default => [],
        };

        Cache::put($cacheKey, $data, 60 * 60);

        return $data;
    }

    public function paginate(array $data = [], int $page = 1, int $perPage = 16): array
    {
        $paginatedPokemons = collect($data)
            ->skip($perPage * ($page - 1))
            ->take($perPage);

        $pokemonData = $paginatedPokemons
            ->filter(fn ($pokemon) => Cache::has($this->getCacheKeyByPokemonName($pokemon['name'])))
            ->map(fn ($pokemon) => Cache::get($this->getCacheKeyByPokemonName($pokemon['name'])))
            ->all();

        $pokemonUrls = $paginatedPokemons
            ->filter(fn ($pokemon) => ! Cache::has($this->getCacheKeyByPokemonName($pokemon['name'])))
            ->pluck('url')
            ->all();

        $pokemonData = [...$pokemonData, ...$this->fetchMultiple($pokemonUrls)];

        collect($pokemonData)
            ->filter(fn ($pokemon) => ! Cache::has($this->getCacheKeyByPokemonName($pokemon['name'])))
            ->each(fn ($pokemon) => Cache::put($this->getCacheKeyByPokemonName($pokemon['name']), $pokemon, 60 * 60));

        usort($pokemonData, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $pokemonData;
    }

    public function getPokemon(string $name): array
    {
        $pokemon = Cache::remember($this->getCacheKeyByPokemonName($name), 60 * 60, function () use ($name) {
            $data = $this->fetch("pokemon/{$name}");

            return $data;
        });

        return $pokemon;
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

    private function filterByType(PokemonSearchDTO $dto)
    {
        $url = "type/{$dto->type}";

        [ 'pokemon' => $pokemons ] = $this->fetch($url);

        $results = collect($pokemons)
            ->map(fn ($pokemon) => $pokemon['pokemon'])
            ->all();

        if ($dto->term) {
            $results = array_filter($results, fn ($pokemon) => str_contains($pokemon['name'], $dto->term));
        }

        return $results;
    }

    private function filterByTerm(string $term): array
    {
        $pokemonList = $this->getAllPokemon();

        $results = array_filter($pokemonList, fn ($pokemon) => str_contains($pokemon['name'], $term));

        return $results;
    }

    protected function getCacheKeyByPokemonName(string $name): string
    {
        return "POKEMON:name={$name}";
    }
}
