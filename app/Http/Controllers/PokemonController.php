<?php

namespace App\Http\Controllers;

use App\Http\Requests\PokemonIndexRequest;
use App\Services\PokemonService;
use App\ViewModels\PokemonsViewModel;
use App\ViewModels\PokemonViewModel;
use Illuminate\Support\Facades\Session;
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
        $pokemons = $this->pokemonService->getAll($request->page ?? 1);

        $viewModel = new PokemonsViewModel($pokemons);

        return view('pokemons.index', $viewModel);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $pokemon = $this->pokemonService->getOne($id);

        $total = $this->pokemonService->getTotal();

        if (! $pokemon || $id > $total) abort(404);

        $viewModel = new PokemonViewModel($pokemon);

        return view('pokemons.show', $viewModel, [
            'total' => $total,
        ]);
    }
}
