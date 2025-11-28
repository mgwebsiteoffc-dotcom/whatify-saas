<?php

return [
    'api_key'     => env('SHOPIFY_API_KEY'),
    'secret'      => env('SHOPIFY_API_SECRET'),
    'scopes'      => env('SHOPIFY_SCOPES', 'read_orders,read_checkouts'),
    'app_url'     => rtrim(env('SHOPIFY_APP_URL', config('app.url')), '/'),
    'api_version' => '2024-10',
];
