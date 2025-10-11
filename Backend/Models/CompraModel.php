<?php 

class CompraModel extends Mysql {
    private $id;
    private $empleado_id;
    private $fecha;
    private $total;
    private $status;
    private $proveedor_id;
    private $tipo_documento;
    private $numero_documento;
    private $metodo_pago_id;
    private $observaciones;
    private $fecha_registro;
    private $detalles = [];

    public function __construct(){
        parent:: __construct();
    }
    public function setId($i) { $this->id = intval($i); }
    public function setEmpleadoId($i) { $this->empleado_id = $i; }
    public function setFecha($f) { $this->fecha = $f; }
    public function setTotal($t) { $this->total = floatval($t); }
    public function setStatus($s) { $this->status = intval($s); }
    public function setProveedorId($p) { $this->proveedor_id = intval($p); }
    public function setTipoDocumento($td) { $this->tipo_documento = strClean($td); }
    public function setNumeroDocumento($nd) { $this->numero_documento = strClean($nd); }
    public function setMetodoPagoId($m) { $this->metodo_pago_id = intval($m); }
    public function setObservaciones($o) { $this->observaciones = strClean($o); }
    public function setFechaRegistro($fr) { $this->fecha_registro = $fr; }
    public function setDetalles($d) { $this->detalles = $d; }

    private function calcularTotal(){
        $total = 0;
        foreach($this->detalles as $det ){
            $total += floatval($det['precio_unitario']) * intval($det['cantidad']);
        }
        return $total;
    }

