<?php
class UsuarioModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerTodos() {
        $result = $this->conn->query("SELECT * FROM usuarios");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function buscarPorEmail($email) {
    $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

    public function crear($data) {
    // Verificar si el email ya está registrado
    if ($this->buscarPorEmail($data->email)) {
        return "correo_existente";
    }

    $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
    $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, creado_en) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $data->nombre, $data->email, $passwordHash, $data->rol);

    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

    public function actualizar($data) {
    if ($this->correoYaEnUsoPorOtro($data->email, $data->id_usuario)) {
        return "correo_existente";
    }

    $stmt = $this->conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id_usuario = ?");
    $stmt->bind_param("sssi", $data->nombre, $data->email, $data->rol, $data->id_usuario);
    return $stmt->execute();
}

public function correoYaEnUsoPorOtro($email, $idUsuarioActual) {
    $stmt = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
    $stmt->bind_param("si", $email, $idUsuarioActual);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() !== null;
}

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
