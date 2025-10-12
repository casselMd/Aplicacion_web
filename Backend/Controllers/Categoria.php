<?php
class Categoria extends Controller {

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

    // Acción por defecto: redirige al listado de categorías
    public function index() {
        $this->view->getView($this, "categoria", $this->model->listar());
    }

    // Registrar una nueva categoría
    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "POST") {
                $post = json_decode(file_get_contents("php://input"), true);

                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["categoria"]) || !is_correct_text($post["categoria"])) {
                    $errorMsg .= "Error en el nombre de la categoria.\n";
                }
                // La descripción puede ser opcional
                if (!empty($errorMsg)) {
                    throw new Exception(nl2br("Se encontraron errores en los datos:\n" . $errorMsg), 200);
                }

                // Obtener y limpiar los datos
                $categoria = strClean($post["categoria"]);
                $descripcion = isset($post["descripcion"]) ? strClean($post["descripcion"]) : "";
                $status = "1"; // 1 indica activo

                // Asignar datos al modelo
                $this->model->setNombre($categoria);
                $this->model->setDescripcion($descripcion);
                $this->model->setStatus($status);

                $request = $this->model->agregar();
                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Categoria registrada correctamente.",
                        "data"   => ["id" => $request]
                    ];
                    $code = 201;
                } else {
                    throw new Exception("No se pudo registrar la categoría.");
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
            $code = ($e->getCode() == 0) ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Actualizar una categoría existente
    public function actualizar($id_categoria) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                if (empty($id_categoria) || !is_numeric($id_categoria)) {
                    throw new Exception("ID de categoria no encontrado.");
                }
                $post = json_decode(file_get_contents("php://input"), true);

                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["categoria"]) || !is_correct_text($post["categoria"])) {
                    $errorMsg .= "Error en el nombre de la categoría.\n";
                }
                if (!empty($errorMsg)) {
                    throw new Exception("Se encontraron errores en los datos:\n" . $errorMsg, 200);
                }

                $categoria = strClean($post["categoria"]);
                $descripcion = isset($post["descripcion"]) ? strClean($post["descripcion"]) : "";
                $status = isset($post["status"]) ? strClean($post["status"]) : 1;

                // Asignar datos al modelo
                $this->model->setId($id_categoria);
                $this->model->setNombre($categoria);
                $this->model->setDescripcion($descripcion);
                $this->model->setStatus((int)$status);

                $request = $this->model->actualizar();
                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Categoria actualizada correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("No se pudo actualizar la categoria.");
                }
            } else {
                throw new Exception("Error en la solicitud {$method}.");
            }
            jsonResponse($response, $code);
            die();
        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg"    => nl2br("ERROR:\n" . $e->getMessage())
            ];
            $code = ($e->getCode() == 0) ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Obtener los datos de una categoría por su ID
    public function ver($id_categoria) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                if (empty($id_categoria) || !is_numeric($id_categoria)) {
                    throw new Exception("ID de categoria no encontrado.");
                }
                $this->model->setId($id_categoria);
                $categoria_data = $this->model->getCategoria();
                if (!empty($categoria_data)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Categoria encontrada.",
                        "data"   => $categoria_data
                    ];
                    $code = 200;
                } else {
                    throw new Exception("La categoria no existe.", 404);
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
            $code = ($e->getCode() == 0) ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Listar todas las categorías
    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                $arrCategorias = $this->model->listar();
                if (!empty($arrCategorias)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Categorias encontradas.",
                        "data"   => $arrCategorias
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No hay categorias para mostrar."
                    ];
                }
                $code = 200;
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
            $code = ($e->getCode() == 0) ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Eliminar (lógicamente) una categoría, actualizando su status a inactivo (0)
    public function eliminar($id_categoria) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "DELETE") {
                if (empty($id_categoria) || !is_numeric($id_categoria)) {
                    throw new Exception("ID de categoria no encontrado.");
                }
                $this->model->setId($id_categoria);
                $request = $this->model->eliminar();
                if ($request) {
                    $response = [
                        "status" => true,
                        "msg"    => "Categoria eliminada (status inactivo)."
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No es posible eliminar la categoria."
                    ];
                }
                $code = 200;
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
            $code = ($e->getCode() == 0) ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
}
?>
