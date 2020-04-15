<?php

namespace Flaxandteal\EspyFM;

use GuzzleHttp;
use InvalidArgumentException;
use ScoutElastic\Facades\ElasticClient;

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
        $shortVector = array_map(function ($entry) {
            return round($entry, 4);
        }, $vector);

        $itemModelIndex = 'item_model_index';
        $recommendations = ElasticClient::search([
            'index' => $itemModelIndex,
            'body' => [
                'query' => [
                    'bool' => [
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
                                                    'vector' => $shortVector
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $lightFmIds = array_map(function ($recommendation) {
            return $recommendation['_source']['original_id'];
        }, $recommendations['hits']['hits']);

        $recommendations = $items->search('')
            ->rule(function ($builder) use ($lightFmIds) {
                return [
                    'filter' => [
                        'ids' => [
                            'values' => $lightFmIds
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
        $response = $this->client->post(
            $this->baseUrl . 'rebuild'
        );

        $result = $response->getBody();

        $recommendations = json_decode($result->getContents());

        return $recommendations->success;
    }
}
