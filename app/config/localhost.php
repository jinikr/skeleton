<?php

$di = new Phalcon\Di\FactoryDefault();

$di->set('session', function () {
    $session = new Phalcon\Session\Adapter\Files();
    $session->start();

    return $session;
 });

$di->set('dbconn', function() {
   return [
        'master' => [
            'dsn'      => "mysql:host=localhost;dbname=wired;charset=utf8",
            'username' => "root",
            'password' => "dbtmdals",
            'charset'  => "utf8",
        ],
        'slave' => [
            'dsn'      => "mysql:host=localhost;dbname=wired;charset=utf8",
            'username' => "root",
            'password' => "dbtmdals",
            'charset'  => "utf8",
        ],
    ];
});

return $di;