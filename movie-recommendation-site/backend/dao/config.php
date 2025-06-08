<?php
// reporting 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ (E_NOTICE | E_DEPRECATED));

class Config {
    private static $connection = null;

    public static function DB_NAME() {
        return Config::get_env("DB_NAME", "movierecommendation");
    }
    
    public static function DB_PORT() {
        return Config::get_env("DB_PORT", 3306);
    }
    
    public static function DB_USER() {
        return Config::get_env("DB_USER", 'root');
    }
    
    public static function DB_PASSWORD() {
        return Config::get_env("DB_PASSWORD", '');
    }
    
    public static function DB_HOST() {
        return Config::get_env("DB_HOST", 'localhost');
    }
    
    public static function JWT_SECRET() {
        return Config::get_env("JWT_SECRET", 'movierecommendation_secret_key_2025');
    }
    
    public static function get_env($name, $default) {
        return isset($_ENV[$name]) && trim($_ENV[$name]) != "" ? $_ENV[$name] : $default;
    }

    public static function connect() {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host=" . self::DB_HOST() . ";port=" . self::DB_PORT() . ";dbname=" . self::DB_NAME();
                
                self::$connection = new PDO(
                    $dsn,
                    self::DB_USER(),
                    self::DB_PASSWORD(),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, // For RailWay
                        PDO::MYSQL_ATTR_SSL_CA => null // SSL support for production
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
?>