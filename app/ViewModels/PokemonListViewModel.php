<?php

namespace App\ViewModels;

use Spatie\ViewModels\ViewModel;

class PokemonListViewModel extends ViewModel
{
    public function __construct(
        public int $total,
        public array $pokemons,
    ){}

    public function pokemons(): array
    {
        $pokemons = collect($this->pokemons)
            ->map(function ($pokemon) {
                return (object) [
                    'id' => $pokemon['id'],
                    'name' => $pokemon['name'],
                    'image' => $pokemon['sprites']['other']['official-artwork']['front_default'] ?? 'https://placehold.co/300x300/white/lightgray?text=NOT+FOUND',
                    'types' => isset($pokemon['types']) ? collect($pokemon['types'])
                        ->map(fn ($type) => $type['type']['name'])
                        ->all() : [],
                ];
            })
            ->all();

        return $pokemons;
    }
}
