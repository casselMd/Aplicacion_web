<?php

// Controlador para Compras
/*
class Compra extends Controller {
    private $hasAuth;
    public function __construct() {
        try {
            $arrHeaders = getallheaders();
            $this->hasAuth = fnAuthorization($arrHeaders);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], 401);
            die();
        }
        parent::__construct();
    }

    public function registrar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido', 405);

            $post = json_decode(file_get_contents('php://input'), true);
            $errorMsg = '';

            if (empty($post['empleado_id'])) $errorMsg .= 'Empleado requerido.\n';
            if (empty($post['fecha'])) $errorMsg .= 'Fecha requerida.\n';
            if (empty($post['total'])) $errorMsg .= 'Total requerido.\n';
            if (empty($post['proveedor_id'])) $errorMsg .= 'Proveedor requerido.\n';
            if (empty($post['tipo_documento'])) $errorMsg .= 'Tipo de documento requerido.\n';
            if (empty($post['numero_documento'])) $errorMsg .= 'Número de documento requerido.\n';
            if (empty($post['metodo_pago_id'])) $errorMsg .= 'Método de pago requerido.\n';

            if (!empty($errorMsg)) throw new Exception($errorMsg, 400);

            $this->model->setEmpleadoId($post['empleado_id']);
            $this->model->setFecha($post['fecha']);
            $this->model->setTotal($post['total']);
            $this->model->setStatus($post['status'] ?? 1);
            $this->model->setProveedorId($post['proveedor_id']);
            $this->model->setTipoDocumento($post['tipo_documento']);
            $this->model->setNumeroDocumento($post['numero_documento']);
            $this->model->setMetodoPagoId($post['metodo_pago_id']);
            $this->model->setObservaciones($post['observaciones'] ?? '');
            $this->model->setFechaRegistro(date('Y-m-d H:i:s'));

            $idCompra = $this->model->agregar();
            if ($idCompra > 0) {
                jsonResponse(["status" => true, "msg" => "Compra registrada.", "data" => ["id" => $idCompra]], 201);
            } else {
                throw new Exception('Error al registrar compra.');
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => 'ERROR: ' . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function actualizar($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') throw new Exception('Método no permitido', 405);
            if (empty($id)) throw new Exception('ID requerido.', 400);

            $put = json_decode(file_get_contents('php://input'), true);

            $this->model->setId($id);
            $this->model->setEmpleadoId($put['empleado_id']);
            $this->model->setFecha($put['fecha']);
            $this->model->setTotal($put['total']);
            $this->model->setStatus($put['status'] ?? 1);
            $this->model->setProveedorId($put['proveedor_id']);
            $this->model->setTipoDocumento($put['tipo_documento']);
            $this->model->setNumeroDocumento($put['numero_documento']);
            $this->model->setMetodoPagoId($put['metodo_pago_id']);
            $this->model->setObservaciones($put['observaciones'] ?? '');
            $this->model->setFechaRegistro($put['fecha_registro'] ?? date('Y-m-d H:i:s'));

            $this->model->actualizar();
            jsonResponse(["status" => true, "msg" => "Compra actualizada."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => 'ERROR: ' . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function ver($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Método no permitido', 405);
            if (empty($id)) throw new Exception('ID requerido.', 400);

            $this->model->setId($id);
            $data = $this->model->getCompra();
            if ($data) {
                jsonResponse(["status" => true, "data" => $data], 200);
            } else {
                throw new Exception('Compra no encontrada', 404);
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function listar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Método no permitido', 405);

            $data = $this->model->listar();
            jsonResponse(["status" => true, "data" => $data], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], 400);
        }
    }

    public function eliminar($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') throw new Exception('Método no permitido', 405);
            if (empty($id)) throw new Exception('ID requerido.', 400);

            $this->model->setId($id);
            $this->model->eliminar();
            jsonResponse(["status" => true, "msg" => "Compra eliminada (lógica)."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}*/


// Controlador Compra con manejo de detalles

require_once ("Models/EmpleadoModel.php");
class Compra extends Controller {
    private $empleado_id;
    public function __construct() {
        try {
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
            $this->empleado_id = fnGetEmpleadoIdToken($arrHeaders);
            
            
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 401);
            die();
        }
        parent::__construct();
        
    }

    /**
     * Registra una compra con su detalle en una sola transacción.
     * Request JSON:
     *  - fecha, total, proveedor_id, tipo_documento, numero_documento, metodo_pago_id, observaciones (opcionales)
     *  - detalles: array de {id_producto, precio_unitario, cantidad}
     */
    public function registrar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido', 405);
            $body = json_decode(file_get_contents('php://input'), true);
            
            $errorMsg = "";
            if (empty($body['detalles']) || !is_array($body['detalles'])) $errorMsg .= 'Detalles obligatorios.';
            if (empty($body["proveedor_id"]))  $errorMsg .= "El proveedor es obligatorio.\n";
            if (empty($body["total"]) || !is_numeric($body["total"])) $errorMsg .= "El total es obligatorio y debe ser numérico.\n";
            if (empty($body["metodo_pago_id"])) $errorMsg .= "El método de pago es obligatorio.\n";
            if (empty($body["tipo_documento"])) $errorMsg .= "El tipo de documento es obligatorio.\n";
            if ($body["tipo_documento"] != "ninguno" && (empty($body["numero_documento"]) || empty($body["fecha"]) ) ) {
                $errorMsg .= "Datos del documento incompletos.\n";
            }
            if (!empty($errorMsg)) throw new Exception(nl2br($errorMsg), 200);
            // Asignar campos al modelo
            $this->model->setEmpleadoId($this->empleado_id);
            $this->model->setTotal($body['total'] ?? 0);
            $this->model->setStatus(1);
            $this->model->setProveedorId($body['proveedor_id'] ?? null);
            $this->model->setTipoDocumento($body['tipo_documento'] ?? '');
            $this->model->setNumeroDocumento($body['numero_documento'] ?? null);
            $this->model->setMetodoPagoId($body['metodo_pago_id'] ?? null);
            $this->model->setObservaciones($body['observaciones'] ?? '');
            $this->model->setFechaRegistro($body['fecha'] ??date('Y-m-d'));

            // Detalles como array
            $this->model->setDetalles($body['detalles']);

            // Registrar en transacción
            $compraId = $this->model->registrarCompra();
            jsonResponse(["status" => true, "msg" => 'Compra registrada.', "data" => ['id' => $compraId]], 201);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => 'ERROR: ' . $e->getMessage()], $e->getCode() ? : 400);
        }
    }

    public function ver($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Método no permitido', 405);
            if (empty($id)) throw new Exception('ID requerido.', 400);
            $this->model->setId($id);
            $compra = $this->model->getCompraConDetalles();
            jsonResponse(["status" => true, "data" => $compra], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function listar() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Método no permitido', 405);
            $list = $this->model->listar();
            $resumen = $this->model->getComprasMensuales();
            jsonResponse(["status" => true, "data" => $list ,"resumen"=> $resumen], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], 400);
        }
    }

    public function eliminar($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') throw new Exception('Método no permitido', 405);
            if (empty($id)) throw new Exception('ID requerido.', 400);
            $this->model->setId($id);
            $this->model->eliminar();
            jsonResponse(["status" => true, "msg" => 'Compra anulada.'], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
