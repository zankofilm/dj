<?php

return [
    'locales' => ['fa', 'ku', 'en'],
    'rtl_locales' => ['fa', 'ku'],
    'default_locale' => env('APP_LOCALE', 'fa'),
    'architecture' => [
        'style' => 'modular-monolith',
        'database_authority' => 'mysql',
        'shared_hosting_first' => true,
    ],
];
