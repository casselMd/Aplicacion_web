<?php 
// Modelo para DetalleCompra
class DetalleCompraModel extends Mysql {
    private $id;
    private $compra_id;
    private $id_producto; 
    private $precio_unitario;
    private $cantidad;
    private $subtotal;

    public function setId($i) { $this->id = intval($i); }
    public function setCompraId($i) { $this->compra_id = intval($i); }
    public function setIdProducto($i) { $this->id_producto = intval($i); }
    public function setPrecioUnitario($p) { $this->precio_unitario = floatval($p); }
    public function setCantidad($c) { $this->cantidad = floatval($c); }
    public function setSubTotal($s) { $this->subtotal = floatval($s); }

    public function getDetalles($compraId) {
        return $this->select("SELECT * FROM detalle_compra WHERE compra_id = ?", [$this->compra_id]);
    }
    public function agregar() {
        $sql = "INSERT INTO detalle_compra (compra_id, id_producto, precio_unitario, cantidad, subtotal)
                VALUES (?, ?, ?, ?, ?)";
        $data = [
            $this->compra_id,
            $this->id_producto,
            $this->precio_unitario,
            $this->cantidad,
            $this->subtotal
        ];
        return $this->insert($sql, $data);
    }

    public function actualizar() {
        $sql = "UPDATE detalle_compra SET compra_id=?, id_producto=?, precio_unitario=?, cantidad=?, subtotal=? WHERE id = ?";
        $data = [
            $this->compra_id,
            $this->id_producto,
            $this->precio_unitario,
            $this->cantidad,
            $this->subtotal,
            $this->id
        ];
        return $this->update($sql, $data);
    }

    public function eliminar() {
        $sql = "DELETE FROM detalle_compra WHERE id = ?"; // o lógica si prefieres
        return $this->update($sql, [$this->id]);
    }

    public function getDetalle() {
        $sql = "SELECT * FROM detalle_compra WHERE id = ?";
        return $this->select($sql, [$this->id]);
    }

    public function listar() {
        $sql = "SELECT * FROM detalle_compra ORDER BY id DESC";
        return $this->selectAll($sql);
    }
}


