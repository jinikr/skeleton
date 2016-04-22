<?php
/**
 * Migration Task class.
 */
class Master_User_Add_Field_Birth
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
     * @return null or string The SQL string to execute for the Up migration.
     */
    public function up()
    {
        $this->conn->exec(<<<SQL
ALTER TABLE `test_user`
ADD COLUMN `user_birth` VARCHAR(10) NOT NULL DEFAULT '0000-00-00' AFTER `user_name`
SQL
        );
    }

    /**
     * Return the SQL statements for the Down migration
     *
     * @return null or string The SQL string to execute for the Down migration.
     */
    public function down()
    {
        $this->conn->exec(<<<SQL
ALTER TABLE `test_user`
DROP COLUMN `user_birth`
SQL
        );
    }

}