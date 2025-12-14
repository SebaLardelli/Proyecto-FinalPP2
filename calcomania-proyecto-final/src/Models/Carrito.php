<?php

namespace App\Models;

use PDO;

class Carrito {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Obtiene el carrito activo sin crearlo (retorna null si no existe)
    public function obtenerActivo($id_usuario) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM carrito WHERE id_usuario = :id AND id_estado_car = 1 LIMIT 1");
            $stmt->execute([':id' => $id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    // Crea un nuevo carrito activo (solo se llama al agregar un producto)
    public function crearActivo($id_usuario) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO carrito (id_usuario, fecha_creacion, fecha_ultima_actualizacion, importe_total, id_estado_car)
                VALUES (:id, NOW(), NOW(), 0, 1)
            ");
            $stmt->execute([':id' => $id_usuario]);
            $id_carrito = $this->pdo->lastInsertId();
            if ($id_carrito) {
                return $this->buscarPorId($id_carrito);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }


    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM carrito WHERE id_carrito = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // IMPORTANTE: Este método NO descuenta stock. Solo verifica disponibilidad y agrega el producto al carrito.
    // El stock SOLO se descuenta cuando se completa la venta en Ventas::pasarItemsDelCarrito()
    public function agregarProducto($id_carrito, $id_producto, $cantidad, $precio, $tamano = null) {
        // Obtener stock del producto SOLO para verificar disponibilidad (NO se descuenta aquí)
        $stmt = $this->pdo->prepare("SELECT stock FROM producto WHERE id_producto = :id");
        $stmt->execute([':id' => $id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            throw new \Exception('Producto no encontrado');
        }
        
        if ($tamano === null) {
            $stmt = $this->pdo->prepare("
                SELECT cantidad FROM detalle_venta 
                WHERE id_carrito = :carrito AND id_producto = :producto AND tamano IS NULL AND id_venta IS NULL
            ");
            $stmt->execute([':carrito' => $id_carrito, ':producto' => $id_producto]);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT cantidad FROM detalle_venta 
                WHERE id_carrito = :carrito AND id_producto = :producto AND tamano = :tamano AND id_venta IS NULL
            ");
            $stmt->execute([':carrito' => $id_carrito, ':producto' => $id_producto, ':tamano' => $tamano]);
        }
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);

        $nueva_cant = $existe ? $existe['cantidad'] + $cantidad : $cantidad;
        
        // Verificar que haya stock suficiente (NO se descuenta, solo se valida)
        if ($nueva_cant > $producto['stock']) {
            throw new \Exception("Stock insuficiente");
        }

        if ($existe) {
            if ($tamano === null) {
                $stmt = $this->pdo->prepare("
                    UPDATE detalle_venta 
                    SET cantidad = :cant, importe_total_detalle = :cant * precio_unitario
                    WHERE id_carrito = :carrito AND id_producto = :producto AND tamano IS NULL AND id_venta IS NULL
                ");
                $result = $stmt->execute([':cant' => $nueva_cant, ':carrito' => $id_carrito, ':producto' => $id_producto]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE detalle_venta 
                    SET cantidad = :cant, importe_total_detalle = :cant * precio_unitario
                    WHERE id_carrito = :carrito AND id_producto = :producto AND tamano = :tamano AND id_venta IS NULL
                ");
                $result = $stmt->execute([':cant' => $nueva_cant, ':carrito' => $id_carrito, ':producto' => $id_producto, ':tamano' => $tamano]);
            }
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO detalle_venta (id_venta, id_carrito, id_producto, cantidad, precio_unitario, importe_total_detalle, tamano)
                VALUES (NULL, :carrito, :producto, :cant, :precio, :total, :tamano)
            ");
            $result = $stmt->execute([
                ':carrito' => $id_carrito,
                ':producto' => $id_producto,
                ':cant' => $nueva_cant,
                ':precio' => $precio,
                ':total' => $nueva_cant * $precio,
                ':tamano' => $tamano
            ]);
        }
        
        if ($result) $this->actualizarTotales($id_carrito);
        return $result;
    }

    public function actualizarCantidadPorDetalle($id_detalle, $cantidad) {
        if ($cantidad <= 0) return $this->eliminarPorDetalle($id_detalle);
        
        $stmt = $this->pdo->prepare("
            SELECT dv.id_producto, p.stock, p.nombre_p 
            FROM detalle_venta dv 
            INNER JOIN producto p ON dv.id_producto = p.id_producto 
            WHERE dv.id_fila = :id AND dv.id_venta IS NULL
        ");
        $stmt->execute([':id' => $id_detalle]);
        $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$detalle) {
            throw new \Exception('Producto no encontrado en el carrito');
        }
        
        if ($cantidad > $detalle['stock']) {
            throw new \Exception("Stock insuficiente");
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE detalle_venta 
            SET cantidad = :cant, importe_total_detalle = :cant * precio_unitario
            WHERE id_fila = :id AND id_venta IS NULL
        ");
        $result = $stmt->execute([':cant' => $cantidad, ':id' => $id_detalle]);
        
        if ($result) {
            $stmt = $this->pdo->prepare("SELECT id_carrito FROM detalle_venta WHERE id_fila = :id");
            $stmt->execute([':id' => $id_detalle]);
            $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($carrito) {
                $this->actualizarTotales($carrito['id_carrito']);
            }
        }
        return $result;
    }

    public function eliminarPorDetalle($id_detalle) {
        try {
            $stmt = $this->pdo->prepare("SELECT id_carrito, id_producto FROM detalle_venta WHERE id_fila = :id AND id_venta IS NULL");
            $stmt->execute([':id' => $id_detalle]);
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$detalle || !isset($detalle['id_carrito'])) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM detalle_venta WHERE id_fila = :id AND id_venta IS NULL");
            $result = $stmt->execute([':id' => $id_detalle]);
            
            if (!$result) {
                return false;
            }
            
            $rowsAffected = $stmt->rowCount();
            
            if ($rowsAffected > 0) {
                $this->actualizarTotales($detalle['id_carrito']);
            }
            
            return $rowsAffected > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function actualizarCantidad($id_carrito, $id_producto, $cantidad, $tamano = null) {
        if ($cantidad <= 0) return $this->eliminarProducto($id_carrito, $id_producto, $tamano);
        
        $stmt = $this->pdo->prepare("SELECT stock, nombre_p FROM producto WHERE id_producto = :id");
        $stmt->execute([':id' => $id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            throw new \Exception('Producto no encontrado');
        }
        
        if ($cantidad > $producto['stock']) {
            throw new \Exception("Stock insuficiente");
        }
        
        if ($tamano === null || $tamano === '') {
            $stmt = $this->pdo->prepare("
                UPDATE detalle_venta 
                SET cantidad = :cant, importe_total_detalle = :cant * precio_unitario
                WHERE id_carrito = :carrito AND id_producto = :producto AND (tamano IS NULL OR tamano = '') AND id_venta IS NULL
            ");
            $result = $stmt->execute([':cant' => $cantidad, ':carrito' => $id_carrito, ':producto' => $id_producto]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE detalle_venta 
                SET cantidad = :cant, importe_total_detalle = :cant * precio_unitario
                WHERE id_carrito = :carrito AND id_producto = :producto AND TRIM(tamano) = :tamano AND id_venta IS NULL
            ");
            $result = $stmt->execute([':cant' => $cantidad, ':carrito' => $id_carrito, ':producto' => $id_producto, ':tamano' => trim($tamano)]);
        }
        
        if ($result) $this->actualizarTotales($id_carrito);
        return $result;
    }

    public function eliminarProducto($id_carrito, $id_producto, $tamano = null) {
        if ($tamano === null || $tamano === '') {
            $stmt = $this->pdo->prepare("DELETE FROM detalle_venta WHERE id_carrito = :carrito AND id_producto = :producto AND (tamano IS NULL OR tamano = '') AND id_venta IS NULL");
            $result = $stmt->execute([':carrito' => $id_carrito, ':producto' => $id_producto]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM detalle_venta WHERE id_carrito = :carrito AND id_producto = :producto AND TRIM(tamano) = :tamano AND id_venta IS NULL");
            $result = $stmt->execute([':carrito' => $id_carrito, ':producto' => $id_producto, ':tamano' => trim($tamano)]);
        }
        
        if ($result) $this->actualizarTotales($id_carrito);
        return $result;
    }

    public function vaciar($id_carrito) {
        $stmt = $this->pdo->prepare("DELETE FROM detalle_venta WHERE id_carrito = :id AND id_venta IS NULL");
        $result = $stmt->execute([':id' => $id_carrito]);
        
        if ($result) $this->actualizarTotales($id_carrito);
        return $result;
    }
//  COALESCE "retorna el valor no nulo"
    public function obtenerItems($id_carrito) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT dv.id_fila, dv.id_venta, dv.id_carrito, dv.id_producto, dv.cantidad, 
                       dv.precio_unitario, dv.importe_total_detalle, dv.tamano,
                       p.nombre_p, p.descripcion_p, p.stock, p.imagen_url, 
                       COALESCE(dv.tamano, p.tamanio) as tamano,          
                       p.tamanio as tamanio_producto,
                       c.nombre_c
                FROM detalle_venta dv
                INNER JOIN producto p ON dv.id_producto = p.id_producto
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                WHERE dv.id_carrito = :id AND dv.id_venta IS NULL
                ORDER BY dv.id_fila
            ");
            $stmt->execute([':id' => $id_carrito]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);            
            return is_array($items) ? $items : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function actualizarTotales($id_carrito) {
        // Calcular el total sumando todos los importes de los items del carrito
        $total = $this->calcularTotal($id_carrito);
        
        // Actualizar el carrito con el total calculado
        $stmt = $this->pdo->prepare("
            UPDATE carrito 
            SET importe_total = :total,
                fecha_ultima_actualizacion = NOW()
            WHERE id_carrito = :id
        ");
        return $stmt->execute([':id' => $id_carrito, ':total' => $total]);
    }

    public function calcularTotal($id_carrito) {
        try {
            // Sumar todos los importes de los items del carrito
            $stmt = $this->pdo->prepare("
                SELECT SUM(importe_total_detalle) as total
                FROM detalle_venta
                WHERE id_carrito = :id AND id_venta IS NULL
            ");
            $stmt->execute([':id' => $id_carrito]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no hay items o el resultado es NULL, retornar 0
            $total = isset($result['total']) && $result['total'] !== null ? (float)$result['total'] : 0.0;
            return $total;
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    // Finaliza el carrito cambiando su estado a 3 (Finalizado) preservando el importe_total
    // IMPORTANTE: Una vez que los items se asocian con la venta (id_venta ya no es NULL), calcularTotal() retornaría 0
    // Por eso debemos preservar explícitamente el importe_total que tenía antes de asociar los items
    public function finalizar($id_carrito, $importe_total_preservar = null) {
        // Si se proporciona un importe_total a preservar, usarlo; si no, obtenerlo del carrito actual
        if ($importe_total_preservar === null) {
            $stmt = $this->pdo->prepare("SELECT importe_total FROM carrito WHERE id_carrito = :id");
            $stmt->execute([':id' => $id_carrito]);
            $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
            $importe_total_preservar = $carrito['importe_total'] ?? 0;
        }
        
        // Actualizar el estado a 3 (Finalizado) y preservar explícitamente el importe_total
        $stmt = $this->pdo->prepare("
            UPDATE carrito 
            SET id_estado_car = 3, 
                fecha_ultima_actualizacion = NOW(),
                importe_total = :importe
            WHERE id_carrito = :id
        ");
        $result = $stmt->execute([':id' => $id_carrito, ':importe' => $importe_total_preservar]);
        
        // Verificar que se actualizó correctamente
        if ($result) {
            $stmt = $this->pdo->prepare("SELECT id_estado_car, importe_total FROM carrito WHERE id_carrito = :id");
            $stmt->execute([':id' => $id_carrito]);
            $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
            return $carrito && isset($carrito['id_estado_car']) && $carrito['id_estado_car'] == 3;
        }
        
        return false;
    }
}

?>