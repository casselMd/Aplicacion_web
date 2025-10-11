<?php
class DetalleVentaModel extends Mysql {
    private $id;
    private $producto; // Instancia de ProductoModel
    private $cantidad;
    private $precioUnitario;
    private $subtotal;

    public function __construct($id = 0, $producto = null, $cantidad = 0, $precioUnitario = 0.0, $subtotal = 0.0) {
        $this->id = $id;
        $this->producto = $producto;
        $this->cantidad = $cantidad;
        $this->precioUnitario = $precioUnitario;
        $this->subtotal = $subtotal;
        parent::__construct();
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getProducto() {
        return $this->producto;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getPrecioUnitario() {
        return $this->precioUnitario;
    }

    public function getSubtotal() {
        return $this->subtotal;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setProducto($producto) {
        $this->producto = $producto;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
        $this->calcularSubtotal();
    }

    public function setPrecioUnitario($precioUnitario) {
        $this->precioUnitario = $precioUnitario;
        $this->calcularSubtotal();
    }

    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;
    }

    // Método para calcular el subtotal
    public function calcularSubtotal() {
        $this->subtotal = $this->precioUnitario * $this->cantidad;
    }
    public function agregar($id_venta) {
        try {
            $sql = "INSERT INTO detalleventa (venta_id,producto_id, cantidad, precio_unitario, subtotal) VALUES (:venta_id,:producto_id, :cantidad, :precio_unitario, :subtotal)";
            $arrData = [
                ":venta_id"      => $id_venta,
                ":producto_id"   => $this->producto,
                ":cantidad"      => $this->cantidad,
                ":precio_unitario" => $this->precioUnitario,
                ":subtotal"      => $this->subtotal
            ];
            return $this->insert($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /*public function actualizar() {
        try {
            $sql = "UPDATE detalleventa SET producto_id = :producto_id, cantidad = :cantidad, precio_unitario = :precio_unitario, subtotal = :subtotal WHERE id = :id";
            $arrData = [
                ":producto_id"   => $this->producto,
                ":cantidad"      => $this->cantidad,
                ":precio_unitario" => $this->precioUnitario,
                ":subtotal"      => $this->subtotal,
                ":id"            => $this->id
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }*/

    public function eliminar() {
        try {
            $sql = "DELETE FROM detalleventa WHERE id = :id";
            return $this->delete($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDetalleVenta($id) {
        try {
            $sql = "SELECT * FROM detalleventa WHERE venda_id = :venta_id";
            return $this->select($sql, [":venta_id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getDetallesVenta() {
        try {
            $sql = "SELECT * FROM detalleventa ORDER BY id ASC";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    
    /**
     * Lista todos los detalles de venta para una venta dada.
     *
     * @param int $venta_id ID de la venta.
     * @return array Lista de detalles.
     */
    public function listarPorVenta($venta_id) {
        try {
            $sql = "SELECT 
                        id,
                        venta_id,
                        producto_id,
                        cantidad,
                        precio_unitario,
                        subtotal
                    FROM detalle_venta
                    WHERE venta_id = :venta_id";
            $arrData = [":venta_id" => $venta_id];
            $result = $this->select($sql, $arrData);
            return $result;
        } catch(Exception $e) {
            throw $e;
        }
    }

    /**
     * Actualiza un detalle de venta.
     *
     * @return mixed Resultado de la actualización.
     */
    /*public function actualizar() {
        try {
            $venta_id = is_object($this->venta_id) ? $this->venta_id->get("id") : $this->venta_id;
            $producto_id = is_object($this->producto_id) ? $this->producto_id->get("id") : $this->producto_id;
            $sql = "UPDATE detalle_venta
                    SET venta_id = :venta_id,
                        producto_id = :producto_id,
                        cantidad = :cantidad,
                        precio_unitario = :precio_unitario
                    WHERE id = :id";
            $arrData = [
                ":venta_id" => $venta_id,
                ":producto_id" => $producto_id,
                ":cantidad" => $this->cantidad,
                ":precio_unitario" => $this->precio_unitario,
                ":id" => $this->id
            ];
            $result = $this->update($sql, $arrData);
            return $result;
        } catch(Exception $e) {
            throw $e;
        }
    }*/

    
    
}
