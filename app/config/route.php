<?php

$app->param('id', '\App\Controllers\V2->checkId');
$app->before('\App\Controllers\V2->before');
$app->after('\App\Controllers\V2->after');

$app->group('v1', function()
{
    $this->param('name', '\App\Controllers\V2->checkName');
    $this->get('info/{name}', '\App\Controllers\V2->getInfo');
    $this->post('info/{name}', '\App\Controllers\V2->postInfo');
    $this->delete('info/{name}', '\App\Controllers\V2->deleteInfo');
    $this->map('all', '\App\Controllers\V2->getInfo');
    $this->after('\App\Controllers\V2->after');
    $this->before('\App\Controllers\V2->before');

    $this->group('new', function()
    {
        $this->after('\App\Controllers\V2->after');
        $this->before('\App\Controllers\V2->before');
        $this->map('', '\App\Controllers\V2\New->getInfo');
        $this->map('all/{name}', '\App\Controllers\V2\New->getInfo');
    });
});
$app->group('v2', function()
{
    $this->param('name', '\App\Controllers\V2->checkName');
    $this->get('', '\App\Controllers\V2->index');

    $this->group('info', function () {
        $this->before('\App\Controllers\V2->before');
        $this->get('{name:[0-9a-zA-Z\-]{5,10}}.{ext}', '\App\Controllers\V2->getInfo');
    });
    $this->post('info2/{name}/', '\App\Controllers\V2->postInfo');
    $this->delete('info2/{name}', '\App\Controllers\V2->deleteInfo');
    $this->map('all2', '\App\Controllers\V2->getInfo');

    $this->group('new3', function()
    {
        $this->map('', '\App\Controllers\V2\New->getInfo');
        $this->map('all2/{name}', '\App\Controllers\V2\New->getInfo');
        $this->before('\App\Controllers\V2->before');

        $this->group('new211', function()
        {
            $this->map('', '\App\Controllers\V2\New->getInfo');
            $this->map('all2/{name}', '\App\Controllers\V2\New->getInfo');
            $this->before('\App\Controllers\V2->before');
        });
    });
});
$app->get   ('{name}', '\App\Controllers\V2->index');

$app->notFound(
    function () use ($app)
    {
        $app->response->setStatusCode(404, 'Not Found');
        $app->response->setContent('404 Page or File Not Found');
        return $app->response;
    }
);
$app->error(
    function ($e) use ($app)
    {
        pr($e);
    }
);