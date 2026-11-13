<?php

use App\Http\Controllers\PokemonController;
use App\Http\Controllers\PokemonSearchController;
use App\Http\Controllers\PokemonSuggestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [PokemonController::class, 'index'])
    ->name('pokemons.index');

Route::get('/pokemon/search', PokemonSearchController::class)
    ->name('pokemons.search');

// Registered before the show route: "suggest" would otherwise match {name}.
Route::get('/pokemon/suggest', PokemonSuggestController::class)
    ->name('pokemons.suggest');

Route::get('/pokemon/{name}', [PokemonController::class, 'show'])
    ->name('pokemons.show')
    ->where('name', '[a-zA-Z\-]+');

Route::fallback(function () {
    abort(404);
});
