<?php

namespace App\Models;

use PDO;

class Ventas {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crear($id_usuario, $id_carrito, $importe_total, $id_punto_retiro) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ventas (id_usuario, id_carrito, fecha_venta, importe_total, id_estado_v, id_punto_retiro)
            VALUES (:usuario, :carrito, NOW(), :total, 1, :punto)
        ");
        
        if ($stmt->execute([
            ':usuario' => $id_usuario, 
            ':carrito' => $id_carrito, 
            ':total' => $importe_total, 
            ':punto' => $id_punto_retiro
        ])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    // IMPORTANTE: Este es el ÚNICO lugar donde se descuenta stock del producto.
    // El stock NO se descuenta al agregar productos al carrito, solo cuando se completa la venta.
    public function pasarItemsDelCarrito($id_venta, $id_carrito) {
        // Primero obtener todos los items del carrito para descontar el stock
        // Agrupar por id_producto para evitar descontar múltiples veces si hay items duplicados
        $stmt = $this->pdo->prepare("
            SELECT id_producto, SUM(cantidad) as cantidad_total
            FROM detalle_venta 
            WHERE id_carrito = :carrito AND id_venta IS NULL
            GROUP BY id_producto
        ");
        $stmt->execute([':carrito' => $id_carrito]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // AQUÍ SE DESCUENTA EL STOCK: solo cuando se completa la venta, no al agregar al carrito
        foreach ($items as $item) {
            $stmt = $this->pdo->prepare("UPDATE producto SET stock = stock - :cantidad WHERE id_producto = :id");
            $stmt->execute([
                ':cantidad' => $item['cantidad_total'],
                ':id' => $item['id_producto']
            ]);
            
            // Actualizar el estado del producto basado en el nuevo stock (stock > 0 = disponible (1), stock = 0 = agotado (2))
            $stmt = $this->pdo->prepare("
                UPDATE producto 
                SET id_estado_p = CASE 
                    WHEN stock > 0 THEN 1 
                    ELSE 2 
                END
                WHERE id_producto = :id
            ");
            $stmt->execute([':id' => $item['id_producto']]);
        }
        
        // Asociar los items del carrito con la venta
        $stmt = $this->pdo->prepare("
            UPDATE detalle_venta 
            SET id_venta = :venta
            WHERE id_carrito = :carrito AND id_venta IS NULL
        ");
        return $stmt->execute([':venta' => $id_venta, ':carrito' => $id_carrito]);
    }

    public function agregarPago($id_venta, $id_metodo_pago, $monto) {
        $stmt = $this->pdo->prepare("INSERT INTO venta_pagos (id_venta, id_metodo_pago, monto) VALUES (:venta, :metodo, :monto)");
        return $stmt->execute([':venta' => $id_venta, ':metodo' => $id_metodo_pago, ':monto' => $monto]);
    }

    public function obtenerPorUsuario($id_usuario) {
        $stmt = $this->pdo->prepare("
            SELECT v.*, pr.nombre_punto, pr.direccion, ev.descripcion_v as estado
            FROM ventas v
            LEFT JOIN punto_retiro pr ON v.id_punto_retiro = pr.id_punto_retiro
            LEFT JOIN estado_v ev ON v.id_estado_v = ev.id_estado_venta
            WHERE v.id_usuario = :id
            ORDER BY v.fecha_venta DESC
        ");
        $stmt->execute([':id' => $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarTodas() {
        return $this->pdo->query("
            SELECT v.*, u.nombre_usuario, u.email, pr.nombre_punto, ev.descripcion_v as estado
            FROM ventas v
            INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
            LEFT JOIN punto_retiro pr ON v.id_punto_retiro = pr.id_punto_retiro
            LEFT JOIN estado_v ev ON v.id_estado_v = ev.id_estado_venta
            ORDER BY v.fecha_venta DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ventas WHERE id_venta = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function obtenerDetalles($id_venta) {
        $stmt = $this->pdo->prepare("
            SELECT dv.*, p.nombre_p, p.imagen_url
            FROM detalle_venta dv
            INNER JOIN producto p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = :id
        ");
        $stmt->execute([':id' => $id_venta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function actualizarEstado($id_venta, $id_estado) {
        $stmt = $this->pdo->prepare("UPDATE ventas SET id_estado_v = :estado WHERE id_venta = :id");
        return $stmt->execute([':estado' => $id_estado, ':id' => $id_venta]);
    }
}

?>