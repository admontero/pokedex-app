<?php

namespace App\Services;

use App\DTOs\PokemonSearchDTO;

/**
 * Entry point for the pokedex. Transport lives in PokeApiClient, caching in
 * PokemonRepository and filtering in PokemonSearch.
 */
class PokemonService
{
    public function __construct(
        private PokemonRepository $repository,
        private PokemonSearch $search,
    ) {}

    public function getAllPokemon(): array
    {
        return $this->repository->all();
    }

    public function getAllBySearch(PokemonSearchDTO $dto): array
    {
        return $this->search->apply($dto);
    }

    public function paginate(array $data = [], int $page = 1, ?int $perPage = null): array
    {
        return $this->repository->paginate($data, $page, $perPage);
    }

    public function getPokemon(string $name): array
    {
        return $this->repository->find($name);
    }

    public function getNextPokemonName(string $name): ?string
    {
        return $this->adjacentPokemonName($name, 1);
    }

    public function getPreviousPokemonName(string $name): ?string
    {
        return $this->adjacentPokemonName($name, -1);
    }

    private function adjacentPokemonName(string $name, int $offset): ?string
    {
        $pokemonList = $this->repository->all();

        $index = collect($pokemonList)
            ->search(fn ($pokemon) => $pokemon['name'] === $name);

        if ($index === false) {
            return null;
        }

        $adjacentPokemon = collect($pokemonList)->get($index + $offset);

        return $adjacentPokemon['name'] ?? null;
    }
}
