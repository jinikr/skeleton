<?php

namespace App\Controllers;

use Peanut\Db\Driver as Db;
use App\Models\User as UserModel;

class V2 extends \Phalcon\Mvc\Controller
{

    /**
     * @brief index
     * @return http response object
     */
    public function index()
    {
        $response = $this->response;
        $request  = $this->request;

        $name = 'index';
        $response->setContent('v2 index');
        phpinfo();
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
        $request  = $this->request;

        UserModel::getUserList();
        $pointSeq = Db::name('master')->transaction(
            function() use  ($response)
            {
                $userSeq = UserModel::setUser('test2'.time());
                return $pointSeq = UserModel::setUserPoint($userSeq, 100);
            }
        );
        $response->setContent('v2 getInfo '.$name.$pointSeq.'.'.$ext);

        return $response;
    }

}
