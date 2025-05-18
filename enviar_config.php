<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\ConnectionSettings;

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id_dispositivo'])) {
        throw new Exception("Falta id_dispositivo");
    }

    $data['tipo_evento'] = $data['tipo_evento'] ?? 'configuracion_inicial';
    $imei = $data['imei'];
    $topic = "geo_little_paws/config/" . $imei;
    $mqtt = new MqttClient('127.0.0.1', 1883, 'php_config_sender');
    $connectionSettings = (new ConnectionSettings)
    	->setUsername('tu_usuario')
    	->setPassword('7kilometrosporta');

    $mqtt->connect($connectionSettings, true);
    $payload = json_encode($data);
    $mqtt->publish($topic, $payload, 1);
    $mqtt->disconnect();

    echo json_encode(["success" => true]);
} catch (Throwable $e) {
    echo "? Excepcion capturada: " . $e->getMessage();
    file_put_contents("logs/mqtt_falla_" . date('Ymd_His') . ".log", print_r($e, true));
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Excepcion en el servidor",
        "message" => $e->getMessage()
    ]);
}
