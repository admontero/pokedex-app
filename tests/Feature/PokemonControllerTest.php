<?php

namespace Tests\Feature;

use App\Exceptions\PokemonRequestException;
use App\Services\PokemonService;
use Mockery\MockInterface;
use Tests\TestCase;

class PokemonControllerTest extends TestCase
{
    public function test_it_lists_pokemon(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
            ]);

            $mock->shouldReceive('paginate')->once()->andReturn([$this->pokemonCard()]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('bulbasaur')
            ->assertSee('Resultados');
    }

    public function test_it_rejects_an_invalid_page(): void
    {
        $this->get('/?page=abc')
            ->assertRedirect()
            ->assertSessionHasErrors('page');

        $this->get('/?page=0')
            ->assertRedirect()
            ->assertSessionHasErrors('page');
    }

    public function test_it_searches_pokemon(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllBySearch')->once()->andReturn([
                ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
            ]);

            $mock->shouldReceive('paginate')->once()->andReturn([
                $this->pokemonCard(['id' => 25, 'name' => 'pikachu']),
            ]);
        });

        $this->get('/pokemon/search?term=pika&page=2')
            ->assertOk()
            ->assertSee('pikachu')
            ->assertSee('Resultados de la búsqueda');
    }

    public function test_it_rejects_an_invalid_search_page_and_type(): void
    {
        $this->get('/pokemon/search?term=pika&page=abc')
            ->assertRedirect()
            ->assertSessionHasErrors('page');

        $this->get('/pokemon/search?term=pika&type=notatype')
            ->assertRedirect()
            ->assertSessionHasErrors('type');
    }

    public function test_it_shows_a_pokemon(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPokemon')->once()->andReturn($this->pokemonCard([
                'id' => 25,
                'name' => 'pikachu',
            ]));

            $mock->shouldReceive('getPreviousPokemonName')->once()->andReturn(null);
            $mock->shouldReceive('getNextPokemonName')->once()->andReturn(null);
        });

        $this->get('/pokemon/pikachu')
            ->assertOk()
            ->assertSee('pikachu');
    }

    public function test_it_returns_502_without_leaking_internal_details(): void
    {
        config(['app.debug' => false]);

        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andThrow(
                new PokemonRequestException('cURL error 7: connection refused to 10.0.0.5:8080')
            );
        });

        $this->get('/')
            ->assertStatus(502)
            ->assertSee('No pudimos completar la solicitud')
            ->assertDontSee('cURL error')
            ->assertDontSee('10.0.0.5');
    }

    public function test_it_shows_the_error_detail_in_debug_mode(): void
    {
        config(['app.debug' => true]);

        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andThrow(
                new PokemonRequestException('cURL error 7: connection refused')
            );
        });

        $this->get('/')
            ->assertStatus(502)
            ->assertSee('cURL error 7');
    }

    private function pokemonCard(array $overrides = []): array
    {
        return array_merge([
            'id' => 1,
            'name' => 'bulbasaur',
            'types' => [],
            'sprites' => [
                'other' => [
                    'official-artwork' => ['front_default' => 'https://img.test/bulbasaur.png'],
                ],
            ],
        ], $overrides);
    }
}
