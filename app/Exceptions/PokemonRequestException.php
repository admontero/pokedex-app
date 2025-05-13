<?php

namespace App\Exceptions;

use Exception;

class PokemonRequestException extends Exception
{
    public function __construct($message = "", $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return abort($this->getCode(), $this->getMessage());
    }
}
