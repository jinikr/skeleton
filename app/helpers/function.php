<?php

function pr($s)
{
    echo '<pre>';
    print_r($s);
    echo '</pre>';
}

function getParam(int $length)
{
    $params = [];
    $paramsStr = isset($_GET['_url']) ? $_GET['_url'] : '/';
    $strParams = trim($paramsStr, '/');
    if($strParams !== "") {
        $params = explode("/", $strParams);
    }
    return implode('/', array_slice($params, 0, $length));
}