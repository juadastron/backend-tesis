<?php
require_once __DIR__ . '/../models/ZonaSeguraModel.php';

class ZonaSeguraController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ZonaSeguraModel($conexion);
    }

    public function manejarRequest($method) {
        switch ($method) {
            case 'GET':
                if (!isset($_GET['id_dispositivo'])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID de dispositivo requerido"]);
                    return;
                }
                $zonas = $this->modelo->obtenerZonaActivaPorDispositivo($_GET['id_dispositivo']);
                echo json_encode($zonas);
                break;

            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                if (!isset($data->id_dispositivo, $data->latitud, $data->longitud, $data->radio_metros)) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "error" => "Faltan campos"]);
                    return;
                }
                $resultado = $this->modelo->guardarZona($data);
                echo json_encode($resultado);
                break;

            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "Método no permitido"]);
        }
    }
}
