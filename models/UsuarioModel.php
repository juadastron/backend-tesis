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
        if ($this->buscarPorEmail($data->email)) {
            return "correo_existente";
        }

        $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, creado_en) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $data->nombre, $data->email, $passwordHash, $data->rol);
        return $stmt->execute();
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
        // Verificar si el usuario tiene dispositivos asignados
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM usuarios_dispositivos WHERE id_usuario = ?");
        if (!$stmt) {
            error_log("❌ Error en SELECT usuarios_dispositivos: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if ($resultado['total'] > 0) {
            // Buscar otro administrador
            $stmtAdmin = $this->conn->prepare("SELECT id_usuario FROM usuarios WHERE rol = 'admin' AND id_usuario != ? LIMIT 1");
            if (!$stmtAdmin) {
                error_log("❌ Error en SELECT admin: " . $this->conn->error);
                return false;
            }
            $stmtAdmin->bind_param("i", $id);
            $stmtAdmin->execute();
            $nuevoAdmin = $stmtAdmin->get_result()->fetch_assoc();

            if (!$nuevoAdmin) {
                error_log("❌ No se encontró otro administrador para reasignar");
                return false;
            }

            // Transferir asignaciones a otro admin
            $stmtTransfer = $this->conn->prepare("UPDATE usuarios_dispositivos SET id_usuario = ? WHERE id_usuario = ?");
            if (!$stmtTransfer) {
                error_log("❌ Error en UPDATE usuarios_dispositivos: " . $this->conn->error);
                return false;
            }
            $stmtTransfer->bind_param("ii", $nuevoAdmin['id_usuario'], $id);
            if (!$stmtTransfer->execute()) {
                error_log("❌ Error ejecutando transferencia: " . $stmtTransfer->error);
                return false;
            }
        }

        // Eliminar usuario
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        if (!$stmt) {
            error_log("❌ Error en DELETE usuario: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function actualizarTokenFcm($idUsuario, $tokenFcm) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET token_fcm = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $tokenFcm, $idUsuario);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
