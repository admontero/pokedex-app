<?php

namespace App\DTOs;

use App\Http\Requests\PokemonSearchRequest;

class PokemonSearchDTO
{
    public function __construct(
        public readonly string | null $name,
        public readonly string | null $type,
    ){}

    public static function fromRequest(PokemonSearchRequest $request): self
    {
        return new self(
            name: isset($request->safe()->name) ? strtolower($request->safe()->name) : null,
            type: isset($request->safe()->type) ? strtolower($request->safe()->type) : null,
        );
    }
}
