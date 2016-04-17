<?php

namespace App\Models;

use Peanut\Db\Driver as Db;

class user
{

    public static function getUser($name)
    {
        return Db::name('slave')->get("select * from user where user_name = :user_name", [
            ':user_name' => $name
        ]);
    }

    public static function getUserList()
    {
        return Db::name('slave')->gets("select * from user");
    }

    public static function setUser($name)
    {
        return Db::name('master')->setId("insert into user (user_name) values (:user_name)", [
            ':user_name' => $name
        ]);
    }

    public static function setUserPoint(int $userSeq, int $point)
    {
        return Db::name('master')->setId("insert into point (user_seq, point) values (:user_seq, :point)", [
            ':user_seq' => $userSeq,
            ':point' => $point
        ]);
    }

}