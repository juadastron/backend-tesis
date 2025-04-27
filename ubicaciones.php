<?php
header('Content-Type: application/json');
include_once 'bd.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // 🔥 SOLO mostrar dispositivos que estén asignados actualmente
        $query = "
            SELECT 
                d.id_dispositivo,
                d.imei,
                d.estado_actual,
                u.latitud,
                u.longitud,
                a.nombre AS nombre_animal,
                a.especie AS especie_animal
            FROM dispositivos d
            INNER JOIN asignaciones_animal_dispositivo aad ON d.id_dispositivo = aad.id_dispositivo
            INNER JOIN animales a ON aad.id_animal = a.id_animal
            INNER JOIN ubicaciones u ON d.id_dispositivo = u.id_dispositivo
            WHERE aad.fecha_fin IS NULL
            ORDER BY d.id_dispositivo ASC
        ";

        $result = $conexion->query($query);
        $dispositivos = [];

        while ($row = $result->fetch_assoc()) {
            $dispositivos[] = $row;
        }

        echo json_encode([
            "success" => true,
            "dispositivos" => $dispositivos
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "message" => "Método no permitido"
        ]);
        break;
}
?>
