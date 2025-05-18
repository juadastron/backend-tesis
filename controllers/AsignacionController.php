<?php
require_once __DIR__ . '/../models/AsignacionModel.php';

class AsignacionController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new AsignacionModel($conexion);
    }

    public function manejarRequest($method) {
        switch ($method) {
            case 'GET':
                if (!isset($_GET['id_animal'])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID de animal requerido"]);
                    return;
                }
                $asignacion = $this->modelo->obtenerAsignacionActiva($_GET['id_animal']);
                echo json_encode($asignacion ?: null);
                break;

            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                if (!isset($data->id_animal, $data->id_dispositivo)) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
                    return;
                }
                $respuesta = $this->modelo->asignarDispositivo($data->id_animal, $data->id_dispositivo);
                echo json_encode($respuesta);
                break;

            case 'PUT':
                $data = json_decode(file_get_contents("php://input"));
                if (!isset($data->id_asignacion)) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID de asignación requerido"]);
                    return;
                }
                $ok = $this->modelo->desvincularDispositivo($data->id_asignacion);
                echo json_encode(["success" => $ok, "message" => $ok ? "Dispositivo desvinculado correctamente" : "Error al desvincular"]);
                break;

            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "Método no permitido"]);
        }
    }
}
