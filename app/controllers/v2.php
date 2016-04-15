<?php

namespace App\Controllers;

class V2 extends \Phalcon\Mvc\Controller
{

    /**
     * @brief index
     * @return http response object
     */
    public function index()
    {
        $response = $this->response;
        $request = $this->request;

        $name = 'index';
        $response->setContent('v2 index');

        return $response;
    }

    /**
     * @brief getInfo
     * @param string $name
     * @param string $ext
     * @return http response object
     */
    public function getInfo($name, $ext)
    {
        $response = $this->response;
        $request = $this->request;

        $users = \App\Models\User::getUserList();
        pr($users);
        $response->setContent('v2 getInfo '.$name.'.'.$ext);

        return $response;
    }

}
