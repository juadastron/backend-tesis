<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'bd.php'; // se conecta usando el .env automáticamente

$data = json_decode(file_get_contents("php://input"));

$email = $data->email ?? '';
$password = $data->password ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit();
}

// Buscar usuario por email
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($usuario = $resultado->fetch_assoc()) {
    if (password_verify($password, $usuario['password_hash'])) {
        echo json_encode([
            "success" => true,
            "nombre" => $usuario['nombre'],
            "email" => $usuario['email'],
            "rol" => $usuario['rol']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
}

$stmt->close();
$conexion->close();
