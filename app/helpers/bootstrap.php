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
    $domains = [
        'localhost'   => ['localhost', 'papi.wish.com'],
        'development' => ['www.devel.com'],
        'staging'     => ['www.staging.com'],
        'production'  => ['www.production.com']
    ];
    // environment config load
    foreach($domains as $environment => $domain) {
        if(in_array(getenv('HTTP_HOST'), $domain)) {
            $di = require_once(__DIR__.'/../config/localhost.php');
            $di->set('ENVIRONMENT', $environment);
            $app->setDI($di);
            break;
        }
    }
}

{
    if(($prefix = getParam(1)) && is_file(__DIR__.'/../collections/'.$prefix.'.php'))
    {
        $collection = require_once(__DIR__.'/../collections/'.$prefix.'.php');
        $app->mount($collection);
    }
}

return $app;