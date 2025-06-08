<?php
// reporting (helpful during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));
//     * Authenticate user and generate JWT token
class Config {
    private static $host = 'localhost';
    private static $dbName = 'movierecommendation';
    private static $username = 'root';
    private static $password = '';
    private static $connection = null;

    public static function connect() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbName,
                    self::$username,
                    self::$password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
    
    /**
     * JWT Secret Key for token encryption/decryption
     * @return string The secret key
     */
    public static function JWT_SECRET() {
        return 'movierecommendation_secret_key_2025';
    }
}

?>