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
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => false,
                //\PDO::ATTR_AUTOCOMMIT => false,
                //\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
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
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => false,
                //\PDO::ATTR_AUTOCOMMIT => false,
                //\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
            ]
        ],
    ],
];
