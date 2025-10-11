<?php 

class Views {

    public function getView($controller, $view, $data="") {
        $controller = get_class($controller);
        if($controller == "Home") {
            $viewFile = "Views/{$view}.php";
        } else {
            $viewFile = "Views/{$controller}/{$view}.php";
        }
        require_once($viewFile);
    }

}