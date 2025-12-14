<?php

namespace App\Models;

use PDO;

class VentaPagos {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crearVentaPago($id_venta, $id_metodo_pago, $monto): bool {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO venta_pagos (id_venta, id_metodo_pago, monto) 
                VALUES (:venta, :metodo, :monto)
            ");
            return $stmt->execute([
                ':venta' => $id_venta, 
                ':metodo' => $id_metodo_pago, 
                ':monto' => $monto
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function traerPagosPorVenta(int $id_venta): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT vp.*, mp.descripcion_mp
                FROM venta_pagos vp
                INNER JOIN metodo_pago mp ON vp.id_metodo_pago = mp.id_metodo_pago
                WHERE vp.id_venta = :venta
                ORDER BY vp.id_venta_pago
            ");
            $stmt->execute([':venta' => $id_venta]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function traerVentaPagoPorId(int $id_venta_pago): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM venta_pagos WHERE id_venta_pago = :id");
            $stmt->execute([':id' => $id_venta_pago]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }
}
