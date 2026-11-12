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
                fn (array $pokemon) => str_contains($pokemon['name'], $dto->term),
            );
        }

        return array_values($results);
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    private function filterByTerm(?string $term): array
    {
        $results = array_filter(
            $this->repository->all(),
            fn (array $pokemon) => str_contains($pokemon['name'], (string) $term),
        );

        return array_values($results);
    }

    private function cacheKey(PokemonSearchDTO $dto): string
    {
        return sprintf('search:%s:%s', $dto->type ?? '-', $dto->term ?? '-');
    }
}
