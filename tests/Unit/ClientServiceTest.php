<?php

namespace Tests\Unit;

use App\Services\ClientService;
use GuzzleHttp\Handler\MockHandler;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    public function test_it_configures_timeouts_and_disables_guzzle_exceptions(): void
    {
        $client = new class('https://example.test/', [], false, new MockHandler) extends ClientService {};

        $this->assertSame(10, $client->client->getConfig('timeout'));
        $this->assertSame(3, $client->client->getConfig('connect_timeout'));
        $this->assertFalse($client->client->getConfig('http_errors'));
    }
}
