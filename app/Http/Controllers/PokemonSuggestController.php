<?php

namespace App\Http\Controllers;

use App\Services\PokemonSuggestions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PokemonSuggestController extends Controller
{
    /**
     * Return the search suggestions for the given term as JSON.
     */
    public function __invoke(Request $request, PokemonSuggestions $suggestions): JsonResponse
    {
        $validated = $request->validate([
            'term' => 'nullable|string|max:30',
        ]);

        $results = collect($suggestions->search($validated['term'] ?? null))
            ->map(fn (array $pokemon): array => [
                'id' => $pokemon['id'],
                'number' => $pokemon['id'] === null ? null : str_pad((string) $pokemon['id'], 3, '0', STR_PAD_LEFT),
                'name' => $pokemon['name'],
                'url' => route('pokemons.show', $pokemon['name']),
            ])
            ->all();

        return response()->json(['results' => $results]);
    }
}
