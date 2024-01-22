<?php

namespace App\Http\Controllers;

use App\Http\Requests\PokemonSearchRequest;
use App\Services\PokemonService;
use App\ViewModels\PokemonsViewModel;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class PokemonSearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PokemonSearchRequest $request, PokemonService $pokemonService): View
    {
        $pokemons = $pokemonService->getAllBySearch($request->safe()->name);

        $viewModel = new PokemonsViewModel($pokemons);

        return view('pokemons.search', $viewModel);
    }
}
