<?php

class CategoriaModel extends Mysql{

    private $id;
    private $categoria;
    private $descripcion;
    private $status;

    public function __construct($id =0, $categoria='', $descripcion='', $status=''){
        $this->id = $id;
        $this->categoria = $categoria;
        $this->descripcion = $descripcion;
        $this->status = $status;
        parent::__construct();
    }

        public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->categoria;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getStatus() {
        return $this->status;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($categoria) {
        $this->categoria = $categoria;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function agregar() {
        try {
            $sql = "INSERT INTO categoria (categoria, descripcion)
                    VALUES (:categoria, :descripcion)";
            $arrData = [
                ":categoria"      => $this->categoria,
                ":descripcion" => $this->descripcion
            ];
            return $this->insert($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizar() {
        try {
            $sql = "UPDATE categoria SET categoria = :categoria, descripcion = :descripcion , status = :status WHERE id = :id";
            $arrData = [
                ":categoria"      => $this->categoria,
                ":descripcion" => $this->descripcion,
                ":id"          => $this->id,
                ":status"          => $this->status
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar() {
        try {
            $sql = "UPDATE categoria SET status = 0 WHERE id = :id";
            $arrData = [":id" => $this->id];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCategoria() {
        try {
            $sql = "SELECT * FROM categoria WHERE id = :id AND status = 1";
            $arrData = [":id" => $this->id];
            return $this->select($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM categoria ORDER BY id ASC";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }
}