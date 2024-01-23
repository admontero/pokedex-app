<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Cache;

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

        if (Cache::has($url)) {
            return $this->getEachPokemon(Cache::get($url));
        }

        $response = $this->client->requestAsync('GET', $url)->wait();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new \Exception('Error al consultar la API, inténtelo de nuevo más tarde...', $response->getStatusCode());
        };

        $results = json_decode($response->getBody()->getContents(), true)['results'];

        $data = $this->pushIdPokemon($results);

        Cache::put($url, $data, 60 * 60);

        return $this->getEachPokemon($data);
    }

    public function getOne(string $id): array
    {
        $key = "pokemon-{$id}";

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $response = $this->client->getAsync("pokemon/{$id}")->wait();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new \Exception('Error al consultar la API, inténtelo de nuevo más tarde...', $response->getStatusCode());
        }

        $data = json_decode($response->getBody()->getContents(), true);

        Cache::put($key, $data, 60 * 60);

        return $data;
    }

    public function getAllBySearch(string $name): array
    {
        $name = strtolower($name);

        $key = "search:{$name}";

        if (Cache::has($key)) {
            return $this->getEachPokemon(Cache::get($key));
        }

        $url = "pokemon-species?limit={$this->getTotal()}";

        $response = $this->client->requestAsync('GET', $url)->wait();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new \Exception('Error al consultar la API, inténtelo de nuevo más tarde...', $response->getStatusCode());
        }

        $results = json_decode($response->getBody()->getContents(), true)['results'];

        $search = array_filter($results, fn ($pokemon) => str_contains($pokemon['name'], $name));

        $data = $this->pushIdPokemon($search);

        Cache::put($key, $data, 60 * 60);

        return $this->getEachPokemon($data);
    }

    public function getSpecie(int $id): array
    {
        $key = "pokemon-specie-{$id}";

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $response = $this->client->getAsync("pokemon-species/{$id}")->wait();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new \Exception('Error al consultar la API, inténtelo de nuevo más tarde...', $response->getStatusCode());
        }

        $data = json_decode($response->getBody()->getContents(), true);

        Cache::put($key, $data, 60 * 60);

        return $data;
    }

    public function getTotal(): int
    {
        $key = 'total';

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $response = $this->client->requestAsync('GET', 'pokemon-species?limit=1')->wait();

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new \Exception('Error al consultar la API, inténtelo de nuevo más tarde...', $response->getStatusCode());
        }

        $data = json_decode($response->getBody()->getContents(), true)['count'];

        Cache::put($key, $data, 60 * 60);

        return $data;
    }

    private function getEachPokemon(array $data): array
    {
        $promises = [];
        $cachedIds = [];
        $keyPrefix = 'pokemon-';

        foreach ($data as $pokemon) {
            if (Cache::has($keyPrefix . $pokemon['id'])) {
                $cachedIds[] = $pokemon['id'];

                continue;
            }

            $promises[] = $this->client->requestAsync('GET', str_replace('-species', '', $pokemon['url']));
        }

        $responses = Utils::settle($promises)->wait();

        $pokemons = [];

        foreach ($responses as $response) {
            if ($response['value']->getStatusCode() < 200 || $response['value']->getStatusCode() >= 300) {
                continue;
            }

            $data = json_decode($response['value']->getBody()->getContents(), true);

            $pokemons[] = $data;

            Cache::put($keyPrefix . $data['id'], $data, 60 * 60);
        }

        foreach ($cachedIds as $id) {
            $pokemons[] = Cache::get($keyPrefix . $id);
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
