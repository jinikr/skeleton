<?php

namespace App\Helpers\Cores;

class Bootstrap
{
    private $di;
    private $collection;
    private $config = [];
    private $environment;

    public function __construct(\Phalcon\DI\FactoryDefault $di)
    {
        $this->di = $di;
    }

    private function initConfig()
    {
        try
        {
            if (true === is_file(__BASE__.'/app/config/environment.php'))
            {
                $environmentsConfig = include_once __BASE__.'/app/config/environment.php';
                if (true === is_array($environmentsConfig)
                    && true === isset($environmentsConfig['domains'])
                    && true === is_array($environmentsConfig['domains']))
                {
                    foreach ($environmentsConfig['domains'] as $environment => $domain)
                    {
                        if (true === in_array(getenv('HTTP_HOST'), $domain)
                            && is_file(__BASE__.'/app/config/environment/'.$environment.'.php'))
                        {
                            $environmentConfig = include_once __BASE__.'/app/config/environment/'.$environment.'.php';
                            $this->environment = $environment;
                            break;
                        }
                    }
                    if (true === isset($environmentConfig)
                        && true === is_array($environmentConfig))
                    {
                        $this->config = array_merge($environmentsConfig, $environmentConfig);
                    }
                }
            }
            if (!$this->config)
            {
                throw new \Exception(__BASE__.'/app/config/environment.php 을 확인하세요.');
            }
        }
        catch(\Exception $e)
        {
            throw new \Exception($e);
        }
    }

    private function initSession()
    {
        $this->di['session'] = function ()
        {
            $session = new \Phalcon\Session\Adapter\Files();
            $session->start();
            return $session;
        };
    }

    private function initCollection()
    {
        function getParam(int $length)
        {
            $params = [];
            $paramsStr = isset($_GET['_url']) ? $_GET['_url'] : '/';
            $strParams = trim($paramsStr, '/');
            if ($strParams !== "")
            {
                $params = explode("/", $strParams);
            }
            return implode('/', array_slice($params, 0, $length));
        }

        if (($prefix = getParam(1))
            && is_file(__BASE__.'/app/config/collections/'.$prefix.'.php'))
        {
            $this->collection = include_once __BASE__.'/app/config/collections/'.$prefix.'.php';
        }
    }

    private function initRoute(\Phalcon\Mvc\Micro $app)
    {
        include_once __BASE__.'/app/config/route.php';
    }

    private function initDatabase()
    {
        if (true === isset($this->config['databases']))
        {
            \Peanut\Db\Driver::setConnectInfo($this->config['databases']);
        }
    }

    private function initRequest()
    {
        $this->di['request'] = function ()
        {
            return new \App\Helpers\Cores\Http\Request();
        };
    }

    public function run(\Phalcon\Mvc\Micro $app)
    {
        $this->initConfig();
        $this->initSession();
        //$this->initCollection();
        $this->initDatabase();
        $this->initRequest();
        $this->initRoute($app);

        $app->setDI($this->di);

        return $app;
    }

    public function __invoke(\Phalcon\Mvc\Micro $app)
    {
        return $this->run($app);
    }

}
