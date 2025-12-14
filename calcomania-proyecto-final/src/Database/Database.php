<?php

namespace App\Database;

use PDO;

class Database {
    
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'];
            $user = $_ENV['DB_USER'];
            $pass = $_ENV['DB_PASS'];
            
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\Exception $e) {
            // Si no se puede conectar, registrar el error (sin exponer credenciales)
            error_log('Error de conexión a la base de datos');
            $this->pdo = null;
        }
    }

    public static function obtenerInstancia() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function obtenerPdo() {
        return $this->pdo;
    }
}

