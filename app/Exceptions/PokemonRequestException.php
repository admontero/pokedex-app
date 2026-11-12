<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class PokemonRequestException extends Exception
{
    public function __construct(string $message = '', int $code = 502)
    {
        parent::__construct($message, $code);
    }

    /**
     * Un fallo al hablar con un servicio externo es un error de pasarela,
     * no un error interno de la aplicación. El mensaje original se manda a la
     * vista pero solo se muestra cuando APP_DEBUG esta activo.
     */
    public function render(): Response
    {
        $status = $this->getCode();

        if ($status < 400 || $status > 599) {
            $status = 502;
        }

        return response()->view('errors.500', ['message' => $this->getMessage()], $status);
    }
}
