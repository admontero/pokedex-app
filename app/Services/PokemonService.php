<?php

namespace App\Services;

use App\DTOs\PokemonSearchDTO;
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

    public function getAll(int $page = 1, int $limit = 20): array
    {
        [$offset, $limit] = $this->getQueryParams($page, $limit);

        $url = "pokemon-species?offset={$offset}&limit={$limit}";

        if (Cache::has($url)) {
            return $this->getEachPokemon(Cache::get($url));
        }

        try {
            $response = $this->client->requestAsync('GET', $url)->wait();

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new \Exception('', $response->getStatusCode());
            };
        } catch (\Exception $e) {
            Log::info("Error al obtener el listado de pokemon. Error: " . $e->getMessage());

            abort(500, 'Error al consultar la API, inténtelo de nuevo más tarde...');
        }

        $results = json_decode($response->getBody()->getContents(), true)['results'];

        $data = $this->getPokemonId($results);

        Cache::put($url, $data, 60 * 60);

        return $this->getEachPokemon($data);
    }

    public function getOne(int $id): array
    {
        $key = "pokemon-{$id}";

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        try {
            $response = $this->client->getAsync("pokemon/{$id}")->wait();

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new \Exception('', $response->getStatusCode());
            }
        } catch (\Exception $e) {
            Log::info("Error al obtener el recurso {$id}. Error: " . $e->getMessage());

            abort(500, 'Error al consultar la API, inténtelo de nuevo más tarde...');
        }

        $data = json_decode($response->getBody()->getContents(), true);

        Cache::put($key, $data, 60 * 60);

        return $data;
    }

    public function getAllBySearch(PokemonSearchDTO $dto): array
    {
        $cacheKey = '';

        $cacheKey .= collect($dto)->sortKeys()->each(fn ($value, $key) => "{$key}:{$value},");

        if (Cache::has($cacheKey)) {
            return $this->getEachPokemon(Cache::get($cacheKey));
        }

        $data = match (true) {
            ! is_null($dto->type) => $this->filterByType($dto),
            is_null($dto->type) => $this->filterByName($dto->name),
            default => [],
        };

        Cache::put($cacheKey, $data, 60 * 60);

        return $this->getEachPokemon($data);
    }

    public function getTotal(): int | null
    {
        $key = 'total';

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        try {
            $response = $this->client->requestAsync('GET', 'pokemon-species?limit=1')->wait();

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new \Exception('', $response->getStatusCode());
            }
        } catch (\Exception $e) {
            Log::info('Error al obtener el total de pokemon. Error: ' . $e->getMessage());

            abort(500, 'Error al consultar la API, inténtelo de nuevo más tarde...');
        }

        $data = json_decode($response->getBody()->getContents(), true)['count'];

        Cache::put($key, $data, 60 * 60);

        return $data;
    }

    private function filterByType(PokemonSearchDTO $dto)
    {
        $url = "type/{$dto->type}";

        try {
            $response = $this->client->requestAsync('GET', $url)->wait();

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new \Exception('', $response->getStatusCode());
            }
        } catch (\Exception $e) {
            Log::info("Error en la búsqueda pokemon. Error: " . $e->getMessage());

            abort(500, 'Error al consultar la API, inténtelo de nuevo más tarde...');
        }

        $results = array_map(function ($pokemon) {
            return $pokemon['pokemon'];
        }, json_decode($response->getBody()->getContents(), true)['pokemon']);

        if ($dto->name) {
            $results = array_filter($results, fn ($pokemon) => str_contains($pokemon['name'], $dto->name));
        }

        return array_filter($this->getPokemonId($results), fn ($pokemon) => $pokemon['id'] <= $this->getTotal());
    }

    private function filterByName(string $name): array
    {
        $url = "pokemon-species?limit={$this->getTotal()}";

        try {
            $response = $this->client->requestAsync('GET', $url)->wait();

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new \Exception('', $response->getStatusCode());
            }
        } catch (\Exception $e) {
            Log::info("Error en la búsqueda pokemon. Error: " . $e->getMessage());

            abort(500, 'Error al consultar la API, inténtelo de nuevo más tarde...');
        }

        $results = json_decode($response->getBody()->getContents(), true)['results'];

        $results = array_filter($results, fn ($pokemon) => str_contains($pokemon['name'], $name));

        return $this->getPokemonId($results);
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

        try {
            $responses = Utils::settle($promises)->wait();
        } catch (\Exception $e) {
            Log::info('Error al obtener el listado de pokemon. Error: ' . $e->getMessage());

            abort(500, 'Error al consultar la API, inténtelo de nuevo más tarde...');
        }

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

    private function getPokemonId(array $data): array
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
