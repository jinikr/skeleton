<?php

try
{
    define('__BASE__', dirname(dirname(__FILE__)));

    {
        include_once __BASE__."/vendor/autoload.php";
        include_once __BASE__.'/app/helpers/debug.php';
        include_once __BASE__.'/app/helpers/function.php';
    }

    $app = (new \App\Helpers\Cores\Bootstrap(new \Phalcon\DI\FactoryDefault))(new \App\Helpers\Cores\Mvc\Micro)
    ->handle();
}
catch (\Exception $e)
{
    echo '<pre>';
    print_r($e->getMessage());
    echo "\n\n";
    print_r($e->getTraceAsString());
    echo '</pre>';
}
