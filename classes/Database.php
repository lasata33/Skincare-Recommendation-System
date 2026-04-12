<?php
class Database {
    private static $conn;

    public static function connect() {
        if (!self::$conn) {
            $host = 'localhost';
            $user = 'root';
            $password = '';
            $database = 'summer_project';

            self::$conn = new mysqli($host, $user, $password, $database);

            if (self::$conn->connect_error) {
                die("Connection failed: " . self::$conn->connect_error);
            }
        }

        return self::$conn;
    }
}
?>
