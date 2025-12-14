<?php

namespace App\Models;

use PDO;

class Productos {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crear($datos) {
        $stmt = $this->pdo->prepare("
            INSERT INTO producto (nombre_p, descripcion_p, precio, stock, id_estado_p, tamanio, id_categoria, imagen_url, id_tematica)
            VALUES (:nombre, :descripcion, :precio, :stock, :estado, :tamanio, :categoria, :imagen, :tematica)
        ");
        return $stmt->execute($datos);
    }

    public function actualizar($id, $datos) {
        $stmt = $this->pdo->prepare("
            UPDATE producto 
            SET nombre_p = :nombre, descripcion_p = :descripcion, precio = :precio, stock = :stock,
                id_estado_p = :estado, tamanio = :tamanio, id_categoria = :categoria,
                imagen_url = :imagen, id_tematica = :tematica
            WHERE id_producto = :id
        ");
        $datos[':id'] = $id;
        return $stmt->execute($datos);
    }

    public function eliminar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM producto WHERE id_producto = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function listar() {
        return $this->listarConFiltros(null, null);
    }

    public function listarConFiltros($categoria = null, $tematica = null) {
        try {
            if (!$this->pdo) {
                throw new \Exception('Error de conexión a la base de datos');
            }
            
            // Mostrar productos disponibles (id_estado_p = 1) y agotados (id_estado_p = 2) para que los agotados se muestren pero no se puedan agregar
            $sql = "
                SELECT p.*, c.nombre_c as nombre_categoria, t.nombre_t 
                FROM producto p 
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
                LEFT JOIN tematica t ON p.id_tematica = t.id_tematica
                WHERE p.id_estado_p IN (1, 2)
            ";
            
            $params = [];
            
            // Agregar filtro por categoría si se especifica
            if ($categoria !== null && $categoria !== '') {
                $sql .= " AND p.id_categoria = :categoria";
                $params[':categoria'] = $categoria;
            }
            
            // Agregar filtro por temática si se especifica
            if ($tematica !== null && $tematica !== '') {
                $sql .= " AND p.id_tematica = :tematica";
                $params[':tematica'] = $tematica;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('ProductosModel::listarConFiltros - Productos encontrados: ' . count($resultados));
            if (count($resultados) > 0) {
                error_log('Ejemplo de producto: ID=' . $resultados[0]['id_producto'] . ', Nombre=' . $resultados[0]['nombre_p'] . ', Stock=' . $resultados[0]['stock'] . ', Estado=' . $resultados[0]['id_estado_p']);
            }
            return $resultados;
        } catch (\Exception $e) {
            error_log('Error en listarConFiltros: ' . $e->getMessage());
            throw $e;
        }
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM producto WHERE id_producto = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorCategoria($id_categoria) {
        return $this->listarConFiltros($id_categoria, null);
    }

    public function buscarPorTematica($id_tematica) {
        return $this->listarConFiltros(null, $id_tematica);
    }

    // Determina el estado del producto basado en el stock: stock > 0 = disponible (1), stock = 0 = agotado (2)
    private function calcularEstadoPorStock($stock) {
        return ((int)$stock > 0) ? 1 : 2;
    }

    public function actualizarStock($id, $cantidad) {
        // Primero actualizar el stock
        $stmt = $this->pdo->prepare("UPDATE producto SET stock = stock + :cantidad WHERE id_producto = :id");
        $result = $stmt->execute([':id' => $id, ':cantidad' => $cantidad]);
        
        if ($result) {
            // Obtener el nuevo stock y actualizar el estado automáticamente
            $producto = $this->buscarPorId($id);
            if ($producto) {
                $nuevoStock = (int)($producto['stock'] ?? 0);
                $nuevoEstado = $this->calcularEstadoPorStock($nuevoStock);
                $stmt = $this->pdo->prepare("UPDATE producto SET id_estado_p = :estado WHERE id_producto = :id");
                $stmt->execute([':id' => $id, ':estado' => $nuevoEstado]);
            }
        }
        
        return $result;
    }


    public function listarCompleto() {
        try {
            if (!$this->pdo) {
                throw new \Exception('Error de conexión a la base de datos');
            }
            
            $sql = "
                SELECT p.*, c.nombre_c as nombre_categoria, t.nombre_t 
                FROM producto p 
                LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
                LEFT JOIN tematica t ON p.id_tematica = t.id_tematica
                ORDER BY p.id_producto DESC
            ";
            $resultado = $this->pdo->query($sql);
            if ($resultado === false) {
                error_log('Error en query listarCompleto: ' . implode(', ', $this->pdo->errorInfo()));
                return [];
            }
            return $resultado->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error en listarCompleto: ' . $e->getMessage());
            return [];
        }
    }

    public function crearConImagen($datos, $archivoImagen = null) {
        try {
            $imagen_url = '';
            
            if ($archivoImagen && $archivoImagen['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/Uploads/ImagenProducto/';
                $ext = strtolower(pathinfo($archivoImagen['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('producto_', true) . '.' . $ext;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($archivoImagen['tmp_name'], $uploadPath)) {
                    $imagen_url = '/calcomania-proyecto-final/Uploads/ImagenProducto/' . $fileName;
                }
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO producto (nombre_p, tamanio, precio, stock, descripcion_p, id_categoria, imagen_url, id_tematica, id_estado_p) 
                VALUES (:nombre, :tamanio, :precio, :stock, :descripcion, :categoria, :imagen, :tematica, :estado)
            ");
            
            // Calcular el estado automáticamente basado en el stock (stock > 0 = disponible (1), stock = 0 = agotado (2))
            $stock = (int)($datos['stock'] ?? 0);
            $estado = $this->calcularEstadoPorStock($stock);
            
            $result = $stmt->execute([
                ':nombre' => $datos['nombre_p'],
                ':tamanio' => isset($datos['tamanio']) ? $datos['tamanio'] : '',
                ':precio' => $datos['precio'],
                ':stock' => $stock,
                ':descripcion' => isset($datos['descripcion_p']) ? $datos['descripcion_p'] : '',
                ':categoria' => $datos['id_categoria'],
                ':imagen' => $imagen_url,
                ':tematica' => isset($datos['id_tematica']) ? $datos['id_tematica'] : null,
                ':estado' => $estado
            ]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new \Exception('Error en base de datos: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'Error desconocido'));
            }
            
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function actualizarConImagen($id, $datos, $archivoImagen = null) {
        try {
            $imagen_url = '';
            
            // Obtener imagen actual para eliminarla si se sube una nueva
            $productoActual = $this->buscarPorId($id);
            $imagenAnterior = isset($productoActual['imagen_url']) ? $productoActual['imagen_url'] : '';
            
            if ($archivoImagen && $archivoImagen['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/Uploads/ImagenProducto/';
                
                
                $ext = strtolower(pathinfo($archivoImagen['name'], PATHINFO_EXTENSION));
                $fileName = uniqid('producto_', true) . '.' . $ext;
                $uploadPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($archivoImagen['tmp_name'], $uploadPath)) {
                    $imagen_url = '/calcomania-proyecto-final/Uploads/ImagenProducto/' . $fileName;
                    
                    // Eliminar imagen anterior
                    if (!empty($imagenAnterior)) {
                        $imagenAnteriorPath = str_replace(['/calcomania-proyecto-final/'], '', $imagenAnterior);
                        $imagenAnteriorPath = __DIR__ . '/../../public' . $imagenAnteriorPath;
                        if (file_exists($imagenAnteriorPath)) {
                            unlink($imagenAnteriorPath);
                        }
                    }
                }
            }
            
            // Calcular el estado automáticamente basado en el stock (stock > 0 = disponible (1), stock = 0 = agotado (2))
            $stock = (int)($datos['stock'] ?? 0);
            $estado = $this->calcularEstadoPorStock($stock);
            
            // Actualizar con nueva imagen, verifica si hay datos, si no es null
            if (!empty($imagen_url)) {
                $stmt = $this->pdo->prepare("
                    UPDATE producto 
                    SET nombre_p = :nombre, tamanio = :tamanio, precio = :precio, stock = :stock, 
                        descripcion_p = :descripcion, id_categoria = :categoria, imagen_url = :imagen, id_tematica = :tematica, id_estado_p = :estado
                    WHERE id_producto = :id
                ");
                $result = $stmt->execute([
                    ':nombre' => $datos['nombre_p'],
                    ':tamanio' => $datos['tamanio'],
                    ':precio' => $datos['precio'],
                    ':stock' => $stock,
                    ':descripcion' => $datos['descripcion_p'],
                    ':categoria' => $datos['id_categoria'],
                    ':imagen' => $imagen_url,
                    ':tematica' => isset($datos['id_tematica']) ? $datos['id_tematica'] : null,
                    ':estado' => $estado,
                    ':id' => $id
                ]);
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    throw new \Exception('Error en base de datos: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'Error desconocido'));
                }
                return $result;
            } else {
                // Calcular el estado automáticamente basado en el stock (stock > 0 = disponible (1), stock = 0 = agotado (2))
                $stock = (int)($datos['stock'] ?? 0);
                $estado = $this->calcularEstadoPorStock($stock);
                
                // Actualizar sin cambiar imagen
                $stmt = $this->pdo->prepare("
                    UPDATE producto 
                    SET nombre_p = :nombre, tamanio = :tamanio, precio = :precio, stock = :stock, 
                        descripcion_p = :descripcion, id_categoria = :categoria, id_tematica = :tematica, id_estado_p = :estado
                    WHERE id_producto = :id
                ");
                $result = $stmt->execute([
                    ':nombre' => $datos['nombre_p'],
                    ':tamanio' => $datos['tamanio'],
                    ':precio' => $datos['precio'],
                    ':stock' => $stock,
                    ':descripcion' => $datos['descripcion_p'],
                    ':categoria' => $datos['id_categoria'],
                    ':tematica' => isset($datos['id_tematica']) ? $datos['id_tematica'] : null,
                    ':estado' => $estado,
                    ':id' => $id
                ]);
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    throw new \Exception('Error en base de datos: ' . (isset($errorInfo[2]) ? $errorInfo[2] : 'Error desconocido'));
                }
                return $result;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}

?>