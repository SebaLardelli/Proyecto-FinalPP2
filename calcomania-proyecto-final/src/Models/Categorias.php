<?php

namespace App\Models;

use PDO;

class Categorias {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crear($nombre, $descripcion) {
        $stmt = $this->pdo->prepare("INSERT INTO categoria (nombre_c, descripcion_c) VALUES (:nombre, :desc)");
        return $stmt->execute([':nombre' => $nombre, ':desc' => $descripcion]);
    }

    public function actualizar($id, $nombre, $descripcion) {
        $stmt = $this->pdo->prepare("UPDATE categoria SET nombre_c = :nombre, descripcion_c = :desc WHERE id_categoria = :id");
        return $stmt->execute([':id' => $id, ':nombre' => $nombre, ':desc' => $descripcion]);
    }

    public function eliminar($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\PDOException $e) {
            // Si es un error de foreign key constraint
            if ($e->getCode() == '23000') {
                throw new \Exception('No se puede eliminar esta categoría porque hay productos asociados a ella');
            }
            throw $e;
        }
    }

    public function listar() {
        if (!$this->pdo) {
            throw new \Exception('Error de conexión a la base de datos');
        }
        return $this->pdo->query("SELECT * FROM categoria ORDER BY nombre_c ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE id_categoria = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

?>