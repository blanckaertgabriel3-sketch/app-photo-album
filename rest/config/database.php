<?php

$env = parse_ini_file(__DIR__ . '/../../.env');

class Database{
    public $conn;

    public function getConnection() {
        global $env;
        $this->conn = null;
        try {

            $user = $env["DB_USERNAME"];
            $pass = $env["DB_PASSWORD"];
            $db_name = $env["DB_NAME"];
            $host = $env["DB_HOST"];

            $this->conn = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
        }  catch (PDOException $e) {
            echo "Connection Error : ".$e->getMessage()."\n";
        }
        return $this->conn;
    }
}