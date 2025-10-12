<?php
class MetodoPago extends Controller {

    public function __construct() {
        //Si deseamos proteger todo el Controlador con sus Métodos
        try {
            //=============== Validar Token ====================//
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
            //==================================================//
        } catch (Exception $e) {
            $response = ["status" => false,"msg" => "ERROR: " . $e->getMessage() ];
            $code = $e->getCode()==0? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
        parent::__construct();
    }

    // Acción por defecto: listar métodos de pago
    public function index() {
        $data = $this->model->listar();
        $this->view->getView($this, "metodopago", $data);
    }

    // Registrar un nuevo método de pago
    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "POST") {
                $post = json_decode(file_get_contents("php://input"), true);

                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["metodo_pago"])) {
                    $errorMsg .= "El nombre del método de pago es obligatorio.\n";
                }
                // Puedes agregar más validaciones según sea necesario

                if (!empty($errorMsg)) {
                    throw new Exception(nl2br("Se encontraron errores en los datos:\n" . $errorMsg), 200);
                }

                // Obtener y limpiar los datos
                $nombre = strClean($post["metodo_pago"]);
                $status = isset($post["status"]) ? (int)$post["status"] : 1;

                // Asignar datos al modelo
                $this->model->setNombre( $nombre);
                

                $result = $this->model->agregar();
                if ($result > 0) {
                    $response = [
                        "status" => true,
                        "msg" => "Metodo de pago registrado correctamente.",
                        "data" => ["id" => $result]
                    ];
                    $code = 201;
                } else {
                    throw new Exception("No se pudo registrar el metodo de pago.");
                }
            } else {
                throw new Exception("Error en la solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg" => "ERROR: " . $e->getMessage()
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
    public function ver($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                
                if (empty($id) || !is_numeric($id)) throw new Exception("ID de usuario no encontrado.");
                
                $this->model->setId($id);
                $metodoPagoData = $this->model->getMetodoPago();
                if (!empty($metodoPagoData)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Datos encontrados.",
                        "data"   => $metodoPagoData
                    ];
                    $code = 200;
                } else {
                    throw new Exception("El metodo de pago no existe.", 404);
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

    // Actualizar un método de pago existente
    public function actualizar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                if (empty($id) || !is_numeric($id)) {
                    throw new Exception("ID de metodo de pago no encontrado.");
                }
                $post = json_decode(file_get_contents("php://input"), true);

                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["metodo_pago"])) {
                    $errorMsg .= "El nombre del metodo de pago es obligatorio.\n";
                }
                if (!empty($errorMsg)) {
                    throw new Exception(nl2br("Se encontraron errores en los datos:\n" . $errorMsg), 200);
                }

                // Obtener y limpiar los datos
                $nombre = strClean($post["metodo_pago"]);
                $status = isset($post["status"]) ? (int)$post["status"] : 1;

                // Asignar datos al modelo
                $this->model->setId( $id);
                $this->model->setNombre( $nombre);
                $this->model->setStatus( $status);

                $result = $this->model->actualizar();
                if ($result > 0) {
                    $response = [
                        "status" => true,
                        "msg" => "Metodo de pago actualizado correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("No se pudo actualizar el metodo de pago.");
                }
            } else {
                throw new Exception("Error en la solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg" => "ERROR: " . $e->getMessage()
            ];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Listar todos los métodos de pago
    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method == "GET") {
                $data = $this->model->listar();
                $response = [
                    "status" => true,
                    "msg" => "Metodos de pago encontrados.",
                    "data" => $data
                ];
                jsonResponse($response, 200);
            } else {
                throw new Exception("Error en la solicitud {$method}.");
            }
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg" => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, 400);
        }
    }

    // Eliminar un método de pago (eliminación lógica, actualiza status a 0)
    public function eliminar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method == "DELETE") {
                if (empty($id) || !is_numeric($id)) {
                    throw new Exception("ID de metodo de pago no encontrado.");
                }
                $this->model->setId($id);
                $result = $this->model->eliminar();
                if ($result) {
                    $response = [
                        "status" => true,
                        "msg" => "Metodo de pago eliminado correctamente."
                    ];
                } else {
                    throw new Exception("No se pudo eliminar el metodo de pago.");
                }
                jsonResponse($response, 200);
            } else {
                throw new Exception("Error en la solicitud {$method}.");
            }
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg" => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, 400);
        }
    }
}
?>
