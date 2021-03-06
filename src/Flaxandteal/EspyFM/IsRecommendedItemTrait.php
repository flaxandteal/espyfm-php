<?php

namespace Flaxandteal\EspyFM;

trait IsRecommendedItemTrait
{
    /**
     * Flesh out the Elasticsearch model
     *
     * @return null
     */
    public function initializeIsRecommendedItemTrait()
    {
        if (config('espyfm.enabled', true)) {
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
    }

    /**
     * Set up recommendations.
     *
     * @return HasMany
     */
    public static function recommenders()
    {
        $class = $this->getEspyFMService()->getRecommenderClass();
        return $this->belongsToMany(
            $class,
            'espyfm_recommendations',
            'item_id',
            'user_id'
        )->withPivot('score');
    }

    /**
     * Get the index name for the Elasticsearch model
     *
     * @return string
     */
    public function getEspyFMIndexingKey()
    {
        return $this->getScoutKeyName();
    }

    /**
     * Get the LightFM ID as a string
     *
     * @return string
     */
    public function getEspyLightfmIdStringAttribute()
    {
        // FIXME: should this use getEspyFMIndexingKey?
        return (string)$this->espy_lightfm_id;
    }
}
