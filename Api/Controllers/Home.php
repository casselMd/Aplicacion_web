<?php

class Home extends Controller{

    public function __construct() {
        parent::__construct();

    }

    public function index($params="") {
        $datos["title"] = "Bienvenido Usuario";
        $datos["subtitle"] = "PÁGINA PRINCIPAL";
        $datos["params"] = $params;
        $this->view->getView($this, "home", $datos);
    }
}