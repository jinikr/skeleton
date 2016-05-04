<?php

$app->group('huga', function() use ($app)
{
    $app->before(function()
    {
        echo 'huga before';
    });
    $app->get(function()
    {
        echo 'huga index page';
    });
    $app->get('add', function()
    {

    });
    $app->get('view/{view_id:[0-9]+}', function()
    {

    });
    $app->get('write', function()
    {

    });
    $app->after(function() {
        echo 'huga after';
    });
});
$app->group('board', function() use ($app)
{
    $app->before(function()
    {
        echo 'board before';
    });
    $app->get(function()
    {
        echo 'board index page';
    });
    $app->group('{board_id:[a-z0-9A-Z]+}', function() use ($app)
    {
        $app->param('board_id', function($boardId)
        {
            $this->board = $boardId;
            echo 'board id : ' .$boardId;
        });
        $app->param('view_id', function($viewId)
        {
            $this->view = $viewId;
            echo 'view id : ' .$viewId;
        });
        $app->get(function($boardId)
        {
            echo 'board index page <b>'.$boardId.'</b>';
        });
        $app->get('add', function($board)
        {
            echo 'add '.($this->board === $board ? $board : false);
        });
        $app->get('view/{view_id:[0-9]+}', function($boardId, $viewId)
        {
            echo '<hr />';
            echo $viewId;
            echo '<hr />';
        });
        $app->get('write', function()
        {

        });
    });
    $app->after(function()
    {
        echo 'board after';
    });
});
$app->get('info', function()
{
    phpinfo();
});
$app->get(function()
{
    echo '/';
});


$app->get(function() {echo '/<br />';});
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


