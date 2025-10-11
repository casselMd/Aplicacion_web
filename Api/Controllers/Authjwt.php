<?php

require_once ('Libraries/vendor/autoload.php');
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \Firebase\JWT\ExpiredException;

class Authjwt extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index(){
        echo "JWT_Auth";
    }

    public function registrar(){
        try{

            $method = $_SERVER['REQUEST_METHOD'];
            $response = [];
            $code = 200;
            if($method != 'POST'){
                $post = json_decode(file_get_contents('php://input'), true);

                #validacion uwu
                $errorMsg = "";
                if(empty($post['nombre']) || !is_correct_text($post["nombre"]) )$errorMsg .= "El nombre es obligatorio.\n ";
                if(empty($post["apellido"]) || !is_correct_text($post["apellido"]) )$errorMsg .= "El apellido es obligatorio.\n ";
                if(empty($post["username"]) )$errorMsg .= "El username es obligatorio. \n";
                if(empty($post["password"]) )$errorMsg .= "El password es obligatorio. \n";

                if(!empty($errorMsg)){throw new Exception("ERROR EN DATOS:\n".$errorMsg, 200);}

                # llena el model y ejecuta al agregar tiii..
                $this->model->set("nombre",          ucwords( strClean($post["nombre"])));
                $this->model->set("apellido",        ucwords( strClean($post["apellido"])));
                $this->model->set("username",        strClean($post["username"]));
                $this->model->set("password",        hash($post["password"], PASSWORD_DEFAULT));
                $request = $this->model->setCliente();

                #resive y verifica el tiempo de respuesta del modelo
                if($request > 0){
                    $response = ["status" => true, "msg" => "Datos guardados correctamente.", "data" => ["id" => $request]];
                    $code = 201;
                }else{
                    throw new Exception("ERROR, algo fallo al momento de registrar.");
                }
            }else{
                throw new Exception("ERROR, de solicitud{$method}.");
            }
            jsonResponse($response, $code);
            die();
            
        }catch(Exception $e){
            $response = ["status" => false, "msg" => nl2br("ERROR" . $e->getMessage())];
            $code = $e->getCode() == 0 ?  400 :$e->getCode();
            jsonResponse($response, $code);
            die();
        }
        die();
    }

    public function crearApp(){
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if($method !== "POST") throw new Exception("en solicitud {$method}.");
            
            $post = json_decode(file_get_contents('php://input'),true);

            #validacion uwu
            $errorMsg = "";
            if(empty($post["username"]) )$errorMsg .= "El username es obligatorio. \n";
            if(empty($post["scope"]) )$errorMsg .= "Nombre de app obligatorio.\n";

            if(!empty($errorMsg)) throw new Exception("ERROR EN DATOS:\n".$errorMsg, 200);

            #validar que el usuario (email) exista
            $strScope = strClean($post["scope"]);
            $this->model->set("username", strClean($post["username"]) ) ;
            $req_cliente = $this->model->getCliente();

            if( !empty($req_cliente) ) {
                $cred = $this->generarCredenciales($req_cliente, $strScope);
                $this->model->set("scope"           , $strScope ) ;
                $this->model->set("client_id"       , $cred["client_id"] ) ;
                $this->model->set("secret_key"      , $cred["secret_key"] ) ;
                $this->model->set("id_cliente_jwt"  , $req_cliente["id"] ) ;
                $req_scope = $this->model->setScope();
                
                if($req_scope > 0) {
                    $arrScope = [
                        "id" => $req_scope,
                        "scope" => $strScope,
                        "username" => $this->model->get("username"), //$req_cliente["email"],
                        "client_id" => $cred["client_id"], 
                        "secret_key" => $cred["secret_key"],
                    ];
                    $response = ["status" => true, "msg" => "Datos guardados correctamente.", "data" => $arrScope];
                    }
                }else{
                    throw new Exception("ERROR, el usuario no existe \n.", 200);
                }
            jsonResponse($response, $code);
            die();
        }catch(Exception $e){
            $response = ["status" => false, "msg" => nl2br("ERROR" . $e->getMessage())];
            $code = $e->getCode() == 0 ?  400 :$e->getCode();
            jsonResponse($response, $code);
            die();
        }
        die();
    }

        public function token() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if($method !== "POST") throw new Exception("en solicitud {$method}.");
            
            if (empty($_SERVER["PHP_AUTH_USER"]) || empty($_SERVER["PHP_AUTH_PW"])) {
                throw new Exception("Autorización requerida.\n", 200);
            }
        
            if (empty($_POST["grant_type"]) || $_POST["grant_type"] != "client_credentials") {
                throw new Exception("en los parámetros.\n", 200);
            }

            
            $this->model->set("client_id", $_SERVER["PHP_AUTH_USER"]);
            $this->model->set("secret_key", $_SERVER["PHP_AUTH_PW"]);
            $req_scope = $this->model->getScope();

            if ($req_scope == 0) {
                throw new Exception("Error en autenticación.");
            }
            $arrPayload = $this->generarPayload($req_scope);
            

            $key = $this->model->get("secret_key");
            $tokenJWT = JWT::encode($arrPayload, $key, 'HS512');//access_token
            $this->model->set("id_cliente_jwt", $req_scope["id_cliente_jwt"]);
            $this->model->set("id_scope_jwt", $req_scope["id_scope_jwt"]);
            $this->model->set("access_token", $tokenJWT);
            $this->model->set("expires_in", $arrPayload["exp"]);

            $req_token = $this->model->setToken();

            if ($req_token == 0) {
                throw new Exception("al registrar token. [Consulte al administrador.]");
            }
            
            $data = [
                "access_token" => $tokenJWT,
                "token_type"   => "Bearer",
                "expires_in"   => $arrPayload["exp"],
                "scope"        => $req_scope["scope"],
            ];
            
            $response = [
                "status" => true,
                "msg"    => "Token generado.",
                "data"   => $data
            ];
            
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = ["status" => false,"msg" => nl2br("Error " . $e->getMessage()) ];
            $code = $e->getCode()== 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
        die();
    }

        public function validate_token($token)
    {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
    $code = 200;

    if ($method != "GET") {
        throw new Exception("en solicitud {$method}", 200);
    }

    if (empty($token)) {
        throw new Exception("en los parámetros.", 200);
    }

    $tokenJWT = strClean($token);
    $this->model->set("access_token", $tokenJWT);
    $req_token = $this->model->getToken();

    if (empty($req_token)) {
        throw new Exception("Token no encontrado.");
    }

    $jwt    = $req_token["access_token"];
    $secret = $req_token["secret_key"];
    $decode = JWT::decode($jwt, new Key($secret, "HS512"));

    $response = [
        "status" => true,
        "msg"    => "Token válido.",
        "data"   => $decode
    ];

    jsonResponse($response, $code);
    die();

        } catch (Exception $e) {
            $response["status"] = false;
            $response["msg"] = nl2br("Error => " . $e->getMessage());
            $code = $e->getCode() ?? 400;
            jsonResponse($response, $code);
        }
    }

    private function generarCredenciales(array $cliente, string $scope) {
        $nombreCompleto = $cliente["nombre"] . " " . $cliente["apellido"];
        $strScopeApp = $cliente["username"] . " " . $scope;
        return [
            "client_id" => hash("SHA256", $nombreCompleto) . "-" . hash("SHA256", $strScopeApp),
            "secret_key" => hash("SHA256", $strScopeApp) . "-" . hash("SHA256", $nombreCompleto)
        ];
    }

    private function generarPayload(array $scope)
    {
        $time = time();
        $exp = $time + (60*60*6); // 1 horas
        //$exp = $time + 300; // 5 minutos (60 * 5) ... testing

        return [
            "id_scope" => $scope["id_scope_jwt"],
            "id_cliente_jwt" => $scope["id_cliente_jwt"],
            "scope"    => $scope["scope"],
            "iat"      => $time,
            "exp"      => $exp,
        ];
    }

}