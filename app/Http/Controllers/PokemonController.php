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
        $data = $this->pokemonService->getAll($request->page ?? 1);

        $viewModel = new PokemonsViewModel($data);

        return view('pokemons.index', $viewModel);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        if ($id > $this->pokemonService->getTotal())
            return view('errors.404');

        $viewModel = new PokemonViewModel(
            pokemon: $this->pokemonService->getOne($id),
            total: $this->pokemonService->getTotal(),
        );

        return view('pokemons.show', $viewModel);
    }
}
