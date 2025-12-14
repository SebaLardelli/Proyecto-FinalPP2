<?php

namespace App\Models;

use PDO;

class PuntosVenta {
    
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crear($datos) {
        try {
            // Validar y procesar codigo_postal
            $codigoPostal = null;
            if (isset($datos['codigo_postal']) && !empty(trim($datos['codigo_postal']))) {
                $codigoPostal = trim($datos['codigo_postal']);
                
                // Verificar que el código postal exista en localidades
                $stmtCheck = $this->pdo->prepare("SELECT codigo_postal FROM localidades WHERE codigo_postal = :cp LIMIT 1");
                $stmtCheck->execute([':cp' => $codigoPostal]);
                if (!$stmtCheck->fetch()) {
                    throw new \Exception('El código postal no existe en la base de datos. Debe existir en la tabla localidades.');
                }
            }
            
            // Usar punto_retiro como tabla base (la tabla punto_venta no existe)
            // Mapear campos: nombre_punto -> nombre_punto, direccion -> direccion
            $stmt = $this->pdo->prepare("
                INSERT INTO punto_retiro (nombre_punto, direccion, horarios, codigo_postal) 
                VALUES (:nombre_punto, :direccion, :horarios, :codigo_postal)
            ");
            $result = $stmt->execute([
                ':nombre_punto' => isset($datos['nombre_punto']) ? trim($datos['nombre_punto']) : '',
                ':direccion' => isset($datos['direccion']) ? trim($datos['direccion']) : '',
                ':horarios' => isset($datos['horarios']) ? trim($datos['horarios']) : '',
                ':codigo_postal' => $codigoPostal
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception('Error en base de datos: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'Error desconocido'));
            }
            
            return $result;
        } catch (\PDOException $e) {
            // Capturar errores de foreign key específicamente
            if ($e->getCode() == 23000) {
                throw new \Exception('El código postal no existe en la base de datos. Debe existir en la tabla localidades.');
            }
            error_log('Error en PuntosVenta::crear: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            error_log('Error en PuntosVenta::crear: ' . $e->getMessage());
            throw $e;
        }
    }

    public function actualizar($id, $datos) {
        try {
            $updateFields = [];
            $params = [':id_punto_retiro' => $id];

            if (isset($datos['nombre_punto'])) {
                $updateFields[] = 'nombre_punto = :nombre_punto';
                $params[':nombre_punto'] = trim($datos['nombre_punto']);
            }
            if (isset($datos['direccion'])) {
                $updateFields[] = 'direccion = :direccion';
                $params[':direccion'] = trim($datos['direccion']);
            }
            if (isset($datos['horarios'])) {
                $updateFields[] = 'horarios = :horarios';
                $params[':horarios'] = trim($datos['horarios']);
            }
            if (isset($datos['codigo_postal'])) {
                $codigoPostal = null;
                if (!empty(trim($datos['codigo_postal']))) {
                    $codigoPostal = trim($datos['codigo_postal']);
                    
                    // Verificar que el código postal exista en localidades
                    $stmtCheck = $this->pdo->prepare("SELECT codigo_postal FROM localidades WHERE codigo_postal = :cp LIMIT 1");
                    $stmtCheck->execute([':cp' => $codigoPostal]);
                    if (!$stmtCheck->fetch()) {
                        throw new \Exception('El código postal no existe en la base de datos. Debe existir en la tabla localidades.');
                    }
                }
                $updateFields[] = 'codigo_postal = :codigo_postal';
                $params[':codigo_postal'] = $codigoPostal;
            }

            if (empty($updateFields)) {
                return false;
            }

            $stmt = $this->pdo->prepare("UPDATE punto_retiro SET " . implode(', ', $updateFields) . " WHERE id_punto_retiro = :id_punto_retiro");
            $result = $stmt->execute($params);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception('Error en base de datos: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'Error desconocido'));
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log('Error en PuntosVenta::actualizar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function eliminar($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM punto_retiro WHERE id_punto_retiro = :id_punto_retiro");
            $result = $stmt->execute([':id_punto_retiro' => $id]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception('Error en base de datos: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'Error desconocido'));
            }
            
            // Verificar si realmente se eliminó una fila
            if ($stmt->rowCount() === 0) {
                throw new \Exception('Punto de venta no encontrado');
            }
            
            return true;
        } catch (\Exception $e) {
            error_log('Error en PuntosVenta::eliminar: ' . $e->getMessage());
            throw $e;
        }
    }

    public function listar() {
        return $this->pdo->query("SELECT * FROM punto_retiro ORDER BY nombre_punto ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM punto_retiro WHERE id_punto_retiro = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
