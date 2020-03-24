<?php

namespace Flaxandteal\EspyFM;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait IsRecommenderTrait
{
    /**
     * Flesh out the Elasticsearch model
     *
     * @return null
     */
    public function initializeIsRecommendedItemTrait()
    {
        $this->mapping['properties'] = array_merge(
            [
                'categories' => [
                    'type' => 'keyword',
                ],
                'embedding_vector' => [
                    'type' => 'binary',
                    'doc_values' => true,
                ],
                'espyfm_lightfm_id' => [
                    'type' => 'integer',
                    'fields' => [
                        'raw' => [
                            'type' => 'keyword',
                        ]
                    ]
                ]
            ],
            $this->mapping['properties']
        );
    }

    /**
     * Default retrieval for EspyFMService, using Laravel
     * app function.
     *
     * @return App
     */
    public function getEspyFMService()
    {
        return app()->make(EspyFMService::class);
    }

    /**
     * Set up recommendations.
     *
     * @return HasMany
     */
    public function recommendedItems()
    {
        $class = $this->getEspyFMService()->getRecommendedItemClass();
        return $this->belongsToMany(
            $class,
            'espyfm_recommendations',
            'user_id',
            'item_id'
        )->withPivot('score');
    }

    /**
     * Create a recommendation for an item.
     */
    public function recommends($item)
    {
        $espyFMService = $this->getEspyFMService();
        $itemClass = $espyFMService->getRecommendedItemClass();

        if (! $item instanceof $itemClass) {
            throw new InvalidArgumentException(
                __('Recommended item was not of type ' . $itemClass)
            );
        }

        $this->recommendedItems()->updateExistingPivot($item->getKey(), ['score' => 1]);
        \Log::info('111');
        \Log::info($this->recommendedItems);
    }

    /**
     * Find the taggable categories for EspyFM
     *
     * return array(string)
     */
    public function getEspyFMCategories()
    {
        return [
            'abdominal'
        ];
    }
}
