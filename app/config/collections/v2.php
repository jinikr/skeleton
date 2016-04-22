<?php

return call_user_func(function() {

    $collection = new \App\Helpers\Cores\Mvc\Micro\Collection();

    $collection->setPrefix('/v2');
    $collection->setHandler('\App\Controllers\V2', true);

    $collection->get('/', 'index');
    $collection->get('/info12', '\App\Controllers\V2\getInfo');
    $collection->get('/info13', 'getInfo');
    $collection->get('/info14', 'getInfo');
    $collection->get('/info15/', 'getInfo');
    $collection->get('/info16', 'getInfo');
    $collection->get('/info17', 'getInfo');
    $collection->get('/info18', 'getInfo');
    $collection->get('/info19', 'getInfo');
    $collection->get('/info/{name:[0-9a-zA-Z\-]{5,10}}.{ext}', 'getInfo');
    $collection->get('/error', 'error');

    $collection->param('name', 'checkParam');

    $collection->param('ext', function ($name) {
        $this->a = 'aaa';
        echo 'ext['.$name.']';
    });
    $collection->before('before');
    $collection->after('after');
    $collection->after(function() {
        echo ';;;;;;;;;;;;;;<br />';
        pr($this->a);
    });

    return $collection;

});