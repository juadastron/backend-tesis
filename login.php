<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AuthController.php';

$database = new Database();
$conn = $database->getConnection();

$controller = new AuthController($conn);
$controller->login();
