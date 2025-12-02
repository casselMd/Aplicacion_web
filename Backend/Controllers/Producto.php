<?php
require_once("Models/CategoriaModel.php");
require_once("Models/UnidadMedidaModel.php");
class Producto extends Controller {

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
    
    // Método por defecto: redirige al listado de productos.
    public function index() {
        // Listar productos y mostrar la vista list.php
        $this->listar();
    }
    
    

    // Registrar un nuevo producto.
    public function registrar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "POST") {
                $post = json_decode(file_get_contents("php://input"), true);
                
                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["nombre"]))  $errorMsg .= "Error en el nombre del producto.\n";
                if (empty($post["descripcion"])) $errorMsg .= "La descripción es obligatoria.\n";
                if (empty($post["precio"]) || !is_numeric($post["precio"])) $errorMsg .= "Error en el precio.\n";
                if (empty($post["categoria_id"]) || !is_numeric($post["categoria_id"])) $errorMsg .= "La categoría es obligatoria.\n";

                if (empty($post["unidad_medida_id"]) || !is_numeric($post["unidad_medida_id"])) $errorMsg .= "La unidad de medida es obligatoria.\n";
                if (!empty($errorMsg)) throw new Exception(nl2br("Se encontraron errores en los datos:\n" . $errorMsg), 200);
                
                // Obtener y limpiar los datos
                $nombre    = strClean($post["nombre"]);
                $descripcion = strClean($post["descripcion"]);
                $precio      = (float)$post["precio"];
                $unidad_medida_id = $post["unidad_medida_id"];
                //$url_imagen  = empty($post["url_imagen"]) ? strClean($post["url_imagen"]): "";
                //debug($categoria_id);exit;
                // Asignar datos al modelo
                $this->model->setNombre( $nombre);
                $this->model->setDescripcion( $descripcion);
                $this->model->setPrecio( $precio);
                $this->model->setCategoria( new CategoriaModel((int)$post["categoria_id"]));

                $this->model->setUnidadMedidaId(new UnidadMedidaModel($unidad_medida_id));
                $this->model->setExistente($post["es_existente"]);
                $this->model->setMargenGanancia($post["margen_ganancia"]);
                $request = $this->model->agregar();
                //debug($request);exit;
                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Producto registrado correctamente.",
                        "data"   => ["id" => $request]
                    ];
                    $code = 201;
                } else {
                    throw new Exception("No se pudo registrar el producto.");
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Actualizar un producto existente.
    public function actualizar($id_producto) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            $code = 200;
            if ($method == "PUT") {
                if (empty($id_producto) || !is_numeric($id_producto)) {
                    throw new Exception("ID de producto no encontrado.");
                }
                $post = json_decode(file_get_contents("php://input"), true);
                
                // Validaciones básicas
                $errorMsg = "";
                if (empty($post["nombre"]) )  $errorMsg .= "Error en el nombre del producto.\n";
                if (empty($post["descripcion"]))  $errorMsg .= "La descripción es obligatoria.\n";
                if (empty($post["precio"]) || !is_numeric($post["precio"])) $errorMsg .= "Error en el precio.\n";
                if (empty($post["categoria_id"]) || !is_numeric($post["categoria_id"])) $errorMsg .= "La categoría es obligatoria.\n";

                if (!empty($errorMsg)) throw new Exception("Se encontraron errores en los datos:\n" . $errorMsg, 200);
                
                $nombre    = strClean($post["nombre"]);
                $descripcion = strClean($post["descripcion"]);
                $unidad_medida_id = $post["unidad_medida_id"];

                $precio      = (float)$post["precio"];
                $status      = (int)strClean($post["status"]) ;
                
                $this->model->setId($id_producto);
                $this->model->setNombre( $nombre);
                $this->model->setDescripcion( $descripcion);
                $this->model->setPrecio( $precio);
                $this->model->setCategoria( new CategoriaModel((int)$post["categoria_id"]));

                $this->model->setUnidadMedidaId(new UnidadMedidaModel($unidad_medida_id));
                $this->model->setStatus($status);
                $this->model->setExistente($post["es_existente"]);
                $this->model->setMargenGanancia($post["margen_ganancia"]);
               // $this->model->set("url_imagen", $url_imagen);

                $request = $this->model->actualizar();
                if ($request > 0) {
                    $response = [
                        "status" => true,
                        "msg"    => "Producto actualizado correctamente."
                    ];
                    $code = 200;
                } else {
                    throw new Exception("No se pudo actualizar el producto.");
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
        
    }

    // Obtener un producto por ID.
    public function ver($id_producto) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                if (empty($id_producto) || !is_numeric($id_producto)) {
                    throw new Exception("ID de producto no encontrado.");
                }
                $this->model->setId($id_producto);
                $producto_data = $this->model->getProducto();
                if (!empty($producto_data)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Producto encontrado.",
                        "data"   => $producto_data
                    ];
                    $code = 200;
                } else {
                    throw new Exception("El producto no existe.", 404);
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }

    // Listar todos los productos.
    public function listar() {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "GET") {
                $arrProductos = $this->model->listar();
                if (!empty($arrProductos)) {
                    $response = [
                        "status" => true,
                        "msg"    => "Productos encontrados.",
                        "data"   => $arrProductos,
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No hay productos para mostrar."
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
    public function stock_minimo(){
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "GET") {
                throw new Exception("Método no permitido", 405);
            }

            $productos = $this->model->getProductosStockMinimo();

            $response = [
                "status" => true,
                "msg" => "Productos con stock bajo encontrados",
                "data" => $productos
            ];
            jsonResponse($response, 200);

        } catch (Exception $e) {
            $response = [
                "status" => false,
                "msg" => "ERROR: " . $e->getMessage()
            ];
            jsonResponse($response, $e->getCode() ?: 400);
        }
    }

    public function productos_bajo_stock()
        {
            try {
                if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                    throw new Exception('Método no permitido.', 405);
                }

                $limit = isset($_GET['limite']) ? intval($_GET['limite']) : 10;

                // Llamamos al modelo
                $res = $this->model->productosBajoStock($limit);

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



    // Eliminar un producto (eliminación física).
    public function eliminar($id_producto) {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            $response = [];
            if ($method == "DELETE") {
                if (empty($id_producto) || !is_numeric($id_producto)) {
                    throw new Exception("ID de producto no encontrado.");
                }
                $this->model->setId($id_producto);
                $request = $this->model->eliminar();
                if($request) {
                    $response = [
                        "status" => true,
                        "msg"    => "Producto eliminado correctamente."
                    ];
                } else {
                    $response = [
                        "status" => false,
                        "msg"    => "No es posible eliminar el producto."
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
            $code = $e->getCode() == 0 ? 400 : $e->getCode();
            jsonResponse($response, $code);
            die();
        }
    }
}
?>
