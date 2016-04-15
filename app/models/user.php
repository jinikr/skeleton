<?php

namespace App\Models;

use Peanut\Db\Driver as db;

class user
{

    public static function getUser($name)
    {
        return db::conn('slave')->get("select * from user where name = :name", [':name' => $name]);
    }

    public static function getUserList()
    {
        return db::conn('slave')->gets("show databases");
    }

    public static function putUser($name, $age)
    {
        return db::conn('master')->put("insert into user (name, age) values (:name, :age", []);
    }

}