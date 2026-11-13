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
        $perPage = (int) config('pokemon.per_page', 16);
        // AJAX requests only need the new page; full loads stay accumulative.
        $accumulate = ! $request->ajax();

        if (! $request->ajax()) {
            session()->put('backUrl', route('pokemons.search', [
                'page' => $page,
                'term' => $request->validated('term'),
                'type' => $request->validated('type'),
            ]));
        }

        $pokemonList = $pokemonService->getAllBySearch(PokemonSearchDTO::fromRequest($request));

        $viewModel = new PokemonListViewModel(
            total: count($pokemonList),
            pokemons: $pokemonService->paginate($pokemonList, $page, $perPage, accumulate: $accumulate),
            page: $page,
            perPage: $perPage,
        );

        return view('pokemons.search', $viewModel);
    }
}
