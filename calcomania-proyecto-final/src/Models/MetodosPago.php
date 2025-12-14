<?php

namespace App\Models;

use PDO;

class MetodosPago {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crearMetodoPago(string $descripcion_mp): bool {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO metodo_pago (descripcion_mp) VALUES (:descripcion_mp)");
            return $stmt->execute([':descripcion_mp' => $descripcion_mp]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function actualizarMetodoPago(int $id_metodo_pago, string $descripcion_mp): bool {
        try {
            $stmt = $this->pdo->prepare("UPDATE metodo_pago SET descripcion_mp = :descripcion_mp WHERE id_metodo_pago = :id_metodo_pago");
            return $stmt->execute([':descripcion_mp' => $descripcion_mp, ':id_metodo_pago' => $id_metodo_pago]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function eliminarMetodoPago(int $id_metodo_pago): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM metodo_pago WHERE id_metodo_pago = :id_metodo_pago");
            return $stmt->execute([':id_metodo_pago' => $id_metodo_pago]);
        } catch (\PDOException $e) {
            // Si es foreign key constraint
            if ($e->getCode() == '23000') {
                throw new \Exception('No se puede eliminar este método de pago porque hay ventas asociadas');
            }
            return false;
        }
    }

    public function traerMetodosPago(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM metodo_pago");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function traerMetodoPagoPorId(int $id_metodo_pago): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM metodo_pago WHERE id_metodo_pago = :id_metodo_pago");
            $stmt->execute([':id_metodo_pago' => $id_metodo_pago]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }
}
