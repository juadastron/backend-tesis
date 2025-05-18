<?php
class ConfiguracionModel {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerPorDispositivo($id_dispositivo) {
        $stmt = $this->conn->prepare("
    SELECT c.*, d.imei
    FROM configuraciones_dispositivo c
    JOIN dispositivos d ON c.id_dispositivo = d.id_dispositivo
    WHERE c.id_dispositivo = ?
");
        $stmt->bind_param("i", $id_dispositivo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function guardar($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO configuraciones_dispositivo 
            (id_dispositivo, activar_horario_nocturno, hora_inicio_nocturna, hora_fin_nocturna,
             activar_siesta, hora_inicio_siesta, hora_fin_siesta,
             umbral_inactividad_min, modo_ahorro, frecuencia_gps_minutos)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
              activar_horario_nocturno = VALUES(activar_horario_nocturno),
              hora_inicio_nocturna = VALUES(hora_inicio_nocturna),
              hora_fin_nocturna = VALUES(hora_fin_nocturna),
              activar_siesta = VALUES(activar_siesta),
              hora_inicio_siesta = VALUES(hora_inicio_siesta),
              hora_fin_siesta = VALUES(hora_fin_siesta),
              umbral_inactividad_min = VALUES(umbral_inactividad_min),
              modo_ahorro = VALUES(modo_ahorro),
              frecuencia_gps_minutos = VALUES(frecuencia_gps_minutos)
        ");

        $stmt->bind_param(
            "iississiis",
            $data->id_dispositivo,
            $data->activar_horario_nocturno,
            $data->hora_inicio_nocturna,
            $data->hora_fin_nocturna,
            $data->activar_siesta,
            $data->hora_inicio_siesta,
            $data->hora_fin_siesta,
            $data->umbral_inactividad_min,
            $data->modo_ahorro,
            $data->frecuencia_gps_minutos
        );

        return $stmt->execute();
    }
}
