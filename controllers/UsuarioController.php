<?php
require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new UsuarioModel($conexion);
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
    		if (!isset($data->nombre, $data->email, $data->password, $data->rol)) {
      		  http_response_code(400);
       		 echo json_encode(["success" => false, "message" => "Faltan datos"]);
       		 return;
    		}

   		$resultado = $this->modelo->crear($data);

    		if ($resultado === true) {
       			echo json_encode(["success" => true, "message" => "Usuario creado"]);
    		} elseif ($resultado === "correo_existente") {
       			 http_response_code(409); // Conflicto
       			 echo json_encode(["success" => false, "message" => "Correo ya está en uso"]);
   		 } else {
        		http_response_code(500);
        		echo json_encode(["success" => false, "message" => "Error al crear usuario"]);
   		 }
   		 break;
            case 'PUT':
   		 $data = json_decode(file_get_contents("php://input"));
    		 if (!isset($data->id_usuario, $data->nombre, $data->email, $data->rol)) {
        		http_response_code(400);
        		echo json_encode(["success" => false, "message" => "Faltan datos"]);
        		return;
    		}

    		$resultado = $this->modelo->actualizar($data);

   		if ($resultado === true) {
        		echo json_encode(["success" => true, "message" => "Usuario actualizado"]);
    		} elseif ($resultado === "correo_existente") {
        		http_response_code(409);
        		echo json_encode(["success" => false, "message" => "Correo ya está en uso por otro usuario"]);
    		} else {
        		http_response_code(500);
        		echo json_encode(["success" => false, "message" => "Error al actualizar usuario"]);
    		}
   		 break;
            case 'DELETE':
                parse_str(file_get_contents("php://input"), $data);
                if (!isset($data['id_usuario'])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID requerido"]);
                    return;
                }
                $ok = $this->modelo->eliminar($data['id_usuario']);
                echo json_encode(["success" => $ok, "message" => $ok ? "Usuario eliminado" : "Error al eliminar usuario"]);
                break;

            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "MÃ©todo no permitido"]);
        }
    }
}
