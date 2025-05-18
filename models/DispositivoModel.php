<?php
class DispositivoModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerTodosConAsignacion() {
        $query = "
            SELECT d.*, a.nombre AS nombre_animal, a.especie AS especie_animal
            FROM dispositivos d
            LEFT JOIN asignaciones_animal_dispositivo aad ON d.id_dispositivo = aad.id_dispositivo AND aad.fecha_fin IS NULL
            LEFT JOIN animales a ON aad.id_animal = a.id_animal
        ";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function crear($data) {
	$stmt = $this->conn->prepare("
            INSERT INTO dispositivos 
	      (imei, estado_actual, numero_celular, ultima_conexion, creado_en) 
            VALUES (?, ?, ?, NOW(), NOW())");
	$stmt->bind_param("sss", $data->imei, $data->estado_actual, $data->numero_celular);
	if ($stmt->execute()) {
	    return $this->conn->insert_id; // ? retorna el ID recién creado
        } else {
            return false;
        } 
    }
    public function actualizar($data) {
        $stmt = $this->conn->prepare("
            UPDATE dispositivos 
            SET imei = ?, estado_actual = ?, numero_celular = ? 
            WHERE id_dispositivo = ?
        ");
        $stmt->bind_param("sssi", $data->imei, $data->estado_actual, $data->numero_celular, $data->id_dispositivo);
        return $stmt->execute();
    }

    public function eliminar($id) {
    // ?? Primero eliminar configuraciones relacionadas
    $stmt1 = $this->conn->prepare("DELETE FROM configuraciones_dispositivo WHERE id_dispositivo = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // ? Ahora sí, eliminar el dispositivo
    $stmt2 = $this->conn->prepare("DELETE FROM dispositivos WHERE id_dispositivo = ?");
    $stmt2->bind_param("i", $id);
    
    if (!$stmt2->execute()) {
        file_put_contents("log.txt", "? Error al eliminar dispositivo $id: " . $stmt2->error . "\n", FILE_APPEND);
        return false;
    }

    return $stmt2->affected_rows > 0;
}
}
