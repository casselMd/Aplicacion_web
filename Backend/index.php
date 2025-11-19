<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$allowed_origins = ['http://localhost:4200','https://precious-peony-bc991c.netlify.app']; // o https://tudominio.com en producción
if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
}
//header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Credentials: true"); // <- necesario para sesiones

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Authorization,Authorization-Empleado, X-Auth-Token");
header("Allow: GET, POST, PUT, DELETE, OPTIONS");
//Manejo de Petición preflight CORS
if( $_SERVER["REQUEST_METHOD"] == "OPTIONS" ) {
    http_response_code(200);
    exit();
}


require_once("Config/Config.php");
require_once("Helpers/Helpers.php");
$url = !empty($_GET["url"]) ? $_GET["url"] : "home" ;
$arrUrl = explode("/", $url);
$controller = ucwords($arrUrl[0]);
$method = "index";
$params = "";
if(!empty($arrUrl[1])) {
    if($arrUrl[1] != "") {
        $method = $arrUrl[1];
    }
}
if(!empty($arrUrl[2])) {
    if($arrUrl[2] != "") {
        for ($i=2; $i < count($arrUrl); $i++) { 
            $params .= $arrUrl[$i] . ",";
        } 
        $params = trim($params, ",");
    }
}
require_once("Libraries/Core/Autoload.php");
require_once("Libraries/Core/Load.php");

