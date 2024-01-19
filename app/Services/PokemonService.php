<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PokemonService
{
    public Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => config('services.pokemon.base_url')]);
    }

    public function getAll(int $page = 1, int $limit = 50): array
    {
        $promises = [];

        $offset = $this->getOffset($page, $limit);

        $url = "pokemon?offset={$offset}&limit={$limit}";

        $pokemonData = Cache::remember($url, 3600, function () use ($url) {
            $response = $this->client->requestAsync('GET', $url)->wait();

            return json_decode($response->getBody()->getContents(), true);
        });

        $cachedPokemons = [];

        foreach ($pokemonData['results'] as $pokemon) {
            if (Cache::has($pokemon['name'])) {
                $cachedPokemons[] = $pokemon['name'];

                continue;
            }

            $promises[] = $this->client->getAsync($pokemon['url']);
        }

        $results = Utils::settle($promises)->wait();

        $pokemons = [];

        foreach ($results as $result) {
            $result = json_decode($result['value']->getBody()->getContents(), true);

            $pokemons[] = $result;

            Cache::put($result['name'], $result);
        }

        foreach ($cachedPokemons as $name) {
            $pokemons[] = Cache::get($name);
        }

        return collect($pokemons)->sortBy('id')->toArray();
    }

    public function getByName(string $name): array
    {
        return Cache::remember($name, 3600, function () use ($name) {
            try {
                $response = $this->client->getAsync("pokemon/{$name}")->wait();

            } catch (\Exception $e) {
                Log::info("Error haciendo la petición al recurso '{$name}'");

                return [];
            }

            return json_decode($response->getBody()->getContents(), true);
        });
    }

    private function getOffset(int $page, int $limit): int
    {
        if ($page === 1) return 0;

        $count = $page - 1;

        return $limit * $count;
    }
}
