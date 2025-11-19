<?php

class Mysql extends Conexion {

    private $conexion, $pdo;

    public function __construct() {
        $this->pdo = new Conexion();
        $this->conexion = $this->pdo->conectar(); 
    }

    public function insert(string $query, array $arrValues) {
        try {
            $insert = $this->conexion->prepare($query);
            $respInsert = $insert->execute($arrValues);
            $lastId = $respInsert ? $this->conexion->lastInsertId() : -1;
            $insert->closeCursor();
            return $lastId;
        } catch (Exception $e) {
            return "Error : {$e->getMessage()}";
        }
    }

    public function selectAll(string $query){
        try {
            $select = $this->conexion->query($query);
            $request = $select->fetchAll(PDO::FETCH_ASSOC);
            $select->closeCursor();
            return $request;
        } catch (Exception $e) {
            return "Error : {$e->getMessage()}";
        }
    }

    public function select(string $query, array $arrValues){
        try {
            $select = $this->conexion->prepare($query);
            $result = $select->execute($arrValues);
            $request = $result ? $select->fetch(PDO::FETCH_ASSOC) : [];
            $select->closeCursor();
            return $request;
        } catch (Exception $e) {
            return "Error : {$e->getMessage()}";
        }
    }

    public function update(string $query, array $arrValues) {
        try {
            $update = $this->conexion->prepare($query);
            $respUpdate = $update->execute($arrValues);
            $update->closeCursor();
            return $respUpdate;
        } catch (Exception $e) {
            return "Error : {$e->getMessage()}";
        }
    }

    public function delete(string $query, array $arrValues) {
        try {
            $delete = $this->conexion->prepare($query);
            $respDelete = $delete->execute($arrValues);
            $delete->closeCursor();
            return $respDelete;
        } catch (Exception $e) {
            return "Error : {$e->getMessage()}";
        }
    }

}