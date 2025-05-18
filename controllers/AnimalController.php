<?php
require_once __DIR__ . '/../models/AnimalModel.php';

class AnimalController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new AnimalModel($conexion);
    }

    public function manejarRequest($method) {
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
                parse_str(file_get_contents("php://input"), $data);
                if ($this->modelo->eliminar($data['id_animal'])) {
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
