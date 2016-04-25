<?php

namespace App\Controllers;

use Peanut\Db\Driver as Db;
use App\Models\User as UserModel;

class V2 extends \Phalcon\Mvc\Controller
{
    public $a = 0;
    public $tmp = 0;
    public function onConstruct()
    {

    }

    public function before()
    {
        $this->a++;
        echo '<hr />';
        echo 'before';
        echo '<hr />';
    }

    public function checkParam()
    {
        $this->a++;

        echo '<hr />';
        echo 'checkParam';
        echo '<hr />';
    }

    public function checkId()
    {
        $this->a++;

        echo '<hr />';
        echo 'V2 checkId';
        echo '<hr />';
    }

    public function after()
    {
        $this->a++;

        echo '<hr />';
        echo 'after';
        echo '<hr />';
    }

    public function checkname($name)
    {
        $this->a++;

        echo '<hr />';
        echo 'checkname';
        echo '<hr />';
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
        phpinfo();
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
        pr('aaaaaaaaaaa : '.$this->a);
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
