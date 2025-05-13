<?php

namespace App\Http\Controllers;

use App\Http\Requests\PokemonIndexRequest;
use App\Services\PokemonService;
use App\ViewModels\PokemonsViewModel;
use App\ViewModels\PokemonViewModel;
use Illuminate\View\View;

class PokemonController extends Controller
{
    public function __construct(
        public PokemonService $pokemonService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(PokemonIndexRequest $request): View
    {
        session()->put('backUrl', route('pokemons.index', ['page' => $request->page ?? 1]));

        $pokemonList = $this->pokemonService->getAllPokemon();

        $viewModel = new PokemonsViewModel(
            total: count($pokemonList),
            pokemons: $this->pokemonService->paginate($pokemonList, $request->page ?? 1),
        );

        return view('pokemons.index', $viewModel);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $name): View
    {
        $viewModel = new PokemonViewModel(
            pokemon: $this->pokemonService->getPokemon($name),
            previous: $this->pokemonService->getPreviousPokemonName($name),
            next: $this->pokemonService->getNextPokemonName($name),
        );

        return view('pokemons.show', $viewModel);
    }
}
