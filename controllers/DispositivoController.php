<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../models/DispositivoModel.php';

class DispositivoController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new DispositivoModel($conexion);
    }

    public function manejarRequest($method) {
        switch ($method) {
            case 'GET':
    // ?? Verificar si está pidiendo solo los dispositivos disponibles
    if (isset($_GET['disponibles'])) {
        $datos = $this->modelo->obtenerDisponibles(); // <- método en el modelo
        echo json_encode($datos);
        return;
    }

    // ? Verificación de permisos (ya lo tienes)
    if (isset($_GET['permiso_edicion'], $_GET['id_usuario'], $_GET['id_dispositivo'])) {
        $puede = $this->modelo->usuarioPuedeEditar($_GET['id_usuario'], $_GET['id_dispositivo']);
        echo json_encode(["puede_editar" => $puede]);
        return;
    }

    // ? Obtener todos con asignación
    $datos = $this->modelo->obtenerTodosConAsignacion();
    echo json_encode($datos);
    break;            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                
                if (!isset($data->imei, $data->estado_actual)) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Faltan datos"]);
                    return;
                }

                $data->numero_celular = $data->numero_celular ?? null;

                $idDispositivo = $this->modelo->crear($data); // âœ… Solo una vez

                if ($idDispositivo && isset($data->id_usuario)) {
                    $this->modelo->asignarUsuarioADispositivo($data->id_usuario, $idDispositivo);
                }
                if ($idDispositivo !== false) {
                    echo json_encode([
                        "success" => true,
                        "id_dispositivo" => $idDispositivo,
                        "imei" => $data->imei
                    ]);
                } else {
                    echo json_encode([
                        "success" => false,
                        "message" => "Error al crear dispositivo"
                    ]);
                }
                break;

            case 'PUT':
   		 $data = file_get_contents("php://input");

    		if (empty($data)) {
       		 echo json_encode(["success" => false, "message" => "JSON vacío"]);
        	return;
    		}

    		$data = json_decode($data);

   		if (!isset($data->id_dispositivo, $data->imei, $data->estado_actual)) {
        		http_response_code(400);
        		echo json_encode(["success" => false, "message" => "Faltan datos"]);
        		return;
    		}

    		$data->numero_celular = $data->numero_celular ?? null;
    		$ok = $this->modelo->actualizar($data);
    		echo json_encode(["success" => $ok, "message" => $ok ? "Dispositivo actualizado" : "Error al actualizar dispositivo"]);
    		break;

            case 'DELETE':
    file_put_contents("log.txt", "?? Entrï¿½ al DELETE\n", FILE_APPEND);

    $input = file_get_contents("php://input");
    file_put_contents("log.txt", "?? Body recibido: $input\n", FILE_APPEND);

    if (empty($input)) {
        file_put_contents("log.txt", "? JSON vacï¿½o\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => "JSON vacï¿½o"]);
        return;
    }

    $data = json_decode($input);
    if (!isset($data->id_dispositivo)) {
        file_put_contents("log.txt", "? ID faltante\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => "ID faltante"]);
        return;
    }

    file_put_contents("log.txt", "?? Intentando eliminar ID: " . $data->id_dispositivo . "\n", FILE_APPEND);
    $ok = $this->modelo->eliminar($data->id_dispositivo);

    file_put_contents("log.txt", "? Resultado: " . ($ok ? "OK" : "ERROR") . "\n", FILE_APPEND);

    echo json_encode([
        "success" => $ok,
        "message" => $ok ? "Dispositivo eliminado" : "Error al eliminar"
    ]);
    return;
            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "MÃ©todo no permitido"]);
        }
    }
}