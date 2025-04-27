<?php
header('Content-Type: application/json');
include_once 'bd.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET':
    if (isset($_GET['id'])) {
      $id = $_GET['id'];
      $query = $conexion->prepare("SELECT * FROM animales WHERE id_animal = ?");
      $query->bind_param("i", $id);
      $query->execute();
      $result = $query->get_result();
      echo json_encode($result->fetch_assoc());
    } else {
      $result = $conexion->query("SELECT * FROM animales");
      $animales = [];
      while ($row = $result->fetch_assoc()) {
        $animales[] = $row;
      }
      echo json_encode($animales);
    }
    break;

  case 'POST':
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->nombre, $data->especie, $data->edad, $data->color)) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Faltan datos"]);
      exit;
    }

    $stmt = $conexion->prepare("INSERT INTO animales (nombre, especie, edad, color, foto_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $data->nombre, $data->especie, $data->edad, $data->color, $data->foto_url);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Animal creado"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al crear animal"]);
    }
    break;

  case 'PUT':
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->id_animal, $data->nombre, $data->especie, $data->edad, $data->color)) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Faltan datos"]);
      exit;
    }

    $stmt = $conexion->prepare("UPDATE animales SET nombre = ?, especie = ?, edad = ?, color = ?, foto_url = ? WHERE id_animal = ?");
    $stmt->bind_param("ssissi", $data->nombre, $data->especie, $data->edad, $data->color, $data->foto_url, $data->id_animal);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Animal actualizado"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al actualizar animal"]);
    }
    break;

  case 'DELETE':
    parse_str(file_get_contents("php://input"), $data);
    if (!isset($data['id_animal'])) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "ID requerido"]);
      exit;
    }

    $id = $data['id_animal'];
    $stmt = $conexion->prepare("DELETE FROM animales WHERE id_animal = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Animal eliminado"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al eliminar animal"]);
    }
    break;

  default:
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    break;
}
?>
