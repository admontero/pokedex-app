<?php

namespace Tests\Feature;

use App\Services\PokemonSuggestions;
use Mockery\MockInterface;
use Tests\TestCase;

class PokemonSuggestControllerTest extends TestCase
{
    public function test_it_returns_suggestions_as_json(): void
    {
        $this->mock(PokemonSuggestions::class, function (MockInterface $mock) {
            $mock->shouldReceive('search')->once()->with('pika')->andReturn([
                ['id' => 25, 'name' => 'pikachu'],
                ['id' => null, 'name' => 'pikachu-libre'],
            ]);
        });

        $this->getJson('/pokemon/suggest?term=pika')
            ->assertOk()
            ->assertExactJson(['results' => [
                [
                    'id' => 25,
                    'number' => '025',
                    'name' => 'pikachu',
                    'url' => route('pokemons.show', 'pikachu'),
                ],
                [
                    'id' => null,
                    'number' => null,
                    'name' => 'pikachu-libre',
                    'url' => route('pokemons.show', 'pikachu-libre'),
                ],
            ]]);
    }

    public function test_it_returns_no_results_for_an_empty_term(): void
    {
        $this->mock(PokemonSuggestions::class, function (MockInterface $mock) {
            $mock->shouldReceive('search')->once()->with(null)->andReturn([]);
        });

        $this->getJson('/pokemon/suggest')
            ->assertOk()
            ->assertExactJson(['results' => []]);
    }

    public function test_it_rejects_a_term_that_is_too_long(): void
    {
        $this->getJson('/pokemon/suggest?term='.str_repeat('a', 31))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('term');
    }
}
