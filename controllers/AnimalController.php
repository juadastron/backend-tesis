<?php
require_once __DIR__ . '/../models/AnimalModel.php';

class AnimalController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new AnimalModel($conexion);
    }

    public function manejarRequest($method) {
        header("Content-Type: application/json; charset=UTF-8");

        switch ($method) {
            case 'GET':
                if (isset($_GET['id'])) {
                    echo json_encode($this->modelo->obtenerPorId($_GET['id']));
                } else {
                    echo json_encode($this->modelo->obtenerTodos());
                }
                break;

            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                if ($this->modelo->crear($data)) {
                    echo json_encode(["success" => true, "message" => "Animal creado"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error al crear animal"]);
                }
                break;

            case 'PUT':
                $data = json_decode(file_get_contents("php://input"));
                if ($this->modelo->actualizar($data)) {
                    echo json_encode(["success" => true, "message" => "Animal actualizado"]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error al actualizar animal"]);
                }
                break;

            case 'DELETE':
    $id = isset($_GET['id_animal']) ? intval($_GET['id_animal']) : null;

    error_log("ID recibido para eliminar: $id");

    if (!method_exists($this->modelo, 'tieneAsignacionActiva')) {
        error_log("ERROR: método tieneAsignacionActiva no existe en AnimalModel");
    }

    if (!$id || $id <= 0) {
        echo json_encode(["success" => false, "message" => "ID inválido o faltante"]);
        return;
    }

    if ($this->modelo->tieneAsignacionActiva($id)) {
        echo json_encode([
            "success" => false,
            "message" => "Este animal tiene un dispositivo asignado. Debes desvincularlo primero."
        ]);
        return;
    }

    if ($this->modelo->eliminar($id)) {
        echo json_encode(["success" => true, "message" => "Animal eliminado"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar animal"]);
    }
    break;
            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "Método no permitido"]);
        }
    }
}
