<?php
// /models/cif.model.php

class CifModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crear($datos) {
        $sql = "INSERT INTO tbl_cif_aplicados (
                    cif_fecha_aplicada, cif_unidades_producidas, pro_id, emp_id, usr_id, 
                    cif_tasa_utilizada, cif_total_aplicado, base_produccion_anual, 
                    costo_mat_indirectos, costo_mo_indirecta, costo_depreciacion, 
                    costo_seguros, costo_combustibles, costo_servicios_basicos, 
                    costo_arriendo, costo_otros_cif
                ) VALUES (
                    :fecha, :unidades, :pro_id, :emp_id, :usr_id, 
                    :tasa, :total_aplicado, :base_anual, 
                    :mat_ind, :mo_ind, :depreciacion,
                    :seguros, :combustibles, :serv_basicos,
                    :arriendo, :otros_cif
                )";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($datos);
    }

    public function listarPorFechaYEmpresa($emp_id, $fecha_inicio, $fecha_fin, $usr_id) {
        $sql = "SELECT c.*, p.pro_nombre 
                FROM tbl_cif_aplicados c
                JOIN tbl_producto p ON c.pro_id = p.pro_id
                WHERE c.emp_id = ? 
                  AND c.usr_id = ?
                  AND c.cif_fecha_aplicada BETWEEN ? AND ?
                ORDER BY c.cif_fecha_aplicada DESC, c.cif_id DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$emp_id, $usr_id, $fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>