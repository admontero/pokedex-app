<?php

namespace Tests\Unit;

use App\DTOs\PokemonSearchDTO;
use App\Exceptions\PokemonNotFoundException;
use App\Exceptions\PokemonRequestException;
use App\Services\PokemonService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class PokemonServiceTest extends TestCase
{
    private const POKEMON_LIST = [
        'count' => 3,
        'results' => [
            ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
            ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
            ['name' => 'venusaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/3/'],
        ],
    ];

    public function test_it_fetches_the_pokemon_list_and_serves_later_calls_from_cache(): void
    {
        $handler = new MockHandler([$this->jsonResponse(self::POKEMON_LIST)]);
        $service = $this->service($handler);

        $first = $service->getAllPokemon();

        $this->assertCount(3, $first);
        $this->assertSame(0, $handler->count(), 'the list should be fetched only once');

        $this->assertSame($first, $service->getAllPokemon(), 'the second call should hit the cache');
    }

    public function test_it_paginates_and_caches_each_pokemon(): void
    {
        $handler = new MockHandler([
            $this->jsonResponse(self::POKEMON_LIST),
            $this->jsonResponse($this->pokemon(1, 'bulbasaur')),
            $this->jsonResponse($this->pokemon(2, 'ivysaur')),
            $this->jsonResponse($this->pokemon(3, 'venusaur')),
        ]);
        $service = $this->service($handler);

        $list = $service->getAllPokemon();

        $firstPage = $service->paginate($list, 1, 2);
        $this->assertSame(['bulbasaur', 'ivysaur'], array_column($firstPage, 'name'));

        $secondPage = $service->paginate($list, 2, 2);
        $this->assertSame(['venusaur'], array_column($secondPage, 'name'));

        $this->assertSame(0, $handler->count(), 'every pokemon should be fetched only once');

        $this->assertSame($firstPage, $service->paginate($list, 1, 2), 'cached pages should not hit the API');
    }

    public function test_different_searches_use_different_cache_entries(): void
    {
        $handler = new MockHandler([$this->jsonResponse(self::POKEMON_LIST)]);
        $service = $this->service($handler);

        $bulbasaur = $service->getAllBySearch(new PokemonSearchDTO('bulba', null));
        $this->assertSame(['bulbasaur'], array_column($bulbasaur, 'name'));

        $venusaur = $service->getAllBySearch(new PokemonSearchDTO('venu', null));
        $this->assertSame(['venusaur'], array_column($venusaur, 'name'), 'the second search must not reuse the first result');

        $this->assertSame(0, $handler->count(), 'both searches should reuse the cached list');
    }

    public function test_it_filters_by_type(): void
    {
        $handler = new MockHandler([
            $this->jsonResponse([
                'pokemon' => [
                    ['pokemon' => ['name' => 'charmander', 'url' => 'https://pokeapi.co/api/v2/pokemon/4/']],
                    ['pokemon' => ['name' => 'charizard', 'url' => 'https://pokeapi.co/api/v2/pokemon/6/']],
                ],
            ]),
        ]);

        $results = $this->service($handler)->getAllBySearch(new PokemonSearchDTO(null, 'fire'));

        $this->assertSame(['charmander', 'charizard'], array_column($results, 'name'));
    }

    public function test_a_missing_pokemon_throws_not_found(): void
    {
        $handler = new MockHandler([new Response(404)]);

        $this->expectException(PokemonNotFoundException::class);

        $this->service($handler)->getPokemon('missingno');
    }

    public function test_it_retries_server_errors(): void
    {
        $handler = new MockHandler([
            new Response(503),
            new Response(503),
            $this->jsonResponse($this->pokemon(25, 'pikachu')),
        ]);

        $pokemon = $this->service($handler)->getPokemon('pikachu');

        $this->assertSame('pikachu', $pokemon['name']);
        $this->assertSame(0, $handler->count(), 'the third attempt should succeed');
    }

    public function test_it_gives_up_after_the_configured_retries(): void
    {
        $handler = new MockHandler([
            new Response(503),
            new Response(503),
            new Response(503),
        ]);

        try {
            $this->service($handler)->getPokemon('pikachu');
            $this->fail('A PokemonRequestException was expected.');
        } catch (PokemonRequestException $exception) {
            $this->assertSame(503, $exception->getCode());
        }

        $this->assertSame(0, $handler->count(), 'the API should be called three times: initial attempt + 2 retries');
    }

    private function service(MockHandler $handler): PokemonService
    {
        return new PokemonService(handler: $handler);
    }

    private function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response($status, ['Content-Type' => 'application/json'], json_encode($data));
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
}
