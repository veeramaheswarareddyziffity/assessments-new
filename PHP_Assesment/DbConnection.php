<?php

class DBConnection {

    private static $host = 'localhost';
    private static $username = 'root';
    private static $password = 'Ziffity@123';
    private static $dbName = 'banking_details';

    public static function getConnection() {
        try {
            $conn = new mysqli(self::$host, self::$username, self::$password, self::$dbName);
            return $conn;
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage();
            return null; 
        }
    }
}

?>






