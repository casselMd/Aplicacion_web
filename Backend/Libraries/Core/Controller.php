<?php

class Controller {
    protected $model, $view;

    public function __construct() {
        $this->view = new Views();
        $this->loadModel();
    }

    public function loadModel() {
        $model = get_class($this) . "Model";
        $modelFile = "Models/{$model}.php";
        if( file_exists($modelFile) ) {
            require_once($modelFile);
            $this->model = new $model();
        }
    }

}
/*
class Controller {
    protected $view, $service;

    public function __construct() {
        $this->view = new Views();
        $this->loadService();
    }

    public function loadService() {
        $service = get_class($this) . "Service";
        $serviceFile = "Services/{$service}.php";
        if(file_exists($serviceFile)) {
            require_once($serviceFile);
            $this->service = new $service();
        }
    }
}*/
