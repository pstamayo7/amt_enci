<?php

class Empleado
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function crear($nombre, $cedula, $usuario_id)
    {
        // Verificar si ya existe la cédula
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_empleado WHERE empl_cedula = ?");
        $check->execute([$cedula]);
        if ($check->fetchColumn() > 0) {
            return false; // Ya existe
        }

        // Insertar nuevo empleado con sue_id = 1 (sueldo básico general)
        $sql = "INSERT INTO tbl_empleado (empl_nombre, empl_cedula, empl_usuario_id, sue_id)
            VALUES (?, ?, ?, 1)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre, $cedula, $usuario_id]);
    }

   public function listarPorEmpresa($emp_id)
{
    $sql = "SELECT e.*, em.emp_nombre, s.sue_valor
            FROM tbl_empleado e
            LEFT JOIN tbl_empresa em ON e.emp_id = em.emp_id
            LEFT JOIN tbl_sueldo s ON e.sue_id = s.sue_id
            WHERE e.emp_id = ?";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$emp_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



    public function listarPorUsuario($usuario_id)
    {
        $sql = "SELECT e.*, em.emp_nombre, s.sue_valor
            FROM tbl_empleado e
            LEFT JOIN tbl_empresa em ON e.emp_id = em.emp_id
            LEFT JOIN tbl_sueldo s ON e.sue_id = s.sue_id
            WHERE e.empl_usuario_id = ? AND e.empl_estado != 'inactivo'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  

    public function asignarEmpresaYActivar($empl_id, $emp_id, $usuario_id)
    {
        $sql = "UPDATE tbl_empleado SET emp_id = ?, empl_estado = 'activo'
                WHERE empl_id = ? AND empl_usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$emp_id, $empl_id, $usuario_id]);
    }

    public function eliminarLogicamente($empl_id, $usuario_id)
    {
        $sql = "UPDATE tbl_empleado SET empl_estado = 'inactivo'
                WHERE empl_id = ? AND empl_usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$empl_id, $usuario_id]);
    }

    public function obtenerActivoPorUsuario($usuario_id)
    {
        $sql = "SELECT e.*, em.emp_nombre
            FROM tbl_empleado e
            JOIN tbl_empresa em ON e.emp_id = em.emp_id
            WHERE e.empl_usuario_id = ? AND e.empl_estado = 'activo'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function listarPorEmpresaUsuario($usuario_id, $emp_id)
    {
        $sql = "SELECT * FROM tbl_empleado
            WHERE empl_usuario_id = ? AND emp_id = ? AND empl_estado = 'activo'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id, $emp_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


public function actualizarSueldo($empl_id, $sue_id)
    {
        $sql = "UPDATE tbl_empleado SET sue_id = ? WHERE empl_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$sue_id, $empl_id]);
    }

}
