<?php

namespace App\Http\Controllers;

use App\Http\Requests\PokemonIndexRequest;
use App\Services\PokemonService;
use App\ViewModels\PokemonListViewModel;
use App\ViewModels\PokemonViewModel;
use Illuminate\Http\Request;
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
        $page = (int) $request->validated('page', 1);
        $perPage = (int) config('pokemon.per_page', 16);
        // AJAX requests only need the new page: the browser appends it to the
        // grid. Full loads keep every page up to $page so URLs stay shareable.
        $accumulate = ! $request->ajax();

        if (! $request->ajax()) {
            session()->put('backUrl', route('pokemons.index', ['page' => $page]));
        }

        $pokemonList = $this->pokemonService->getAllPokemon();

        $viewModel = new PokemonListViewModel(
            total: count($pokemonList),
            pokemons: $this->pokemonService->paginate($pokemonList, $page, $perPage, accumulate: $accumulate),
            page: $page,
            perPage: $perPage,
        );

        return view('pokemons.index', $viewModel);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $name): View
    {
        $from = $this->safeBackUrl($request->query('from'));

        $viewModel = new PokemonViewModel(
            pokemon: $this->pokemonService->getPokemon($name),
            previous: $this->pokemonService->getPreviousPokemonName($name),
            next: $this->pokemonService->getNextPokemonName($name),
            backUrl: $from ?? session()->get('backUrl') ?? route('pokemons.index'),
            from: $from,
        );

        return view('pokemons.show', $viewModel);
    }

    /**
     * Only accept local URLs as the "back" destination to avoid open
     * redirects and unexpected cross-site navigation.
     */
    private function safeBackUrl(?string $from): ?string
    {
        if (! is_string($from) || $from === '') {
            return null;
        }

        if (str_starts_with($from, '/') && ! str_starts_with($from, '//')) {
            return $from;
        }

        $target = parse_url($from);
        $base = parse_url(url('/'));

        if ($target === false || $base === false || ! isset($target['host'], $base['host'])) {
            return null;
        }

        $sameOrigin = $target['host'] === $base['host']
            && ($target['scheme'] ?? null) === ($base['scheme'] ?? null)
            && ($target['port'] ?? null) === ($base['port'] ?? null);

        return $sameOrigin ? $from : null;
    }
}
