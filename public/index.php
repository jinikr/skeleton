<?php

try
{
    define('__BASE__', dirname(dirname(__FILE__)));

    {
        include_once __BASE__."/vendor/autoload.php";
        include_once __BASE__.'/app/helpers/debug.php';
        include_once __BASE__.'/app/helpers/function.php';
    }

    $app = (new \App\Helpers\Cores\Bootstrap(new \Phalcon\DI\FactoryDefault))(new \Phalcon\Mvc\Micro);

    $app->before(
        function () use ($app)
        {
            return App\Middlewares\Auth::call($app);
        }
    );
    $app->get(
        '/',
        function () use ($app)
        {
            echo '/';
        }
    );
    $app->notFound(
        function () use ($app)
        {
            $app->response->setStatusCode(404, 'Not Found');
            $app->response->setContent('404 Page or File Not Found');
            return $app->response;
        }
    );
    $app->after(
        function () use ($app)
        {
            $content = $app->response->getContent();
        }
    );

    $app->handle();
}
catch (\Exception $e)
{
    echo '<pre>';
    print_r($e->getMessage());
    echo "\n\n";
    print_r($e->getTraceAsString());
    echo '</pre>';
}
