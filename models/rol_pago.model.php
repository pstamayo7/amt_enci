<?php   
class RolPago {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crearRol($emp_id, $rol_mes, $rol_fecha_emision, $total_ingresos, $total_deducciones, $total_liquido) {
        $sql = "INSERT INTO tbl_rol_pago (emp_id, rol_mes, rol_fecha_emision, rol_total_ingresos, rol_total_deducciones, rol_total_liquido)
                VALUES (?, ?, ?, ?, ?, ?) RETURNING rol_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$emp_id, $rol_mes, $rol_fecha_emision, $total_ingresos, $total_deducciones, $total_liquido]);
        return $stmt->fetchColumn();
    }

    public function insertarDetalle($rol_id, $detalle) {
        $sql = "INSERT INTO tbl_rol_pago_detalle (rol_id, empl_id, rpd_sueldo, rpd_bono, rpd_total_ingresos, rpd_aporte_personal, rpd_anticipo, rpd_total_deducciones, rpd_liquido_recibir)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $rol_id,
            $detalle['empl_id'],
            $detalle['rpd_sueldo'],
            $detalle['rpd_bono'],
            $detalle['rpd_total_ingresos'],
            $detalle['rpd_aporte_personal'],
            $detalle['rpd_anticipo'],
            $detalle['rpd_total_deducciones'],
            $detalle['rpd_liquido_recibir']
        ]);
    }
}
