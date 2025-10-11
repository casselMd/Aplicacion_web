<?php 

class TipoMovimientoModel extends Mysql {
    
    private $id;
    private $nombre;
    private $afecta_stock;
    private $status;

    public function setId($id) {
        $this->id = intval($id);
    }

    public function getId() {
        return $this->id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setAfectaStock($afecta) {
        $this->afecta_stock = intval($afecta);
    }

    public function getAfectaStock() {
        return $this->afecta_stock;
    }

    public function setStatus($status) {
        $this->status = intval($status);
    }

    public function getStatus() {
        return $this->status;
    }

    public function agregar() {
        $sql = "INSERT INTO tipo_movimiento (nombre, afecta_stock, status) VALUES (?, ?, 1)";
        $data = [$this->nombre, $this->afecta_stock];
        return $this->insert($sql, $data);
    }

    public function actualizar() {
        $sql = "UPDATE tipo_movimiento SET nombre = ?, afecta_stock = ?, status = ? WHERE id = ?";
        $data = [$this->nombre, $this->afecta_stock, $this->status, $this->id];
        return $this->update($sql, $data);
    }

    public function eliminar() {
        $sql = "UPDATE tipo_movimiento SET status = 0 WHERE id = ?";
        $data = [$this->id];
        return $this->update($sql, $data);
    }

    public function getTipoMovimiento() {
        $sql = "SELECT * FROM tipo_movimiento WHERE id = ?";
        $data = [$this->id];
        return $this->select($sql, $data);
    }

    public function listar() {
        $sql = "SELECT * FROM tipo_movimiento WHERE status != 0";
        return $this->selectAll($sql);
    }
}
