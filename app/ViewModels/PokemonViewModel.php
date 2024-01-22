<?php

namespace App\ViewModels;

use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;

class PokemonViewModel extends ViewModel
{
    public function __construct(
        public array $pokemon
    ){}

    public function pokemon(): object
    {
        $pokemon = (object) [
            'id' => $this->pokemon['id'],
            'name' => $this->pokemon['name'],
            'image' => $this->pokemon['sprites']['other']['official-artwork']['front_default'] ?? 'https://placehold.co/300x300/white/lightgray?text=NOT+FOUND',
            'weight' => ($this->pokemon['weight'] / 10) . ' kg',
            'height' => $this->pokemon['height'] . ' m',
            'base_experience' => $this->pokemon['base_experience'],
            'types' => collect($this->pokemon['types'])->map(fn($type) => $type['type']['name']),
            'abilities' => collect($this->pokemon['abilities'])->map(fn($type) => str_replace('-', ' ', $type['ability']['name'])),
            'items' => collect($this->pokemon['held_items'])->map(fn($type) => str_replace('-', ' ', $type['item']['name'])),
            'stats' => $this->formatStats(),
            'sprites' => $this->formatSprites(),
            'female_sprites' => $this->formatFemaleSprites(),
        ];

        return $pokemon;
    }

    private function formatStats(): Collection
    {
        return collect($this->pokemon['stats'])
            ->map(function ($stat) {
                return (object) [
                    'name' => str_replace('-', ' ', $stat['stat']['name']),
                    'value' => $stat['base_stat'],
                    'percentage' => ($stat['base_stat'] * 100) / 255,
                ];
            });
    }

    private function formatSprites(): Collection
    {
        return collect($this->pokemon['sprites'])
            ->only('back_default', 'back_shiny', 'front_default', 'front_shiny')
            ->mapWithKeys(function ($item, $key) {
                return (object) [
                    $key => (object) [
                        'name' => ucfirst(str_replace('_', ' ', $key)),
                        'image' => $item ?? 'https://placehold.co/90x90/white/lightgray?text=NOT+FOUND'
                    ]
                ];
            });
    }

    private function formatFemaleSprites(): Collection
    {
        return collect($this->pokemon['sprites'])
            ->only('back_female', 'back_shiny_female', 'front_female', 'front_shiny_female')
            ->reject(fn ($value) => is_null($value))
            ->mapWithKeys(function ($item, $key) {
                return (object) [
                    $key => (object) [
                        'name' => ucfirst(str_replace(' female', '', str_replace('_', ' ', $key))),
                        'image' => $item ?? 'https://placehold.co/90x90/white/lightgray?text=NOT+FOUND'
                    ]
                ];
            });
    }
}
