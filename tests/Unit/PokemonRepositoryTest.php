<?php

namespace Tests\Unit;

use App\Exceptions\PokemonNotFoundException;
use App\Exceptions\PokemonRequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Tests\Concerns\FakesPokeApi;
use Tests\TestCase;

class PokemonRepositoryTest extends TestCase
{
    use FakesPokeApi;

    public function test_it_fetches_the_pokemon_list_once(): void
    {
        $handler = new MockHandler([$this->jsonResponse($this->pokemonList())]);
        $repository = $this->repository($handler);

        $first = $repository->all();

        $this->assertCount(3, $first);
        $this->assertSame(0, $handler->count(), 'the list should be fetched only once');

        $this->assertSame($first, $repository->all(), 'the second call should hit the cache');
    }

    public function test_it_paginates_and_caches_each_pokemon(): void
    {
        $handler = new MockHandler([
            $this->jsonResponse($this->pokemonList()),
            $this->jsonResponse($this->pokemon(1, 'bulbasaur')),
            $this->jsonResponse($this->pokemon(2, 'ivysaur')),
            $this->jsonResponse($this->pokemon(3, 'venusaur')),
        ]);
        $repository = $this->repository($handler);

        $list = $repository->all();

        $firstPage = $repository->paginate($list, 1, 2);
        $this->assertSame(['bulbasaur', 'ivysaur'], array_column($firstPage, 'name'));

        $secondPage = $repository->paginate($list, 2, 2);
        $this->assertSame(['venusaur'], array_column($secondPage, 'name'));

        $this->assertSame(0, $handler->count(), 'every pokemon should be fetched only once');

        $this->assertSame($firstPage, $repository->paginate($list, 1, 2), 'cached pages should not hit the API');
    }

    public function test_it_finds_a_pokemon_and_caches_it(): void
    {
        $handler = new MockHandler([$this->jsonResponse($this->pokemon(25, 'pikachu'))]);
        $repository = $this->repository($handler);

        $this->assertSame('pikachu', $repository->find('pikachu')['name']);
        $this->assertSame('pikachu', $repository->find('pikachu')['name'], 'the second lookup should hit the cache');
        $this->assertSame(0, $handler->count());
    }

    public function test_it_lists_pokemon_by_type(): void
    {
        $handler = new MockHandler([
            $this->jsonResponse([
                'pokemon' => [
                    ['pokemon' => ['name' => 'charmander', 'url' => 'https://pokeapi.co/api/v2/pokemon/4/']],
                    ['pokemon' => ['name' => 'charizard', 'url' => 'https://pokeapi.co/api/v2/pokemon/6/']],
                ],
            ]),
        ]);

        $results = $this->repository($handler)->byType('fire');

        $this->assertSame(['charmander', 'charizard'], array_column($results, 'name'));
    }

    public function test_a_missing_pokemon_throws_not_found(): void
    {
        $handler = new MockHandler([new Response(404)]);

        $this->expectException(PokemonNotFoundException::class);

        $this->repository($handler)->find('missingno');
    }

    public function test_it_retries_server_errors(): void
    {
        $handler = new MockHandler([
            new Response(503),
            new Response(503),
            $this->jsonResponse($this->pokemon(25, 'pikachu')),
        ]);

        $pokemon = $this->repository($handler)->find('pikachu');

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
            $this->repository($handler)->find('pikachu');
            $this->fail('A PokemonRequestException was expected.');
        } catch (PokemonRequestException $exception) {
            $this->assertSame(503, $exception->getCode());
        }

        $this->assertSame(0, $handler->count(), 'the API should be called three times: initial attempt + 2 retries');
    }
}
