<?php

namespace Tests\Concerns;

use App\Services\PokeApiClient;
use App\Services\PokemonRepository;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

trait FakesPokeApi
{
    private function repository(MockHandler $handler): PokemonRepository
    {
        return new PokemonRepository(new PokeApiClient(handler: $handler));
    }

    private function pokemonList(): array
    {
        return [
            'count' => 3,
            'results' => [
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
                ['name' => 'venusaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/3/'],
            ],
        ];
    }

    private function pokemon(int $id, string $name): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'types' => [],
            'sprites' => [],
        ];
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
    }
}
