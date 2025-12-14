<?php

namespace App\Controllers;

use App\Models\MetodosPago;
use App\Database\Database;
use Psr\Http\Message\ServerRequestInterface as Request;

class MetodosPagoController {
    
    private $metodosPago;
    
    public function __construct() {
        $pdo = Database::obtenerInstancia()->obtenerPdo();
        $this->metodosPago = new MetodosPago($pdo);
    }

    public function listar(Request $request) {
        $datos = $this->metodosPago->traerMetodosPago();
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'metodos' => $datos, 'datos' => $datos], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function obtener(Request $request, $id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $metodo = $this->metodosPago->traerMetodoPagoPorId(intval($id));
        if (!$metodo) {
            http_response_code(404);
            echo json_encode(['error' => 'Método de pago no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'datos' => $metodo], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function crear(Request $request) {
        $this->verificarAdmin($request);
        
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        
        if (empty($body['descripcion_mp'])) {
            http_response_code(400);
            echo json_encode(['error' => 'La descripción es requerida'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if ($this->metodosPago->crearMetodoPago($body['descripcion_mp'])) {
            http_response_code(201);
            echo json_encode(['ok' => true, 'mensaje' => 'Método de pago creado correctamente'], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear método de pago'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function actualizar(Request $request, $id) {
        $this->verificarAdmin($request);
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        
        if (empty($body['descripcion_mp'])) {
            http_response_code(400);
            echo json_encode(['error' => 'La descripción es requerida'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if ($this->metodosPago->actualizarMetodoPago(intval($id), $body['descripcion_mp'])) {
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Método de pago actualizado correctamente'], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar método de pago'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function eliminar(Request $request, $id) {
        $this->verificarAdmin($request);
        
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            if ($this->metodosPago->eliminarMetodoPago(intval($id))) {
                http_response_code(200);
                echo json_encode(['ok' => true, 'mensaje' => 'Método de pago eliminado correctamente'], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar método de pago'], JSON_UNESCAPED_UNICODE);
                exit;
            }
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
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado. Se requiere rol de administrador.'], JSON_UNESCAPED_UNICODE);
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
