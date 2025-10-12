<?php 
// Controlador para DetalleCompra
class DetalleCompra extends Controller {
    public function __construct() {
        try {
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
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

            if (empty($post['compra_id'])) $errorMsg .= 'Compra ID requerido.\n';
            if (empty($post['id_producto'])) $errorMsg .= 'Producto requerido.\n';
            if (empty($post['precio_unitario'])) $errorMsg .= 'Precio unitario requerido.\n';
            if (empty($post['cantidad'])) $errorMsg .= 'Cantidad requerida.\n';

            if (!empty($errorMsg)) throw new Exception($errorMsg, 400);

            $this->model->setCompraId($post['compra_id']);
            $this->model->setIdProducto($post['id_producto']);
            $this->model->setPrecioUnitario($post['precio_unitario']);
            $this->model->setCantidad($post['cantidad']);
            $this->model->setSubTotal($post['subtotal'] ?? ($post['precio_unitario'] * $post['cantidad']));

            $idDetalle = $this->model->agregar();
            if ($idDetalle > 0) {
                jsonResponse(["status" => true, "msg" => "Detalle de compra registrado.", "data" => ["id" => $idDetalle]], 201);
            } else {
                throw new Exception('Error al registrar detalle de compra.');
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
            $this->model->setCompraId($put['compra_id']);
            $this->model->setIdProducto($put['id_producto']);
            $this->model->setPrecioUnitario($put['precio_unitario']);
            $this->model->setCantidad($put['cantidad']);
            $this->model->setSubTotal($put['subtotal']);

            $this->model->actualizar();
            jsonResponse(["status" => true, "msg" => "Detalle de compra actualizado."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => 'ERROR: ' . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function ver($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Método no permitido', 405);
            if (empty($id)) throw new Exception('ID requerido.', 400);

            $this->model->setId($id);
            $data = $this->model->getDetalle();
            if ($data) {
                jsonResponse(["status" => true, "data" => $data], 200);
            } else {
                throw new Exception('Detalle no encontrado', 404);
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
            jsonResponse(["status" => true, "msg" => "Detalle de compra eliminado (lógico)."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
