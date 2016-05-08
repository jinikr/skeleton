<?php

namespace App\Controllers;

class V1 extends \Phalcon\Mvc\Controller
{
    public $a;

    public function index()
    {
        $response = $this->response;
        $request = $this->request;

        echo $name = 'index';
        $response->setContent('v1 index');

        return $response;
    }

    public function checkName()
    {
        $this->a++;

        echo '<hr />';
        echo 'V1 checkName';
        echo '<hr />';
    }

    public function getNameId()
    {
        $this->a++;

        echo '<hr />';
        echo 'V1 getNameId';
        echo '<hr />';
    }

    public function getInfo($name)
    {
        $response = $this->response;
        $request = $this->request;
        $response->setContent($response->getContent().' getInfo');

        return $response;
    }

    public function getNumber($huga, $name)
    {
        $response = $this->response;
        $request = $this->request;
        $response->setContent($response->getContent().' getNumber');

        return $response;
    }

    public function checkId()
    {
        $response = $this->response;
        $request = $this->request;
        $response->setContent($response->getContent().' checkId');

        return $response;
    }

    public function checkId2()
    {
        $response = $this->response;
        $request = $this->request;
        $response->setContent($response->getContent().' checkId2');

        return $response;
    }
    public function before()
    {
        $response = $this->response;
        $request = $this->request;
        $response->setContent($response->getContent().' before');

        return $response;
    }
    public function after()
    {
        $response = $this->response;
        $request = $this->request;
        $response->setContent($response->getContent().' after');

        return $response;
    }

}