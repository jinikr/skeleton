<?php

use \App\Helpers\Cores\Bootstrap;
use \Phalcon\DI\FactoryDefault as Di;
use \App\Helpers\Cores\Mvc\Micro;

try
{
    define('__BASE__', dirname(dirname(__FILE__)));

    {
        include_once __BASE__."/vendor/autoload.php";
        include_once __BASE__.'/app/helpers/debug.php';
        include_once __BASE__.'/app/helpers/function.php';
    }

    (new Bootstrap(new Di))(new Micro)->handle();
}
catch (\Exception $e)
{
    echo '<pre>';
    print_r($e->getMessage());
    echo "\n\n";
    print_r($e->getTraceAsString());
    echo '</pre>';
}
