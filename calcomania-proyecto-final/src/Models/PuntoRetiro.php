<?php

namespace App\Models;

use PDO;

class PuntoRetiro {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function traerPuntosRetiro(): array {
        try {
            $stmt = $this->pdo->query("SELECT * FROM punto_retiro");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function traerPuntoRetiroPorId(int $id_punto_retiro): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM punto_retiro WHERE id_punto_retiro = :id_punto_retiro");
            $stmt->execute([':id_punto_retiro' => $id_punto_retiro]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (\PDOException $e) {
            return null;
        }
    }
}
