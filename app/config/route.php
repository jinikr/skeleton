<?php

/*
$app->get('/test', function() {echo '/test<br />';});
$app->get('/test/{name}', function($name) {echo '/test/info : '.$name.'<br />';});
$app->param('/test/{name}', 'name', '\App\Controllers\V2->checkId');
$app->after('/test/{name}', function() {echo '!!!after<br />';});
$app->after('/test/{name:[0-9]+}', function() {echo 'iiiafter<br />';});
*/
$app->pattern('test')
    ->get(function(){echo 'test';});

$app->pattern('v2/info/{name}.{ext}')
    ->get('\App\Controllers\V2->getInfo');

$app->group('test', function() {
    $this->pattern('test')
        ->methods(['get'])
        ->any(function() {echo 'any';});
});
$app->methods(['post', 'get'])->after(function()
{
    echo '<b>after</b>';
});
/*
$app->group('test', function() {
    $this->get('max', function() {echo 'real name : seungmin<br />';});
    $this->group('test', function() {
        $this->get('max', function() {echo 'real name : seungmin<br />';});
    });
});
*/
$app->get('/', function() {echo '/<br />';});
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


