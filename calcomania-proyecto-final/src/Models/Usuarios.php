<?php

namespace App\Models;

use PDO;

class Usuarios {

    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function crear($datos) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO usuarios (nombre_usuario, apellido, email, contrasena_hash, direccion, telefono, codigo_postal, cuenta_verificada, fecha_registro, id_rol)
                VALUES (:nombre, :apellido, :email, :pass, :direccion, :telefono, :cp, :verificada, :fecha, :rol)
            ");
            $resultado = $stmt->execute($datos);
            if (!$resultado) {
                $errorInfo = $stmt->errorInfo();
                throw new \PDOException('Error al crear usuario: ' . ($errorInfo[2] ?? 'Error desconocido'));
            }
            return $resultado;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public function buscarPorEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function listar() {
        return $this->pdo->query("SELECT id_usuario, nombre_usuario, apellido, email, telefono, direccion, codigo_postal, cuenta_verificada, fecha_registro, id_rol FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $datos) {
        $campos = [];
        $params = [':id' => $id];
        
        foreach ($datos as $key => $value) {
            $campos[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id_usuario = :id";
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function actualizarPassword($id, $hash) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET contrasena_hash = :hash WHERE id_usuario = :id");
        return $stmt->execute([':hash' => $hash, ':id' => $id]);
    }

    public function verificar($email) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET cuenta_verificada = 1 WHERE email = :email");
        return $stmt->execute([':email' => $email]);
    }

    public function eliminar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        return $stmt->execute([':id' => $id]);
    }
}

?>