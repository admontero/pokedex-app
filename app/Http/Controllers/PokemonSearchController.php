<?php

namespace App\Http\Controllers;

use App\Http\Requests\PokemonSearchRequest;
use App\Services\PokemonService;
use App\ViewModels\PokemonsViewModel;
use Illuminate\View\View;

class PokemonSearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PokemonSearchRequest $request, PokemonService $pokemonService): View
    {
        try {
            $pokemons = $pokemonService->getAllBySearch($request->safe()->name);
        } catch (\Exception $e) {
            return view('errors.500', ['error' => $e->getMessage()]);
        }

        $viewModel = new PokemonsViewModel($pokemons);

        return view('pokemons.search', $viewModel);
    }
}
