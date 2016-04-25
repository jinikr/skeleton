<?php

namespace App\Helpers\Cores;

class Bootstrap
{
    private $di;

    public function __construct(\Phalcon\DI\FactoryDefault $di)
    {
        $this->setDi($di);
    }

    public function setDi(\Phalcon\DI\FactoryDefault $di)
    {
        $this->di = $di;
    }

    public function getDI()
    {
        return $this->di;
    }

    private function getConfigFile()
    {
        try
        {
            if (true === is_file(__BASE__.'/app/config/environment.php'))
            {
                $globalConfig = include_once __BASE__.'/app/config/environment.php';
                if (true === is_array($globalConfig)
                    && true === isset($globalConfig['domains'])
                    && true === is_array($globalConfig['domains']))
                {
                    foreach ($globalConfig['domains'] as $environment => $domain)
                    {
                        if (true === in_array(getenv('HTTP_HOST'), $domain)
                            && true === is_file(__BASE__.'/app/config/environment/'.$environment.'.php'))
                        {
                            $environmentConfig = include_once __BASE__.'/app/config/environment/'.$environment.'.php';
                            $globalConfig['environment'] = $environment;
                            break;
                        }
                    }
                    if (true === isset($environmentConfig)
                        && true === is_array($environmentConfig))
                    {
                        $config = array_merge($globalConfig, $environmentConfig);
                    }
                }
            }
            if (false === isset($config) || !$config)
            {
                throw new \Exception(__BASE__.'/app/config/environment.php 을 확인하세요.');
            }
        }
        catch(\Exception $e)
        {
            throw $e;
        }
        return $config;
    }

    private function initConfig(array $config)
    {
        $this->di['config'] = function () use ($config)
        {
            return (new \Phalcon\Config($config))->toArray();
        };
    }

    private function initSession(array $config)
    {
        $this->di['session'] = function () use ($config)
        {
            if(true === isset($config['session']))
            {
                $session = new \Phalcon\Session\Adapter\Files();
                $session->start();
                return $session;
            }
            else
            {
                throw new \Exception('session config를 확인하세요.');
            }
        };
    }

    private function initPeanutDb(array $config)
    {
        if (true === isset($config['databases']))
        {
            \Peanut\Db\Driver::setConnectInfo($config['databases']);
        }
    }

    private function initRoute(\Phalcon\Mvc\Micro $app)
    {
        if(true === is_file(__BASE__.'/app/config/route.php'))
        {
            include_once __BASE__.'/app/config/route.php';
        }
        else
        {
            throw new \Exception(__BASE__.'/app/config/route.php 을 확인하세요.');
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
        $config = $this->getConfigFile();

        // $this->initConfig($config);
        $this->initSession($config);
        $this->initPeanutDb($config);
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
