<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;

class Database {
    private $conexion;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->conexion = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME']
        );

        if ($this->conexion->connect_error) {
            die(json_encode([
                "success" => false,
                "message" => "Error de conexión: " . $this->conexion->connect_error
            ]));
        }
    }

    public function getConnection() {
        return $this->conexion;
    }
}
