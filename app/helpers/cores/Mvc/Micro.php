<?php

namespace App\Helpers\Cores\Mvc;

class Micro extends \Phalcon\Mvc\Micro
{

    public $__before;
    public $__after;
    public $__param;
    public $prefix;

    /**
     * Handle the whole request
     *
     * @param string uri
     * @return mixed
     */
    public function handle($uri = null)
    {

        foreach(Store::getInstance()->getRoutes() as $key => $value)
        {
             parent::{$value['method']}($value['prefix'].$value['pattern'], $value['handler']);
        }

        $dependencyInjector = $this->_dependencyInjector;
        if (false === is_object($dependencyInjector))
        {
            throw new \Exception("A dependency injection container is required to access required micro services");
        }

        try {

            $returnedValue = null;
            $router = $dependencyInjector->getShared("router");
            $router->handle($uri);
            $matchedRoute = $router->getMatchedRoute();

            if (true === is_object($matchedRoute))
            {

                $handler = $this->_handlers[$matchedRoute->getRouteId()];

                if (!$handler)
                {
                    throw new \Exception("Matched route doesn't have an associated handler");
                }

                /**
                 * Updating active handler
                 */
                $this->_activeHandler = $handler;
                $params = $router->getParams();

                $middleHandler = $handler;


                $paramHandlers = Store::getInstance()->get('param');

                if (true === is_array($paramHandlers))
                {
                    foreach($paramHandlers as $key => $param)
                    {
                        if(true === isset($params[$key]))
                        {
                            $middleHandler[1] = $param;
                            if(true === is_callable($param))
                            {
                                $status = $param($params[$key]);
                            }
                            else if(true === is_string($param) && false !== strpos($param, '->'))
                            {
                                $tmp = explode('->', $param);
                                $class = new $tmp[0];
                                $status = call_user_func_array([$class, $tmp[1]]);
                            }
                            else
                            {
                                $status = call_user_func_array($param, [$params[$key]]);
                            }
                            if(false === $status)
                            {
                                return false;
                            }

                        }
                    }
                }

                $beforeHandlers = Store::getInstance()->get('before');
                if (true === is_array($beforeHandlers))
                {
                    foreach($beforeHandlers as $before)
                    {
                        if(true === is_callable($before))
                        {
                            $status = $before();
                        }
                        else if(true === is_string($before) && false !== strpos($before, '->'))
                        {
                            $tmp = explode('->', $before);
                            $class = new $tmp[0];
                            $status = call_user_func_array([$class, $tmp[1]], []);
                        }
                        else
                        {
                            $status = call_user_func_array($before, []);
                        }
                        if(false === $status)
                        {
                            return false;
                        }
                    }
                }

                if(true === is_callable($handler))
                {
                    $returnedValue = $handler();
                }
                else if(true === is_string($handler) && false !== strpos($handler, '->'))
                {
                    $tmp = explode('->', $handler);
                    $class = new $tmp[0];
                    $returnedValue = call_user_func_array([$class, $tmp[1]], $params);
                }
                else
                {
                    $returnedValue = call_user_func_array($handler, $params);
                }

                $afterHandlers = Store::getInstance()->get('after');
                if (true === is_array($afterHandlers))
                {
                    foreach($afterHandlers as $after)
                    {
                        if(true === is_callable($after))
                        {
                            $status = $after();
                        }
                        else if(true === is_string($after) && false !== strpos($after, '->'))
                        {
                            $tmp = explode('->', $after);
                            $class = new $tmp[0];
                            $status = call_user_func_array([$class, $tmp[1]], []);
                        }
                        else {
                            $status = call_user_func_array($after, []);
                        }
                        if(false === $status)
                        {
                            return false;
                        }
                    }
                }

            }
            else
            {

                /**
                 * Check if a notfoundhandler is defined and it's callable
                 */
                $notFoundHandler = $this->_notFoundHandler;
                if (false === is_callable($notFoundHandler))
                {
                    throw new \Exception("Not-Found handler is not callable or is not defined");
                }

                /**
                 * Call the Not-Found handler
                 */
                $returnedValue = call_user_func($notFoundHandler);
            }

            $this->_returnedValue = $returnedValue;
        }
        catch (\Exception $e)
        {

            /**
             * Check if an errorhandler is defined and it's callable
             */
            $errorHandler = $this->_errorHandler;
            if ($errorHandler)
            {

                if (false === is_callable($errorHandler))
                {
                    throw new \Exception("Error handler is not callable");
                }

                /**
                 * Call the Error handler
                 */
                $returnedValue = call_user_func_array($errorHandler, [$e]);
                if (true === is_object($returnedValue))
                {
                    if (!($returnedValue instanceof \Phalcon\Http\ResponseInterface))
                    {
                        throw $e;
                    }
                } else {
                    if (false !== $returnedValue)
                    {
                        throw $e;
                    }
                }

            } else {
                if (false !== $returnedValue) {
                    throw $e;
                }
            }
        }

        /**
         * Check if the returned object is already a response
         */
        if (true === is_object($returnedValue))
        {
            if ($returnedValue instanceof \Phalcon\Http\ResponseInterface)
            {
                /**
                 * Automatically send the response
                 */
                $returnedValue->send();
            }
        }

        return $returnedValue;
    }

