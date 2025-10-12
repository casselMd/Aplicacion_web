<?php
class TipoMovimiento extends Controller {
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

    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "POST") {
                $post = json_decode(file_get_contents("php://input"), true);
                $errorMsg = "";
                if (empty($post["nombre"]) || !is_correct_text($post["nombre"])) {
                    $errorMsg .= "Nombre inválido. \n";
                }


                if (!isset($post["afecta_stock"])) {
                    $errorMsg .= "Debe indicar si afecta stock. \n";
                }
                if (!empty($errorMsg)) throw new Exception(nl2br("Errores:\n" . $errorMsg), 200);

                $this->model->setNombre(ucwords(strClean($post["nombre"])));
                $this->model->setAfectaStock(intval($post["afecta_stock"]));
                $request = $this->model->agregar();
                

                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg" => "Tipo de movimiento registrado.",
                        "data" => ["id" => $request]
                    ];
                    $code = 201;
                } else {
                    throw new Exception("No se pudo registrar el tipo de movimiento.");
                }
            } else {
                throw new Exception("Método no permitido: $method.");
            }
            jsonResponse($response, $code);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $code);
        }
    }

    public function actualizar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "PUT") throw new Exception("Método no permitido: $method");

            if (empty($id) || !is_numeric($id)) throw new Exception("ID inválido.");

            $put = json_decode(file_get_contents("php://input"), true);
            $errorMsg = "";

            if (empty($put["nombre"]) || !is_correct_text($put["nombre"])) {
                $errorMsg .= "Nombre inválido. \n";
            }

            if (!isset($put["afecta_stock"])) {
                $errorMsg .= "Debe indicar si afecta stock. \n";
            }

            if (!empty($errorMsg)) throw new Exception(nl2br("Errores:\n" . $errorMsg), 200);

            $this->model->setId($id);
            $this->model->setNombre(ucwords(strClean($put["nombre"])));
            $this->model->setAfectaStock(intval($put["afecta_stock"]));
            $this->model->setStatus(isset($put["status"]) ? intval($put["status"]) : 1);

            $request = $this->model->actualizar();

            if ($request > 0) {
                jsonResponse(["status" => true, "msg" => "Tipo de movimiento actualizado."], 200);
            } else {
                throw new Exception("No se pudo actualizar el tipo de movimiento.");
            }
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $code);
        }
    }

    public function ver($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "GET") throw new Exception("Método no permitido: $method");
            if (empty($id) || !is_numeric($id)) throw new Exception("ID inválido.");

            $this->model->setId($id);
            $tipo = $this->model->getTipoMovimiento();

            if (!empty($tipo)) {
                jsonResponse(["status" => true, "msg" => "Registro encontrado.", "data" => $tipo], 200);
            } else {
                throw new Exception("Tipo de movimiento no encontrado.", 404);
            }
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $code);
        }
    }

    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "GET") throw new Exception("Método no permitido: $method");

            $tipos = $this->model->listar();

            if (!empty($tipos)) {
                jsonResponse(["status" => true, "msg" => "Datos encontrados.", "data" => $tipos], 200);
            } else {
                jsonResponse(["status" => false, "msg" => "No hay registros."], 200);
            }
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $code);
        }
    }

    public function eliminar($id) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method != "DELETE") throw new Exception("Método no permitido: $method");
            if (empty($id) || !is_numeric($id)) throw new Exception("ID inválido.");

            $this->model->setId($id);
            $request = $this->model->eliminar();

            if ($request) {
                jsonResponse(["status" => true, "msg" => "Tipo de movimiento desactivado (status 0)."], 200);
            } else {
                jsonResponse(["status" => false, "msg" => "No se pudo desactivar el registro."], 200);
            }
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $code);
        }
    }
}
