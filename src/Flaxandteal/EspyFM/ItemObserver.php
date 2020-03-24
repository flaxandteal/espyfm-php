<?php

namespace Flaxandteal\EspyFM;

use Cviebrock\LaravelElasticsearch\Manager as Elasticsearch;

class ItemObserver
{
    /**
     * @var Elasticsearch
     */
    protected $elasticsearch;

    /**
     * @var string
     */
    protected $itemClass;

    /**
     * EspyFMService
     */
    public function __construct(Elasticsearch $elasticsearch, string $itemClass)
    {
        $this->elasticsearch = $elasticsearch;
        $this->itemClass = $itemClass;
    }

/**
    public function saved($model)
    {
        if (! $model instanceof $this->itemClass) {
            throw new InvalidArgumentException(
                __('Item for Elasticsearch was not of type ' . $this->itemClass)
            );
        }

        $body = array_merge(
            $model->toSearchArray(),
            [
                'dirty' => true
            ]
        );
        $this->elasticsearch->index($body);
    }

    public function deleted($model)
    {
        if (! $model instanceof $this->itemClass) {
            throw new InvalidArgumentException(
                __('Item for Elasticsearch was not of type ' . $this->itemClass)
            );
        }

        $this->elasticsearch->delete([
            'index' => $model->getTable(),
            'type' => $this->itemClass,
            'id' => $model->getKey(),
        ]);
    }
*/
}
