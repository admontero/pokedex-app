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
        session()->put('backUrl', route('pokemons.search', ['page' => 1, 'term' => $request->term, 'type' => $request->type]));

        $pokemonList = $pokemonService->getAllBySearch(PokemonSearchDTO::fromRequest($request));

        $viewModel = new PokemonsViewModel(
            total: count($pokemonList),
            pokemons: $pokemonService->paginate($pokemonList, $request->page ?? 1)
        );

        return view('pokemons.search', $viewModel);
    }
}
