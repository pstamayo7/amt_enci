<?php
// /models/cif.model.php

class CifModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crear($datos) {
        $sql = "INSERT INTO tbl_cif_aplicados (cif_tasa_utilizada, cif_unidades_producidas, cif_total_aplicado, pro_id, emp_id, usr_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $datos['tasa'],
            $datos['unidades'],
            $datos['total'],
            $datos['pro_id'], // Corregido
            $datos['emp_id'],
            $datos['usuario_id']
        ]);
    }

    public function listarPorFechaYEmpresa($emp_id, $fecha_inicio, $fecha_fin, $usr_id) {
        $sql = "SELECT c.*, p.pro_nombre 
                FROM tbl_cif_aplicados c
                JOIN tbl_producto p ON c.pro_id = p.pro_id
                WHERE c.emp_id = ? 
                AND c.usr_id = ?
                AND DATE(c.cif_fecha_generacion) BETWEEN ? AND ?
                ORDER BY c.cif_fecha_generacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$emp_id, $usr_id, $fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll();
    }
}
?>