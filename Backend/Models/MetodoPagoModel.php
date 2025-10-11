<?php



class MetodoPagoModel extends Mysql{
    private $id;
    private $nombre;
    private $status;

    public function __construct($id = 0, $nombre = '', $status = '') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->status = $status;
        parent::__construct();
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getStatus() {
        return $this->status;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
    public function agregar() {
        try {
            $sql = "INSERT INTO metodo_pago (metodo_pago, status) VALUES (:metodo_pago, :status)";
            $arrData = [
                ":metodo_pago" => $this->nombre,
                ":status" => $this->status
            ];
            return $this->insert($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizar() {
        try {
            $sql = "UPDATE metodo_pago SET metodo_pago = :metodo_pago, status = :status WHERE id = :id";
            $arrData = [
                ":metodo_pago" => $this->nombre,
                ":status" => $this->status,
                ":id"     => $this->id
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar() {
        try {
            $sql = "UPDATE metodo_pago SET status = 0 WHERE id = :id";
            return $this->update($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getMetodoPago() {
        try {
            $sql = "SELECT * FROM metodo_pago WHERE id = :id AND status = 1";
            return $this->select($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM metodo_pago ORDER BY id ASC";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
