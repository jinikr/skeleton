<?php

namespace App\Controllers;

class V1 extends \Phalcon\Mvc\Controller
{

    public function index()
    {
        $response = $this->response;
        $request = $this->request;

        $name = 'index';
        $response->setContent('v1 index');

        return $response;
    }

    public function getInfo($name)
    {
        $response = $this->response;
        $request = $this->request;

        $response->setContent('v1 getInfo '.$name);

        return $response;
    }

}