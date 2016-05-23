<?php
namespace App;

class Bootstrap
{
    /**
     * @var mixed
     */
    private $di;
    /**
     * @var string
     */
    private $stageName = '';
    /**
     * @var mixed
     */
    private $debug = false;

    /**
     * @param \Phalcon\DI\FactoryDefault $di
     */
    private function setDi(\Phalcon\DI\FactoryDefault $di)
    {
        $this->di = $di;
    }

    /**
     * @return mixed
     */
    private function getDI()
    {
        return $this->di;
    }

    /**
     * @return mixed
     */
    private function getHttpHost()
    {
        return $this->getDi()->get('request')->getHttpHost();
    }

    private function initRequest()
    {
        $this->di['request'] = function () {
            return new \Peanut\Phalcon\Http\Request();
        };
    }

    /**
     * @param  array   $config
     * @return mixed
     */
    private function initConfig(array $config)
    {
        $this->di['config'] = function () use ($config) {
            return $config; //(new \Phalcon\Config($config))->toArray();
        };
    }

    /**
     * @param  array   $config
     * @return mixed
     */
    private function initSession(array $config)
    {
        $this->di['session'] = function () use ($config) {
            if (true === isset($config['session'])) {
                $session = new \Phalcon\Session\Adapter\Files();
                $session->start();

                return $session;
            } else {
                throw new \Exception('session config를 확인하세요.');
            }
        };
    }

    private function initEventsManager()
    {
        $this->di['eventsManager'] = function () {
            return new \Phalcon\Events\Manager();
        };
    }

    private function initDbProfiler()
    {
        $this->di['profiler'] = function () {
            return new \Phalcon\Db\Profiler();
        };
    }

    /**
     * @param  array  $config
     * @return null
     */
    private function dbProfiler(array $config)
    {
        if ('local' !== $this->stageName) {
            return;
        }

        $this->initDbProfiler();
        $eventsManager = $this->di['eventsManager'];
        $eventsManager->attach('db', function ($event, $connection) {
            $profiler = $this->di['profiler'];

            if ($event->getType() == 'beforeQuery') {
                $profiler->startProfile(
                    $connection->getSQLStatement(),
                    $connection->getSQLVariables(),
                    $connection->getSQLBindTypes()
                );
            }

            if ($event->getType() == 'afterQuery') {
                $profiler->stopProfile();
            }
        });

        if (true === isset($config['stages'][$this->stageName]['database']['server'])) {
            foreach ($config['stages'][$this->stageName]['database']['server'] as $name => $dsn) {
                \Peanut\Phalcon\Pdo\Mysql::name($name)->setEventsManager($eventsManager);
            }
        }
    }

    /**
     * @param  $configFile
     * @return mixed
     */
    private function getConfigFile($configFile)
    {
        try {
            $config = yaml_parse_file($configFile);

            if (false === is_array($config)) {
                throw new \Exception($configFile.' can\'t be loaded.');
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $config;
    }

    /**
     * @param \Phalcon\DI\FactoryDefault $di
     */
    public function __construct(\Phalcon\DI\FactoryDefault $di)
    {
        $this->setDi($di);
    }

    /**
     * @param  \Phalcon\Mvc\Micro $app
     * @return mixed
     */
    public function __invoke(\Phalcon\Mvc\Micro $app)
    {
        $config = $this->getConfigFile(__BASE__.'/env.yml');

        return $this->run($app, $config);
    }

    /**
     * @param $config
     */
    private function init($config)
    {
        $this->initRequest();
        $this->initConfig($config);
        $this->initEnvironment($config);
        $this->initDebug($config);
        $this->initRouter($config);
        $this->initSession($config);
        $this->initDatabase($config);
    }

    /**
     * @param  \Phalcon\Mvc\Micro $app
     * @param  array              $config
     * @return mixed
     */
    public function run(\Phalcon\Mvc\Micro $app, array $config)
    {
        $this->init($config);
        $app->setDI($this->di);
        $app->notFound(
            function () use ($app) {
                $app->response->setStatusCode(404, 'Not Found');
                $app->response->setContent('404 Page or File Not Found');

                return $app->response;
            }
        );
        $app->error(
            function ($e) use ($app) {
                pr($e);
            }
        );

        $app->get('/', function () {
            echo '/';
        });
        $app->get('/info', function () {
            phpinfo();
        });

        return $app;
    }

    /**
     * @return mixed
     */
    public function getDeployer()
    {
        $config         = $this->di['config'];
        $stageName      = $this->stageName;
        $deployerConfig = [];

        foreach ($config['stages'] as $stageName => $stage) {
            $serverList = $stage['deploy']['server'];
            unset($stage['deploy']['server']);

            foreach ($serverList as $server) {
                $deployerConfig[] = array_merge(['server' => $server, 'stage' => $stageName], $stage['deploy']);
            }
        }

        return $deployerConfig;
    }

    /**
     * @param  $config
     * @return mixed
     */
    private function initDatabase($config)
    {
        $stageName = $this->stageName;
        $debug     = $this->debug;

        $stage                 = $config['stages'][$stageName];
        $this->di['databases'] = function () use ($stage, $debug) {
            if (true === isset($stage['database']) && true === is_array($stage['database'])) {
                $databaseConfig = [];
                $serverList     = $stage['database']['server'];
                unset($stage['database']['server']);

                foreach ($serverList as $server => $dsn) {
                    $databaseConfig[$server] = array_merge(['dsn' => $dsn], $stage['database']);
                }

                return $databaseConfig;
            } else {
                throw new \Exception('databases config를 확인하세요.');
            }
        };

        if (true === $debug) {
            $this->dbProfiler($config);
        }

        $this->di['db'] = function () {
            return \Peanut\Phalcon\Pdo\Mysql::name('master');
        };
    }

    /**
     * @param $config
     */
    private function initEnvironment($config)
    {
        $host = $this->getHttpHost();
        $env  = '';

        foreach ($config['stages'] as $stageName => $stage) {
            foreach ($stage['vhosts'] as $vhost) {
                if (true === in_array($host, array_merge([$vhost['server_name']], $vhost['server_alias']))) {
                    $env = $stageName;
                    break;
                }
            }
        }

        if (!$env) {
            throw new \Exception('stage를 확인할수 없습니다.');
        }

        $this->stageName = $env;
    }

    /**
     * @param $config
     */
    private function initDebug($config)
    {
        if (true === isset($config['stages'][$this->stageName]['app']['debug'])) {
            $this->debug = $config['stages'][$this->stageName]['app']['debug'];
        }

        if ($this->debug) {
            include_once __BASE__.'/app/helpers/debug.php';
        }
    }

    /**
     * @param  $config
     * @return mixed
     */
    private function initRouter($config)
    {
        $this->di['router'] = function () use ($config) {
            $router = new \Peanut\Phalcon\Mvc\Router\Rules\Hash();
            $router->group($config['routes']);

            return $router;
        };
    }
}
