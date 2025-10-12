<?php 
require_once("Models/EmpleadoModel.php");
class Inventario extends Controller {
    private $empleado;
    public function __construct() {
        try {
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
            $id_empleado = fnGetEmpleadoIdToken($arrHeaders);
            
            
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 401);
            die();
        }
        parent::__construct();
        $this->empleado = new EmpleadoModel();
        $this->empleado->setId($id_empleado);
        $this->empleado = $this->empleado->getEmpleado();
    }

    public function registrar() {
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception("Método no permitido", 405);

            $post = json_decode(file_get_contents("php://input"), true);
            $errorMsg = "";

            if (empty($post["producto_id"])) $errorMsg .= "Producto requerido.\n";
            if (!isset($post["tipo_movimiento_id"])) $errorMsg .= "Tipo de movimiento requerido.\n";
            if (empty($post["cantidad"])) $errorMsg .= "Cantidad requerida.\n";

            if (!empty($errorMsg)) throw new Exception($errorMsg, 400);

            $this->model->setProductoId($post["producto_id"]);
            $this->model->setTipoMovimientoId($post["tipo_movimiento_id"]);
            $this->model->setCantidad($post["cantidad"]);
            $this->model->setObservaciones($post["observaciones"] ?? "");
            $this->model->setStatus(1);
            $this->model->setEmpleadoId($this->empleado->getId()); // ← Viene del JWT

            $idInsertado = $this->model->agregar();
            if ($idInsertado > 0) {
                jsonResponse(["status" => true, "msg" => "Movimiento registrado.", "data" => ["id" => $idInsertado]], 201);
            } else {
                throw new Exception("Error al registrar movimiento.");
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function actualizar($idMovimiento) {
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "PUT") throw new Exception("Método no permitido", 405);
            if (empty($idMovimiento)) throw new Exception("ID requerido.");

            $put = json_decode(file_get_contents("php://input"), true);

            $this->model->setIdMovimiento($idMovimiento);
            $this->model->setProductoId($put["productoId"]);
            $this->model->setTipoMovimientoId($put["tipoMovimientoId"]);
            $this->model->setCantidad($put["cantidad"]);
            $this->model->setFecha($put["fecha"]);
            $this->model->setObservaciones($put["observaciones"] ?? "");
            $this->model->setStatus($put["status"] ?? 1);
            $this->model->setEmpleadoId($this->empleado->getId());

            $result = $this->model->actualizar();
            jsonResponse(["status" => true, "msg" => "Movimiento actualizado."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function ver($idMovimiento) {
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "GET") throw new Exception("Método no permitido", 405);
            if (empty($idMovimiento)) throw new Exception("ID requerido.");

            $this->model->setIdMovimiento($idMovimiento);
            $data = $this->model->getMovimiento();
            if ($data) {
                jsonResponse(["status" => true, "data" => $data],200);
            } else {
                throw new Exception("Movimiento no encontrado", 404);
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function listar() {
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "GET") throw new Exception("Método no permitido", 405);

            $data = $this->model->listar();
            jsonResponse(["status" => true, "data" => $data],200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], 400);
        }
    }

    public function eliminar($idMovimiento) {
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "DELETE") throw new Exception("Método no permitido", 405);
            if (empty($idMovimiento)) throw new Exception("ID requerido");

            $this->model->setIdMovimiento($idMovimiento);
            $this->model->eliminar();
            jsonResponse(["status" => true, "msg" => "Movimiento eliminado (lógico)."],200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], (int)$e->getCode() ?: 400);
        }
    }
}
