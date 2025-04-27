<?php
header('Content-Type: application/json');
include_once 'bd.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Listar todos los dispositivos con info de animal asignado (si existe)
        $query = "
          SELECT d.*, a.nombre AS nombre_animal, a.especie AS especie_animal
          FROM dispositivos d
          LEFT JOIN asignaciones_animal_dispositivo aad ON d.id_dispositivo = aad.id_dispositivo AND aad.fecha_fin IS NULL
          LEFT JOIN animales a ON aad.id_animal = a.id_animal
        ";
      
        $result = $conexion->query($query);
        $dispositivos = [];
        while ($row = $result->fetch_assoc()) {
          $dispositivos[] = $row;
        }
      
        echo json_encode($dispositivos);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->imei, $data->estado_actual)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Faltan datos"]);
            exit;
        }

        $stmt = $conexion->prepare("INSERT INTO dispositivos (imei, estado_actual, ultima_conexion, creado_en) VALUES (?, ?, NOW(), NOW())");
        $stmt->bind_param("ss", $data->imei, $data->estado_actual);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Dispositivo creado"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al crear dispositivo"]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id_dispositivo, $data->imei, $data->estado_actual)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Faltan datos"]);
            exit;
        }

        $stmt = $conexion->prepare("UPDATE dispositivos SET imei = ?, estado_actual = ? WHERE id_dispositivo = ?");
        $stmt->bind_param("ssi", $data->imei, $data->estado_actual, $data->id_dispositivo);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Dispositivo actualizado"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar dispositivo"]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id_dispositivo)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID requerido"]);
            exit;
        }

        $stmt = $conexion->prepare("DELETE FROM dispositivos WHERE id_dispositivo = ?");
        $stmt->bind_param("i", $data->id_dispositivo);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Dispositivo eliminado"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar dispositivo"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido"]);
        break;
}
?>
