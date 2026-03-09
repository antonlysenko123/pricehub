<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        // ¬—≤ прайси та ≥нший приватний контент Ч сюди
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'throw'  => false,
            'report' => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
            'report'     => false,
        ],

        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'                   => false,
            'report'                  => false,
        ],
        
        'matrix_ftp' => [
    'driver' => 'ftp',
    'host' => env('MATRIX_FTP_HOST'),
    'username' => env('MATRIX_FTP_USERNAME'),
    'password' => env('MATRIX_FTP_PASSWORD'),
    'root' => '/',
    'passive' => true,
    'ssl' => false,
    'timeout' => 300,
],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
