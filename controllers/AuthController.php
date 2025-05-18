<?php
require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new UsuarioModel($conexion);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        // Sanitizar entrada
        $email = isset($data->email) ? trim($data->email) : '';
        $password = isset($data->password) ? trim($data->password) : '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Faltan datos"]);
            return;
        }

        $usuario = $this->modelo->buscarPorEmail($email);
        if (!$usuario) {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
            return;
        }
        
        if (!password_verify($password, $usuario['password_hash'])) {
            echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
            return;
        }

        // Login exitoso
        echo json_encode([
            "success" => true,
            "id_usuario" => $usuario['id_usuario'],
            "nombre" => $usuario['nombre'],
            "email" => $usuario['email'],
            "rol" => $usuario['rol']
        ]);
    }
}
