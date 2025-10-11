<?php
class ClienteModel extends Mysql {
    private $id;
    private $nombre;
    private $direccion;
    private $telefono;
    private $status;

    public function __construct($id = null, $nombre = '', $direccion='', $telefono='', $status = 1) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->direccion = $direccion;
        $this->telefono = $telefono;
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
            $sql = "INSERT INTO cliente (id, cliente, direccion, telefono) VALUES (:id, :cliente, :direccion, :telefono)";
            $arrData = [
                ":id" => $this->id,
                ":cliente" => $this->nombre,
                ":direccion" => $this->direccion,
                ":telefono" => $this->telefono
            ];
            return $this->insert($sql, $arrData);
        } catch (Exception $e) { 
            throw $e;
        }
    }

    public function actualizar() {
        try {
            $sql = "UPDATE cliente SET 
            cliente = :nombre,
            status = :status

            WHERE id = :id";
            $arrData = [
                ":nombre" => $this->nombre,
                ":status" => $this->status,
                ":id" => $this->id
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar() {
        try {
            $sql = "UPDATE cliente SET status = 0 WHERE id = :id";
            return $this->update($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCliente() {
        try {
            $sql = "SELECT * FROM cliente WHERE id = :id ";
            return $this->select($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM cliente ORDER BY id ASC";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getClientesActivosCount()
    {
        try {
            $sql = "SELECT COUNT(*) AS total FROM cliente WHERE status = 1";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }



    /**
     * Get the value of direccion
     */ 
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set the value of direccion
     *
     * @return  self
     */ 
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

        return $this;
    }

    /**
     * Get the value of telefono
     */ 
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set the value of telefono
     *
     * @return  self
     */ 
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }
}
