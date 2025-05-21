<?php
require_once __DIR__ . '/../models/AsignacionModel.php';

class AsignacionController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new AsignacionModel($conexion);
    }

    public function manejarRequest($method) {
        header('Content-Type: application/json; charset=utf-8');

        switch ($method) {
            case 'GET':
                if (isset($_GET['id_dispositivo'])) {
                    // Historial completo por dispositivo
                    $asignaciones = $this->modelo->obtenerHistorialPorDispositivo($_GET['id_dispositivo']);
                    echo json_encode(["success" => true, "asignaciones" => $asignaciones]);
                } elseif (isset($_GET['id_animal'])) {
                // Asignación activa de un animal (para editar o verificar estado)
                $asignacion = $this->modelo->obtenerAsignacionActiva($_GET['id_animal']);
                if ($asignacion) {
                    echo json_encode([
                        "success" => true,
                        "asignaciones" => $asignacion
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "No hay asignación activa"
                    ]);
                }
            }
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
                echo json_encode([
                    "success" => $ok,
                    "message" => $ok ? "Dispositivo desvinculado correctamente" : "Error al desvincular"
                ]);
                break;

            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "Método no permitido"]);
        }
    }
}
