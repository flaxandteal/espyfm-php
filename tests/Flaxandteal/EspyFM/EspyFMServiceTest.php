<?php

namespace Flaxandteal\EspyFM;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

/**
 * EspyFMServiceTest checks the EspyFM Service.
 */
class EspyFMServiceTest extends TestCase
{
    /**
     * Guzzle client
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    protected function setUp(): void
    {
        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'toast',
                'visible'
            ])),
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $this->client = new Client(['handler' => $handlerStack]);

        $this->baseUrl = 'http://localhost:5000';
    }

    /**
     * @covers Flaxandteal\EspyFM\EspyFMService::__construct
     */
    public function testConstruct()
    {
        $espyService = new EspyFMService($this->client, $this->baseUrl);

        $this->assertEquals($this->client, $espyService->getClient());
        $this->assertEquals($this->baseUrl . '/', $espyService->getBaseUrl());

        $espyService = new EspyFMService($this->client, $this->baseUrl . '/');
        $this->assertEquals($this->baseUrl . '/', $espyService->getBaseUrl());
    }

    /**
     * @covers Flaxandteal\EspyFM\EspyFMService::getRecommendations
     */
    public function testGetRecommendations()
    {
        $espyService = new EspyFMService($this->client, $this->baseUrl);

        $recommendations = $espyService->getRecommendations();

        $this->assertEquals($recommendations, [
            'toast',
            'visible'
        ]);
    }
}
