<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/NotificacionController.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new NotificacionController($conn);
$controller->manejarRequest($_SERVER['REQUEST_METHOD']);
