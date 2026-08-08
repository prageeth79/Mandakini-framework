<?php

class m0001_initials {
    public function up() {
        $db = \app\core\Application::$app->db;
        $SQL = "CREATE TABLE users (
                loging_id varchar(10) NOT NULL,
                firstName varchar(255) NOT NULL,
                lastName varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                category varchar(10) NOT NULL,
                status int(11) NOT NULL,
                created_at timestamp NOT NULL DEFAULT current_timestamp()
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;";
        $db->pdo->exec($SQL);
    }

    public function down() {
        $db = \app\core\Application::$app->db;
        $SQL = "DROP TABLE users";
        $db->pdo->exec($SQL);
    }
}