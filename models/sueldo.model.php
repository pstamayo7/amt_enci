<?php

class Sueldo
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar()
    {
        $sql = "SELECT sue_id, sue_valor FROM tbl_sueldo ORDER BY sue_valor ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorEmpresa($emp_id)
    {
        $sql = "SELECT sue_id, sue_valor 
                FROM tbl_sueldo 
                WHERE emp_id = ? OR sue_tipo = 'general'
                ORDER BY sue_valor ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$emp_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

 public function crear($emp_id, $valor) {
    $stmt = $this->pdo->prepare("INSERT INTO tbl_sueldo (emp_id, sue_valor, sue_tipo) VALUES (?, ?, 'empresa')");
    return $stmt->execute([$emp_id, $valor]);
}

    public function eliminar($sue_id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM tbl_sueldo WHERE sue_id = ?");
        return $stmt->execute([$sue_id]);
    }




}


