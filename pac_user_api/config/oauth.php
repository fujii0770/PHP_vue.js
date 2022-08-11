<?php

return [

    'box' => [
        'clientId' => env('BOX_CLIENT_ID', 'nyd55tvz1qs51a4cvmmejvbeenbwr29e'),
        'clientSecret' => env('BOX_CLIENT_SECRET', 'a1WmVrgY85i5LI8Edk81WoAHwzS337kI'),
        'urlAuthorize' => 'https://account.box.com/api/oauth2/authorize',
        'urlAccessToken' => 'https://api.box.com/oauth2/token',
        'urlResourceOwnerDetails' => 'https://api.box.com/2.0/users/me',
        'base_url' => 'https://api.box.com/2.0/',
        'upload_url' => 'https://upload.box.com/api/2.0/'
    ],
];
