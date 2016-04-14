<?php

namespace app\controllers;

class v2 extends \Phalcon\Mvc\Controller
{

    public function index()
    {
        $response = $this->response;
        $request = $this->request;

        $name = 'index';
        $response->setContent('v2 index');

        return $response;
    }

    public function getInfo($name, $ext)
    {
        $response = $this->response;
        $request = $this->request;

        $users = \app\models\user::getUserList();
        pr($users);
        $response->setContent('v2 getInfo '.$name.'.'.$ext);

        return $response;
    }

}