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
        'item_max_limit' => '1000',
    ],

    'onedrive' => [
        'clientId' => env('ONEDRIVE_CLIENT_ID', ''),
        'clientSecret' => env('ONEDRIVE_CLIENT_SECRET', ''),
        'urlAuthorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
        'urlAccessToken' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
        'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/users/me',
        'base_url' => 'https://graph.microsoft.com/v1.0/',
        'upload_url' => 'https://graph.microsoft.com/v1.0/'

    ],

    'google' => [
        'clientId' => env('GOOGLE_CLIENT_ID', ''),
        'clientSecret' => env('GOOGLE_CLIENT_SECRET', ''),
        'urlAuthorize' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'urlAccessToken' => 'https://oauth2.googleapis.com/token',
        'urlResourceOwnerDetails' => '',
        'base_url' => 'https://www.googleapis.com/drive/v2/',
        'upload_url' => 'https://www.googleapis.com/upload/drive/v2/'
    ],

    'dropbox' => [
        'clientId' => env('DROPBOX_CLIENT_ID', ''),
        'clientSecret' => env('DROPBOX_CLIENT_SECRET', ''),
        'urlAuthorize' => 'https://www.dropbox.com/oauth2/authorize',
        'urlAccessToken' => 'https://api.dropboxapi.com/oauth2/token',
        'urlResourceOwnerDetails' => '',
        'base_url' => 'https://api.dropboxapi.com/2/',
        'upload_url' => 'https://content.dropboxapi.com/2/'
    ],

    'return_url' => env('OAUTH_RETURN_URL', 'https://enhrsryfyk42h.x.pipedream.net/'),
];
