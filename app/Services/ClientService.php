<?php

namespace App\Services;

use App\Exceptions\PokemonNotFoundException;
use App\Exceptions\PokemonRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Log;

abstract class ClientService
{
    public Client $client;

    public function __construct(
        public string $baseUrl,
        public array $headers = [],
        public bool $withErrorHandler = true,
    ) {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $this->headers,
            'http_errors' => $this->withErrorHandler,
        ]);
    }

    protected function fetch(string $url): array
    {
        $response = $this->client->get($url);

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

        foreach ($urls as $key => $url) {
            $promises[$key] = $this->client->getAsync($url);
        }

        $responses = Utils::settle($promises)->wait();

        $results = [];

        foreach ($responses as $key => $response) {
            if ($response['state'] === 'fulfilled') {
                $results[$key] = json_decode($response['value']->getBody()->getContents(), true);
            } else {
                Log::error('Error fetching data from URL: ' . $urls[$key], [
                    'exception' => $response['reason'],
                ]);

                throw new PokemonRequestException(
                    'Error fetching data from URL: ' . $urls[$key],
                    500,
                );
            }
        }

        return $results;
    }
}
