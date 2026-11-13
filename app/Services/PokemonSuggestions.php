<?php

namespace App\Services;

/**
 * Builds the search suggestions from the cached Pokemon list.
 *
 * Results are ranked so the most likely match comes first: names that start
 * with the term, then the exact Pokedex number, then numbers that start with
 * it and finally names that contain the term somewhere else.
 */
class PokemonSuggestions
{
    public const DEFAULT_LIMIT = 8;

    public function __construct(private PokemonRepository $repository) {}

    /**
     * @return array<int, array{id: int|null, name: string}>
     */
    public function search(?string $term, int $limit = self::DEFAULT_LIMIT): array
    {
        $term = strtolower(ltrim(trim((string) $term), '#'));

        if ($term === '') {
            return [];
        }

        $numeric = ctype_digit($term);
        $matches = [];

        foreach ($this->repository->all() as $pokemon) {
            $name = (string) ($pokemon['name'] ?? '');
            $id = $this->pokedexNumber($pokemon);
            $rank = $this->rank($term, $numeric, $name, $id);

            if ($rank === null) {
                continue;
            }

            $matches[] = ['rank' => $rank, 'id' => $id, 'name' => $name];
        }

        usort(
            $matches,
            fn (array $a, array $b): int => [$a['rank'], $a['id'] ?? PHP_INT_MAX] <=> [$b['rank'], $b['id'] ?? PHP_INT_MAX],
        );

        return array_map(
            fn (array $match): array => ['id' => $match['id'], 'name' => $match['name']],
            array_slice($matches, 0, max(0, $limit)),
        );
    }

    private function rank(string $term, bool $numeric, string $name, ?int $id): ?int
    {
        if (str_starts_with($name, $term)) {
            return 0;
        }

        if ($numeric) {
            if ($id === (int) $term) {
                return 1;
            }

            $digits = ltrim($term, '0') ?: '0';

            if ($id !== null && str_starts_with((string) $id, $digits)) {
                return 2;
            }
        }

        if (str_contains($name, $term)) {
            return 3;
        }

        return null;
    }

    /**
     * @param  array{url?: string}  $pokemon
     */
    private function pokedexNumber(array $pokemon): ?int
    {
        $id = basename(rtrim($pokemon['url'] ?? '', '/'));

        return ctype_digit($id) ? (int) $id : null;
    }
}
