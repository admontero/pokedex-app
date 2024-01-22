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

    public function getAll(int $page = 1, int $limit = 32): array
    {
        [$offset, $limit] = $this->getQueryParams($page, $limit);

        $url = "pokemon-species?offset={$offset}&limit={$limit}";

        $pokemonData = Cache::remember($url, 3600, function () use ($url) {
            $response = $this->client->requestAsync('GET', $url)->wait();

            $results = json_decode($response->getBody()->getContents(), true)['results'];

            return $this->pushIdPokemon($results);
        });

        return $this->getEachPokemon($pokemonData);
    }

    public function getOne(string $id): array
    {
        

        return Cache::remember('pokemon-' . $id, 3600, function () use ($id) {
            try {
                $response = $this->client->getAsync("pokemon/{$id}")->wait();

            } catch (\Exception $e) {
                Log::info("Error haciendo la petición al recurso '{$id}'");

                return [];
            }

            return json_decode($response->getBody()->getContents(), true);
        });
    }

    public function getAllBySearch(string $name): array
    {
        $url = "pokemon-species?limit={$this->getTotal()}";

        $pokemonData = Cache::remember('search:' . strtolower($name), 3600, function () use ($url, $name) {
            $response = $this->client->requestAsync('GET', $url)->wait();

            $results = json_decode($response->getBody()->getContents(), true)['results'];

            $search = array_filter($results, fn ($pokemon) => str_contains($pokemon['name'], strtolower($name)));

            return $this->pushIdPokemon($search);
        });

        return $this->getEachPokemon($pokemonData);
    }

    public function getTotal(): int
    {
        return Cache::remember('total', 3600, function () {
            $response = $this->client->requestAsync('GET', 'pokemon-species?limit=1')->wait();

            return json_decode($response->getBody()->getContents(), true)['count'];
        });
    }

    private function getEachPokemon(array $data): array
    {
        $promises = [];
        $cachedIds = [];
        $prefix = 'pokemon-';

        foreach ($data as $pokemon) {
            if (Cache::has($prefix . $pokemon['id'])) {
                $cachedIds[] = $pokemon['id'];

                continue;
            }

            $promises[] = $this->client->requestAsync('GET', str_replace('-species', '', $pokemon['url']));
        }

        $results = Utils::settle($promises)->wait();

        $pokemons = [];

        foreach ($results as $result) {
            $result = json_decode($result['value']->getBody()->getContents(), true);

            $pokemons[] = $result;

            Cache::put($prefix . $result['id'], $result);
        }

        foreach ($cachedIds as $id) {
            $pokemons[] = Cache::get($prefix . $id);
        }

        usort($pokemons, fn ($a, $b) => $a['id'] <=> $b['id']);

        return $pokemons;
    }

    private function pushIdPokemon (array $data): array
    {
       return array_map(function ($pokemon) {
            $pokemon['id'] = explode('/', $pokemon['url'])[6];

            return $pokemon;
        }, $data);
    }

    private function getQueryParams(int $page, int $limit): array
    {
        if ($page === 1) return [0, $limit];

        $count = $page - 1;

        $nextOffset = $limit * $count;

        return [$nextOffset, $limit];
    }
}
