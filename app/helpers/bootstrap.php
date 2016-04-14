<?php

{
    //require_once __DIR__.'/../helpers/debug.php';
    require_once __DIR__.'/function.php';
    //require_once __DIR__."/../../vendor/autoload.php";
}

{
    $loader = new Phalcon\Loader();
    $loader->registerNamespaces(
        array(
           "app"         => "../app"
        )
    );
    $loader->register();
}

{
    $app = new Phalcon\Mvc\Micro();
}

{
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

    $app->setDI($di);
}

{
    if(($prefix = getParam(1)) && is_file(__DIR__.'/../collections/'.$prefix.'.php'))
    {
        $collection = require_once(__DIR__.'/../collections/'.$prefix.'.php');
        $app->mount($collection);
    }
}

return $app;