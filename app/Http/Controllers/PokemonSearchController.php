<?php

namespace App\Http\Controllers;

use App\DTOs\PokemonSearchDTO;
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
        $viewModel = new PokemonsViewModel(
            pokemons: $pokemonService->getAllBySearch(PokemonSearchDTO::fromRequest($request))
        );

        return view('pokemons.search', $viewModel);
    }
}
