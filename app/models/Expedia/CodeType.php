<?php
namespace App\Models\Expedia;

use Peanut\Phalcon\Pdo\Mysql as Db;
use Phalcon\Db\Column;

class CodeType
{
    use \App\Traits\Model\QueryBuilder;
    use \App\Models\Expedia\CodeTypeQuery;

    private static function setListCondition(array &$bind, $parentId)
    {
        $condition = "";
        $bind = [];
        if ($parentId !== false) {
            $condition .= self::$and;
            $condition .= self::$whereParentId;
            self::setBind($bind, 'parent_id', $parentId);
        }
        return $condition;
    }

    public static function getTotalCount($parentId=false)
    {
        $bind = [];
        $condition = self::setListCondition($bind, $parentId);
        return Db::name('slave')->get1(
            self::setQuery(
                self::$totalCount, [
                    'condition' => $condition
                ]
            ),
            $bind['parameters'],
            $bind['types']
        );
    }

    public static function getList($offset=0, $limit=10, $parentId=false)
    {
        $bind = [];
        $condition = self::setListCondition($bind, $parentId);

        self::setBind($bind, 'offset', $offset, Column::BIND_PARAM_INT);
        self::setBind($bind, 'limit', $limit, Column::BIND_PARAM_INT);

        $result = Db::name('slave')->gets(
            self::setQuery(
                self::$list, [
                    'condition' => $condition
                ]
            ),
            $bind['parameters'],
            $bind['types']
        );
        return $result;
    }

    public static function get($id, $db='slave')
    {
        $bind = [];
        self::setBind($bind, 'id', $id, Column::BIND_PARAM_INT);

        $result = Db::name($db)->get(
            self::$get,
            $bind['parameters'],
            $bind['types']
        );
        return $result;
    }

    public static function create($codeType)
    {
        // validate
        $validation = new CodeTypeValidation();
        $validation->validate($codeType);

        $bind = [];
        self::setBind($bind, 'name', $codeType['name'], Column::BIND_PARAM_STR);
        self::setBind($bind, 'parent_id', $codeType['parent_id'], Column::BIND_PARAM_INT);

        return Db::name('master')->setId(
            self::$insert,
            $bind['parameters'],
            $bind['types']
        );
    }

    public static function update($codeType)
    {
        // validate
        $validation = new CodeTypeValidation();
        $validation->validate($codeType);

        $bind = [];
        self::setBind($bind, 'id', $codeType['id'], Column::BIND_PARAM_INT);
        self::setBind($bind, 'name', $codeType['name'], Column::BIND_PARAM_STR);
        self::setBind($bind, 'parent_id', $codeType['parent_id'], Column::BIND_PARAM_INT);

        return Db::name('master')->set(
            self::$update,
            $bind['parameters'],
            $bind['types']
        );
    }

    public static function delete($id)
    {
        $bind = [];
        self::setBind($bind, 'id', $id, Column::BIND_PARAM_INT);

        return Db::name('master')->set(
            self::$delete,
            $bind['parameters'],
            $bind['types']
        );
    }
}
