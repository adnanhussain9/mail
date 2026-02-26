<?php

return [
    /*
    |----------------------------------------------------------------------------
    | Google application name
    |----------------------------------------------------------------------------
    */
    'application_name' => env('APP_NAME', 'Laravel'),

    /*
    |----------------------------------------------------------------------------
    | Google service account
    |----------------------------------------------------------------------------
    |
    | Set the credentials JSON's location to use assert credentials, otherwise
    | app engine or compute engine will be used.
    |
    */
    'service' => [
        /*
        | Enable service account auth or not.
        */
        'enable' => env('GOOGLE_SERVICE_ENABLED', true),

        /*
        | Path to service account json file.
        */
        'file' => base_path(env('GOOGLE_SERVICE_ACCOUNT_JSON', 'storage/app/google-service-account.json')),
    ],

    'scopes' => [\Google\Service\Sheets::DRIVE, \Google\Service\Sheets::SPREADSHEETS],
];
