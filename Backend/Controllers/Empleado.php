<?php
class Empleado extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->listar();
    }

    // Registro de usuario (método registrar)
    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "POST") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                //==================================================//
                $post = json_decode(file_get_contents('php://input'), true);
                

                
                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["dni"]) || !is_correct_number($post["dni"])) $errorMsg .= "Error en nombre de usuario.\n";
                // Puedes validar también nombre y apellido si son obligatorios
                if (empty($post["nombre"]) || !is_correct_text($post["nombre"])) $errorMsg .= "Error en Nombre.\n";
                if (empty($post["apellidos"]) || !is_correct_text($post["apellidos"])) $errorMsg .= "Error en Apellido.\n";
                if (empty($post["username"]) || !is_correct_text($post["username"])) $errorMsg .= "Error en nombre de usuario.\n";
                if (empty($post["password"]))  $errorMsg .= "Error en Password.\n";
                if (empty($post["rol"]))  $errorMsg .= "Error en Rol.\n";
                if (!empty($errorMsg))  throw new Exception(nl2br("SE ENCONTRARON ERRORES EN LOS DATOS:\n" . $errorMsg, 200));
            
                
                // Obtener y limpiar datos
                $dni         = strClean($post["dni"]);
                $nombre      = ucwords(strClean($post["nombre"]));
                $apellido    = ucwords(strClean($post["apellidos"]));
                $username    = strClean($post["username"]);
                $password    = hash("SHA256", $post["password"]);
                $rol         = strClean($post["rol"]);
                $estado      = 1;
                
                // Asignar datos al modelo
                $this->model->setUsername( $username);
                $this->model->setNombre( $nombre);
                $this->model->setApellidos( $apellido);
                $this->model->setPassword($password);
                $this->model->setDni( $dni);
                $this->model->setRol($rol);
                $this->model->setStatus($estado);
                
                // Ejecutar el método agregar del modelo
                $request = $this->model->agregar();
                
                if ($request > 0) {
                    $responseData = ["id" => $request];
                    $response = [
                        "status" => true,
                        "msg"    => "Datos guardados correctamente.",
                        "data"   => $responseData
                    ];
                    $code = 201;
                } else {
                    throw new Exception("Algo falló al registrar el usuario.");
                }
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Actualización de usuario
    public function actualizar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                //==================================================//
                if (empty($id) || !is_numeric($id)) {
                    throw new Exception("ID de usuario no encontrado.");
                }
                $post = json_decode(file_get_contents('php://input'), true);
                
                // Validaciones
                $errorMsg = "";
                if (empty($post["dni"]) || !is_correct_number($post["dni"])) $errorMsg .= "Error en DNI.\n";
                // Puedes validar también nombre y apellido si son obligatorios
                if (empty($post["nombre"]) || !is_correct_text($post["nombre"])) $errorMsg .= "Error en Nombre.\n";
                if (empty($post["apellidos"]) || !is_correct_text($post["apellidos"])) $errorMsg .= "Error en Apellido.\n";
                //if (empty($post["username"]) || !is_correct_text($post["username"])) $errorMsg .= "Error en nombre de usuario.\n";
                //if (empty($post["password"]))  $errorMsg .= "Error en Password.\n";
                if (empty($post["rol"]))  $errorMsg .= "Error en Rol.\n";
                if (!empty($errorMsg))  throw new Exception(nl2br("SE ENCONTRARON ERRORES EN LOS DATOS:\n" . $errorMsg, 200));
                
                
                // Obtener y limpiar datos
                $dni         = strClean($post["dni"]);
                $empleado_id = isset($post["empleado_id"]) ? strClean($post["empleado_id"]) : null;
                $nombre      = ucwords(strClean($post["nombre"]));
                $apellido    = ucwords(strClean($post["apellidos"]));
                //$username    = strClean($post["username"]);
                //$password    = isset($post["password"])  ?  hash("SHA256", $post["password"]) : "";
                $rol         = strClean($post["rol"]);
                $estado      = isset($post["status"]) ? (int)$post["status"] : 1;
                
                // Asignar datos al modelo
                $this->model->setId($empleado_id);
                //$this->model->setUsername( $username);
                $this->model->setNombre( $nombre);
                $this->model->setApellidos( $apellido);
                //$this->model->setPassword($password);
                $this->model->setDni( $dni);
                $this->model->setRol($rol);
                $this->model->setStatus($estado);
                // Asignar ID y demás datos al modelo
                $this->model->setId($id);
                $request = $this->model->actualizar();

                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos actualizados correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("Algo falló al actualizar el usuario.");
                }
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => nl2br("ERROR:\n" . $e->getMessage())
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    public function actualizarDatosPersonales() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                $empleado_id = fnGetEmpleadoIdToken($arrHeaders);
                //==================================================//
                $post = json_decode(file_get_contents('php://input'), true);
                
                // Validaciones
                $errorMsg = "";
                if (empty($post["dni"]) || !is_correct_number($post["dni"])) $errorMsg .= "Error en DNI.\n";
                // Puedes validar también nombre y apellido si son obligatorios
                if (empty($post["nombre"]) || !is_correct_text($post["nombre"])) $errorMsg .= "Error en Nombre.\n";
                if (empty($post["apellidos"]) || !is_correct_text($post["apellidos"])) $errorMsg .= "Error en Apellido.\n";
                if (!empty($errorMsg))  throw new Exception(nl2br("SE ENCONTRARON ERRORES EN LOS DATOS:\n" . $errorMsg, 200));
                
                
                // Obtener y limpiar datos
                $dni         = strClean($post["dni"]);
                $nombre      = ucwords(strClean($post["nombre"]));
                $apellido    = ucwords(strClean($post["apellidos"]));
                $estado      = isset($post["status"]) ? (int)$post["status"] : 1;
                
                // Asignar datos al modelo
                $this->model->setNombre( $nombre);
                $this->model->setApellidos( $apellido);
                $this->model->setDni( $dni);
                $this->model->setStatus($estado);
                $this->model->setId($empleado_id);
                $request = $this->model->actualizarDatosPersonales();

                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos actualizados correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("Algo falló al actualizar el usuario.");
                }
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => nl2br("ERROR:\n" . $e->getMessage())
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
        public function actualizarCredenciales() {
        try {

            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                $empleado_id = fnGetEmpleadoIdToken($arrHeaders);

                //==================================================//
                
                $post = json_decode(file_get_contents('php://input'), true);
                // Validaciones
                $errorMsg = "";
                if (empty($post["username"]) || !is_correct_text($post["username"])) $errorMsg .= "Error en nombre de usuario.\n";
                if (empty($post["password"]))  $errorMsg .= "Error en Password.\n";
                if (!empty($errorMsg))  throw new Exception(nl2br("SE ENCONTRARON ERRORES EN LOS DATOS:\n" . $errorMsg, 200));
                
                
                // Obtener y limpiar datos
                $username    = strClean($post["username"]);
                $password    = isset($post["password"])  ?  hash("SHA256", $post["password"]) : "";
                $estado      = isset($post["status"]) ? (int)$post["status"] : 1;
                
                // Asignar datos al modelo
                $this->model->setId($empleado_id);
                $this->model->setUsername( $username);
                $this->model->setPassword($password);
                $this->model->setStatus($estado);
                // Asignar ID y demás datos al modelo
                $this->model->setId($empleado_id);
                $buscar_usuario = $this->model->getEmpleado();
                
                $request = $this->model->actualizarCredenciales();
                
                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos actualizados correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("Algo falló al actualizar el usuario.");
                }
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => nl2br("ERROR:\n" . $e->getMessage())
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
    // Obtener un usuario por ID
    public function ver($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                //==================================================//
                if (empty($id) || !is_numeric($id)) {
                    throw new Exception("ID de usuario no encontrado.");
                }
                $this->model->setId($id);
                $usuario_data = $this->model->getEmpleado();
                if (!empty($usuario_data)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos encontrados.",
                        "data"   => $usuario_data
                    ];
                    $code = 200;
                } else {
                    throw new Exception("El usuario no existe.", 404);
                }
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() == 0 ? 400 : $e->getCode());
            die();
        }
    }

    // Listar todos los usuarios
    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                //==================================================//
                $arrUsuarios = $this->model->listar();
                $reqEmpleadosActivos = $this->model->getEmpleadosActivosCount();
                if (!empty($arrUsuarios)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos encontrados.",
                        "data"   => $arrUsuarios,
                        "total_activos"  => (int)$reqEmpleadosActivos[0]['total'] ?? 0
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No hay datos para mostrar."
                    ];
                }
                $code = 200;
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() == 0 ? 400 : $e->getCode());
            die();
        }
    }

    // Eliminación lógica de un usuario
    public function eliminar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "DELETE") {
                //=============== Validar Token ====================//
                $arrHeaders = getallheaders();
                $hasAuth = fnAuthorization($arrHeaders);
                // //==================================================//
                if (empty($id) || !is_numeric($id)) {
                    throw new Exception("ID de usuario no encontrado.");
                }
                $this->model->setId($id);
                $request = $this->model->eliminar();
                if ($request) {
                    $response = [
                        "status" => true,
                        "msg"    => "Registro eliminado."
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No es posible eliminar el registro."
                    ];
                }
                $code = 200;
            } else {
                throw new Exception("Error en solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() == 0 ? 400 : $e->getCode());
            die();
        }
    }

    // Login de usuario
    public function login() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method !== "POST") throw new Exception("Error en la solicitud {$method}.");
            $post = json_decode( file_get_contents("php://input"), true);
            if( empty($post["username"])  || empty($post["password"]) )  throw new Exception("Error en datos.");
            

            $this->model->setUsername(strClean($post["username"]) );
            $this->model->setPassword(hash("SHA256" , $post["password"]) );
            $request = $this->model->login();

            //debug($request); exit;
            if ( !empty($request) ) {
                $req_tokenJWT = getTokenApi();
                
                

                if(!$req_tokenJWT["status"]) throw new Exception(" al generar el token de autorización.");
                guardarTokenClienteJWT($req_tokenJWT["data"]);
                //enviamos datos del empleado obtenido del modelo
                $req_tokenEmpleado = fnGenerateTokenEmpleado($request); // <-- Token propio del empleado
                if(!$req_tokenEmpleado) throw new Exception("Error al generar el token de autorización del empleado.");
                
                $arrAuth = $req_tokenJWT["data"]; // Este va al frontend
                
                $arrAuth["token_empleado"] = $req_tokenEmpleado; // Este va al frontend 
                    //debug($arrAuth);exit;
                $response = ["status" => true, "msg" => "Acceso concedido.", "data"=>$arrAuth];
                //debug($response); exit;
            } else {
                $response = ["status" => false, "msg" => "El nombre de usuario o la contraseña es incorrecto."];
            }
            $code = 200;
            
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            $code = $e->getCode() == 0 ? 401 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    public function validar_token($token) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
    
            if ($method == "GET") {
                if(empty($token)) throw new Exception("Token no recibido");
                // Validamos el token con una función helper (asumida: validateToken)
                $result = fnValidateToken($token);
                
            if (!is_array($result) || !isset($result["status"])) {
                throw new Exception("Error al validar el token.");
            }

                if ($result["status"] == true) {
                    $response = [
                        "status" => true,
                        "msg"    => "Token válido(limitado).",
                        //"data"   => $result["data"]  // Datos decodificados del token
                    ];
                } else {
                    throw new Exception("Token inválido o expirado.");
                }
            } else {
                throw new Exception("Error en la solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
    
}
?>
