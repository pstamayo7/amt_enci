<?php

class Producto {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function crear($nombre, $codigo, $unidad, $empresa_id) {
        // Verificar si el código ya existe
        $check = $this->pdo->prepare("SELECT COUNT(*) FROM tbl_producto WHERE pro_codigo = ?");
        $check->execute([$codigo]);
        if ($check->fetchColumn() > 0) {
            return false; // Código duplicado
        }

        $sql = "INSERT INTO tbl_producto (pro_nombre, pro_codigo, pro_unidad_medida, emp_id)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$nombre, $codigo, $unidad, $empresa_id]);
    }

    public function listarPorEmpresa($empresa_id) {
        $sql = "SELECT * FROM tbl_producto 
                WHERE emp_id = ? AND pro_estado = 'activo'
                ORDER BY pro_fecha_registro DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$empresa_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminarLogicamente($producto_id, $empresa_id) {
        $sql = "UPDATE tbl_producto SET pro_estado = 'inactivo' 
                WHERE pro_id = ? AND emp_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$producto_id, $empresa_id]);
    }
}
