<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pokemon Options
    |--------------------------------------------------------------------------
    |
    | Values used across the pokedex: how long API responses stay cached, how
    | many cards are shown per page, the fallback image and the types offered
    | in the search form.
    |
    */

    'cache_ttl' => (int) env('POKEMON_CACHE_TTL', 3600),

    'per_page' => (int) env('POKEMON_PER_PAGE', 16),

    'placeholder' => env(
        'POKEMON_PLACEHOLDER',
        'https://placehold.co/300x300/white/lightgray?text=NOT+FOUND'
    ),

    'sprite_placeholder' => env(
        'POKEMON_SPRITE_PLACEHOLDER',
        'https://placehold.co/90x90/white/lightgray?text=NOT+FOUND'
    ),

    'types' => [
        'normal',
        'fighting',
        'flying',
        'poison',
        'ground',
        'rock',
        'bug',
        'ghost',
        'steel',
        'fire',
        'water',
        'grass',
        'electric',
        'psychic',
        'ice',
        'dragon',
        'dark',
        'fairy',
    ],

];
