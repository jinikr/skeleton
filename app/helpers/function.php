<?php

function pr()
{
    $trace = debug_backtrace()[0];
    echo '<pre xstyle="font-size:9px;font: small monospace;">';
    echo PHP_EOL.'=========================================================================================='.PHP_EOL;
    echo 'file '.$trace['file'].' line '.$trace['line'];
    echo PHP_EOL.'------------------------------------------------------------------------------------------'.PHP_EOL;

    if(1 === func_num_args())
    {
        print_r(func_get_arg(0));
    }
    else
    {
        print_r(func_get_args());
    }

    echo PHP_EOL.'=========================================================================================='.PHP_EOL;
    echo '</pre>';
}

function prx()
{
    $trace = debug_backtrace()[0];
    echo '<pre xstyle="font-size:9px;font: small monospace;">';
    echo PHP_EOL.'=========================================================================================='.PHP_EOL;
    echo 'file '.$trace['file'].' line '.$trace['line'];
    echo PHP_EOL.'------------------------------------------------------------------------------------------'.PHP_EOL;

    if(1 === func_num_args())
    {
        VarExport::dump(func_get_arg(0));
    }
    else
    {
        VarExport::dump(func_get_args());
    }

    echo PHP_EOL.'=========================================================================================='.PHP_EOL;
    echo '</pre>';
}

class VarExport
{
    private static $_objects;
    private static $_output;
    private static $_depth;

    public static function dump($var,$return = false)
    {
        self::$_output='';
        self::$_objects=array();
        self::$_depth=100;
        self::dumpInternal($var,0);
        if($return === true)
        {
            return self::$_output;
        }
        else
        {
            echo self::$_output;
        }
    }

    private static function dumpInternal($var,$level)
    {
        switch(gettype($var))
        {
            case 'array':
                if(self::$_depth<=$level)
                {
                    self::$_output.='array(...)';
                }
                else if(empty($var))
                {
                    self::$_output.='array()';
                }
                else
                {
                    $keys=array_keys($var);
                    $spaces=str_repeat(' ',$level*8);
                    self::$_output.="Array"." (size=".count($keys).")"."\n".$spaces.'(';
                    foreach($keys as $key)
                    {
                        self::$_output.="\n".$spaces."    [$key] => ";
                        self::$_output.=self::dumpInternal($var[$key],$level+1);
                    }
                    self::$_output.="\n".$spaces.')';
                }
                break;
            case 'object':
                if($var instanceof \Closure)
                {
                   // self::$_output.="Closure";
                }
                if(($id=array_search($var,self::$_objects,true))!==false)
                {
                    self::$_output.=get_class($var).' #'.($id+1).'(...)';
                }
                else if(self::$_depth<=$level)
                {
                    self::$_output.=get_class($var).'(...)';
                }
                else
                {
                    $id=array_push(self::$_objects,$var);
                    $className=get_class($var);
                    $members=(array)$var;
                    $keys=array_keys($members);
                    $spaces=str_repeat(' ',$level*8);
                    self::$_output.="$className #$id\n".$spaces.'(';
                    foreach($keys as $key)
                    {
                        $keyDisplay=strtr(trim($key),array("\0"=>':'));
                        self::$_output.="\n".$spaces."    [$keyDisplay] => ";
                        self::$_output.=self::dumpInternal($members[$key],$level+1);
                    }
                    self::$_output.="\n".$spaces.')';
                }
                break;
            default :
                self::$_output.= var_export($var, true);
        }
    }
}