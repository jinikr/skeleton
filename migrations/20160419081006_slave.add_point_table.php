<?php
/**
 * Migration Task class.
 */
class Slave_AddPointTable
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
CREATE TABLE `test_point` (
  `point_seq` INT NOT NULL AUTO_INCREMENT,
  `user_seq` int NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`point_seq`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
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
DROP TABLE `test_point`
SQL
        );
    }

}