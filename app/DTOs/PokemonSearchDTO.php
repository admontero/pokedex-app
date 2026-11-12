<?php

namespace App\DTOs;

use App\Http\Requests\PokemonSearchRequest;

class PokemonSearchDTO
{
    public function __construct(
        public readonly string | null $term,
        public readonly string | null $type,
    ){}

    public static function fromRequest(PokemonSearchRequest $request): self
    {
        $validated = $request->validated();

        $term = $validated['term'] ?? null;
        $type = $validated['type'] ?? null;

        return new self(
            term: $term ? strtolower($term) : null,
            type: $type ? strtolower($type) : null,
        );
    }
}
