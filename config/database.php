<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_COLUMN,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of envuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql' => [
            'driver'    => env('MYSQL_DRIVER', 'mysql'),
            'host'      => env('MYSQL_HOST', '192.168.99.100'),
            'port'      => env('MYSQL_PORT', 4306),
            'database'  => env('MYSQL_DB', 'bank_db'),
            'username'  => env('MYSQL_USERNAME', 'root'),
            'password'  => env('MYSQL_PASSWORD', 'root'),
            'charset'   => env('MYSQL_CHARSET', 'utf8'),
            'collation' => env('MYSQL_COLLATION', 'utf8_unicode_ci'),
            'timezone'  => env('MYSQL_TIMEZONE', '+00:00'),
            'strict'    => env('STRICT', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',
];
