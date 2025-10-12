<?php
class Cliente extends Controller {

    public function __construct() {
        //Si deseamos proteger todo el Controlador con sus Métodos
        try {
            //=============== Validar Token ====================//
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
            //debug($hasAuth);
            //==================================================//
        } catch (Exception $e) {
            $response = ["status" => false,"msg" => "ERROR: " . $e->getMessage() ];
            $code = $e->getCode()==0? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
        parent::__construct();
        
    }

    // Acción por defecto: redirige al listado de clientes
    public function index() {
        $this->view->getView($this,"cliente","");
    }

    // Registrar un nuevo cliente
    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "POST") {
                $post = json_decode(file_get_contents('php://input'), true);

                // Validaciones básicas
                $errorMsg = "";
                
                if (empty($post["cliente"]) || !is_correct_text($post["cliente"])) $errorMsg .= "Error en el nombre del cliente. \n ";
                if(empty($post["id"]) ) $errorMsg .= "Error en el id del cliente. \n ";
                if (!empty($errorMsg)) throw new Exception(nl2br("Se encontraron errores en los datos:\n" . $errorMsg), 200);
                

                // Obtener y limpiar los datos
                $id        = strClean($post["id"]);
                $cliente   = ucwords(strClean($post["cliente"]));
                $direccion = isset($post["direccion"])? strClean( $post["direccion"]): '' ;
                $telefono =  isset($post["telefono"]) ? strClean($post["telefono"] ) : '' ;
                // Asignar datos al modelo (mapea la columna 'cliente' en BD)
                $this->model->setId($id);
                $this->model->setNombre($cliente);
                $this->model->setDireccion($direccion);
                $this->model->setTelefono($telefono);

                $request = $this->model->agregar();
                
                $response = [
                    "status" => true,
                    "msg"    => "Cliente registrado correctamente.",
                    "data"   => ["id" => $request]
                ];
                $code = 201;
                
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

    // Actualizar un cliente existente
    public function actualizar($id_cliente) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                if (empty($id_cliente) || !is_numeric($id_cliente)) {
                    throw new Exception("ID de cliente no encontrado.");
                }
                $post = json_decode(file_get_contents('php://input'), true);

                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["cliente"]) || !is_correct_text($post["cliente"])) {
                    $errorMsg .= "Error en el nombre del cliente.\n";
                }
                if (!empty($errorMsg)) {
                    throw new Exception("Se encontraron errores en los datos:\n" . $errorMsg, 200);
                }

                $cliente   = ucwords(strClean($post["cliente"]));
                $status    = (int)$post["status"];

                $this->model->setId($id_cliente);
                // Opcional: verificar que el cliente exista (por ejemplo, usando getCliente)
                $this->model->setNombre( $cliente);
                $this->model->setStatus($status);

                $request = $this->model->actualizar();
                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Cliente actualizado correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("No se pudo actualizar el cliente.");
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

    // Obtener los datos de un cliente por su ID
    public function ver($id_cliente) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                if (empty($id_cliente) || !is_numeric($id_cliente)) {
                    throw new Exception("ID de cliente no encontrado.");
                }
                $this->model->setId( $id_cliente);
                $cliente = $this->model->getCliente();
                if (!empty($cliente)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Cliente encontrado.",
                        "data"   => $cliente
                    ];
                    $code = 200;
                } else {
                    throw new Exception("El cliente no existe.", 404);
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

    // Listar todos los clientes
    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                $clientes = $this->model->listar();
                $reqClientesActivos = $this->model->getClientesActivosCount();
                if (!empty($clientes)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos encontrados.",
                        "data"   => $clientes,
                        "total_activos"  => (int)$reqClientesActivos[0]['total'] ?? 0
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Eliminar (lógicamente) un cliente: actualiza status a 'inactivo'
    public function eliminar($id_cliente) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "DELETE") {
                if (empty($id_cliente) || !is_numeric($id_cliente)) {
                    throw new Exception("ID de cliente no encontrado.");
                }
                $this->model->setId( $id_cliente);
                $request = $this->model->eliminar();
                if ($request) {
                    $response = [
                        "status" => true,
                        "msg"    => "Cliente eliminado (status inactivo)."
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No es posible eliminar el cliente."
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
}
?>
