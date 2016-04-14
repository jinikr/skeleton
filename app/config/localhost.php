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

$di = new Phalcon\Di\FactoryDefault();

$di->set('session', function () {
    $session = new Phalcon\Session\Adapter\Files();
    $session->start();

    return $session;
 });

$di->set('dbconn', function() {
   return [
        'master' => [
            'dsn'      => "mysql:host=localhost;dbname=dbname;charset=utf8",
            'username' => "user",
            'password' => "password",
            'charset'  => "utf8",
        ],
        'slave' => [
            'dsn'      => "mysql:host=localhost;dbname=dbname;charset=utf8",
            'username' => "user",
            'password' => "password",
            'charset'  => "utf8",
        ],
    ];
});

return $di;