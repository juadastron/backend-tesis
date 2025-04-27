<?php
header('Content-Type: application/json');
include_once 'bd.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'POST':
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->id_animal, $data->id_dispositivo)) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Datos incompletos"]);
      exit;
    }

    // Verificar si el dispositivo ya está asignado
    $check = $conexion->prepare("SELECT * FROM asignaciones_animal_dispositivo 
                                 WHERE id_dispositivo = ? AND fecha_fin IS NULL");
    $check->bind_param("i", $data->id_dispositivo);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
      echo json_encode(["success" => false, "message" => "El dispositivo ya está asignado"]);
      exit;
    }

    // Registrar asignación
    $stmt = $conexion->prepare("INSERT INTO asignaciones_animal_dispositivo (id_animal, id_dispositivo, fecha_inicio) 
                                VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $data->id_animal, $data->id_dispositivo);

    if ($stmt->execute()) {
      // Cambiar estado del dispositivo a "asignado"
      $update = $conexion->prepare("UPDATE dispositivos SET estado_actual = 'asignado' WHERE id_dispositivo = ?");
      $update->bind_param("i", $data->id_dispositivo);
      $update->execute();

      echo json_encode(["success" => true, "message" => "Dispositivo asignado correctamente"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al asignar dispositivo"]);
    }
    break;

  case 'PUT':
    // 🔥 Ahora añadimos el soporte para desvincular
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->id_asignacion)) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "ID de asignación requerido"]);
      exit;
    }

    // Cerrar la asignación (poner fecha_fin)
    $stmt = $conexion->prepare("UPDATE asignaciones_animal_dispositivo SET fecha_fin = NOW() WHERE id_asignacion = ?");
    $stmt->bind_param("i", $data->id_asignacion);

    if ($stmt->execute()) {
      // Ahora también actualizar el estado del dispositivo a "disponible"
      $query = "SELECT id_dispositivo FROM asignaciones_animal_dispositivo WHERE id_asignacion = ?";
      $buscar = $conexion->prepare($query);
      $buscar->bind_param("i", $data->id_asignacion);
      $buscar->execute();
      $resultado = $buscar->get_result();
      $row = $resultado->fetch_assoc();
      $id_dispositivo = $row['id_dispositivo'];

      $update = $conexion->prepare("UPDATE dispositivos SET estado_actual = 'disponible' WHERE id_dispositivo = ?");
      $update->bind_param("i", $id_dispositivo);
      $update->execute();

      echo json_encode(["success" => true, "message" => "Dispositivo desvinculado correctamente"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al desvincular dispositivo"]);
    }
    break;

    case 'GET':
      if (isset($_GET['id_animal'])) {
          $idAnimal = $_GET['id_animal'];
  
          $stmt = $conexion->prepare("
              SELECT a.id_asignacion, a.fecha_inicio, d.imei 
              FROM asignaciones_animal_dispositivo a
              JOIN dispositivos d ON a.id_dispositivo = d.id_dispositivo
              WHERE a.id_animal = ? AND a.fecha_fin IS NULL
              LIMIT 1
          ");
          $stmt->bind_param("i", $idAnimal);
          $stmt->execute();
          $result = $stmt->get_result();
  
          if ($row = $result->fetch_assoc()) {
              echo json_encode($row); // Devuelve los datos de asignación activa
          } else {
              echo json_encode(null); // No hay asignación activa
          }
      } else {
          http_response_code(400);
          echo json_encode(["success" => false, "message" => "ID de animal requerido"]);
      }
      break;
  

  default:
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    break;
}
?>
