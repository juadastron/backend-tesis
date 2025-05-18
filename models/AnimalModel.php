<?php
class AnimalModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerTodos() {
        $result = $this->conn->query("SELECT * FROM animales");
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

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM animales WHERE id_animal = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
