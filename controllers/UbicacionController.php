<?php
require_once __DIR__ . '/../models/UbicacionModel.php';

class UbicacionController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new UbicacionModel($conexion);
    }

    public function manejarRequest($method) {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id_dispositivo']) && isset($_GET['recorrido'])) {
                $id = intval($_GET['id_dispositivo']);
                $recorrido = $this->modelo->obtenerRecorridoUltimoDia($id);
                echo json_encode([
                    "success" => true,
                    "recorrido" => $recorrido
                ]);
                return;
            }

            $asignados = $this->modelo->obtenerDispositivosAsignados();
            $ubicaciones = [];

            foreach ($asignados as $d) {
                $ultimaUbicacion = $this->modelo->obtenerUltimaUbicacion($d['id_dispositivo']);
                if ($ultimaUbicacion) {
                    $ubicaciones[] = array_merge($d, $ultimaUbicacion);
                }
            }

            echo json_encode([
                "success" => true,
                "dispositivos" => $ubicaciones
            ]);
            break;

            http_response_code(405);
            echo json_encode([
                "success" => false,
                "message" => "Metodo no permitido"
            ]);
            break;
            default:
                http_response_code(405);
                echo json_encode([
                    "success" => false,
                    "message" => "Metodo no permitido"
                ]);
                break;
        }
    }
}
