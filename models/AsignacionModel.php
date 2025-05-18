<?php
class AsignacionModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function asignarDispositivo($id_animal, $id_dispositivo) {
        // Verificar si ya está asignado
        $check = $this->conn->prepare("SELECT id_asignacion FROM asignaciones_animal_dispositivo WHERE id_dispositivo = ? AND fecha_fin IS NULL");
        $check->bind_param("i", $id_dispositivo);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            return ["success" => false, "message" => "El dispositivo ya está asignado"];
        }

        // Insertar asignación
        $stmt = $this->conn->prepare("
            INSERT INTO asignaciones_animal_dispositivo (id_animal, id_dispositivo, fecha_inicio)
            VALUES (?, ?, NOW())
        ");
        $stmt->bind_param("ii", $id_animal, $id_dispositivo);
        $ok = $stmt->execute();

        if ($ok) {
            // Cambiar estado del dispositivo
            $update = $this->conn->prepare("UPDATE dispositivos SET estado_actual = 'asignado' WHERE id_dispositivo = ?");
            $update->bind_param("i", $id_dispositivo);
            $update->execute();
        }

        return ["success" => $ok, "message" => $ok ? "Dispositivo asignado correctamente" : "Error al asignar dispositivo"];
    }

    public function desvincularDispositivo($id_asignacion) {
        // Obtener id_dispositivo para actualizar estado
        $buscar = $this->conn->prepare("SELECT id_dispositivo FROM asignaciones_animal_dispositivo WHERE id_asignacion = ?");
        $buscar->bind_param("i", $id_asignacion);
        $buscar->execute();
        $res = $buscar->get_result();
        if (!$res || !$res->num_rows) return false;
        $row = $res->fetch_assoc();

        // Cerrar asignación
        $stmt = $this->conn->prepare("UPDATE asignaciones_animal_dispositivo SET fecha_fin = NOW() WHERE id_asignacion = ?");
        $stmt->bind_param("i", $id_asignacion);
        $ok = $stmt->execute();

        if ($ok) {
            $update = $this->conn->prepare("UPDATE dispositivos SET estado_actual = 'disponible' WHERE id_dispositivo = ?");
            $update->bind_param("i", $row['id_dispositivo']);
            $update->execute();
        }

        return $ok;
    }

    public function obtenerAsignacionActiva($id_animal) {
        $stmt = $this->conn->prepare("
            SELECT a.id_asignacion, a.fecha_inicio, d.imei 
            FROM asignaciones_animal_dispositivo a
            JOIN dispositivos d ON a.id_dispositivo = d.id_dispositivo
            WHERE a.id_animal = ? AND a.fecha_fin IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("i", $id_animal);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
