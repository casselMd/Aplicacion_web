<?php 


class UnidadMedida extends Controller {

    public function __construct() {
        try {
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
        } catch (Exception $e) {
            $response = ["status" => false, "msg" => "ERROR: " . $e->getMessage()];
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
        parent::__construct();
    }
    public function index(){
        $data = $this->model->listar();
        $this->view->getView($this, "unidadmedida", $data);
    }

    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $code = 200;
            $response = [];

            if ($method == "POST") {
                $post = json_decode(file_get_contents("php://input"), true);

                // Validación
                $errorMsg = "";
                if (empty($post["nombre"]) || !is_correct_text($post["nombre"])) $errorMsg .= "Nombre inválido.\n";
                if (empty($post["simbolo"]) || !is_correct_text($post["simbolo"])) $errorMsg .= "Símbolo inválido.\n";

                if (!empty($errorMsg)) {
                    throw new Exception("Errores:\n" . $errorMsg, 200);
                }

                $nombre  = ucwords(strClean($post["nombre"]));
                $simbolo = strtoupper(strClean($post["simbolo"]));

                $this->model->setNombre($nombre);
                $this->model->setSimbolo($simbolo);

                $result = $this->model->agregar();

                if ($result > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Unidad registrada correctamente.",
                        "data"   => ["id" => $result]
                    ];
                    $code = 201;
                } else {
                    throw new Exception("No se pudo registrar la unidad.");
                }
            } else {
                throw new Exception("Método {$method} no permitido.");
            }

            jsonResponse($response, $code);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => nl2br("ERROR:\n" . $e->getMessage())], $e->getCode() ?: 400);
        }
    }

    public function actualizar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "PUT") throw new Exception("Método {$method} no permitido.");

            if (empty($id) || !is_numeric($id)) throw new Exception("ID inválido.");

            $post = json_decode(file_get_contents("php://input"), true);
            $errorMsg = "";
            if (empty($post["nombre"]) || !is_correct_text($post["nombre"])) $errorMsg .= "Nombre inválido.\n";
            if (empty($post["simbolo"]) || !is_correct_text($post["simbolo"])) $errorMsg .= "Símbolo inválido.\n";

            if (!empty($errorMsg)) {
                throw new Exception("Errores:\n" . $errorMsg, 200);
            }

            $nombre  = ucwords(strClean($post["nombre"]));
            $simbolo = strtoupper(strClean($post["simbolo"]));
            $status  = isset($post["status"]) ? intval($post["status"]) : 1;

            $this->model->setId($id);
            $this->model->setNombre($nombre);
            $this->model->setSimbolo($simbolo);
            $this->model->setStatus($status);

            $result = $this->model->actualizar();
            if ($result > 0) {
                jsonResponse(["status" => true, "msg" => "Unidad actualizada correctamente."],200);
            } else {
                throw new Exception("No se pudo actualizar.");
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => nl2br("ERROR:\n" . $e->getMessage())], $e->getCode() ?: 400);
        }
    }

    public function ver($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "GET") throw new Exception("Método {$method} no permitido.");
            if (empty($id) || !is_numeric($id)) throw new Exception("ID inválido.");

            $this->model->setId($id);
            $data = $this->model->getUnidadMedida();
            if (!empty($data)) {
                jsonResponse(["status" => true, "msg" => "Unidad encontrada.", "data" => $data],200);
            } else {
                throw new Exception("Unidad no encontrada.", 404);
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "GET") throw new Exception("Método {$method} no permitido.");

            $data = $this->model->listar();
            if (!empty($data)) {
                jsonResponse(["status" => true, "msg" => "Datos encontrados.", "data" => $data],200);
            } else {
                jsonResponse(["status" => false, "msg" => "No hay datos disponibles."],400);
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function eliminar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "DELETE") throw new Exception("Método {$method} no permitido.");
            if (empty($id) || !is_numeric($id)) throw new Exception("ID inválido.");

            $this->model->setId($id);
            $result = $this->model->eliminar();
            if ($result) {
                jsonResponse(["status" => true, "msg" => "Unidad eliminada (status = 0)."],200);
            } else {
                throw new Exception("No se pudo eliminar la unidad.");
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
