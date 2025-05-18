<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/UbicacionController.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new UbicacionController($conn);
$controller->manejarRequest($_SERVER['REQUEST_METHOD']);
