<?php

namespace Tests\Unit;

use App\DTOs\PokemonSearchDTO;
use App\Services\PokemonSearch;
use GuzzleHttp\Handler\MockHandler;
use Tests\Concerns\FakesPokeApi;
use Tests\TestCase;

class PokemonSearchTest extends TestCase
{
    use FakesPokeApi;

    public function test_it_filters_by_term_and_caches_each_search(): void
    {
        $handler = new MockHandler([$this->jsonResponse($this->pokemonList())]);
        $search = new PokemonSearch($this->repository($handler));

        $bulbasaur = $search->apply(new PokemonSearchDTO('bulba', null));
        $this->assertSame(['bulbasaur'], array_column($bulbasaur, 'name'));

        $venusaur = $search->apply(new PokemonSearchDTO('venu', null));
        $this->assertSame(['venusaur'], array_column($venusaur, 'name'), 'the second search must not reuse the first result');

        $this->assertSame(['venusaur'], array_column($search->apply(new PokemonSearchDTO('venu', null)), 'name'), 'the second search should hit the cache');

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

        $results = (new PokemonSearch($this->repository($handler)))
            ->apply(new PokemonSearchDTO(null, 'fire'));

        $this->assertSame(['charmander', 'charizard'], array_column($results, 'name'));
    }

    public function test_it_combines_type_and_term(): void
    {
        $handler = new MockHandler([
            $this->jsonResponse([
                'pokemon' => [
                    ['pokemon' => ['name' => 'charmander', 'url' => 'https://pokeapi.co/api/v2/pokemon/4/']],
                    ['pokemon' => ['name' => 'charizard', 'url' => 'https://pokeapi.co/api/v2/pokemon/6/']],
                ],
            ]),
        ]);

        $results = (new PokemonSearch($this->repository($handler)))
            ->apply(new PokemonSearchDTO('charizard', 'fire'));

        $this->assertSame(['charizard'], array_column($results, 'name'));
    }
}
