<?php

namespace Tests\Feature;

use App\Exceptions\PokemonRequestException;
use App\Services\PokemonService;
use Mockery;
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
            ->assertSee('Mostrando');
    }

    public function test_the_search_form_exposes_an_accessible_combobox(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
            ]);

            $mock->shouldReceive('paginate')->once()->andReturn([$this->pokemonCard()]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('role="combobox"', false)
            ->assertSee('aria-autocomplete="list"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="term-suggestions"', false)
            ->assertSee('role="listbox"', false)
            ->assertSee(route('pokemons.suggest'), false);
    }

    public function test_the_layout_exposes_a_theme_toggle(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
            ]);

            $mock->shouldReceive('paginate')->once()->andReturn([$this->pokemonCard()]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('data-bs-theme="light"', false)
            ->assertSee('data-theme-toggle', false)
            ->assertSee('aria-pressed="false"', false)
            ->assertSee("localStorage.getItem('pk-theme')", false);
    }

    public function test_the_layout_uses_local_fonts_and_inline_icons(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([]);
            $mock->shouldReceive('paginate')->once()->andReturn([]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('vendor/fonts/nunito-400.woff2', false)
            ->assertSee('vendor/fonts/nunito-800.woff2', false)
            ->assertDontSee('fonts.bunny.net')
            ->assertDontSee('cdnjs.cloudflare.com');
    }

    public function test_the_search_page_renders_inline_icons(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllBySearch')->once()->andReturn([
                ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
            ]);

            $mock->shouldReceive('paginate')->once()->andReturn([
                $this->pokemonCard(['id' => 25, 'name' => 'pikachu']),
            ]);
        });

        $this->get('/pokemon/search?term=pika')
            ->assertOk()
            ->assertSee('class="pk-icon', false)
            ->assertDontSee('fa fa-', false);
    }

    public function test_it_serves_modern_image_formats_with_srcset(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([]);
            $mock->shouldReceive('paginate')->once()->andReturn([]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('type="image/avif"', false)
            ->assertSee('type="image/webp"', false)
            ->assertSee('logo-250.avif 1x', false)
            ->assertSee('logo-500.avif 2x', false)
            ->assertSee('pikachu-200.avif 1x', false)
            ->assertSee('pikachu-400.webp 2x', false);
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
            ->assertSee('Resultados para');
    }

    public function test_it_searches_pokemon_by_number(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllBySearch')
                ->once()
                ->with(Mockery::on(fn ($dto) => $dto->term === '25' && $dto->type === null))
                ->andReturn([
                    ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
                ]);

            $mock->shouldReceive('paginate')->once()->andReturn([
                $this->pokemonCard(['id' => 25, 'name' => 'pikachu']),
            ]);
        });

        $this->get('/pokemon/search?term=25')
            ->assertOk()
            ->assertSee('pikachu')
            ->assertSee('Resultados para');
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

    public function test_it_uses_a_local_from_url_as_the_back_destination(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPokemon')->once()->andReturn($this->pokemonCard([
                'id' => 25,
                'name' => 'pikachu',
            ]));

            $mock->shouldReceive('getPreviousPokemonName')->once()->andReturn(null);
            $mock->shouldReceive('getNextPokemonName')->once()->andReturn(null);
        });

        $from = urlencode('/pokemon/search?term=pika');

        $this->get('/pokemon/pikachu?from='.$from)
            ->assertOk()
            ->assertSee('href="/pokemon/search?term=pika"', false);
    }

    public function test_it_ignores_a_cross_site_from_url(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getPokemon')->twice()->andReturn($this->pokemonCard([
                'id' => 25,
                'name' => 'pikachu',
            ]));

            $mock->shouldReceive('getPreviousPokemonName')->twice()->andReturn(null);
            $mock->shouldReceive('getNextPokemonName')->twice()->andReturn(null);
        });

        $this->get('/pokemon/pikachu?from='.urlencode('https://evil.test/phish'))
            ->assertOk()
            ->assertDontSee('evil.test');

        $this->get('/pokemon/pikachu?from='.urlencode('http://localhost.evil.test/phish'))
            ->assertOk()
            ->assertDontSee('localhost.evil.test');
    }

    public function test_it_shows_a_load_more_link_when_there_are_more_results(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn(
                array_map(
                    fn (int $id): array => ['name' => 'pokemon-'.$id, 'url' => 'https://pokeapi.co/api/v2/pokemon/'.$id.'/'],
                    range(1, 17),
                )
            );

            $mock->shouldReceive('paginate')->once()->andReturn([$this->pokemonCard()]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('Mostrando')
            ->assertSee('Cargar más Pokémon');
    }

    public function test_it_hides_the_load_more_link_on_the_last_page(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
            ]);

            $mock->shouldReceive('paginate')->once()->andReturn([$this->pokemonCard()]);
        });

        $this->get('/?page=2')
            ->assertOk()
            ->assertSee('Has llegado al final de la lista')
            ->assertDontSee('Cargar más Pokémon');
    }

    public function test_it_marks_the_scroller_with_a_custom_attribute(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn(
                array_map(
                    fn (int $id): array => ['name' => 'pokemon-'.$id, 'url' => 'https://pokeapi.co/api/v2/pokemon/'.$id.'/'],
                    range(1, 17),
                )
            );

            $mock->shouldReceive('paginate')->once()->andReturn([$this->pokemonCard()]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('data-pk-infinite-scroll', false)
            ->assertDontSee('data-infinite-scroll=', false);
    }

    public function test_it_accumulates_pages_on_full_loads(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
            ]);

            $mock->shouldReceive('paginate')
                ->once()
                ->with(Mockery::type('array'), 2, 16, true)
                ->andReturn([$this->pokemonCard(), $this->pokemonCard(['id' => 2, 'name' => 'ivysaur'])]);
        });

        $this->get('/?page=2')
            ->assertOk()
            ->assertSee('bulbasaur')
            ->assertSee('ivysaur');
    }

    public function test_it_returns_only_the_requested_page_for_ajax_requests(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllPokemon')->once()->andReturn([
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
            ]);

            $mock->shouldReceive('paginate')
                ->once()
                ->with(Mockery::type('array'), 2, 16, false)
                ->andReturn([$this->pokemonCard(['id' => 2, 'name' => 'ivysaur'])]);
        });

        $this->get('/?page=2', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('ivysaur');
    }

    public function test_it_returns_only_the_requested_search_page_for_ajax_requests(): void
    {
        $this->mock(PokemonService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAllBySearch')->once()->andReturn([
                ['name' => 'pikachu', 'url' => 'https://pokeapi.co/api/v2/pokemon/25/'],
            ]);

            $mock->shouldReceive('paginate')
                ->once()
                ->with(Mockery::type('array'), 2, 16, false)
                ->andReturn([$this->pokemonCard(['id' => 25, 'name' => 'pikachu'])]);
        });

        $this->get('/pokemon/search?term=pika&page=2', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('pikachu')
            ->assertDontSee('pagination__next');
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
