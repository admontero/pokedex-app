<?php

namespace App\Services;

use App\Exceptions\PokemonNotFoundException;
use App\Exceptions\PokemonRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class ClientService
{
    private const TIMEOUT = 10;

    private const CONNECT_TIMEOUT = 3;

    private const MAX_RETRIES = 2;

    public Client $client;

    public function __construct(
        public string $baseUrl,
        public array $headers = [],
        public bool $withErrorHandler = true,
    ) {
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            $this->shouldRetry(),
            static fn (int $retries): int => 100 * (2 ** ($retries - 1)),
        ));

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $this->headers,
            'http_errors' => $this->withErrorHandler,
            'timeout' => self::TIMEOUT,
            'connect_timeout' => self::CONNECT_TIMEOUT,
            'handler' => $stack,
        ]);
    }

    /**
     * Reintenta errores de red y respuestas 5xx del servicio externo.
     * Con http_errors desactivado Guzzle no lanza excepción en 5xx, por eso
     * tambien se inspecciona la respuesta.
     */
    private function shouldRetry(): callable
    {
        return static function (
            int $retries,
            RequestInterface $request,
            ?ResponseInterface $response = null,
            ?Throwable $exception = null,
        ): bool {
            if ($retries >= self::MAX_RETRIES) {
                return false;
            }

            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($exception instanceof RequestException) {
                return ! $exception->hasResponse()
                    || $exception->getResponse()->getStatusCode() >= 500;
            }

            return $response !== null && $response->getStatusCode() >= 500;
        };
    }

    protected function fetch(string $url): array
    {
        try {
            $response = $this->client->get($url);
        } catch (\Exception $e) {
            Log::error('Error constructing URL: ' . $e->getMessage());

            throw new PokemonRequestException('Error constructing URL: ' . $e->getMessage());
        }

        if ($response->getStatusCode() === 404) {
            throw new PokemonNotFoundException('Pokemon not found');
        }

        if ($response->getStatusCode() !== 200) {
            Log::error('Error fetching data from URL: ' . $url, [
                'status_code' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
            ]);

            throw new PokemonRequestException(
                'Error fetching data from URL: ' . $url,
                $response->getStatusCode(),
            );
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function fetchMultiple(array $urls): array
    {
        $promises = [];
        $results = [];

        foreach ($urls as $key => $url) {
            $promises[$key] = $this->client->getAsync($url);
        }

        $responses = Utils::settle($promises)->wait();

        foreach ($responses as $key => $response) {
            if ($response['state'] === 'fulfilled') {
                $results[$key] = json_decode($response['value']->getBody()->getContents(), true);
            } else {
                Log::error('Error fetching data from URL: ' . $urls[$key], [
                    'exception' => $response['reason'],
                ]);

                throw new PokemonRequestException(
                    'Error fetching data from URL: ' . $urls[$key],
                    502,
                );
            }
        }

        return $results;
    }
}
