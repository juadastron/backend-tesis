<?php
class ZonaSeguraModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerZonaActivaPorDispositivo($id_dispositivo) {
        $stmt = $this->conn->prepare("SELECT * FROM zonas_seguras WHERE id_dispositivo = ? AND activo = 1");
        $stmt->bind_param("i", $id_dispositivo);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    public function guardarZona($data) {
        // Validar coordenadas geográficas
        if ($data->latitud < -90 || $data->latitud > 90 || $data->longitud < -180 || $data->longitud > 180) {
            return ["success" => false, "error" => "Coordenadas geográficas inválidas"];
        }

        // Verificar si ya existe una zona activa
        $stmt = $this->conn->prepare("SELECT id_zona FROM zonas_seguras WHERE id_dispositivo = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("i", $data->id_dispositivo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $id_zona = $row['id_zona'];
            $update = $this->conn->prepare("UPDATE zonas_seguras SET latitud = ?, longitud = ?, radio_metros = ? WHERE id_zona = ?");
            $update->bind_param("dddi", $data->latitud, $data->longitud, $data->radio_metros, $id_zona);
            $ok = $update->execute();
        } else {
            $insert = $this->conn->prepare("INSERT INTO zonas_seguras (id_dispositivo, latitud, longitud, radio_metros, activo) VALUES (?, ?, ?, ?, 1)");
            $insert->bind_param("iddd", $data->id_dispositivo, $data->latitud, $data->longitud, $data->radio_metros);
            $ok = $insert->execute();
        }

        return ["success" => $ok];
    }
}