    public function param($key, $methodName)
    {
        Store::getInstance()->set('param', $this->prefix, $methodName);
    }

    public function before($methodName)
    {
        Store::getInstance()->set('before', $this->prefix, $methodName);
    }

    public function after($methodName)
    {
        Store::getInstance()->set('after', $this->prefix, $methodName);
    }

    public function group($prefix, \Closure $callback)
    {

        $scope = clone $this;
        $scope->prefix .= '/'.trim($prefix, '/');

        $callback = $callback->bindTo($scope);
        $tmp = $callback();

        return $this;
    }

    public function map($routePattern, $handler)
    {
        Store::getInstance()->setRoute('map', $this->prefix, $routePattern, $handler);
    }

    public function get($routePattern, $handler)
    {
        Store::getInstance()->setRoute('get', $this->prefix, $routePattern, $handler);
    }

    public function post($routePattern, $handler)
    {
        Store::getInstance()->setRoute('post', $this->prefix, $routePattern, $handler);
    }

    public function put($routePattern, $handler)
    {
        Store::getInstance()->setRoute('put', $this->prefix, $routePattern, $handler);
    }

    public function patch($routePattern, $handler)
    {
        Store::getInstance()->setRoute('patch', $this->prefix, $routePattern, $handler);
    }

    public function head($routePattern, $handler)
    {
        Store::getInstance()->setRoute('head', $this->prefix, $routePattern, $handler);
    }

    public function delete($routePattern, $handler)
    {
        Store::getInstance()->setRoute('delete', $this->prefix, $routePattern, $handler);
    }

    public function options($routePattern, $handler)
    {
        Store::getInstance()->setRoute('options', $this->prefix, $routePattern, $handler);
    }
}

class Store
{
    private static $instance; //The single instance
    public $routes=[];
    public $before=[];
    public $after=[];
    public $param=[];
    public $prefix;
    public $segments;

    public static function getInstance()
    {
        if(!static::$instance)
        {
            static::$instance = new self();
            static::$instance->segments = explode('/', $_GET['_url']);
            static::$instance->prefix = static::$instance->segments[1];

            static::$instance->seg = [];
            $tmp = '';
            foreach(static::$instance->segments as $key => $value)
            {
                $tmp .= ($value ? '/'.$value : '');
                static::$instance->seg[] = ($tmp ?: '/');
            }
            array_pop(static::$instance->seg);
        }
        return static::$instance;
    }

    public function setRoute($method, $prefix, $routePattern, $handler)
    {
        $prefix = trim($prefix,'/');
        $routePattern = trim($routePattern,'/');

        if(true === empty($prefix) || 0 === strpos($prefix, $this->prefix))
        {
            $this->routes[] = [
                'method' => $method,
                'prefix' => ($prefix ? '/'.$prefix : ''),
                'pattern' => ($routePattern ? '/'.$routePattern : ''),
                'handler' => $handler
            ];
        }
    }

    public function set($method, $prefix, $handler)
    {
        $prefix = trim($prefix,'/');

        if(true === in_array('/'.$prefix, $this->seg))
        {
            $this->{$method}[($prefix ? '/'.$prefix : '')] = $handler;
        }
    }

    public function get($method)
    {
        return $this->{$method};
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}