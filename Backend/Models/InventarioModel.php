<?php 


class InventarioModel extends Mysql {

    private $idMovimiento;
    private $productoId;
    private $tipoMovimientoId;
    private $cantidad;
    private $fecha;
    private $observaciones;
    private $status;
    private $empleadoId;

    public function setIdMovimiento($id) { $this->idMovimiento = intval($id); }
    public function setProductoId($id) { $this->productoId = intval($id); }
    public function setTipoMovimientoId($id) { $this->tipoMovimientoId = intval($id); }
    public function setCantidad($cantidad) { $this->cantidad = floatval($cantidad); }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    public function setObservaciones($obs) { $this->observaciones = strClean($obs); }
    public function setStatus($status) { $this->status = intval($status); }
    public function setEmpleadoId($id) { $this->empleadoId = intval($id); }

    public function agregar() {
        $sql = "SELECT stock FROM producto where id = $this->productoId";
        $resProducto = $this->selectAll($sql);

        if ($resProducto["stock"] < $this->cantidad)  throw new Exception("Stock insuficiente. Solo quedan {$resProducto["stock"]} unidades.",200);
        


        $sql = "INSERT INTO inventario (producto_id, tipo_movimiento_id, cantidad, fecha, observaciones, status, empleado_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $data = [
            $this->productoId,
            $this->tipoMovimientoId,
            $this->cantidad,
            $this->fecha,
            $this->observaciones,
            $this->status,
            $this->empleadoId
        ];


        return $this->insert($sql, $data);
    }

    public function actualizar() {
        $sql = "UPDATE inventario SET producto_id=?, tipo_movimiento_id=?, cantidad=?, fecha=?, observaciones=?, status=?, empleado_id=?
                WHERE id_movimiento = ?";
        $data = [
            $this->productoId,
            $this->tipoMovimientoId,
            $this->cantidad,
            $this->fecha,
            $this->observaciones,
            $this->status,
            $this->empleadoId,
            $this->idMovimiento
        ];
        return $this->update($sql, $data);
    }

    public function eliminar() {
        try {
            $sql = "UPDATE inventario SET status = 0 WHERE idMovimiento = :id";
            return $this->update($sql, [":id" => $this->idMovimiento]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getMovimiento() {
        try{
            $sql = "SELECT * FROM inventario WHERE id_movimiento = ?";
            return $this->select($sql, [$this->idMovimiento]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
    try {
        $sql = "SELECT 
                i.idMovimiento,
                i.cantidad,
                i.fecha,
                i.observaciones,
                i.status,
                
                -- Producto
                p.id AS producto_id,
                p.nombre AS producto_nombre,

                -- Tipo de Movimiento
                tm.id AS tipo_movimiento_id,
                tm.nombre AS tipo_movimiento_nombre,

                -- Empleado
                e.id AS empleado_id,
                e.nombre AS empleado_nombre,
                e.dni AS empleado_dni

            FROM inventario i
            INNER JOIN producto p ON p.id = i.producto_id
            INNER JOIN tipo_movimiento tm ON tm.id = i.tipo_movimiento_id
            INNER JOIN empleado e ON e.id = i.empleado_id
            WHERE i.status != 0
            ORDER BY i.fecha DESC";

    $raw = $this->selectAll($sql);

    $inventario = [];

    foreach ($raw as $row) {
        $inventario[] = [
            'idMovimiento' => (int)$row['idMovimiento'],
            'cantidad' => (int)$row['cantidad'],
            'fecha' => $row['fecha'],
            'observaciones' => $row['observaciones'],
            'status' => (int)$row['status'],

            'producto' => [
                'id' => (int)$row['producto_id'],
                'nombre' => $row['producto_nombre'],
            ],

            'tipo_movimiento' => [
                'id' => (int)$row['tipo_movimiento_id'],
                'nombre' => $row['tipo_movimiento_nombre'],
            ],

            'empleado' => [
                'id' => (int)$row['empleado_id'],
                'nombre' => $row['empleado_nombre'],
                'dni' => $row['empleado_dni'],
            ],
        ];
    }

    return $inventario;
    } catch (Exception $e) {
        throw $e;
    }
}

}
