<?php

try {
    $app = include_once __DIR__.'/../app/helpers/bootstrap.php';

    $app->before(
        function () use ($app) {
            return App\Middlewares\Auth::call($app);
        }
    );
    $app->get(
        '/',
        function () use ($app) {
            echo '/';
        }
    );
    $app->notFound(
        function () use ($app) {
            $app->response->setStatusCode(404, 'Not Found');
            $app->response->setContent('404 Page or File Not Found');
            return $app->response;
        }
    );
    $app->after(
        function () use ($app) {
            $content = $app->response->getContent();
        }
    );

    $app->handle();
} catch (\Exception $e) {
    pr($e->getMessage());
    pr($e->getTraceAsString());
}//end try
