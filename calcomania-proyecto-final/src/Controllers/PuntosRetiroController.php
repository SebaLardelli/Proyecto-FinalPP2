<?php

namespace App\Controllers;

use App\Models\PuntoRetiro;
use App\Database\Database;
use Psr\Http\Message\ServerRequestInterface as Request;

class PuntosRetiroController {
    
    private $puntoRetiro;

    public function __construct() {
        $pdo = Database::obtenerInstancia()->obtenerPdo();
        $this->puntoRetiro = new PuntoRetiro($pdo);
    }

    public function listar(Request $request) {
        $datos = $this->puntoRetiro->traerPuntosRetiro();
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function obtener(Request $request, $id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $punto = $this->puntoRetiro->traerPuntoRetiroPorId(intval($id));
        if (!$punto) {
            http_response_code(404);
            echo json_encode(['error' => 'Punto de retiro no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'datos' => $punto], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

