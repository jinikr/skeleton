<?php

return call_user_func(function() {

    $collection = new Phalcon\Mvc\Micro\Collection();

    $collection->setPrefix('/v1');
    $collection->setHandler('\App\Controllers\V1', true);

    $collection->get('/', 'index');
    $collection->get('/info12', 'getInfo');
    $collection->get('/info13', 'getInfo');
    $collection->get('/info14', 'getInfo');
    $collection->get('/info15', 'getInfo');
    $collection->get('/info16', 'getInfo');
    $collection->get('/info17', 'getInfo');
    $collection->get('/info18', 'getInfo');
    $collection->get('/info19', 'getInfo');
    $collection->get('/info/{name:[0-9a-zA-Z\-]{5,10}}', 'getInfo');
    $collection->get('/error', 'error');

    return $collection;

});