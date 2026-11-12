<?php

namespace App\Http\Controllers;

use App\DTOs\PokemonSearchDTO;
use App\Http\Requests\PokemonSearchRequest;
use App\Services\PokemonService;
use App\ViewModels\PokemonListViewModel;
use Illuminate\View\View;

class PokemonSearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PokemonSearchRequest $request, PokemonService $pokemonService): View
    {
        $page = (int) $request->validated('page', 1);

        session()->put('backUrl', route('pokemons.search', [
            'page' => $page,
            'term' => $request->validated('term'),
            'type' => $request->validated('type'),
        ]));

        $pokemonList = $pokemonService->getAllBySearch(PokemonSearchDTO::fromRequest($request));

        $viewModel = new PokemonListViewModel(
            total: count($pokemonList),
            pokemons: $pokemonService->paginate($pokemonList, $page)
        );

        return view('pokemons.search', $viewModel);
    }
}
