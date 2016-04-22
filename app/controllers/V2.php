<?php

namespace App\Controllers;

use Peanut\Db\Driver as Db;
use App\Models\User as UserModel;

class V2 extends \Phalcon\Mvc\Controller
{

    public $tmp = 0;
    public function onConstruct()
    {

    }

    public function before()
    {
        echo '<hr />';
        echo 'before';
        echo '<hr />';
    }

    public function checkParam()
    {
        echo '<hr />';
        echo 'before';
        echo '<hr />';
    }

    public function after()
    {
        echo '<hr />';
        echo 'after';
        echo '<hr />';
    }

    public function checkname($name)
    {
        $this->response->setContent('checkname : '. $name. ' ');
    }
    /**
     * index
     *
     * @return $response
     */
    public function index()
    {
        $response = $this->response;
        $request  = $this->request;

        $name = 'index';
        $response->setContent('v2 index');
        //phpinfo();
        return $response;
    }

    /**
     * getInfo
     *
     * @param string $name
     * @param string $ext
     * @return $response
     */
    public function getInfo($name, $ext)
    {
        $response = $this->response;
        $request  = $this->request;
        pr($request->getQuery('a'));
        pr($request->getParam('name'));
        pr($this->tmp);
        UserModel::getUserList();
        $pointSeq = Db::name('master')->transaction(
            function() use  ($response)
            {
                $userSeq = UserModel::setUser('test2'.microtime());
                return $pointSeq = UserModel::setUserPoint($userSeq, 100);
            }
        );
        $response->setContent($response->getContent().'v2 getInfo '.$name.$pointSeq.'.'.$ext);

        return $response;
    }

}
