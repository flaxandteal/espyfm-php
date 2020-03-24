<?php

return [

    /**
     * Default location of EspyFM API server
     */
    'api-base-url' => env('ESPYFM_API_BASE_URL', 'http://localhost:5000'),

    /**
     * Set the class that is used for recommended items (can be interface)
     */
    'item-class' => \Flaxandteal\EspyFM\IsRecommendedItem::class,

    /**
     * Set the class that is used for recommenders (can be interface)
     */
    'user-class' => \App\Models\User::class

];
