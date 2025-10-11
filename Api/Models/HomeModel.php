<?php 

class HomeModel extends Mysql{
    #Atributos de la clase: Campos de la BD
    #private $campo1;
    #private $campo2;
    #private $campo3;
    #...
    
    public function __construct() {
        parent::__construct();
    }

    public function set(string $campo, $valor) { $this->$campo = $valor; }
    public function get(string $campo) { return $this->$campo; }
}