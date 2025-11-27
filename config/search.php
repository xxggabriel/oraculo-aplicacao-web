<?php

return [
    'driver' => env('SEARCH_DRIVER', 'elasticsearch'),

    'elasticsearch' => [
        'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
        'host' => env('ELASTICSEARCH_HOST', 'elasticsearch'),
        'port' => env('ELASTICSEARCH_PORT', 9200),
        'user' => env('ELASTICSEARCH_USER'),
        'password' => env('ELASTICSEARCH_PASSWORD'),
        'index' => env('ELASTICSEARCH_INDEX', 'itens-normalizados'),
        'ca_bundle' => env('ELASTICSEARCH_CA_BUNDLE'),
    ],
];
