<?php
class Home extends Controller{

    public function __construct() {
        parent::__construct();

    }
    public function index($params="") {
        $datos["title"] = "Bienvenidos Al Sistema";
        $datos["subtitle"] = "Página de Inicio";
        $datos["params"] = $params;
        $this->view->getView($this, "home", $datos);
    }
}