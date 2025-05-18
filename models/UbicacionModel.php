<?php
class UbicacionModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerDispositivosAsignados() {
    $query = "
        SELECT 
            d.id_dispositivo, 
            d.imei, 
            d.estado_actual,
            a.nombre AS nombre_animal, 
            a.especie AS especie_animal
        FROM dispositivos d
        INNER JOIN asignaciones_animal_dispositivo aad ON d.id_dispositivo = aad.id_dispositivo
        INNER JOIN animales a ON aad.id_animal = a.id_animal
        WHERE aad.fecha_fin IS NULL
    ";

    $result = $this->conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}
    public function obtenerUltimaUbicacion($idDispositivo) {
        $query = "
            SELECT latitud, longitud, timestamp
            FROM ubicaciones
            WHERE id_dispositivo = ?
            ORDER BY timestamp DESC
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $idDispositivo);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
