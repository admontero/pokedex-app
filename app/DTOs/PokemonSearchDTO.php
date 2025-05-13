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
        return new self(
            term: $request->safe()->term ? strtolower($request->safe()->term) : null,
            type: $request->safe()->type ? strtolower($request->safe()->type) : null,
        );
    }
}
