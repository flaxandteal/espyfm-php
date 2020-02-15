<?php

namespace Flaxandteal\EspyFM;

use GuzzleHttp;

class EspyFMService
{
    /**
     * GuzzleHTTP client
     *
     * @val GuzzleHttp\Client
     */
    protected $client;

    /**
     * Base URL
     *
     * @val string
     */
    protected $baseUrl = '';

    /**
     * EspyFMService
     */
    public function __construct(GuzzleHttp\Client $client, string $baseUrl)
    {
        $this->client = $client;

        if (substr($baseUrl, -1) != '/') {
            $baseUrl .= '/';
        }

        $this->baseUrl = $baseUrl;
    }

    /**
     * Retrieve current base URL
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Retrieve current GuzzleHTTP client
     *
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Find recommendations for a user's categories.
     */
    public function getRecommendations()
    {
        $response = $this->client->post(
            $this->baseUrl . 'users/0/rhs',
            [
                'json' => [
                    'user' => [
                        'ageGroup' => 'abdominal',
                        'mostWantedCategories' => ['abdominal'],
                        'affected' => ['abdominal'],
                        'localGovernmentDistrict' => 'abdominal',
                        'gender' => 'abdominal'
                    ]
                ]
            ]
        );

        $result = $response->getBody();

        $recommendations = json_decode($result->getContents());

        return $recommendations;
    }
}
