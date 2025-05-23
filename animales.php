

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
file_put_contents('debug_log.txt', "Ruta actual: " . __DIR__ . PHP_EOL, FILE_APPEND);
file_put_contents('debug_log.txt', "¿Existe config? " . (file_exists(__DIR__ . '/../config/Database.php') ? "sí" : "no") . PHP_EOL, FILE_APPEND);


require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AnimalController.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new AnimalController($conn);
$controller->manejarRequest($_SERVER['REQUEST_METHOD']);
