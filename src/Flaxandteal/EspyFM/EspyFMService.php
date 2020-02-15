<?php

namespace Flaxandteal\EspyFM;

use GuzzleHttp;

class EspyFMService {
    /**
     * GuzzleHTTP client
     *
     * @val GuzzleHttp\Client
     */
    protected $client;

    /**
     * EspyFMService
     */
    public function __construct(GuzzleHttp\Client $client) {
        $this->client = $client;
    }

    /**
     * Find recommendations for a user's categories.
     */
    public function getRecommendations() {
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

        $recommendations = $response->getBody();
        \Log::info($recommendations);
    }
}
