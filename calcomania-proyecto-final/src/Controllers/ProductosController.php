<?php

namespace App\Controllers;

use App\Models\Productos;
use App\Database\Database;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ProductosController {
    
    private $productos;

    public function __construct() {
        $this->productos = new Productos(Database::obtenerInstancia()->obtenerPdo());
    }

    // Listar productos con filtros (público)
    public function listar(Request $request) {
        try {
            $queryParams = $request->getQueryParams();
            $categoria = isset($queryParams['categoria']) ? $queryParams['categoria'] : null;
            $tematica = isset($queryParams['tematica']) ? $queryParams['tematica'] : null;
            
            $productos = $this->productos->listarConFiltros($categoria, $tematica);
            
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(200);
            echo json_encode(['ok' => true, 'productos' => $productos], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Listar todos los productos (admin)
    public function listarAdmin(Request $request, Response $response): Response {
        try {
            $this->verificarAdmin($request);
            
            $productos = $this->productos->listarCompleto();
            
            $response->getBody()->write(json_encode(['ok' => true, 'productos' => $productos], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['ok' => false, 'error' => 'Error al cargar productos: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function obtener(Request $request, $id) {
        $producto = $this->productos->buscarPorId($id);
        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'datos' => $producto], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function crear(Request $request) {
        try {
            $this->verificarAdmin($request);
            
            // Para POST con FormData, usar $_POST directamente ya que PHP lo parsea automáticamente
            $contentType = $request->getHeaderLine('Content-Type');
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $body = $_POST; // PHP parsea automáticamente multipart/form-data en $_POST
            } else {
                $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
            }
            
            $archivo = isset($_FILES['imagen']) ? $_FILES['imagen'] : null;
            
            // Validar campos requeridos
            $requeridos = ['nombre_p', 'precio', 'stock', 'id_categoria'];
            foreach ($requeridos as $campo) {
                if (empty($body[$campo])) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo {$campo} es requerido"], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            if ($this->productos->crearConImagen($body, $archivo)) {
                http_response_code(201);
                echo json_encode(['ok' => true, 'mensaje' => 'Producto creado correctamente'], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear producto'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear producto: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function actualizar(Request $request, $id) {
        try {
            $this->verificarAdmin($request);
            
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            // Para POST con multipart/form-data, PHP parsea automáticamente en $_POST
            // Para PUT, usar el body parseado o $_POST si está disponible
            $contentType = $request->getHeaderLine('Content-Type');
            $method = $request->getMethod();
            
            // Obtener el body parseado
            $body = $request->getParsedBody();
            
            // Si está vacío y es multipart/form-data, usar $_POST (PHP lo parsea automáticamente para POST)
            if (empty($body) && strpos($contentType, 'multipart/form-data') !== false) {
                $body = $_POST;
            }
            
            // Si aún está vacío, intentar como array vacío
            if (!is_array($body)) {
                $body = [];
            }
            
            // Remover _method si existe (es solo un indicador para el routing)
            if (isset($body['_method'])) {
                unset($body['_method']);
            }
            
            $archivo = isset($_FILES['imagen']) ? $_FILES['imagen'] : null;
            
            // Validar campos requeridos
            $requeridos = ['nombre_p', 'precio', 'stock', 'id_categoria'];
            foreach ($requeridos as $campo) {
                if (empty($body[$campo])) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo {$campo} es requerido"], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            if ($this->productos->actualizarConImagen($id, $body, $archivo)) {
                http_response_code(200);
                echo json_encode(['ok' => true, 'mensaje' => 'Producto actualizado correctamente'], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar producto'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar producto: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
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
        
        if ($this->productos->eliminar($id)) {
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Producto eliminado correctamente'], JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar producto'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // Método privado para verificar que el usuario es admin
    private function verificarAdmin(Request $request): void {
        $token = $request->getAttribute("token");
        if (!$token) {
            throw new \Slim\Exception\HttpUnauthorizedException($request, 'No autorizado. Se requiere token.');
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
            throw new \Slim\Exception\HttpForbiddenException($request, 'Acceso denegado. Se requiere rol de administrador.');
        }
    }
}
