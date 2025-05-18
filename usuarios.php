<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/UsuarioController.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new UsuarioController($conn);
$controller->manejarRequest($_SERVER['REQUEST_METHOD']);
