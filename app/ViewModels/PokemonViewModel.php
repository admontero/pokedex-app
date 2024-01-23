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
            'id' => $this->formatId(),
            'name' => $this->formatName(),
            'image' => $this->formatImage(),
            'weight' => $this->formatWeight(),
            'height' => $this->formatHeight(),
            'base_experience' => $this->formatBaseExperience(),
            'types' => $this->formatTypes(),
            'abilities' => $this->formatAbilities(),
            'items' => $this->formatItems(),
            'stats' => $this->formatStats(),
            'sprites' => $this->formatSprites(),
            'female_sprites' => $this->formatFemaleSprites(),
        ];

        return $pokemon;
    }

    protected function formatId(): string
    {
        return $this->pokemon['id'] ?? '';
    }

    protected function formatName(): string
    {
        return $this->pokemon['name'] ?? '';
    }

    protected function formatImage(): string
    {
        return $this->pokemon['sprites']['other']['official-artwork']['front_default'] ?? 'https://placehold.co/300x300/white/lightgray?text=NOT+FOUND';
    }

    protected function formatWeight(): string
    {
        return isset($this->pokemon['weight']) ? ($this->pokemon['weight'] / 10) . ' kg' : '';
    }

    protected function formatHeight(): string
    {
        return isset($this->pokemon['height']) ? ($this->pokemon['height'] / 10) . ' m' : '';
    }

    protected function formatBaseExperience(): string
    {
        return $this->pokemon['base_experience'] ?? '';
    }

    protected function formatTypes(): Collection
    {
        if (! isset($this->pokemon['types'])) return collect();

        return collect($this->pokemon['types'])->map(fn($type) => $type['type']['name']);
    }

    protected function formatAbilities(): string
    {
        if (! isset($this->pokemon['abilities'])) return '';

        return collect($this->pokemon['abilities'])->map(fn($type) => str_replace('-', ' ', $type['ability']['name']));
    }

    protected function formatItems(): string
    {
        if (! isset($this->pokemon['held_items'])) return '';

        return collect($this->pokemon['held_items'])->map(fn($type) => str_replace('-', ' ', $type['item']['name']));
    }

    protected function formatStats(): Collection
    {
        if (! isset($this->pokemon['stats'])) return collect();

        return collect($this->pokemon['stats'])
            ->map(function ($stat) {
                return (object) [
                    'name' => str_replace('-', ' ', $stat['stat']['name']),
                    'value' => $stat['base_stat'],
                    'percentage' => ($stat['base_stat'] * 100) / 255,
                ];
            });
    }

    protected function formatSprites(): Collection
    {
        if (! isset($this->pokemon['sprites'])) return collect();

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

    protected function formatFemaleSprites(): Collection
    {
        if (! isset($this->pokemon['sprites'])) return collect();

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
