<?php
/**
 * Created by PhpStorm.
 * User: darlane
 * Date: 22.05.18
 * https://github.com/darlane
 */

return [
    'read'     => [
        'host' => env('ORACLE_HOST_FOR_READ'),
    ],
    'write'    => [
        'host' => env('ORACLE_HOST_FOR_WRITE'),
    ],
    'port'     => env('ORACLE_PORT', 1521),
    'database' => env('ORACLE_DATABASE'),
    'username' => env('ORACLE_USER'),
    'password' => env('ORACLE_PASSWORD'),
    'charset'  => env('ORACLE_ENCODING', 'utf8'),
    'timeout'  => env('ORACLE_TIMEOUT', 1),
    'server'   => env('ORACLE_SERVER_TYPE', 'dedicated'),
];