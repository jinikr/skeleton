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

        $response->setContent('v1 getInfo '.$name);

        return $response;
    }

}