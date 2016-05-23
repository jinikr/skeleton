<?php
namespace App\Models\Expedia;

use Peanut\Phalcon\Pdo\Mysql as Db;

class Code
{
    /**
     * @param $offset
     * @param $limit
     * @param $typeId
     */
    public static function getList($offset = 0, $limit = 10, $typeId = 0)
    {
        $bindQuery      = "";
        $bindParameters = [];

        if ($typeId) {
            $bindQuery .= " AND a.type_id = :type_id ";
            $bindParameters[':type_id'] = (int) $typeId;
        }

        $sql = "
SELECT
    COUNT(*) as total
FROM
    expedia_code AS a
INNER JOIN
    expedia_code_type AS b
ON
    b.id = a.type_id
AND b.deleted = 0
WHERE
    a.deleted = 0
$bindQuery
        ";
        $total = Db::name('slave')->get1($sql, $bindParameters);

        $sql = "
SELECT
    a.*,
    b.name AS type_name
FROM
    expedia_code AS a
INNER JOIN
    expedia_code_type AS b
ON
    b.id = a.type_id
AND b.deleted = 0
WHERE
    a.deleted = 0
$bindQuery
ORDER BY
    a.id ASC
LIMIT :limit OFFSET :offset
        ";
        $results = Db::name('slave')->gets($sql, array_merge($bindParameters, [
            ':offset' => (int) $offset,
            ':limit'  => (int) $limit
        ]));

        $listCodesId = [];

        foreach ($results as $code) {
            array_push($listCodesId, $code['id']);
        }

        $subCodes = self::getSubCodes($listCodesId);

        foreach ($results as $key => $code) {
            $results[$key]              = self::setOutputFormat($code);
            $results[$key]['sub_codes'] = array_key_exists($code['id'], $subCodes) ? $subCodes[$code['id']] : [];
        }

        return [$total, $results];
    }

    /**
     * @param $code
     */
    private static function setOutputFormat($code)
    {
        $code['type'] = [
            'id'   => $code['type_id'],
            'name' => $code['type_name']
        ];
        unset($code['type_id'], $code['type_name']);

        return $code;
    }

    /**
     * @param $id
     * @param $db
     */
    public static function get($id, $db = 'slave')
    {
        $sql = "
SELECT
    a.*,
    b.name AS type_name
FROM
    expedia_code AS a
INNER JOIN
    expedia_code_type AS b
ON
    b.id = a.type_id
AND b.deleted = 0
WHERE
    a.deleted = 0
AND a.id = :id
        ";

        $code = self::setOutputFormat(Db::name($db)->get($sql, [
            ':id' => (int) $id
        ]));
        $code['sub_codes'] = self::getSubCodes($id, $db)[$id];

        return $code;
    }

    /**
     * @param $id
     * @param $db
     */
    public static function getSubCodes($id, $db = 'slave')
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        $bindQuery      = '';
        $bindParameters = [];

        foreach ($id as $key => $codeId) {
            $bindKey = ':code_id_'.$key;
            $bindQuery .= ($bindQuery ? ',' : '').$bindKey;
            $bindParameters[$bindKey] = (int) $codeId;
        }

        $sql = "
SELECT
    a.expedia_code_id,
    b.*,
    c.name AS type_name
FROM
    expedia_code_sub AS a
INNER JOIN
    expedia_code AS b
ON
    b.id = a.sub_expedia_code_id
INNER JOIN
    expedia_code_type AS c
ON
    c.id = b.type_id
AND c.deleted = 0
WHERE
    b.deleted = 0
AND a.expedia_code_id IN ($bindQuery)
        ";

        $results = Db::name($db)->gets($sql, $bindParameters);
        $codes   = [];

        if ($results) {
            foreach ($results as $key => $code) {
                $codeId = $code['expedia_code_id'];
                unset($code['expedia_code_id']);
                $codes[$codeId][] = self::setOutputFormat($code);
            }
        }

        return $codes;
    }

    /**
     * @param  $code
     * @return mixed
     */
    public static function create($code)
    {
        $id = Db::name('master')->setId(
            "INSERT INTO expedia_code (name, description, type_id) VALUES (:name, :description, :type_id)",
            [
                ':name'        => $code['name'],
                ':description' => empty($code['description']) === false ? $code['description'] : '',
                ':type_id'     => (int) $code['type_id']
            ]
        );

        if (array_key_exists('sub_codes', $code) && is_array($code['sub_codes'])) {
            self::setSubCodes($id, $code['sub_codes']);
        }

        return $id;
    }

    /**
     * @param  $id
     * @param  $subCodes
     * @return null
     */
    private static function setSubCodes($id, $subCodes)
    {
        if (!$subCodes) {
            Db::name('master')->set("DELETE FROM expedia_code_sub WHERE expedia_code_id = :id", [
                ':id' => (int) $id
            ]);

            return;
        }

        $bindQuery      = '';
        $bindParameters = [];

        foreach ($subCodes as $key => $codeId) {
            $bindKey = ':sub_code_id_'.$key;
            $bindQuery .= ($bindQuery ? ',' : '').$bindKey;
            $bindParameters[$bindKey] = (int) $codeId;
        }

        $codesCount = Db::name('master')->get1(
            "SELECT COUNT(*) FROM expedia_code WHERE id IN (".$bindQuery.")",
            $bindParameters
        );

        if (count($subCodes) !== $codesCount) {
            throw new \Exception('invalid sub code!');
        }

        Db::name('master')->set(
            "DELETE FROM expedia_code_sub WHERE expedia_code_id = :id AND sub_expedia_code_id NOT IN (".$bindQuery.")",
            array_merge($bindParameters, [
                ':id' => (int) $id
            ])
        );

        $existsSubCodes = Db::name('master')->gets(
            "SELECT sub_expedia_code_id FROM expedia_code_sub WHERE expedia_code_id = :id",
            [
                ":id" => (int) $id
            ]
        );
        $existsSubCodesId = [];

        foreach ($existsSubCodes as $code) {
            array_push($existsSubCodesId, $code['sub_expedia_code_id']);
        }

        $bind           = '';
        $bindParameters = [];

        foreach ($subCodes as $codeId) {
            if (in_array($codeId, $existsSubCodesId)) {
                continue;
            }

            $bind .= ($bind ? ',' : '').'(?,?)';
            array_push($bindParameters, (int) $id);
            array_push($bindParameters, (int) $codeId);
        }

        if ($bindParameters) {
            Db::name('master')->set(
                "INSERT INTO expedia_code_sub (expedia_code_id, sub_expedia_code_id) VALUES ".$bind,
                $bindParameters
            );

            if (Db::name('master')->affectedRows() !== count($subCodes)) {
                throw new \Exception('set sub codes failed!');
            }
        }
    }

    /**
     * @param  $code
     * @return mixed
     */
    public static function update($code)
    {
        $result = Db::name('master')->set(
            "UPDATE expedia_code SET name = :name, description = :description, type_id = :type_id WHERE id = :id",
            [
                ':name'        => $code['name'],
                ':description' => empty($code['description']) === false ? $code['description'] : '',
                ':type_id'     => (int) $code['type_id'],
                ':id'          => (int) $code['id']
            ]
        );

        if (empty($code['sub_codes'])) {
            $code['sub_codes'] = [];
        }

        self::setSubCodes($code['id'], $code['sub_codes']);

        return $result;
    }

    /**
     * @param $id
     */
    public static function delete($id)
    {
        return Db::name('master')->set("UPDATE expedia_code SET deleted = 1 WHERE id = :id", [
            ':id' => (int) $id
        ]);
    }
}
