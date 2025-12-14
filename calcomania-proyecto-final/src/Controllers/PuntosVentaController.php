<?php

namespace App\Controllers;

use App\Models\PuntosVenta;
use App\Database\Database;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PuntosVentaController {
    
    private $puntosVenta;
    
    public function __construct() {
        $pdo = Database::obtenerInstancia()->obtenerPdo();
        $this->puntosVenta = new PuntosVenta($pdo);
    }

    public function listar(Request $request, Response $response): Response {
        try {
            $this->verificarAdmin($request);
            
            $puntos = $this->puntosVenta->listar();
            
            $response->getBody()->write(json_encode(['ok' => true, 'puntos' => $puntos], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            error_log('Error en PuntosVentaController::listar: ' . $e->getMessage());
            $statusCode = ($e->getCode() === 401 || $e->getCode() === 403) ? $e->getCode() : 500;
            $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
        }
    }

    public function obtener(Request $request, Response $response, $id): Response {
        try {
            $this->verificarAdmin($request);
            
            if (empty($id)) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'ID requerido'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            $punto = $this->puntosVenta->buscarPorId($id);
            if (!$punto) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'Punto de venta no encontrado'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            $response->getBody()->write(json_encode(['ok' => true, 'datos' => $punto], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            error_log('Error en PuntosVentaController::obtener: ' . $e->getMessage());
            $statusCode = ($e->getCode() === 401 || $e->getCode() === 403) ? $e->getCode() : 500;
            $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
        }
    }

    public function crear(Request $request, Response $response): Response {
        try {
            $this->verificarAdmin($request);
            
            $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
            
            if (empty($body['nombre_punto']) || empty($body['direccion'])) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'Nombre y dirección son requeridos'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            $this->puntosVenta->crear($body);
            
            $response->getBody()->write(json_encode(['ok' => true, 'mensaje' => 'Punto de venta creado correctamente'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            error_log('Error en PuntosVentaController::crear: ' . $e->getMessage());
            $statusCode = ($e->getCode() === 401 || $e->getCode() === 403) ? $e->getCode() : 500;
            $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
        }
    }

    public function actualizar(Request $request, Response $response, $id): Response {
        try {
            $this->verificarAdmin($request);
            
            if (empty($id)) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'ID requerido'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
            
            if (empty($body['nombre_punto']) || empty($body['direccion'])) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'Nombre y dirección son requeridos'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            $this->puntosVenta->actualizar($id, $body);
            
            $response->getBody()->write(json_encode(['ok' => true, 'mensaje' => 'Punto de venta actualizado correctamente'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            error_log('Error en PuntosVentaController::actualizar: ' . $e->getMessage());
            $statusCode = ($e->getCode() === 401 || $e->getCode() === 403) ? $e->getCode() : 500;
            $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
        }
    }

    public function eliminar(Request $request, Response $response, $id): Response {
        try {
            $this->verificarAdmin($request);
            
            if (empty($id)) {
                $response->getBody()->write(json_encode(['ok' => false, 'error' => 'ID requerido'], JSON_UNESCAPED_UNICODE));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            $this->puntosVenta->eliminar($id);
            
            $response->getBody()->write(json_encode(['ok' => true, 'mensaje' => 'Punto de venta eliminado correctamente'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            error_log('Error en PuntosVentaController::eliminar: ' . $e->getMessage());
            $statusCode = ($e->getCode() === 401 || $e->getCode() === 403) ? $e->getCode() : 500;
            $response->getBody()->write(json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
        }
    }
    
    // Método privado para verificar que el usuario es admin
    private function verificarAdmin(Request $request): void {
        $token = $request->getAttribute("token");
        if (!$token) {
            throw new \Exception('No autorizado. Se requiere token.', 401);
        }
        
        $data = is_object($token) && isset($token->data) ? $token->data : (is_array($token) && isset($token['data']) ? $token['data'] : null);
        $rol = is_object($data) && isset($data->id_rol) ? (int)$data->id_rol : (is_array($data) && isset($data['id_rol']) ? (int)$data['id_rol'] : null);
        
        if ($rol !== 1) {
            throw new \Exception('Acceso denegado. Se requiere rol de administrador.', 403);
        }
    }
}
