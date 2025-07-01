<?php

class Empresa {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crear($nombre, $ruc, $actividad, $usuario_id) {
        $sql = "INSERT INTO tbl_empresa (emp_nombre, emp_ruc, emp_actividad, emp_usuario_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre, $ruc, $actividad, $usuario_id]);
    }

    public function listarPorUsuario($usuario_id) {
        $sql = "SELECT * FROM tbl_empresa WHERE emp_usuario_id = ? AND emp_estado = 'activo'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarLogicamente($emp_id, $usuario_id) {
        $sql = "UPDATE tbl_empresa SET emp_estado = 'inactivo' WHERE emp_id = ? AND emp_usuario_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$emp_id, $usuario_id]);
    }
}