<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ConfiguracionController.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new ConfiguracionController($conn);
$controller->manejarRequest($_SERVER['REQUEST_METHOD']);
