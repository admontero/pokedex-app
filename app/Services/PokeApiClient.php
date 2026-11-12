<?php

namespace App\Services;

class PokeApiClient extends ClientService
{
    public function __construct(?callable $handler = null)
    {
        parent::__construct(
            baseUrl: config('services.pokemon.base_url'),
            headers: [
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
            ],
            withErrorHandler: false,
            handler: $handler,
        );
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function listPokemons(): array
    {
        return $this->fetch('pokemon?limit=100000')['results'] ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getPokemon(string $name): array
    {
        return $this->fetch("pokemon/{$name}");
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function getPokemonsByType(string $type): array
    {
        $payload = $this->fetch("type/{$type}");

        return collect($payload['pokemon'] ?? [])
            ->map(fn (array $entry) => $entry['pokemon'])
            ->all();
    }

    /**
     * @param  array<int, string>  $urls
     * @return array<int, array<string, mixed>>
     */
    public function getPokemonsByUrl(array $urls): array
    {
        return $this->fetchMultiple($urls);
    }
}
