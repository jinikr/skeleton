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

        $app->param('id', '\App\Controllers\V2->checkId');
        $app->before('\App\Controllers\V2->before');
        $app->after('\App\Controllers\V2->after');

        $app->group('v1', function()
        {
            $this->param('name', '\App\Controllers\V2->checkName');
            $this->get('info/{name}', '\App\Controllers\V2->getInfo');
            $this->post('info/{name}', '\App\Controllers\V2->postInfo');
            $this->delete('info/{name}', '\App\Controllers\V2->deleteInfo');
            $this->map('all', '\App\Controllers\V2->getInfo');
            $this->after('\App\Controllers\V2->after');
            $this->before('\App\Controllers\V2->before');

            $this->group('new', function()
            {
                $this->after('\App\Controllers\V2->after');
                $this->before('\App\Controllers\V2->before');

                $this->map('', '\App\Controllers\V2\New->getInfo');
                $this->map('all/{name}', '\App\Controllers\V2\New->getInfo');
            });
        });
        $app->group('v2', function()
        {
            $this->param('name', '\App\Controllers\V2->checkName');
            $this->get('', '\App\Controllers\V2->index');

            $this->group('info', function () {
                $this->before('\App\Controllers\V2->before');
                $this->get('{name:[0-9a-zA-Z\-]{5,10}}.{ext}', '\App\Controllers\V2->getInfo');
            });
            $this->post('info2/{name}/', '\App\Controllers\V2->postInfo');
            $this->delete('info2/{name}', '\App\Controllers\V2->deleteInfo');
            $this->map('all2', '\App\Controllers\V2->getInfo');

            $this->group('new3', function()
            {
                $this->map('', '\App\Controllers\V2\New->getInfo');
                $this->map('all2/{name}', '\App\Controllers\V2\New->getInfo');
                $this->before('\App\Controllers\V2->before');

                $this->group('new211', function()
                {
                    $this->map('', '\App\Controllers\V2\New->getInfo');
                    $this->map('all2/{name}', '\App\Controllers\V2\New->getInfo');
                    $this->before('\App\Controllers\V2->before');
                });
            });
        });
        $app->get   ('{name}', '\App\Controllers\V2->index');

        $app->notFound(
            function () use ($app)
            {
                $app->response->setStatusCode(404, 'Not Found');
                $app->response->setContent('404 Page or File Not Found');
                return $app->response;
            }
        );
        $app->error(
            function ($e) use ($app)
            {
                pr($e);
            }
        );
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
