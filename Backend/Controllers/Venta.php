<?php

require_once("Models/ClienteModel.php");
require_once("Models/InventarioModel.php");
require_once("Models/MetodoPagoModel.php");
require_once("Models/EmpleadoModel.php");

class Venta extends Controller {
    private $empleado_id;
    public function __construct() {
        //Si deseamos proteger todo el Controlador con sus Métodos
        try {
            //=============== Validar Token ====================//
            $arrHeaders = getallheaders();
            $hasAuth = fnAuthorization($arrHeaders);
            //==================================================//
            //obtener el id del empleado 
            $this->empleado_id = fnGetEmpleadoIdToken($arrHeaders);
            
            
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => "ERROR: " . $e->getMessage()], $e->getCode() ?: 401);
            die();
        }
        parent::__construct();
        
        
    }

    // Accion por defecto: listar ventas (y renderizar vista si se requiere)
    public function index() {
        
        //debug($this->empleado["id"]);exit;
    }

    /**
     * Registrar una nueva venta junto con sus detalles.
     * Se espera un JSON con los campos:
     * - cliente_id (o un objeto con propiedad id)
     * - empleado_id (o un objeto con propiedad id)
     * - fecha_venta (opcional)
     * - total
     * - metodo_pago_id (o un objeto con propiedad id)
     * - estado (opcional, default "pendiente")
     * - observaciones (opcional)
     * - detalles: array de objetos (cada detalle debe incluir al menos: producto_id, cantidad, precio_unitario,
     *   y opcionalmente descuento, impuesto)
     */
    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method == "POST") {
                $post = json_decode(file_get_contents("php://input"), true);

                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["cliente_id"]))  $errorMsg .= "El cliente es obligatorio.\n";
                if (empty($post["total"]) || !is_numeric($post["total"])) $errorMsg .= "El total es obligatorio y debe ser numérico.\n";
                if (empty($post["metodo_pago_id"])) $errorMsg .= "El método de pago es obligatorio.\n";
                if (empty($post["detalles"]) || !is_array($post["detalles"])) $errorMsg .= "Los detalles de la venta son obligatorios.\n";
                if (!empty($errorMsg)) throw new Exception(nl2br($errorMsg), 200);

                // Requerir manualmente los modelos relacionados si no usas autoloading
                


   // Asignar datos al modelo utando objetos para las relaciones
                $this->model->setEmpleado(new EmpleadoModel($this->empleado_id));
                $this->model->setCliente(new ClienteModel((int)$post["cliente_id"]));
                $this->model->setTotal( (float)$post["total"]);
                $this->model->setMetodoPago( new MetodoPagoModel((int)$post["metodo_pago_id"]));

                // Extraer el array de detalles
                $this->model->setDetalleVentas($post["detalles"]);

                // Llamar al método registrarVenta() del modelo, el cual se encargará de:
                // - Iniciar transacción
                // - Insertar la venta y obtener el ID
                // - Insertar cada detalle
                // - Commit o rollback según corresponda
                $result = $this->model->agregar();

                if ($result > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Venta registrada correctamente.",
                        "data"   => ["id" => $result]
                    ];
                    jsonResponse($response, 201);
                } else {
                    throw new Exception("No se pudo registrar la venta.");
                }
            } else {
                throw new Exception("Método de solicitud {$method} no permitido.");
            }
        } catch(Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, (int)$e->getCode() ?: 400);
        }
    }


    //Obtiene los datos de una venta por su ID, incluyendo sus detalles.

    public function ver($id_venta) {
        try {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                if (empty($id_venta)) {
                    throw new Exception("ID de venta no encontrado.");
                }
                $this->model->setId($id_venta);
                $venta = $this->model->getVenta();
                if (!empty($venta["id"])) {
                    $response = [
                        "status" => true,
                        "msg"    => "Venta encontrada.",
                        "data"   => $venta
                    ];
                    jsonResponse($response, 200);
                } else {
                    throw new Exception("La venta no existe.", 404);
                }
            } else {
                throw new Exception("Método de solicitud no permitido.");
            }
        } catch(Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() ? (int) $e->getCode() : 400);
        }
    }
    public function actualizar($id_venta) {
        try {
            if ($_SERVER["REQUEST_METHOD"] == "PUT") {
                $put = json_decode(file_get_contents("php://input"), true);

                if (empty($id_venta)) throw new Exception("ID de venta no proporcionado.");
                if (empty($put["cliente_id"])) throw new Exception("El cliente es obligatorio.");
                if (!isset($put["total"]) || !is_numeric($put["total"])) throw new Exception("El total es obligatorio y debe ser numérico.");
                if (empty($put["metodo_pago_id"])) throw new Exception("El método de pago es obligatorio.");
                if (empty($put["detalles"]) || !is_array($put["detalles"])) throw new Exception("Los detalles de la venta son obligatorios.");

                $this->model->setId($id_venta);
                $this->model->setEmpleado(new EmpleadoModel($this->empleado_id));
                $this->model->setCliente(new ClienteModel((int)$put["cliente_id"]));
                $this->model->setTotal((float)$put["total"]);
                $this->model->setMetodoPago(new MetodoPagoModel((int)$put["metodo_pago_id"]));
                $this->model->setDetalleVentas($put["detalles"]);

                $resultado = $this->model->actualizar(); // Este método debe implementarse en el modelo

                if ($resultado) {
                    jsonResponse([
                        "status" => true,
                        "msg"    => "Venta actualizada correctamente."
                    ], 200);
                } else {
                    throw new Exception("No se pudo actualizar la venta.");
                }
            } else {
                throw new Exception("Método de solicitud no permitido.");
            }
        } catch (Exception $e) {
            jsonResponse([
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ], (int)$e->getCode() ?: 400);
        }
    }

    public function confirmar($id_venta) {
        try {
            if ($_SERVER["REQUEST_METHOD"] == "PUT") {
                if (empty($id_venta)) throw new Exception("ID de venta no proporcionado.");

                $this->model->setId($id_venta);
                $this->model->setEmpleado(new EmpleadoModel($this->empleado_id));
                $resultado = $this->model->confirmar(); // Este método también debe implementarse en el modelo

                if ($resultado) {
                    jsonResponse([
                        "status" => true,
                        "msg"    => "Venta confirmada correctamente."
                    ], 200);
                } else {
                    throw new Exception("No se pudo confirmar la venta.");
                }
            } else {
                throw new Exception("Método de solicitud no permitido.");
            }
        } catch (Exception $e) {
            jsonResponse([
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ], (int)$e->getCode() ?: 400);
        }
    }


    //Elimina una venta de forma logica.

    public function eliminar($id_venta) {
        try {
            if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
                if (empty($id_venta)) throw new Exception("ID de venta no encontrado.");
            
                $this->model->setId( $id_venta);
                $result = $this->model->eliminar();
                if ($result) {
                    $response = [
                        "status" => true,
                        "msg"    => "Venta eliminada correctamente."
                    ];
                    jsonResponse($response, 200);
                } else {
                    throw new Exception("No se pudo eliminar la venta.");
                }
            } else {
                throw new Exception("Método de solicitud no permitido.");
            }
        } catch(Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() ? $e->getCode() : 400);
        }
    }

    
    public function listar() {
        try {
            if ($_SERVER["REQUEST_METHOD"] == "GET") {
                $data = $this->model->listar();
                $resumen = $this->model->getVentasMensuales();
                
                $response = [
                    "status" => true,
                    "msg"    => "Ventas encontradas.",
                    "data"   => $data,
                    "resumen"=> $resumen
                ];
                jsonResponse($response, 200);
            } else {
                throw new Exception("Método de solicitud no permitido.");
            }
        } catch(Exception $e) {
            $response = [
                "status" => false,
                "msg"    => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() ? (int)$e->getCode() : 400);
        }
    }

    
    public function total_ventas_del_dia()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.', 405);
            }

            // Llamamos al modelo (Models)
            $res = $this->model->totalVentasDelDia();

            jsonResponse([
                'status' => true,
                'data'   => $res
            ], 200);

        } catch(Exception $e) {
            jsonResponse([
                'status' => false,
                'msg'    => 'ERROR: ' . $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function productos_mas_vendidos($limit)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.', 405);
            }
            $limit = (int) $limit;
            if(!is_numeric($limit)) throw new Exception("Limite no válido", 400);
            
            if($limit < 1 ) $limit = 5;
            // Llamar al modelo
            $data = $this->model->productosMasVendidos($limit);

            jsonResponse([
                'status' => true,
                'data'   => $data
            ], 200);

        } catch(Exception $e) {
            jsonResponse([
                'status' => false,
                'msg'    => 'ERROR: ' . $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
    public function ventas_por_dia_semana() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.', 405);
            }
            $result = $this->model->obtenerVentasPorDiaSemana();
            jsonResponse(['status' => true, 'data' => $result], 200);
        } catch (Exception $e) {
            jsonResponse(['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }
    public function ventas_mensuales() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.', 405);
            }

            $result = $this->model->obtenerVentasMensuales();
            jsonResponse(['status' => true, 'data' => $result], 200);

        } catch (Exception $e) {
            jsonResponse(['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }
    
}



?>
