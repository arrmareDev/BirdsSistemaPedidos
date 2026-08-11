<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // config/services.php — agregar dentro del array return

    'delivery_central' => [
        'url'             => env('DELIVERY_CENTRAL_URL'),
        'api_key'         => env('DELIVERY_CENTRAL_API_KEY'),
        'webhook_secret'  => env('DELIVERY_CENTRAL_WEBHOOK_SECRET'),
    ],

    'local' => [
        'lat'          => env('LOCAL_LAT'),
        'lng'          => env('LOCAL_LNG'),
        'radio_max_km' => env('LOCAL_RADIO_MAX_KM', 25.0),
    ],

];
