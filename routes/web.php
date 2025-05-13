<?php

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

Route::get('/', [App\Http\Controllers\PokemonController::class, 'index'])
    ->name('pokemons.index');

Route::get('/pokemon/search', App\Http\Controllers\PokemonSearchController::class)
    ->name('pokemons.search');

Route::get('/pokemon/{name}', [App\Http\Controllers\PokemonController::class, 'show'])
    ->name('pokemons.show')
    ->where('name', '[a-zA-Z\-]+');

Route::fallback(function () {
    abort(404);
});

