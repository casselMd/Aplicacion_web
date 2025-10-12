<?php

class Proveedor extends Controller {
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

            if (empty($post['nombre'])) $errorMsg .= 'Nombre requerido.\n';
            if (isset($post['ruc']) && strlen($post['ruc']) > 0 && !preg_match('/^[0-9]{11}$/', $post['ruc'])) $errorMsg .= 'RUC inválido.\n';
            if (!empty($errorMsg)) throw new Exception($errorMsg, 400);

            $this->model->setNombre($post['nombre']);
            $this->model->setRuc($post['ruc'] ?? null);
            $this->model->setDireccion($post['direccion'] ?? '');
            $this->model->setTelefono($post['telefono'] ?? '');
            $this->model->setTipo($post['tipo'] ?? 'INFORMAL');
            $this->model->setObservaciones($post['observaciones'] ?? '');
            $this->model->setEstado(1);

            $idInsertado = $this->model->agregar();
            if ($idInsertado > 0) {
                jsonResponse(["status" => true, "msg" => "Proveedor registrado.", "data" => ["id" => $idInsertado]], 201);
            } else {
                throw new Exception('Error al registrar proveedor.');
            }
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => 'ERROR: ' . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function actualizar($idProveedor) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') throw new Exception('Método no permitido', 405);
            if (empty($idProveedor)) throw new Exception('ID requerido.', 400);

            $put = json_decode(file_get_contents('php://input'), true);

            $this->model->setIdProveedor($idProveedor);
            $this->model->setNombre($put['nombre']);
            $this->model->setRuc($put['ruc'] ?? null);
            $this->model->setDireccion($put['direccion'] ?? '');
            $this->model->setTelefono($put['telefono'] ?? '');
            $this->model->setTipo($put['tipo'] ?? 'INFORMAL');
            $this->model->setObservaciones($put['observaciones'] ?? '');
            $this->model->setEstado($put['estado'] ?? 1);

            $this->model->actualizar();
            jsonResponse(["status" => true, "msg" => "Proveedor actualizado."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => 'ERROR: ' . $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function ver($idProveedor) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') throw new Exception('Método no permitido', 405);
            if (empty($idProveedor)) throw new Exception('ID requerido.', 400);

            $this->model->setIdProveedor($idProveedor);
            $data = $this->model->getProveedor();
            if ($data) {
                jsonResponse(["status" => true, "data" => $data], 200);
            } else {
                throw new Exception('Proveedor no encontrado', 404);
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

    public function eliminar($idProveedor) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') throw new Exception('Método no permitido', 405);
            if (empty($idProveedor)) throw new Exception('ID requerido.', 400);

            $this->model->setIdProveedor($idProveedor);
            $this->model->eliminar();
            jsonResponse(["status" => true, "msg" => "Proveedor eliminado (lógico)."], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
