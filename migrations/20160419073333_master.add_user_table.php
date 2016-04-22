<?php
/**
 * Migration Task class.
 */
class Master_AddUserTable
{

    public $conn; // pdo connection

    public function __construct(\PDO $connection)
    {
        $this->conn = $connection;
    }

    public function preUp()
    {
        // add the pre-migration code here
    }

    public function postUp()
    {
        // add the post-migration code here
    }

    public function preDown()
    {
        // add the pre-migration code here
    }

    public function postDown()
    {
        // add the post-migration code here
    }

    /**
     * Return the SQL statements for the Up migration
     *
     * @return string The SQL string to execute for the Up migration.
     */
    public function up()
    {
        $this->conn->exec(<<<SQL
ALTER TABLE `test_user`
ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
SQL
        );
    }

    /**
     * Return the SQL statements for the Down migration
     *
     * @return string The SQL string to execute for the Down migration.
     */
    public function down()
    {
        $this->conn->exec(<<<SQL
ALTER TABLE `test_user`
DROP COLUMN `updated_at`;
SQL
        );
    }

}