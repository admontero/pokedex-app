<?php

namespace App\Exceptions;

use Exception;

class PokemonNotFoundException extends Exception
{
    protected $originalException;

    public function __construct($message = "")
    {
        parent::__construct($message, 404);
    }

    public function render()
    {
        return abort(404, $this);
    }
}
