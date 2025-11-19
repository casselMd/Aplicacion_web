<?php

class Conexion {
    private $conexion;
    
    public function __construct()
    {
        try {
            $cadenaConexion = "mysql:host=".DB_HOST."; dbname=".DB_NAME."; charset=".DB_CHARSET;
            $this->conexion = new PDO($cadenaConexion, DB_USER, DB_PASSWORD);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "ERROR  {$e->getMessage()}. ";
        }
    }

    public function conectar(){
        return $this->conexion;
    }
}