<?php
require_once __DIR__ . '/../models/NotificacionModel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Auth\OAuth2;
use GuzzleHttp\Client;

class NotificacionController {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new NotificacionModel($conexion);
    }

    public function manejarRequest($method) {
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id_dispositivo, $data->titulo, $data->mensaje)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Faltan datos"]);
            return;
        }

        $tokens = $this->modelo->obtenerTokensPorDispositivo($data->id_dispositivo);
        if (empty($tokens)) {
            echo json_encode(["success" => false, "message" => "No se encontraron tokens"]);
            return;
        }

        // Cargar credenciales de Firebase
        $jsonPath = __DIR__ . '/../firebase-adminsdk.json';
        $json = json_decode(file_get_contents($jsonPath));
        $projectId = $json->project_id;

        $oauth = new OAuth2([
            'audience' => 'https://oauth2.googleapis.com/token',
            'issuer' => $json->client_email,
            'signingAlgorithm' => 'RS256',
            'signingKey' => $json->private_key,
            'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ]);
        $accessToken = $oauth->fetchAuthToken()['access_token'];

        $client = new Client();
        $errores = [];

        foreach ($tokens as $token) {
            try {
                $client->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'headers' => [
                        'Authorization' => "Bearer $accessToken",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $data->titulo,
                                'body' => $data->mensaje
                            ],
                            'android' => [
                                'priority' => 'high',
                                'notification' => [
				    'icon' => 'icnotificacion',
                                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    'color' => '#FF0000',
                                    'channel_id' => 'high_importance_channel'
                                ]
                            ]
                        ]
                    ]
                ]);
            } catch (Exception $e) {
                $errores[] = $e->getMessage();
            }
        }

        echo json_encode([
            "success" => count($errores) === 0,
            "tokens_enviados" => count($tokens),
            "errores" => $errores
        ]);
    }
}
