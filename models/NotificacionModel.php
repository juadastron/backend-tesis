<?php

class NotificacionModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

   public function obtenerTokensPorDispositivo($idDispositivo) {
    $tokens = [];

    // 1. Tokens de usuarios relacionados al dispositivo
    $stmt = $this->conn->prepare("
        SELECT u.token_fcm
        FROM usuarios_dispositivos ud
        JOIN usuarios u ON u.id_usuario = ud.id_usuario
        WHERE ud.id_dispositivo = ? AND u.token_fcm IS NOT NULL
    ");
    $stmt->bind_param("i", $idDispositivo);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tokens[] = $row["token_fcm"];
    }

    // 2. Tokens de todos los administradores con token no nulo
    $resultAdmin = $this->conn->query("
        SELECT token_fcm FROM usuarios 
        WHERE rol = 'admin' AND token_fcm IS NOT NULL
    ");
    while ($row = $resultAdmin->fetch_assoc()) {
        $tokens[] = $row["token_fcm"];
    }

    // 3. Eliminar duplicados por si algún admin está también asignado
    return array_values(array_unique($tokens));
}
}
