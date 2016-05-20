<?php
namespace App\Models\Expedia;

trait CodeTypeQuery
{
    private static $whereParentId = <<<SQL
    parent_id = :parent_id
SQL;

    private static $totalCount = <<<SQL
SELECT
    count(*) AS count
FROM
    expedia_code_type
WHERE
    deleted = 0
!condition
SQL;

    private static $list = <<<SQL
SELECT
    *
FROM
    expedia_code_type
WHERE
    deleted = 0
!condition
ORDER BY
    id ASC
LIMIT
    :offset, :limit
SQL;

    private static $get = <<<SQL
SELECT
    *
FROM
    expedia_code_type
WHERE
    id = :id
AND
    deleted = 0
SQL;

    private static $insert = <<<SQL
INSERT INTO expedia_code_type
    (name, parent_id)
VALUES
    (:name, :parent_id)
SQL;

    private static $update = <<<SQL
UPDATE expedia_code_type
SET
    name = :name,
    parent_id = :parent_id
WHERE
    id = :id
SQL;

    private static $delete = <<<SQL
UPDATE expedia_code_type
SET
    deleted = 1
WHERE
    id = :id
SQL;
}
