<?php

namespace Flaxandteal\EspyFM;

use GuzzleHttp;
use InvalidArgumentException;

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
     * Class of the recommenders
     *
     * @val string
     */
    protected $userClass = IsRecommender::class;

    /**
     * Class of the recommended items
     *
     * @val string
     */
    protected $itemClass = IsRecommendedItem::class;

    /**
     * EspyFMService
     */
    public function __construct(GuzzleHttp\Client $client, string $baseUrl, string $itemClass, string $userClass)
    {
        $this->client = $client;
        $this->itemClass = $itemClass;
        $this->userClass = $userClass;

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
     * Get the recommender class.
     *
     * @return string
     */
    public function getRecommenderClass()
    {
        return $this->userClass;
    }

    /**
     * Get the recommended item class.
     *
     * @return string
     */
    public function getRecommendedItemClass()
    {
        return $this->itemClass;
    }

    /**
     * Find recommendations for a user's categories.
     *
     * @param User $user
     */
    public function getRecommendations($user)
    {
        $userClass = $this->getRecommenderClass();
        if (! $user instanceof $userClass) {
            throw new InvalidArgumentException(
                __('User for Elasticsearch was of type ' . get_class($user) . ' not of type ' . $userClass)
            );
        }

        $items = app()->make($this->getRecommendedItemClass());

        $response = $this->client->get(
            $this->baseUrl . 'users/' . $user->id . '/rhs' // RMV: should this be espyfm_lightfm_id?
        );
        $vector = json_decode($response->getBody()->getContents());

        $recommendations = $items->search('')
            ->rule(function ($builder) use ($vector) {
                return [
                    'must' => [
                        'function_score' => [
                            'query' => [
                                'exists' => [
                                    'field' => 'embedding_vector'
                                ],
                            ],
                            'functions' => [
                                [
                                    'script_score' => [
                                        'script' => [
                                            'source' => 'binary_vector_score',
                                            'lang' => 'knn',
                                            'params' => [
                                                'cosine' => false,
                                                'field' => 'embedding_vector',
                                                'vector' => $vector
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            });

        return $recommendations->get();
    }

    /**
     * Populate items for a user's categories.
     */
    public function generateItems()
    {
        $response = $this->client->post(
            $this->baseUrl . 'populate_items',
            [
                'json' => [
                ]
            ]
        );

        $result = $response->getBody();
        $items = json_decode($result->getContents());
        return $items;
    }

    /**
     * Populate recommendations for a user's categories.
     */
    public function populateRecommendations()
    {
        $response = $this->client->post(
            $this->baseUrl . 'populate',
            [
                'json' => [
                ]
            ]
        );

        $result = $response->getBody();

        $recommendations = json_decode($result->getContents());

        return $recommendations->users;
    }

    /**
     * Rebuild recommendations for a user's categories.
     */
    public function rebuildRecommendations()
    {
        // TODO: work out how to handle not-yet-lightfmed-item-recommendations
        $itemIndex = app()->make($this->itemClass)->getEspyFMIndexingKey();
        $users = app()->make($this->userClass)
            ->with('recommendedItems')
            ->get()
            ->map(
                function ($user) use ($itemIndex) {
                    return [
                        $user->espyfm_lightfm_id,
                        $user->recommendedItems->pluck('pivot.score', $itemIndex)
                    ];
                }
            );

        $response = $this->client->post(
            $this->baseUrl . 'rebuild',
            [
                'json' => $users
            ]
        );

        $result = $response->getBody();

        $recommendations = json_decode($result->getContents());

        return $recommendations->success;
    }
}
