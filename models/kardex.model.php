<?php

class Kardex
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerUltimaExistencia($pro_id)
    {
        $sql = "SELECT kar_excantidad, kar_exvunitario, kar_extotal
                FROM tbl_kardex
                WHERE pro_id = ? AND kar_estado = 'activo'
                ORDER BY kar_id DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$pro_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'kar_excantidad' => 0,
            'kar_exvunitario' => 0,
            'kar_extotal' => 0
        ];
    }

    public function registrarMovimiento($data)
    {
        $tipo = $data['tipo_movimiento'];
        $cantidad = floatval($data['cantidad']);
        $valor_unitario = floatval($data['valor_unitario']);

        $kar_ecantidad = 0;
        $kar_evunitario = 0;
        $kar_evtotal = 0;
        $kar_scantidad = 0;
        $kar_svunitario = 0;
        $kar_svtotal = 0;

        switch ($tipo) {
            case 'Inventario Inicial':
            case 'Compra de MPD':
            
                // ENTRADA SUMA
                $kar_ecantidad = $cantidad;
                $kar_evunitario = $valor_unitario;
                $kar_evtotal = $kar_ecantidad * $kar_evunitario;
                break;

            case 'Devolución en compras':
                // ENTRADA RESTA
                $kar_ecantidad = -$cantidad;
                $kar_evunitario = $valor_unitario;
                $kar_evtotal = $kar_ecantidad * $kar_evunitario;
                break;

            case 'Envío a producción (OP)':
                // SALIDA RESTA
                $kar_scantidad = $cantidad;
                $kar_svunitario = $valor_unitario;
                $kar_svtotal = $kar_scantidad * $kar_svunitario;
                break;

            case 'Devolución de producción':
                // SALIDA SUMA
                $kar_scantidad = -$cantidad; // negativa para sumar a la existencia
                $kar_svunitario = $valor_unitario;
                $kar_svtotal = $kar_scantidad * $kar_svunitario;
                break;


            default:
                throw new Exception("Tipo de movimiento inválido.");
        }

        // Obtener existencia anterior
        $existencia = $this->obtenerUltimaExistencia($data['producto_id']);
        $ex_cant_anterior = floatval($existencia['kar_excantidad']);
        $ex_val_anterior = floatval($existencia['kar_extotal']);

        // Calcular nueva existencia
        $nueva_cantidad = $ex_cant_anterior + $kar_ecantidad - $kar_scantidad;
        $nueva_total = $ex_val_anterior + $kar_evtotal - $kar_svtotal;
        $nuevo_unitario = $nueva_cantidad > 0 ? $nueva_total / $nueva_cantidad : 0;

        // 🚫 VALIDAR NEGATIVO
        if ($nueva_cantidad < 0 || $nueva_total < 0) {
            throw new Exception("El movimiento genera existencia negativa. No se puede registrar.");
        }

        // Registrar movimiento
        $sql = "INSERT INTO tbl_kardex (
                emp_id, empl_id, pro_id,
                kar_ecantidad, kar_evunitario, kar_evtotal,
                kar_scantidad, kar_svunitario, kar_svtotal,
                kar_excantidad, kar_exvunitario, kar_extotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['empresa_id'],
            $data['empleado_id'],
            $data['producto_id'],
            $kar_ecantidad,
            $kar_evunitario,
            $kar_evtotal,
            $kar_scantidad,
            $kar_svunitario,
            $kar_svtotal,
            $nueva_cantidad,
            $nuevo_unitario,
            $nueva_total
        ]);
    }



    public function historialPorProducto($pro_id)
    {
        $sql = "SELECT k.*, e.empl_nombre, p.pro_nombre
                FROM tbl_kardex k
                JOIN tbl_empleado e ON k.empl_id = e.empl_id
                JOIN tbl_producto p ON k.pro_id = p.pro_id
                WHERE k.pro_id = ? AND k.kar_estado = 'activo'
                ORDER BY k.kar_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$pro_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function anularMovimiento($kar_id)
    {
        $sql = "UPDATE tbl_kardex SET kar_estado = 'anulado' WHERE kar_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$kar_id]);
    }

    
}
