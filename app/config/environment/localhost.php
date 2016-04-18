<?php

define('LANG', 'ko');
date_default_timezone_set('ROK');
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 'On');
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('log_errors', 'On');
ini_set('track_errors', 'On');
ini_set('session.cookie_lifetime', '0');
ini_set('session.cookie_path', '/');

return [
    'databases' => [
        'master' => [
            'dsn'      => "mysql:host=localhost;dbname=api;charset=utf8",
            'username' => "apiuser",
            'password' => "apipw",
            'charset'  => "utf8",
            'options'  => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]
        ],
        'slave' => [
            'dsn'      => "mysql:host=localhost;dbname=api;charset=utf8",
            'username' => "apiuser",
            'password' => "apipw",
            'charset'  => "utf8",
            'options'  => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false
            ]
        ],
    ],
];