<?php

namespace App\Traits\Model;

trait QueryBuilder
{
    private static $where = <<<SQL
WHERE

SQL;

    private static $and = <<<SQL

AND

SQL;

    private final static function getArrayData($key, array $array = [])
    {
        return array_key_exists($key, $array) ? $array[$key] : [];
    }

    private final static function setBind(array &$bind, $name, $value, $type=null)
    {
        $parameters = self::getArrayData('parameters', $bind);
        $types = self::getArrayData('types', $bind);

        $parameters[$name] = $value;
        if ($type !== null) {
            $types[$name] = $type;
        }
        $bind = [
            'parameters' => $parameters,
            'types' => $types
        ];
    }

    private final static function setQuery($query, array $params)
    {
        $pattern = [];
        $replace = [];
        foreach ($params as $key => $value) {
            array_push($pattern, '/!'.$key.'/');
            array_push($replace, $value);
        }
        return preg_replace($pattern, $replace, $query);
    }
}
