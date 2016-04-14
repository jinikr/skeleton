<?php

namespace app\traits;

trait singleton {
    private static $instance; //The single instance

    public static function getInstance()
    {
        if(!static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
}