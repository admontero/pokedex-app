<?php

namespace App\ViewModels;

use Illuminate\Support\Collection;
use Spatie\ViewModels\ViewModel;

class PokemonViewModel extends ViewModel
{
    public function __construct(
        public array $pokemon,
        public ?string $previous = null,
        public ?string $next = null,
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
        return $this->pokemon['id'] ?? '--';
    }

    protected function formatName(): string
    {
        return $this->pokemon['name'] ?? '--';
    }

    protected function formatImage(): string
    {
        return $this->pokemon['sprites']['other']['official-artwork']['front_default'] ?? 'https://placehold.co/300x300/white/lightgray?text=NOT+FOUND';
    }

    protected function formatWeight(): string
    {
        return isset($this->pokemon['weight']) ? ($this->pokemon['weight'] / 10) . ' kg' : '--';
    }

    protected function formatHeight(): string
    {
        return isset($this->pokemon['height']) ? ($this->pokemon['height'] / 10) . ' m' : '--';
    }

    protected function formatBaseExperience(): string
    {
        return $this->pokemon['base_experience'] ?? '--';
    }

    protected function formatTypes(): array
    {
        if (! isset($this->pokemon['types'])) return [];

        return collect($this->pokemon['types'])
            ->map(fn($type) => $type['type']['name'])
            ->all();
    }

    protected function formatAbilities(): string
    {
        if (! isset($this->pokemon['abilities']) || empty($this->pokemon['abilities'])) return '--';

        $abilities = collect($this->pokemon['abilities'])
            ->map(fn($type) => ucfirst(str_replace('-', ' ', $type['ability']['name'])));

        return implode(' - ', $abilities->all());
    }

    protected function formatItems(): string
    {
        if (! isset($this->pokemon['held_items']) || empty($this->pokemon['held_items'])) return '--';

        $items = collect($this->pokemon['held_items'])
            ->map(fn($type) => ucfirst(str_replace('-', ' ', $type['item']['name'])));

        return implode(' - ', $items->all());
    }

    protected function formatStats(): array
    {
        if (! isset($this->pokemon['stats'])) return [];

        return collect($this->pokemon['stats'])
            ->map(function ($stat) {
                return (object) [
                    'name' => str_replace('-', ' ', $stat['stat']['name']),
                    'value' => $stat['base_stat'],
                    'percentage' => ($stat['base_stat'] * 100) / 255,
                ];
            })
            ->all();
    }

    protected function formatSprites(): Collection
    {
        if (! isset($this->pokemon['sprites'])) return collect([]);

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
        if (! isset($this->pokemon['sprites'])) return collect([]);

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
