<?php
// config/webpush.php

return [
    'vapid' => [
        // Identifica tu sitio ante los navegadores — un mailto: o una URL https:// tuya.
        'subject'     => env('VAPID_SUBJECT', 'mailto:soporte@birds.pe'),
        'public_key'  => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
    ],
];
