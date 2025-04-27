<?php
header('Content-Type: application/json');
include_once 'bd.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET':
    if (isset($_GET['id'])) {
      $id = $_GET['id'];
      $query = $conexion->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
      $query->bind_param("i", $id);
      $query->execute();
      $result = $query->get_result();
      echo json_encode($result->fetch_assoc());
    } else {
      $result = $conexion->query("SELECT * FROM usuarios");
      $usuarios = [];
      while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
      }
      echo json_encode($usuarios);
    }
    break;

  case 'POST':
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->nombre, $data->email, $data->password, $data->rol)) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Faltan datos"]);
      exit;
    }

    $passwordHash = password_hash($data->password, PASSWORD_BCRYPT);
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, creado_en) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $data->nombre, $data->email, $passwordHash, $data->rol);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Usuario creado"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al crear usuario"]);
    }
    break;

  case 'PUT':
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->id_usuario, $data->nombre, $data->email, $data->rol)) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "Faltan datos"]);
      exit;
    }

    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id_usuario = ?");
    $stmt->bind_param("sssi", $data->nombre, $data->email, $data->rol, $data->id_usuario);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Usuario actualizado"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al actualizar usuario"]);
    }
    break;

  case 'DELETE':
    parse_str(file_get_contents("php://input"), $data);
    if (!isset($data['id_usuario'])) {
      http_response_code(400);
      echo json_encode(["success" => false, "message" => "ID requerido"]);
      exit;
    }

    $id = $data['id_usuario'];
    $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
      echo json_encode(["success" => true, "message" => "Usuario eliminado"]);
    } else {
      echo json_encode(["success" => false, "message" => "Error al eliminar usuario"]);
    }
    break;

  default:
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    break;
}
?>