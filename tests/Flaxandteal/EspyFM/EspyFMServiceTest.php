<?php

namespace Flaxandteal\EspyFM;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class EspyFMServiceTest extends TestCase
{
    /**
     * Guzzle client
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    protected function setUp()
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
    }

    /**
     * @covers Flaxandteal\EspyFM\EspyFMService::__construct
     */
    public function testConstruct()
    {
        $espyService = new EspyFMService($client);
    }

    /**
     * @covers Flaxandteal\EspyFM\EspyFMService::getRecommendations
     */
    public function testGetRecommendations()
    {
    }
}
