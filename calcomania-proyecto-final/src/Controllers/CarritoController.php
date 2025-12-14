<?php

namespace App\Controllers;

use App\Models\Carrito;
use App\Models\Productos;
use App\Database\Database;
use PDO;
use Psr\Http\Message\ServerRequestInterface as Request;

class CarritoController {
    
    private $carrito;
    private $productos;

    public function __construct() {
        $pdo = Database::obtenerInstancia()->obtenerPdo();
        $this->carrito = new Carrito($pdo);
        $this->productos = new Productos($pdo);
    }

    public function obtener(Request $request) {
        try {
            // Obtener token del request
            $token = $request->getAttribute("token");
            
            // Extraer datos del usuario de forma clara
            $data = null;
            if (is_object($token)) {
                $data = $token->data;
            } else {
                if (isset($token['data'])) {
                    $data = $token['data'];
                } else {
                    $data = null;
                }
            }
            
            // Extraer ID de usuario
            $usuarioId = null;
            if (is_object($data)) {
                $usuarioId = isset($data->id_usuario) ? $data->id_usuario : null;
            } else {
                if (is_array($data)) {
                    $usuarioId = isset($data['id_usuario']) ? $data['id_usuario'] : null;
                }
            }
            
            if (!$usuarioId) {
                http_response_code(400);
                echo json_encode(['error' => 'Usuario no válido'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $carrito = $this->carrito->obtenerActivo($usuarioId);
            
            // Si no hay carrito activo, retornar carrito vacío (no crear uno)
            if (!$carrito || !isset($carrito['id_carrito'])) {
                http_response_code(200);
                echo json_encode([
                    'ok' => true, 
                    'carrito' => null, 
                    'items' => [], 
                    'total' => 0.0
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            
            $items = $this->carrito->obtenerItems($carrito['id_carrito']);
            $total = $this->carrito->calcularTotal($carrito['id_carrito']);
            
            $itemsArray = is_array($items) ? $items : [];
            $totalFloat = is_numeric($total) ? (float)$total : 0.0;
            
            http_response_code(200);
            echo json_encode([
                'ok' => true, 
                'carrito' => $carrito, 
                'items' => $itemsArray, 
                'total' => $totalFloat
            ], JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function agregar(Request $request) {
        // Obtener token y usuario
        $token = $request->getAttribute("token");
        
        $data = null;
        if (is_object($token)) {
            $data = $token->data;
        } else {
            if (isset($token['data'])) {
                $data = $token['data'];
            } else {
                $data = null;
            }
        }
        
        $usuarioId = null;
        if (is_object($data)) {
            $usuarioId = isset($data->id_usuario) ? $data->id_usuario : null;
        } else {
            if (is_array($data)) {
                $usuarioId = isset($data['id_usuario']) ? $data['id_usuario'] : null;
            }
        }
        
        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Obtener datos del body
        $input = file_get_contents('php://input');
        $bodyData = [];
        if (!empty($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                $bodyData = $decoded;
            }
        } else {
            $bodyData = (isset($_POST) && is_array($_POST)) ? $_POST : [];
        }
        
        if (empty($bodyData['id_producto'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de producto requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $producto = $this->productos->buscarPorId($bodyData['id_producto']);
        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            // Obtener carrito activo o crear uno nuevo si no existe
            $carrito = $this->carrito->obtenerActivo($usuarioId);
            if (!$carrito) {
                $carrito = $this->carrito->crearActivo($usuarioId);
                if (!$carrito) {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al crear carrito'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            $cantidad = isset($bodyData['cantidad']) ? $bodyData['cantidad'] : 1;
            $tamano = isset($bodyData['tamano']) ? $bodyData['tamano'] : null;
            
            $resultado = $this->carrito->agregarProducto(
                $carrito['id_carrito'], 
                $bodyData['id_producto'], 
                $cantidad,
                $producto['precio'],
                $tamano
            );
            
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Producto agregado'], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function actualizar(Request $request) {
        // Obtener token y usuario
        $token = $request->getAttribute("token");
        
        $data = null;
        if (is_object($token)) {
            $data = $token->data;
        } else {
            if (isset($token['data'])) {
                $data = $token['data'];
            } else {
                $data = null;
            }
        }
        
        $usuarioId = null;
        if (is_object($data)) {
            $usuarioId = isset($data->id_usuario) ? $data->id_usuario : null;
        } else {
            if (is_array($data)) {
                $usuarioId = isset($data['id_usuario']) ? $data['id_usuario'] : null;
            }
        }
        
        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Obtener datos del body
        $input = file_get_contents('php://input');
        $bodyData = [];
        if (!empty($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                $bodyData = $decoded;
            }
        } else {
            $bodyData = (isset($_POST) && is_array($_POST)) ? $_POST : [];
        }
        
        // Validar datos
        if (empty($bodyData['id_detalle'])) {
            if (!isset($bodyData['id_producto']) || $bodyData['id_producto'] === null || $bodyData['id_producto'] === '' || intval($bodyData['id_producto']) <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de producto inválido'], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        
        if (!isset($bodyData['cantidad']) || $bodyData['cantidad'] === null || $bodyData['cantidad'] === '' || !is_numeric($bodyData['cantidad'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Cantidad inválida'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        try {
            // Actualizar por id_detalle (cambio puede ser positivo o negativo)
            if (!empty($bodyData['id_detalle'])) {
                $id_detalle = intval($bodyData['id_detalle']);
                $cambio = intval($bodyData['cantidad']); // Cambio puede ser positivo (+) o negativo (-)
                
                if ($id_detalle <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de detalle inválido'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                // Obtener la cantidad actual del detalle
                $pdo = Database::obtenerInstancia()->obtenerPdo();
                $stmt = $pdo->prepare("SELECT cantidad, id_producto FROM detalle_venta WHERE id_fila = :id AND id_venta IS NULL");
                $stmt->execute([':id' => $id_detalle]);
                $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$detalle) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Producto no encontrado en el carrito'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $cantidad_actual = intval($detalle['cantidad']);
                $nueva_cantidad = $cantidad_actual + $cambio;
                
                // Si la nueva cantidad es <= 0, eliminar el producto del carrito
                if ($nueva_cantidad <= 0) {
                    $this->carrito->eliminarPorDetalle($id_detalle);
                } else {
                    // Verificar stock si se aumenta cantidad
                    if ($cambio > 0) {
                        $stmt = $pdo->prepare("SELECT stock FROM producto WHERE id_producto = :producto");
                        $stmt->execute([':producto' => $detalle['id_producto']]);
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$producto || intval($producto['stock']) < $nueva_cantidad) {
                            http_response_code(400);
                            echo json_encode(['error' => 'Stock insuficiente'], JSON_UNESCAPED_UNICODE);
                            exit;
                        }
                    }
                    
                    // Actualizar la cantidad
                    $this->carrito->actualizarCantidadPorDetalle($id_detalle, $nueva_cantidad);
                }
            } else {
                // Actualizar por producto y tamaño
                $carrito = $this->carrito->obtenerActivo($usuarioId);
                if (!$carrito || !isset($carrito['id_carrito'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Carrito no encontrado'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $id_producto = intval($bodyData['id_producto']);
                $cambio = intval($bodyData['cantidad']);
                
                $tamano = null;
                if (isset($bodyData['tamano'])) {
                    $valorTamano = $bodyData['tamano'];
                    if ($valorTamano !== null && $valorTamano !== '' && $valorTamano !== 'null' && $valorTamano !== 'NULL') {
                        $tamano = trim($valorTamano);
                    }
                }
                
                $pdo = Database::obtenerInstancia()->obtenerPdo();
                
                // Buscar el producto en el carrito
                $detalle = null;
                if ($tamano !== null && $tamano !== '') {
                    $stmt = $pdo->prepare("SELECT cantidad, id_fila FROM detalle_venta WHERE id_carrito = :carrito AND id_producto = :producto AND (TRIM(tamano) = :tamano OR tamano = :tamano) AND id_venta IS NULL LIMIT 1");
                    $stmt->execute([':carrito' => $carrito['id_carrito'], ':producto' => $id_producto, ':tamano' => $tamano]);
                    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                if (!$detalle) {
                    $stmt = $pdo->prepare("SELECT cantidad, id_fila FROM detalle_venta WHERE id_carrito = :carrito AND id_producto = :producto AND id_venta IS NULL LIMIT 1");
                    $stmt->execute([':carrito' => $carrito['id_carrito'], ':producto' => $id_producto]);
                    $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                
                if (!$detalle) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Producto no encontrado en el carrito'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $id_fila = $detalle['id_fila'];
                $cantidad_actual = intval($detalle['cantidad']);
                $nueva_cantidad = $cantidad_actual + $cambio;
                
                // Verificar stock si se aumenta cantidad
                if ($cambio > 0) {
                    $stmt = $pdo->prepare("SELECT stock FROM producto WHERE id_producto = :producto");
                    $stmt->execute([':producto' => $id_producto]);
                    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$producto || intval($producto['stock']) < $nueva_cantidad) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Stock insuficiente'], JSON_UNESCAPED_UNICODE);
                        exit;
                    }
                }
                
                // Actualizar o eliminar según la cantidad
                if ($nueva_cantidad <= 0) {
                    $this->carrito->eliminarPorDetalle($id_fila);
                } else {
                    $this->carrito->actualizarCantidadPorDetalle($id_fila, $nueva_cantidad);
                }
            }
            
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Cantidad actualizada'], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function eliminar(Request $request) {
        // Obtener token y usuario
        $token = $request->getAttribute("token");
        
        $data = null;
        if (is_object($token)) {
            $data = $token->data;
        } else {
            if (isset($token['data'])) {
                $data = $token['data'];
            } else {
                $data = null;
            }
        }
        
        $usuarioId = null;
        if (is_object($data)) {
            $usuarioId = isset($data->id_usuario) ? $data->id_usuario : null;
        } else {
            if (is_array($data)) {
                $usuarioId = isset($data['id_usuario']) ? $data['id_usuario'] : null;
            }
        }
        
        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Obtener datos del body
        $input = file_get_contents('php://input');
        $bodyData = [];
        if (!empty($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                $bodyData = $decoded;
            }
        } else {
            $bodyData = (isset($_POST) && is_array($_POST)) ? $_POST : [];
        }
        
        try {
            // Eliminar por id_detalle
            if (!empty($bodyData['id_detalle'])) {
                $id_detalle = intval($bodyData['id_detalle']);
                
                if ($id_detalle <= 0) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de detalle inválido'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $resultado = $this->carrito->eliminarPorDetalle($id_detalle);
                
                if (!$resultado) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No se pudo eliminar el producto'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
                // Eliminar por producto y tamaño
                if (empty($bodyData['id_producto'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de producto requerido'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                $carrito = $this->carrito->obtenerActivo($usuarioId);
                if (!$carrito || !isset($carrito['id_carrito'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Carrito no encontrado'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                
                // Normalizar tamaño
                $tamano = null;
                if (isset($bodyData['tamano'])) {
                    $valorTamano = $bodyData['tamano'];
                    if ($valorTamano !== '' && $valorTamano !== 'null' && $valorTamano !== 'NULL') {
                        $tamano = $valorTamano;
                    }
                }
                
                $resultado = $this->carrito->eliminarProducto($carrito['id_carrito'], $bodyData['id_producto'], $tamano);
                
                if (!$resultado) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No se pudo eliminar el producto'], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
            
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Producto eliminado'], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function vaciar(Request $request) {
        // Obtener token y usuario
        $token = $request->getAttribute("token");
        
        $data = null;
        if (is_object($token)) {
            $data = $token->data;
        } else {
            if (isset($token['data'])) {
                $data = $token['data'];
            } else {
                $data = null;
            }
        }
        
        $usuarioId = null;
        if (is_object($data)) {
            $usuarioId = isset($data->id_usuario) ? $data->id_usuario : null;
        } else {
            if (is_array($data)) {
                $usuarioId = isset($data['id_usuario']) ? $data['id_usuario'] : null;
            }
        }
        
        if (!$usuarioId) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $carrito = $this->carrito->obtenerActivo($usuarioId);
        if (!$carrito || !isset($carrito['id_carrito'])) {
            http_response_code(200);
            echo json_encode(['ok' => true, 'mensaje' => 'Carrito ya está vacío'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $this->carrito->vaciar($carrito['id_carrito']);
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'mensaje' => 'Carrito vaciado'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}