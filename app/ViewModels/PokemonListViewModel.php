<?php

namespace App\ViewModels;

use Spatie\ViewModels\ViewModel;

class PokemonListViewModel extends ViewModel
{
    public function __construct(
        public int $total,
        public array $pokemons,
        public int $page = 1,
        public int $perPage = 16,
    ) {}

    public function pokemons(): array
    {
        $pokemons = collect($this->pokemons)
            ->map(function ($pokemon) {
                return (object) [
                    'id' => $pokemon['id'],
                    'name' => $pokemon['name'],
                    'image' => $pokemon['sprites']['other']['official-artwork']['front_default'] ?? config('pokemon.placeholder'),
                    'types' => isset($pokemon['types']) ? collect($pokemon['types'])
                        ->map(fn ($type) => $type['type']['name'])
                        ->all() : [],
                ];
            })
            ->all();

        return $pokemons;
    }

    public function shown(): int
    {
        return count($this->pokemons());
    }

    public function hasMore(): bool
    {
        // Compare the page window against the total instead of counting the
        // rendered cards: AJAX responses only carry the new page, so `shown`
        // is not the number of cards already displayed.
        return $this->page * $this->perPage < $this->total;
    }

    public function nextPage(): ?int
    {
        return $this->hasMore() ? $this->page + 1 : null;
    }
}
