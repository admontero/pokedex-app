<?php

namespace App\Exceptions;

use Exception;

class PokemonNotFoundException extends Exception
{
    public function __construct(string $message = '')
    {
        parent::__construct($message, 404);
    }

    public function render()
    {
        return abort(404);
    }
}
