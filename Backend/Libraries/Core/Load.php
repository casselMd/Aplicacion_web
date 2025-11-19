<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$controllerFile = "Controllers/{$controller}.php";
if( file_exists($controllerFile) ) {
    require_once($controllerFile);
    $controller = new $controller();
    if( method_exists($controller, $method) ) {
        
        $controller->{$method}($params);
    } else {
        require_once("Controllers/Errors.php");
        $controller = new Error();
    }

} else {
    require_once("Controllers/Errors.php");
        $controller = new Error();
}