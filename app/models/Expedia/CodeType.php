<?php
namespace App\Models\Expedia;

use Peanut\Phalcon\Pdo\Mysql as Db;

class CodeType
{
    /**
     * @param $offset
     * @param $limit
     * @param $parentId
     */
    public static function getList($offset, $limit, $parentId = false)
    {
        $bindQuery      = "";
        $bindParameters = [];

        if (false !== $parentId) {
            $bindQuery .= " AND parent_id = :parent_id ";
            $bindParameters[':parent_id'] = (int) $parentId;
        }

        $total = Db::name('slave')->get1(
            "SELECT COUNT(*) AS count FROM expedia_code_type WHERE deleted = 0 ".$bindQuery,
            $bindParameters
        );

        $results = Db::name('slave')->gets(
            "SELECT * FROM expedia_code_type WHERE deleted = 0 $bindQuery ORDER BY id ASC LIMIT :limit OFFSET :offset",
            array_merge($bindParameters, [
                ':offset' => (int) $offset,
                ':limit'  => (int) $limit
            ])
        );

        return [$total, $results];
    }

    /**
     * @param $id
     * @param $db
     */
    public static function get($id, $db = 'slave')
    {
        return Db::name($db)->get("SELECT * FROM expedia_code_type WHERE id = :id AND deleted = 0", [
            ':id' => (int) $id
        ]);
    }

    /**
     * @param $codeType
     */
    public static function create($codeType)
    {
        return Db::name('master')->setId(
            "INSERT INTO expedia_code_type (name, parent_id) VALUES (:name, :parent_id)",
            [
                ':name'      => $codeType['name'],
                ':parent_id' => (int) $codeType['parent_id']
            ]
        );
    }

    /**
     * @param $codeType
     */
    public static function update($codeType)
    {
        return Db::name('master')->set(
            "UPDATE expedia_code_type SET name = :name, parent_id = :parent_id WHERE id = :id",
            [
                ':name'      => $codeType['name'],
                ':parent_id' => (int) $codeType['parent_id'],
                ':id'        => (int) $codeType['id']
            ]
        );
    }

    /**
     * @param $id
     */
    public static function delete($id)
    {
        return Db::name('master')->set("UPDATE expedia_code_type SET deleted = 1 WHERE id = :id", [
            ':id' => (int) $id
        ]);
    }
}
