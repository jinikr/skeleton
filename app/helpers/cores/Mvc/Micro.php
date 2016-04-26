<?php

namespace App\Helpers\Cores\Mvc;

class Micro extends \Phalcon\Mvc\Micro
{

    public $prefix;

    private function callHandler($name, $handlers, $args = [])
    {
        if (true === is_callable($handlers))
        {
            $status = call_user_func_array($handlers, $args);
        }
        else if (true === is_string($handlers))
        {
            if (false !== strpos($handlers, '->'))
            {
                $tmp = explode('->', $handlers);
                $class = Store::getInstance()->load($tmp[0]);
                $status = call_user_func_array([$class, $tmp[1]], $args);
            }
            else
            {
                throw new \Exception($name.' '.$handlers.' handler is not callable');
            }
        }
        else
        {
            throw new \Exception($name.' handler is not callable');
        }
        return $status;
    }

    /**
     * Handle the whole request
     *
     * @param string uri
     * @return mixed
     */
    public function handle($uri = null)
    {
        foreach (Store::getInstance()->getRoutes() as $key => $value)
        {
             parent::{$value['method']}($value['prefix'].$value['pattern'], $value['handler']);
        }

        $dependencyInjector = $this->_dependencyInjector;
        if (false === is_object($dependencyInjector))
        {
            throw new \Exception("A dependency injection container is required to access required micro services");
        }

        try
        {
            $returnedValue = null;
            $router = $dependencyInjector->getShared("router");
            $router->handle($uri);
            $matchedRoute = $router->getMatchedRoute();
            $params = $router->getParams();

            if (true === is_object($matchedRoute))
            {
                $handler = $this->_handlers[$matchedRoute->getRouteId()];
                if (!$handler)
                {
                    throw new \Exception("Matched route doesn't have an associated handler");
                }
                $this->_activeHandler = $handler;

                $routeParamHandlers = Store::getInstance()->get('param');
                if (true === is_array($routeParamHandlers))
                {
                    foreach ($routeParamHandlers as $paramHandlers)
                    {
                        if (true === is_array($paramHandlers))
                        {
                            foreach ($paramHandlers as $paramHandler)
                            {
                                if (true === isset($paramHandler[0])
                                    && true === isset($paramHandler[1])
                                    && true === isset($params[$paramHandler[0]]))
                                {
                                    $status = $this->callHandler('param', $paramHandler[1], [$params[$paramHandler[0]]]);
                                    if (false === $status)
                                    {
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                }

                $routeBeforeHandlers = Store::getInstance()->get('before');
                if (true === is_array($routeBeforeHandlers))
                {
                    foreach ($routeBeforeHandlers as $beforeHandlers)
                    {
                        if (true === is_array($beforeHandlers))
                        {
                            foreach ($beforeHandlers as $beforeHandler)
                            {
                                $status = $this->callHandler('before', $beforeHandler);
                                if (false === $status)
                                {
                                    return false;
                                }
                            }
                        }
                    }
                }

                $returnedValue = $this->callHandler('class', $handler, $params);

                $routeAfterHandlers = Store::getInstance()->get('after');
                if (true === is_array($routeAfterHandlers))
                {
                    foreach ($routeAfterHandlers as $afterHandlers)
                    {
                        if (true === is_array($afterHandlers))
                        {
                            foreach ($afterHandlers as $afterHandler)
                            {
                                $status = $this->callHandler('after', $afterHandler);
                                if (false === $status)
                                {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                $returnedValue = $this->callHandler('notFound', $this->_notFoundHandler);
            }

            $this->_returnedValue = $returnedValue;
        }
        catch (\Exception $e)
        {
            if ($this->_errorHandler)
            {
                $returnedValue = $this->callHandler('error', $this->_errorHandler, [$e]);

                if (true === is_object($returnedValue)
                    && !($returnedValue instanceof \Phalcon\Http\ResponseInterface))
                {
                    throw $e;
                }
            }
            else if (false !== $returnedValue)
            {
                throw $e;
            }
        }

        if (true === is_object($returnedValue)
            && $returnedValue instanceof \Phalcon\Http\ResponseInterface)
        {
            $returnedValue->send();
        }

        return $returnedValue;
    }

    public function group($prefix, \Closure $callback)
    {
        $scope = clone $this;
        $scope->prefix .= '/'.trim($prefix, '/');

        $callback = $callback->bindTo($scope);
        $tmp = $callback();

        return $this;
    }

    public function param($key, $methodName)
    {
        Store::getInstance()->set('param', $this->prefix, [$key, $methodName]);
        return $this;
    }

    public function before($methodName)
    {
        Store::getInstance()->set('before', $this->prefix, $methodName);
        return $this;
    }

    public function after($methodName)
    {
        Store::getInstance()->set('after', $this->prefix, $methodName);
        return $this;
    }

    public function map($routePattern, $handler)
    {
        Store::getInstance()->setRoute('map', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function get($routePattern, $handler)
    {
        Store::getInstance()->setRoute('get', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function post($routePattern, $handler)
    {
        Store::getInstance()->setRoute('post', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function put($routePattern, $handler)
    {
        Store::getInstance()->setRoute('put', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function patch($routePattern, $handler)
    {
        Store::getInstance()->setRoute('patch', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function head($routePattern, $handler)
    {
        Store::getInstance()->setRoute('head', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function delete($routePattern, $handler)
    {
        Store::getInstance()->setRoute('delete', $this->prefix, $routePattern, $handler);
        return $this;
    }

    public function options($routePattern, $handler)
    {
        Store::getInstance()->setRoute('options', $this->prefix, $routePattern, $handler);
        return $this;
    }

}

class Store
{
    private static $instance; //The single instance

    private $routes = [];
    private $before = [];
    private $after = [];
    private $param = [];

    private $segments = [];
    private $segmentParts = [];

    private $prefix;
    private $method;
    private $class;

    private function getSegments()
    {
        return \Phalcon\Di::getDefault()->get('request')->getSegments();
    }

    private function getMethod()
    {
        return \Phalcon\Di::getDefault()->get('request')->getMethod();
    }

    public function init()
    {
        $this->segments = $this->getSegments();
        array_unshift($this->segments, '');
        $this->prefix = true === isset($this->segments[1]) ? $this->segments[1] : '';
        $this->method = strtolower($this->getMethod());
        $this->segmentParts = [];
        $tmp = '';
        foreach ($this->segments as $key => $value)
        {
            $tmp .= ($value ? '/'.$value : '');
            $this->segmentParts[] = ($tmp ?: '/');
        }
        return $this;
    }

    public static function getInstance()
    {
        if (!static::$instance)
        {
            static::$instance = (new self())->init();
        }
        return static::$instance;
    }

    public function setRoute($method, $prefix, $routePattern, $handler)
    {
        $prefix = trim($prefix,'/');
        $routePattern = trim($routePattern,'/');

        if ('map' === $method || $method === static::$instance->method)
        {
            if (true === empty($prefix)
                || (false === empty($this->prefix) && 0 === strpos($prefix, $this->prefix)))
            {
                $this->routes[] = [
                    'method' => $method,
                    'prefix' => ($prefix ? '/'.$prefix : ''),
                    'pattern' => ($routePattern ? '/'.$routePattern : ''),
                    'handler' => $handler
                ];
            }
        }
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function set($method, $prefix, $handler)
    {
        $prefix = trim($prefix,'/');
        if (true === in_array('/'.$prefix, $this->segmentParts))
        {
            $this->{$method}[($prefix ? '/'.$prefix : '')][] = $handler;
        }
    }

    public function get($method)
    {
        return $this->{$method};
    }

    public function load($className)
    {
        if (false === isset($this->class[$className]))
        {
            $this->class[$className] = new $className;
        }
        return $this->class[$className];
    }

}