<?php
class UnidadMedidaModel extends Mysql {
    private $id;
    private $nombre;
    private $simbolo;
    private $status;

    public function __construct($id = null,$nombre ="", $simbolo = "", $status = 1)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->simbolo = $simbolo;
        $this->status = $status;
        parent::__construct();
    }



    public function setId($id) { $this->id = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setSimbolo($simbolo) { $this->simbolo = $simbolo; }
    public function setStatus($status) { $this->status = $status; }

    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getSimbolo() { return $this->simbolo; }
    public function getStatus() { return $this->status; }

    public function agregar() {
        $sql = "INSERT INTO unidad_medida (nombre, simbolo, status) VALUES (:nombre, :simbolo, 1)";
        $arrData = [
            ":nombre"  => $this->nombre,
            ":simbolo" => $this->simbolo
        ];
        return $this->insert($sql, $arrData);
    }

    public function actualizar() {
        $sql = "UPDATE unidad_medida SET nombre = :nombre, simbolo = :simbolo, status = :status WHERE id = :id";
        $arrData = [
            ":nombre"  => $this->nombre,
            ":simbolo" => $this->simbolo,
            ":status"  => $this->status,
            ":id"      => $this->id
        ];
        return $this->update($sql, $arrData);
    }

    public function getUnidadMedida() {
        try {
            $sql = "SELECT * FROM unidad_medida WHERE id = :id";
            return $this->select($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM unidad_medida";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar() {
        $sql = "UPDATE unidad_medida SET status = 0 WHERE id = :id";
        return $this->update($sql, [":id" => $this->id]);
    }
}
