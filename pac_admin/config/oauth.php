<?php

return [

    'box' => [
        'clientId' => env('BOX_CLIENT_ID', ''),
        'clientSecret' => env('BOX_CLIENT_SECRET', ''),
        'urlAuthorize' => 'https://account.box.com/api/oauth2/authorize',
        'urlAccessToken' => 'https://api.box.com/oauth2/token',
        'urlResourceOwnerDetails' => 'https://api.box.com/2.0/users/me',
        'base_url' => 'https://api.box.com/2.0/',
        'upload_url' => 'https://upload.box.com/api/2.0/',
        'item_max_limit' => 1000, // api folders limit
    ],

    'return_url' => env('OAUTH_RETURN_URL', 'https://pac-ne1.com/app/externalCallbackDone'),
];
