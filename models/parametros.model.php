<?php
class Parametros {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function obtenerTodosAsociativo() {
        $sql = "SELECT par_nombre, par_valor FROM tbl_parametros";
        $stmt = $this->pdo->query($sql);
        $resultado = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultado[$row['par_nombre']] = $row['par_valor'];
        }
        return $resultado;
    }
}
