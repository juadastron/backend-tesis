<?php
class AnimalModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerTodos() {
    $result = $this->conn->query("SELECT * FROM animales");
    if (!$result) {
        error_log("Error en SELECT animales: " . $this->conn->error);
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

    public function obtenerPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM animales WHERE id_animal = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($data) {
        $stmt = $this->conn->prepare("INSERT INTO animales (nombre, especie, edad, color, foto_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $data->nombre, $data->especie, $data->edad, $data->color, $data->foto_url);
        return $stmt->execute();
    }

    public function actualizar($data) {
        $stmt = $this->conn->prepare("UPDATE animales SET nombre = ?, especie = ?, edad = ?, color = ?, foto_url = ? WHERE id_animal = ?");
        $stmt->bind_param("ssissi", $data->nombre, $data->especie, $data->edad, $data->color, $data->foto_url, $data->id_animal);
        return $stmt->execute();
    }

    public function tieneAsignacionActiva($id_animal) {
    $stmt = $this->conn->prepare("SELECT id_asignacion FROM asignaciones_animal_dispositivo WHERE id_animal = ? AND fecha_fin IS NULL");
    $stmt->bind_param("i", $id_animal);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
}
    public function eliminar($id) {
    // Eliminar primero las asignaciones relacionadas
    $stmt1 = $this->conn->prepare("DELETE FROM asignaciones_animal_dispositivo WHERE id_animal = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // Luego eliminar el animal
    $stmt2 = $this->conn->prepare("DELETE FROM animales WHERE id_animal = ?");
    $stmt2->bind_param("i", $id);
    return $stmt2->execute();
}
}
