<?php

namespace App\Helpers\Cores;

class Bootstrap
{
    private $di;

    public function __construct(\Phalcon\DI\FactoryDefault $di)
    {
        $this->setDi($di);
        $this->initRequest(); // request는 config에서 사용하므로 생성자에서 초기화
    }

    private function initRequest()
    {
        $this->di->set('request', function ()
        {
            return new \App\Helpers\Cores\Http\Request();
        });
    }

    public function setDi(\Phalcon\DI\FactoryDefault $di)
    {
        $this->di = $di;
    }

    public function getDI()
    {
        return $this->di;
    }

    private function getHttpHost()
    {
        return $this->getDi()->get('request')->getHttpHost();
    }

    private function getConfigFile()
    {
        try
        {
            $globalConfigFile = __BASE__.'/app/config/environment.php';
            $environmentConfigFolder = __BASE__.'/app/config/environment';

            if (true === is_file($globalConfigFile))
            {
                $globalConfig = include_once $globalConfigFile;
                if (true === is_array($globalConfig)
                    && true === isset($globalConfig['domains'])
                    && true === is_array($globalConfig['domains']))
                {
                    foreach ($globalConfig['domains'] as $environment => $domain)
                    {
                        if (true === in_array($this->getHttpHost(), $domain))
                        {
                            $globalConfig['environment'] = $environment;
                            break;
                        }
                    }
                    if (false === isset($globalConfig['environment']))
                    {
                        throw new \Exception($globalConfigFile.'을 확인하세요. $_SERVER[\'HTTP_HOST\']에 해당하는 domains 설정이 있는지 확인하세요.');
                    }
                    $environmentConfigFile = $environmentConfigFolder.'/'.$globalConfig['environment'].'.php';
                    if(true === is_file($environmentConfigFile))
                    {
                        $environmentConfig = include_once $environmentConfigFile;

                        if (true === isset($environmentConfig)
                            && true === is_array($environmentConfig))
                        {
                            $config = array_merge($globalConfig, $environmentConfig);
                        }
                        else
                        {
                            throw new \Exception($globalConfigFile.'을 확인하세요. $_SERVER[\'HTTP_HOST\']에 해당하는 domains 설정이 있는지 확인하세요.');
                        }
                    }
                    else
                    {
                        throw new \Exception('Configuration file '.$environmentConfigFile.' can\'t be loaded.');
                    }
                }
                else
                {
                    throw new \Exception($globalConfigFile.'을 확인하세요. domains 설정이 잘못 되었습니다.');
                }
            }
            else
            {
                throw new \Exception('Configuration file '.$globalConfigFile.' can\'t be loaded.');
            }
            if (false === isset($config) || !$config)
            {
                throw new \Exception($globalConfigFile.'을 확인하세요.');
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
            return $config;//(new \Phalcon\Config($config))->toArray();
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

    public function run(\Phalcon\Mvc\Micro $app)
    {
        $config = $this->getConfigFile();

        // $this->initConfig($config);
        $this->initSession($config);
        $this->initPeanutDb($config);
        $this->initRoute($app);

        $app->setDI($this->di);
        return $app;
    }

    public function __invoke(\Phalcon\Mvc\Micro $app)
    {
        return $this->run($app);
    }

}
