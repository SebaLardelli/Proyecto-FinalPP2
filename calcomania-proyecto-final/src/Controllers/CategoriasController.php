<?php

namespace App\Controllers;

use App\Models\Categorias;
use App\Database\Database;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoriasController {
    
    private $categorias;

    public function __construct() {
        $this->categorias = new Categorias(Database::obtenerInstancia()->obtenerPdo());
    }

    public function listar(Request $request) {
        try {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode(['ok' => true, 'categorias' => $this->categorias->listar()], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function obtener(Request $request, $id) {
        $cat = $this->categorias->buscarPorId($id);
        if (!$cat) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoría no encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'datos' => $cat], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function crear(Request $request) {
        $this->verificarAdmin($request);
        
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        
        if (empty($body['nombre_c']) || empty($body['descripcion_c'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y descripción requeridos'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->categorias->crear($body['nombre_c'], $body['descripcion_c']);
        
        http_response_code(201);
        echo json_encode(['ok' => true, 'mensaje' => 'Categoría creada correctamente'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function actualizar(Request $request, $id) {
        $this->verificarAdmin($request);
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        
        if (empty($body['nombre_c']) || empty($body['descripcion_c'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y descripción requeridos'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $this->categorias->actualizar($id, $body['nombre_c'], $body['descripcion_c']);
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'mensaje' => 'Categoría actualizada correctamente'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function eliminar(Request $request, $id) {
        $this->verificarAdmin($request);
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            $this->categorias->eliminar($id);
            
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Categoría eliminada correctamente'], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    // Método privado para verificar que el usuario es admin
    private function verificarAdmin(Request $request): void {
        $token = $request->getAttribute("token");
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado. Se requiere token de autenticación.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $data = null;
        if (is_object($token)) {
            if (isset($token->data)) {
                $data = $token->data;
            }
        } else {
            if (is_array($token) && isset($token['data'])) {
                $data = $token['data'];
            }
        }
        
        $rol = null;
        if (is_object($data)) {
            $rol = isset($data->id_rol) ? (int)$data->id_rol : null;
        } else {
            if (is_array($data)) {
                $rol = isset($data['id_rol']) ? (int)$data['id_rol'] : null;
            }
        }
        
        if ($rol !== 1) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado. Se requiere rol de administrador.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
