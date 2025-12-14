<?php

namespace App\Controllers;

use App\Models\Ventas;
use App\Models\Carrito;
use App\Models\VentaPagos;
use App\Database\Database;
use Psr\Http\Message\ServerRequestInterface as Request;

class VentasController {
    
    private $ventas;
    private $carrito;
    private $ventaPagos;

    public function __construct() {
        $pdo = Database::obtenerInstancia()->obtenerPdo();
        $this->ventas = new Ventas($pdo);
        $this->carrito = new Carrito($pdo);
        $this->ventaPagos = new VentaPagos($pdo);
    }

    public function crear(Request $request) {
        $usuario = $this->extraerUsuarioDelToken($request);
        if (!$usuario || !isset($usuario['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $body = is_array($request->getParsedBody()) ? $request->getParsedBody() : [];
        
        if (empty($body['punto_retiro_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Punto de retiro requerido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($body['pagos']) || !is_array($body['pagos'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Métodos de pago requeridos'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $carrito = $this->carrito->obtenerActivo($usuario['id']);
        $items = $this->carrito->obtenerItems($carrito['id_carrito']);
        
        if (empty($items)) {
            http_response_code(400);
            echo json_encode(['error' => 'Carrito vacío'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Guardar el importe_total ANTES de procesar la venta para preservarlo en el carrito finalizado
        $total = $carrito['importe_total'];
        $importe_total_carrito = $total; // Preservar este valor para el carrito finalizado

        // Validar que la suma de pagos coincida con el total
        $sumaPagos = array_reduce($body['pagos'], fn($s, $p) => $s + $p['monto'], 0);
        if (abs($sumaPagos - $total) > 0.01) {
            http_response_code(400);
            echo json_encode(['error' => 'El total de pagos no coincide'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        try {
            Database::obtenerInstancia()->obtenerPdo()->beginTransaction();

            // Crear la venta primero
            $id_venta = $this->ventas->crear(
                $usuario['id'],
                $carrito['id_carrito'],
                $total,
                $body['punto_retiro_id']
            );

            // Pasar items del carrito a la venta y descontar stock
            $this->ventas->pasarItemsDelCarrito($id_venta, $carrito['id_carrito']);

            // Agregar los métodos de pago
            foreach ($body['pagos'] as $pago) {
                $this->ventas->agregarPago($id_venta, $pago['id_metodo_pago'], $pago['monto']);
            }

            // IMPORTANTE: Finalizar el carrito DESPUÉS de pasar los items, pero preservando el importe_total
            // Una vez que los items se asocian con la venta (id_venta ya no es NULL), calcularTotal() retornaría 0
            // Por eso debemos preservar explícitamente el importe_total que teníamos antes
            $resultadoFinalizar = $this->carrito->finalizar($carrito['id_carrito'], $importe_total_carrito);
            if (!$resultadoFinalizar) {
                throw new \Exception('Error al finalizar el carrito');
            }

            Database::obtenerInstancia()->obtenerPdo()->commit();
            
            http_response_code(201);
            echo json_encode([
                'ok' => true, 
                'mensaje' => 'Compra realizada', 
                'id_venta' => $id_venta,
                'carrito_eliminado' => true
            ], JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Exception $e) {
            Database::obtenerInstancia()->obtenerPdo()->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public function listar(Request $request) {
        $usuario = $this->extraerUsuarioDelToken($request);
        if (!$usuario || !isset($usuario['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'datos' => $this->ventas->obtenerPorUsuario($usuario['id'])], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function obtenerDetalle(Request $request, $id) {
        $usuario = $this->extraerUsuarioDelToken($request);
        if (!$usuario || !isset($usuario['id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $venta = $this->ventas->buscarPorId($id);
        if (!$venta) {
            http_response_code(404);
            echo json_encode(['error' => 'No encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        if ($venta['id_usuario'] != $usuario['id']) {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        http_response_code(200);
        echo json_encode([
            'ok' => true, 
            'venta' => $venta, 
            'detalles' => $this->ventas->obtenerDetalles($id),
            'pagos' => $this->ventaPagos->traerPagosPorVenta($id)
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Método privado para extraer datos del usuario del token
    private function extraerUsuarioDelToken(Request $request): ?array {
        $token = $request->getAttribute("token");
        if (!$token) {
            return null;
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
        
        if (!$data) {
            return null;
        }
        
        return [
            'id' => is_object($data) ? (isset($data->id_usuario) ? $data->id_usuario : null) : (isset($data['id_usuario']) ? $data['id_usuario'] : null),
            'email' => is_object($data) ? (isset($data->email) ? $data->email : null) : (isset($data['email']) ? $data['email'] : null),
            'nombre' => is_object($data) ? (isset($data->nombre) ? $data->nombre : null) : (isset($data['nombre']) ? $data['nombre'] : null),
            'rol' => is_object($data) ? (isset($data->id_rol) ? $data->id_rol : null) : (isset($data['id_rol']) ? $data['id_rol'] : null)
        ];
    }
}
