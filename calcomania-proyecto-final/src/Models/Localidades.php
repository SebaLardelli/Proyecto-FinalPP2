<?php

namespace App\Models;

use PDO;

class Localidades {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function buscarPorCodigoPostal($codigoPostal) {
        $stmt = $this->pdo->prepare("SELECT codigo_postal FROM localidades WHERE codigo_postal = :cp LIMIT 1");
        $stmt->execute([':cp' => $codigoPostal]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($codigoPostal, $nombreLocalidad) {
        // Obtener el nombre correcto de la columna dinámicamente
        $stmt = $this->pdo->query("DESCRIBE localidades");
        $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $columnaNombre = null;
        
        // Buscar columna que contenga "nombre" o "localidad" (excluyendo codigo_postal)
        foreach ($columnas as $col) {
            if ($col !== 'codigo_postal' && (stripos($col, 'nombre') !== false || stripos($col, 'localidad') !== false)) {
                $columnaNombre = $col;
                break;
            }
        }
        
        if (!$columnaNombre) {
            throw new \Exception('Error en la estructura de la base de datos: no se encontró la columna para el nombre de localidad');
        }
        
        $sql = "INSERT INTO localidades (codigo_postal, {$columnaNombre}) VALUES (:cp, :nombre)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':cp' => $codigoPostal, ':nombre' => $nombreLocalidad]);
    }

    public function existe($codigoPostal) {
        $resultado = $this->buscarPorCodigoPostal($codigoPostal);
        return $resultado !== false;
    }
}

?>

