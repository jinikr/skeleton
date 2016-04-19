<?php

namespace App\Middlewares;

use Phalcon\Mvc\Micro as Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class Auth //implements MiddlewareInterface
{

    public static function call($app)
    {
        try {
            //throw new \Exception('error auth');
            return true;
        } catch (\Exception $e) {
            $app->response
                ->setStatusCode(403, 'Forbidden')
                ->setContentType('application/json')
                ->setJsonContent(array(
                    'error'   => TRUE,
                    'status'  => 403,
                    'message' => $e->getMessage()
                ))->send();
            return false;
        }
    }

}