<?php
require_once __DIR__ . '/../models/ConfiguracionModel.php';

class ConfiguracionController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new ConfiguracionModel($conexion);
    }

    public function manejarRequest($method) {
        switch ($method) {
            case 'GET':
                if (!isset($_GET['id_dispositivo'])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "ID de dispositivo requerido"]);
                    return;
                }
                $config = $this->modelo->obtenerPorDispositivo($_GET['id_dispositivo']);
                echo json_encode($config ?: []);
                break;

            case 'POST':
            $data = json_decode(file_get_contents("php://input"), true);
            $camposRequeridos = [
                'id_dispositivo',
                'activar_horario_nocturno',
                'hora_inicio_nocturna',
                'hora_fin_nocturna',
                'activar_siesta',
                'hora_inicio_siesta',
                'hora_fin_siesta',
                'umbral_inactividad_min',
                'modo_ahorro',
                'frecuencia_gps_minutos'
            ];

            foreach ($camposRequeridos as $campo) {
                if (!isset($data[$campo])) {
                    http_response_code(400);
                    echo json_encode(["success" => false, "error" => "Falta el campo: $campo"]);
                    return;
                }
            }

            // Sanitizar booleanos
            $data['activar_horario_nocturno'] = $data['activar_horario_nocturno'] ? 1 : 0;
            $data['activar_siesta'] = $data['activar_siesta'] ? 1 : 0;
            $data['modo_ahorro'] = $data['modo_ahorro'] ? 1 : 0;

            $ok = $this->modelo->guardar((object) $data);
            echo json_encode(["success" => $ok]);
            break;

            default:
                http_response_code(405);
                echo json_encode(["success" => false, "message" => "Método no permitido"]);
        }
    }
}
