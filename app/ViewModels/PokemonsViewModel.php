<?php

namespace App\ViewModels;

use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;

class PokemonsViewModel extends ViewModel
{
    public function __construct(
        public array $pokemons
    ){}

    public function pokemons(): Collection
    {
        $pokemons = collect($this->pokemons)->map(function ($pokemon) {
            return (object) [
                'id' => $pokemon['id'],
                'name' => $pokemon['name'],
                'image' => $pokemon['sprites']['other']['official-artwork']['front_default'] ?? 'https://placehold.co/300x300/white/lightgray?text=NOT+FOUND',
                'types' => collect($pokemon['types'])->map(fn ($type) => $type['type']['name']),
            ];
        });

        return $pokemons;
    }
}
