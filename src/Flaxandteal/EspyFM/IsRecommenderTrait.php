<?php

namespace Flaxandteal\EspyFM;

use InvalidArgumentException;
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
                'recommendations' => [
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
     * Cancel a recommendation for an item.
     */
    public function recommendsNot($item)
    {
        $this->checkIsRecommendedItem($item);
        $this->recommendedItems()->detach($item->id);
    }

    /**
     * Create a recommendation for an item.
     */
    public function recommends($item)
    {
        $this->checkIsRecommendedItem($item);
        $this->recommendedItems()->syncWithoutDetaching([$item->getKey() => ['score' => 1]]);
        $this->recommendedItems->push($item);
    }

    /**
     * Find the taggable categories for EspyFM
     *
     * @return array(string)
     */
    public function getEspyFMCategories()
    {
        return [
            'abdominal'
        ];
    }

    /**
     * Confirm an argument is of the correct class
     *
     * @param IsRecommendedItem
     * @throws InvalidArgumentException
     */
    public function checkIsRecommendedItem($val)
    {
        $espyFMService = $this->getEspyFMService();
        $itemClass = $espyFMService->getRecommendedItemClass();

        if (! $val instanceof $itemClass) {
            throw new InvalidArgumentException(
                __('Recommended item was not of type ' . $itemClass)
            );
        }
    }


    /**
     * Check if this user recommends this item.
     * Efficient only for small users, many items.
     *
     * @param IsRecommendedItem
     * @return bool
     */
    public function hasRecommended($item)
    {
        $this->checkIsRecommendedItem($item);
        return $this->recommendedItems->contains($item);
    }
}
