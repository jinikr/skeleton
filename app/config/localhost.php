<?php

{
    define('LANG', 'ko');
    date_default_timezone_set('ROK');
    ini_set('display_errors', 'On');
    ini_set('display_startup_errors', 'On');
    ini_set('error_reporting', E_ALL);
    ini_set('log_errors', 'On');
    ini_set('track_errors', 'On');
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', '/');

    if (false == in_array(getenv('REMOTE_ADDR'), array('000.000.000.000'))) {
        //header("HTTP/1.0 404 Not Found");
        //exit();
    }
}

Peanut\Db\Driver::setConnectInfo([
    'master' => [
        'dsn'      => "mysql:host=localhost;dbname=wired;charset=utf8",
        'username' => "root",
        'password' => "dbtmdals",
        'charset'  => "utf8",
        'options'  => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ]
    ],
    'slave' => [
        'dsn'      => "mysql:host=localhost;dbname=wired;charset=utf8",
        'username' => "root",
        'password' => "dbtmdals",
        'charset'  => "utf8",
        'options'  => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false
        ]
    ],
]);

$di = new Phalcon\Di\FactoryDefault();

$di->set('session', function () {
    $session = new Phalcon\Session\Adapter\Files();
    $session->start();

    return $session;
});

return $di;