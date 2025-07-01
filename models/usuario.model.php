<?php
require_once __DIR__ . '/../config/db.php';

class Usuario
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function registrar($nombre, $correo, $contrasena)
    {
        $sql = "INSERT INTO tbl_usuario 
                (usu_nombre, usu_correo, usu_contrasena, usu_estado) 
                VALUES (:nombre, :correo, :contrasena, 'pendiente')";
        $stmt = $this->pdo->prepare($sql);
        $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':contrasena' => $hashedPassword
        ]);
    }

    public function existeCorreo($correo)
    {
        $sql = "SELECT usu_id FROM tbl_usuario WHERE usu_correo = :correo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        return $stmt->fetch() !== false;
    }

    public function login($correo, $contrasena)
    {
        $sql = "SELECT * FROM tbl_usuario WHERE usu_correo = :correo AND usu_estado = 'aprobado' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($contrasena, $usuario['usu_contrasena'])) {
            return $usuario;
        }

        return false;
    }

    public function obtenerTodosLosUsuarios()
    {
        $sql = "SELECT * FROM tbl_usuario ORDER BY usu_id ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoYRol($id, $estado, $rol)
    {
        $sql = "UPDATE tbl_usuario 
            SET usu_estado = :estado, usu_rol = :rol 
            WHERE usu_id = :id";

        // Si el estado no es 'aprobado', se limpia el rol
        if ($estado !== 'aprobado') {
            $rol = null;
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':estado' => $estado,
            ':rol' => $rol,
            ':id' => $id
        ]);
    }

    public function obtenerUsuariosPorEstado($estado = null)
    {
        if ($estado) {
            $sql = "SELECT * FROM tbl_usuario WHERE usu_estado = :estado ORDER BY usu_id ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':estado' => $estado]);
        } else {
            $sql = "SELECT * FROM tbl_usuario ORDER BY usu_id ASC";
            $stmt = $this->pdo->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
