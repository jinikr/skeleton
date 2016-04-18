<?php

{
    require_once __BASE__."/vendor/autoload.php";
    //require_once __BASE__.'/app/helpers/debug.php';
    require_once __BASE__.'/app/helpers/function.php';
}

class Bootstrap
{
    private $di;
    private $collection;
    private $config = [];

    public function __construct($di)
    {
        $this->di = $di;
    }

    protected function initConfig()
    {
        if(is_file(__BASE__.'/app/config/environment.php'))
        {
            $environments = require_once __BASE__.'/app/config/environment.php';
            if(true === is_array($environments))
            {
                foreach ($environments as $environment => $domain)
                {
                    if (true === in_array(getenv('HTTP_HOST'), $domain)
                        && is_file(__BASE__.'/app/config/environment/'.$environment.'.php'))
                    {
                        $this->config = require_once __BASE__.'/app/config/environment/'.$environment.'.php';
                        break;
                    }
                }
            }
        }

        if(!$this->config)
        {
            throw new \Exception(__BASE__.'/app/config/environment.php 을 확인하세요.');
        }
    }

    protected function initSession()
    {
        $this->di['session'] = function () {
            $session = new Phalcon\Session\Adapter\Files();
            $session->start();
            return $session;
        };
    }

    protected function initCollection()
    {
        function getParam(int $length)
        {
            $params = [];
            $paramsStr = isset($_GET['_url']) ? $_GET['_url'] : '/';
            $strParams = trim($paramsStr, '/');
            if($strParams !== "")
            {
                $params = explode("/", $strParams);
            }
            return implode('/', array_slice($params, 0, $length));
        }

        if (($prefix = getParam(1))
            && is_file(__BASE__.'/app/collections/'.$prefix.'.php'))
        {
            $this->collection = require_once __BASE__.'/app/collections/'.$prefix.'.php';
        }
    }

    protected function initDatabase()
    {
        if(true === isset($this->config['databases']))
        {
            Peanut\Db\Driver::setConnectInfo($this->config['databases']);
        }
    }

    public function run()
    {
        $this->initConfig();
        $this->initSession();
        $this->initCollection();
        $this->initDatabase();

        $app = new Phalcon\Mvc\Micro();

        $app->setDI($this->di);
        if($this->collection)
        {
            $app->mount($this->collection);
        }

        return $app;
    }
}

$bootstrap = new Bootstrap(new \Phalcon\DI\FactoryDefault);
return $app = $bootstrap->run();