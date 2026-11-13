<?php

namespace Tests\Unit;

use App\Services\PokemonSuggestions;
use GuzzleHttp\Handler\MockHandler;
use Tests\Concerns\FakesPokeApi;
use Tests\TestCase;

class PokemonSuggestionsTest extends TestCase
{
    use FakesPokeApi;

    public function test_it_returns_nothing_for_an_empty_term(): void
    {
        $handler = new MockHandler;
        $suggestions = new PokemonSuggestions($this->repository($handler));

        $this->assertSame([], $suggestions->search(''));
        $this->assertSame([], $suggestions->search('   '));
        $this->assertSame([], $suggestions->search('#'));
        $this->assertSame(0, $handler->count(), 'an empty term must not hit the API');
    }

    public function test_it_ranks_name_prefixes_before_other_matches(): void
    {
        $suggestions = $this->suggestions();

        $this->assertSame(
            ['charmander', 'charizard'],
            array_column($suggestions->search('char'), 'name'),
            'prefix matches must come first and be ordered by Pokedex number',
        );

        $this->assertSame(
            ['bulbasaur', 'ivysaur', 'venusaur'],
            array_column($suggestions->search('saur'), 'name'),
            'matches in the middle of the name are ordered by Pokedex number',
        );
    }

    public function test_it_matches_the_exact_pokedex_number_before_number_prefixes(): void
    {
        $suggestions = $this->suggestions();

        $this->assertSame(
            ['ivysaur', 'pikachu', 'raichu'],
            array_column($suggestions->search('2'), 'name'),
        );
    }

    public function test_it_normalizes_the_hash_and_leading_zeroes(): void
    {
        $suggestions = $this->suggestions();

        $this->assertSame(['pikachu'], array_column($suggestions->search('#25'), 'name'));
        $this->assertSame(['pikachu'], array_column($suggestions->search('025'), 'name'));
        $this->assertSame([], $suggestions->search('999'), 'an unknown number must not match everything');
    }

    public function test_it_limits_the_number_of_suggestions(): void
    {
        $this->assertCount(2, $this->suggestions()->search('saur', 2));
    }

    public function test_it_returns_a_null_id_when_the_url_has_no_number(): void
    {
        $suggestions = new PokemonSuggestions($this->repository(new MockHandler([
            $this->jsonResponse(['results' => [
                ['name' => 'missingno', 'url' => 'https://pokeapi.co/api/v2/pokemon/missingno/'],
            ]]),
        ])));

        $this->assertSame(
            [['id' => null, 'name' => 'missingno']],
            $suggestions->search('missing'),
        );
    }

    private function suggestions(): PokemonSuggestions
    {
        $handler = new MockHandler([
            $this->jsonResponse(['results' => [
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
                ['name' => 'venusaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/3/'],
                ['name' => 'charmander', 'url' => 'https://pokeapi.co/api/v2/pokemon/4/'],
                ['name' => 'charizard', 'url' => 'https://pokeapi.co/api/v2/pokemon/6/'],
                ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
                ['name' => 'raichu', 'url' => 'https://pokeapi.co/api/v2/pokemon/26/'],
            ]]),
        ]);

        return new PokemonSuggestions($this->repository($handler));
    }
}
