<?php

return [

    'maintenanceMode' => [
        'type' => 'boolean',
        'value' => false,
    ],

    'maxUploadSize' => [
        'type' => 'number',
        'value' => 10,
    ],

    'supportedFileTypes' => [
        'type' => 'json',
        'value' => ['jpg', 'png', 'pdf'],
    ],

    'featureFlags' => [
        'type' => 'json',
        'value' => [
            'enableChat' => false,
            'enableNotifications' => false
        ],
    ],

    'appVersion' => [
        'type' => 'string',
        'value' => '1.0.0',
    ],

];