    /**
     * Inserta compra y sus detalles en una transacción.
     */
    public function registrarCompra() {
        try {
            $this->beginTransaction();
            // Insertar compra
            $sql = "INSERT INTO compra (empleado_id, fecha, total, proveedor_id, tipo_documento, numero_documento, metodo_pago_id, observaciones, fecha_registro)
                    VALUES (:empleado_id, :fecha, :total, :proveedor_id, :tipo_documento, :numero_documento, :metodo_pago_id, :observaciones, :fecha_registro)";
            $params = [
                ":empleado_id" => $this->empleado_id,
                ":fecha" => $this->fecha,
                ":total" => $this->calcularTotal(),
                ":proveedor_id" => $this->proveedor_id,
                ":tipo_documento" => $this->tipo_documento,
                ":numero_documento" => $this->numero_documento,
                ":metodo_pago_id" => $this->metodo_pago_id,
                ":observaciones" => $this->observaciones,
                ":fecha_registro" => $this->fecha_registro
            ];
            $compraId = $this->insert($sql, $params);

            if (!$compraId) throw new Exception('Error al insertar compra.');
            
            // Insertar detalles
            $sqlDet = "INSERT INTO detalle_compra (compra_id, id_producto, precio_unitario, cantidad, subtotal)
            VALUES (:compra_id ,:id_producto, :precio_unitario, :cantidad, :subtotal)";

            foreach ($this->detalles as $d) {
                $subtotal = floatval($d['precio_unitario']) * intval($d['cantidad']);
                $dataDet = [
                    ":compra_id" => $compraId,
                    ":id_producto" => intval($d['id_producto']),
                    ":precio_unitario" => floatval($d['precio_unitario']),
                    ":cantidad" => intval($d['cantidad']),
                    ":subtotal" => $subtotal
                ];
                $res = $this->insert($sqlDet, $dataDet);
                if (!$res) throw new Exception('Error en detalle de compra.');
            
                // 3) Insertar en inventario
                $sqlInv = "INSERT INTO inventario (producto_id, tipo_movimiento_id,  cantidad,fecha, observaciones, status,empleado_id ) 
                VALUES ( :producto_id, 1,  :cantidad,NOW(),:observaciones,1,:empleado_id
                )";
                $obs = "Entrada por compra ID $compraId";
                $dataInv = [
                    ":producto_id"   => intval($d['id_producto']),
                    ":cantidad"      => intval($d['cantidad']),
                    ":observaciones" => $obs,
                    ":empleado_id"   => $this->empleado_id
                ];
                $resInv = $this->insert($sqlInv, $dataInv);
                if (!$resInv) {
                    throw new Exception("Error al insertar movimiento en inventario para detalle de compra $resInv.");
                }
            }
            $this->commit();
            return $compraId;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function getCompraConDetalles() {
    // Traer los datos completos de la compra junto con joins
    $sql = "SELECT 
                c.*,
                p.id as proveedor_id, p.nombre as proveedor_nombre, p.ruc as proveedor_ruc, 
                e.id as empleado_id, e.dni, e.nombre as empleado_nombre,
                mp.id as metodo_pago_id , mp.metodo_pago as metodo_pago_nombre 
            FROM compra c
            INNER JOIN proveedor p  ON p.id = c.proveedor_id 
            INNER JOIN empleado e ON e.id = c.empleado_id 
            INNER JOIN metodo_pago mp ON mp.id = c.metodo_pago_id
            WHERE c.id = :id 
            LIMIT 1";
    
    $row = $this->select($sql, [":id" => $this->id]);

    if (!$row) return null;

    // Organizar la compra principal
    $compra = [
        'id' => (int)$row['id'],
        'fecha' => $row['fecha'],
        'total' => $row['total'],
        'tipo_documento' => $row['tipo_documento'],
        'numero_documento' => $row['numero_documento'],
        'observaciones' => $row['observaciones'],
        'status' => (int)$row['status'],
        'fecha_registro' => $row['fecha_registro'],

        'empleado' => [
            'id' => (int)$row['empleado_id'],
            'nombre' => $row['empleado_nombre'],
            'dni' => $row['dni'],
        ],
        'proveedor' => [
            'id' => (int)$row['proveedor_id'],
            'nombre' => $row['proveedor_nombre'],
            'ruc' => $row['proveedor_ruc'],
        ],
        'metodo_pago' => [
            'id' => (int)$row['metodo_pago_id'],
            'nombre' => $row['metodo_pago_nombre'],
        ],
    ];

    // Traer los detalles de la compra (unir con productos si deseas más info)
    $sqlDetalles = "SELECT 
                        dc.id,
                        dc.precio_unitario,
                        dc.cantidad,
                        dc.subtotal,
                        p.id AS producto_id,
                        p.nombre AS producto_nombre
                    FROM detalle_compra dc
                    INNER JOIN producto p ON p.id = dc.id_producto
                    WHERE dc.compra_id = $this->id";

    $detallesRaw = $this->selectAll($sqlDetalles);
    $detalles = [];

    foreach ($detallesRaw as $det) {
        $detalles[] = [
            'id' => $det['id'],
            'producto' => [
                'id' => (int)$det['producto_id'],
                'nombre' => $det['producto_nombre'],
            ],
            'precio_unitario' => (float)$det['precio_unitario'],
            'cantidad' => (int)$det['cantidad'],
            'subtotal' => (float)$det['subtotal'],
        ];
    }

    $compra['detalles'] = $detalles;

    return $compra;
}


    public function listar() {
        $sql = "SELECT 
            c.*,
            p.id as proveedor_id, p.nombre as proveedor_nombre, p.ruc as proveedor_ruc, 
            e.id as empleado_id, e.dni, e.nombre as empleado_nombre,
            mp.id as metodo_pago_id , mp.metodo_pago as metodo_pago_nombre 
            
            FROM compra c
            INNER JOIN proveedor p  ON p.id = c.proveedor_id 
            INNER JOIN empleado e on e.id = c.empleado_id 
            INNER JOIN metodo_pago mp on mp.id = c.metodo_pago_id
            WHERE c.status = 1 
        ORDER BY fecha DESC";

        $datosCrudos =  $this->selectAll($sql);
        $compras = [];
        foreach ($datosCrudos as $row) {
            $compras[] = [
                'id' => (int)$row['id'],
                'fecha' => $row['fecha'],
                'total' => $row['total'],
                'tipo_documento' => $row['tipo_documento'],
                'numero_documento' => $row['numero_documento'],
                'observaciones' => $row['observaciones'],
                'status' => (int)$row['status'],
                'fecha_registro' => $row['fecha_registro'],
                
                'empleado' => [
                    'id' => (int)$row['empleado_id'],
                    'nombre' => $row['empleado_nombre'],
                    'dni' => $row['dni'],
                ],
                'proveedor' => [
                    'id' => (int)$row['proveedor_id'],
                    'nombre' => $row['proveedor_nombre'],
                    'ruc' => $row['proveedor_ruc'],
                ],
                'metodo_pago' => [
                    'id' => (int)$row['metodo_pago_id'],
                    'nombre' => $row['metodo_pago_nombre'],
                ],

            ];
        }
        return $compras;
    }

    public function eliminar() {
        return $this->update("UPDATE compra SET status = 0 WHERE id = ?", [$this->id]);
    }

    public function getResumenCompra(){
        try {
            $sql = "SELECT MONTH(fecha) AS mes, SUM(total) AS total_compras
                FROM compra
                WHERE status = 1
                GROUP BY MONTH(fecha)
                ORDER BY mes";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }

    }public function getComprasMensuales() {
        $sql = "SELECT MONTH(fecha) AS mes, SUM(total) AS total_compras
                FROM compra
                WHERE status = 1
                GROUP BY MONTH(fecha)";
                
        $result = $this->selectAll($sql);

        // Inicializar array con 12 meses (de enero a diciembre)
        $comprasPorMes = array_fill(0, 12, 0);

        foreach ($result as $fila) {
            $mes = intval($fila['mes']) - 1; // enero = 0, diciembre = 11
            $comprasPorMes[$mes] = floatval($fila['total_compras']);
        }

        return $comprasPorMes;
    }


}







