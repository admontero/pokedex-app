<?php

namespace App\Services;

use App\DTOs\PokemonSearchDTO;

class PokemonSearch
{
    public function __construct(private PokemonRepository $repository) {}

    public function apply(PokemonSearchDTO $dto): array
    {
        return $this->repository->remember(
            $this->cacheKey($dto),
            fn (): array => ! empty($dto->type)
                ? $this->filterByType($dto)
                : $this->filterByTerm($dto->term),
        );
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function filterByType(PokemonSearchDTO $dto): array
    {
        $results = $this->repository->byType($dto->type);

        if ($dto->term) {
            $results = array_filter(
                $results,
                fn (array $pokemon) => $this->matches($pokemon, $dto->term),
            );
        }

        return array_values($results);
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function filterByTerm(?string $term): array
    {
        $term = (string) $term;

        $results = array_filter(
            $this->repository->all(),
            fn (array $pokemon) => $this->matches($pokemon, $term),
        );

        return array_values($results);
    }

    /**
     * A term matches either the name (substring) or the Pokedex number
     * (exact). Numbers accept "#25" and "025" as well as "25".
     *
     * @param  array{name?: string, url?: string}  $pokemon
     */
    private function matches(array $pokemon, string $term): bool
    {
        $term = $this->normalizeTerm($term);

        if ($term === '') {
            return true;
        }

        if (str_contains($pokemon['name'] ?? '', $term)) {
            return true;
        }

        return ctype_digit($term) && $this->pokedexNumber($pokemon) === (int) $term;
    }

    private function normalizeTerm(?string $term): string
    {
        return strtolower(ltrim(trim((string) $term), '#'));
    }

    /**
     * @param  array{url?: string}  $pokemon
     */
    private function pokedexNumber(array $pokemon): ?int
    {
        $id = basename(rtrim($pokemon['url'] ?? '', '/'));

        return ctype_digit($id) ? (int) $id : null;
    }

    private function cacheKey(PokemonSearchDTO $dto): string
    {
        // v2: numeric terms changed the matching rules, so old cached
        // searches must not be reused.
        return sprintf(
            'search:v2:%s:%s',
            $dto->type ?? '-',
            $this->normalizeTerm($dto->term) ?: '-',
        );
    }
}
