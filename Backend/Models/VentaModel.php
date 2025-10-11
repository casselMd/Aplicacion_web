<?php


require_once("Models/ProductoModel.php");

class VentaModel extends Mysql {
    private $id;
    private $empleado; // Instancia de EmpleadoModel
    private $fecha; // Instancia de DateTime
    private $total;
    private $metodoPago; // Instancia de MetodoPagoModel
    private $es_delivery ;
    private $cliente; // Instancia de ClienteModel
    private $detalleVentas = []; // Array de DetalleVentaModel

    public function __construct($id = 0, $empleado = null, $fecha = null, $total = 0.0, $metodoPago = null, $es_delivery= null, $cliente = null, $detalleVentas = []) {
        $this->id = $id;
        $this->empleado = $empleado;
        $this->fecha = $fecha ;
        $this->total = $total;
        $this->metodoPago = $metodoPago;
        $this->es_delivery = $es_delivery;
        $this->cliente = $cliente;
        $this->detalleVentas = $detalleVentas;
        parent::__construct();
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getEmpleado() {
        return $this->empleado;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getMetodoPago() {
        return $this->metodoPago;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getDetalleVentas() {
        return $this->detalleVentas;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setEmpleado($empleado) {
        $this->empleado = $empleado;
    }

    public function setFecha($fecha) {
        if ($fecha instanceof DateTime) {
            $this->fecha = $fecha;
        } else {
            $this->fecha = new DateTime($fecha);
        }
    }

    public function setTotal($total) {
        $this->total = $total;
    }

    public function setMetodoPago($metodoPago) {
        $this->metodoPago = $metodoPago;
    }
    

    public function setDelivery($valor) {
        $this->es_delivery = (int) $valor;
    }

    public function isDelivery() {
        return $this->es_delivery;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setDetalleVentas($detalleVentas) {
        $this->detalleVentas = $detalleVentas;
    }
    public function listar() {
        $sql = "SELECT 
                    v.*,
                    e.id AS empleado_id, e.nombre AS empleado_nombre, e.dni AS empleado_dni,
                    c.id AS cliente_id, c.cliente AS cliente_nombre,
                    mp.id AS metodo_pago_id, mp.metodo_pago AS metodo_pago_nombre
                FROM venta v
                INNER JOIN empleado e ON e.id = v.empleado_id
                INNER JOIN cliente c ON c.id = v.cliente_id
                INNER JOIN metodo_pago mp ON mp.id = v.metodo_pago_id
                
                ORDER BY v.fecha DESC";
        
        $datosCrudos = $this->selectAll($sql);
        $ventas = [];

        foreach ($datosCrudos as $row) {
            $ventas[] = [
                "id" => (int)$row['id'],
                "fecha" => $row["fecha"],
                "total" => $row["total"],
                "status" => (int)$row["status"],

                'empleado' => [
                    'id' => (int)$row['empleado_id'],
                    'nombre' => $row['empleado_nombre'],
                    'dni' => $row['empleado_dni'],
                ],
                'cliente' => [
                    'id' => (int)$row['cliente_id'],
                    'nombre' => $row['cliente_nombre'],
                ],
                'metodo_pago' => [
                    'id' => (int)$row['metodo_pago_id'],
                    'nombre' => $row['metodo_pago_nombre'],
                ],
                "es_delivery" => $row["es_delivery"]
            ];
        }

        return $ventas;
    }

    public function agregar() {
        try {
            
            $this->beginTransaction();
            $empleado_id = is_object($this->empleado) ? $this->empleado->getId() : $this->empleado;
            $metodoPago_id = is_object($this->metodoPago) ? $this->metodoPago->getId() : $this->metodoPago;
            $cliente_id = is_object($this->cliente) ? $this->cliente->getId() : $this->cliente;

            $sql = "INSERT INTO venta (empleado_id, total, metodo_pago_id,es_delivery ,cliente_id,status) VALUES (:empleado_id, :total, :metodo_pago_id, :es_delivery,:cliente_id,:status)";
            $arrData = [
                ":empleado_id"   => $empleado_id,
                //":fecha"         => $this->fecha,
                ":total"         => $this->total,
                ":metodo_pago_id" => $metodoPago_id,
                ":es_delivery"    => $this->es_delivery,
                ":cliente_id"    => $cliente_id,
                "status"         => 2
            ];
            $venta_id =  $this->insert($sql, $arrData);
            if (!$venta_id) {
                throw new Exception("Error al insertar la venta.");
            }
            //debug($detalles);exit;
            
            foreach ($this->detalleVentas as $detalle) {
            
                $fields = "venta_id, producto_id, cantidad, precio_unitario,subtotal";
                $placeholders = ":venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal";
                $arrDataDetalle = [
                    ":venta_id" => $venta_id,
                    ":producto_id" => (int)$detalle["producto_id"],
                    ":cantidad" => (int)$detalle["cantidad"],
                    ":precio_unitario" => (float)$detalle["precio_unitario"],
                    ":subtotal" => (float) $detalle["subtotal"]
                ];
                
                
                $sqlDetalle = "INSERT INTO detalle_venta ($fields) VALUES ($placeholders)";
                $detalleResult = $this->insert($sqlDetalle, $arrDataDetalle);
                if (!$detalleResult) {
                    throw new Exception("Error al insertar un detalle de venta.");
                }


                // $sqlInv = "INSERT INTO inventario (producto_id, 3,  cantidad,fecha, observaciones, status,empleado_id ) 
                //             VALUES ( :producto_id, 2,  :cantidad,NOW(),:observaciones,1,:empleado_id)";
                // $obs = "Salida por Venta ID $venta_id";
                // $dataInv = [
                //     ":producto_id"   => intval($detalle['producto_id']),
                //     ":cantidad"      => intval($detalle['cantidad']),
                //     ":observaciones" => $obs,
                //     ":empleado_id"   => $this->empleado->getId()
                // ];
                // $resInv = $this->insert($sqlInv, $dataInv);
                // if (!$resInv) {
                //     throw new Exception("Error al insertar movimiento en inventario para detalle de compra $resInv.");
                // }

            }
            
            $this->commit();
            return $venta_id;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar() {
        try {
            $sql = "UPDATE venta SET status = 0 WHERE id = :id";
            return $this->update($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function actualizar() {
        try {
            $this->beginTransaction();

            // IDs relacionados
            $empleado_id = is_object($this->empleado) ? $this->empleado->getId() : $this->empleado;
            $cliente_id = is_object($this->cliente) ? $this->cliente->getId() : $this->cliente;
            $metodoPago_id = is_object($this->metodoPago) ? $this->metodoPago->getId() : $this->metodoPago;

            // Actualizar venta principal
            $sql = "UPDATE venta SET 
                        empleado_id = :empleado_id,
                        cliente_id = :cliente_id,
                        total = :total,
                        metodo_pago_id = :metodo_pago_id,
                        es_delivery = :es_delivery
                    WHERE id = :id";

            $params = [
                ":empleado_id"    => $empleado_id,
                ":cliente_id"     => $cliente_id,
                ":total"          => $this->total,
                ":metodo_pago_id" => $metodoPago_id,
                ":es_delivery" => $this->es_delivery,
                ":id"             => $this->id
            ];
            $resVenta = $this->update($sql, $params);
            if (!$resVenta) throw new Exception("No se pudo actualizar la venta.");

            // Eliminar detalles anteriores
            $this->delete("DELETE FROM detalle_venta WHERE venta_id = :id", [":id" => $this->id]);

            // Insertar nuevos detalles
            foreach ($this->detalleVentas as $detalle) {
                $sqlDetalle = "INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                            VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
                $paramsDetalle = [
                    ":venta_id"       => $this->id,
                    ":producto_id"    => (int)$detalle["producto_id"],
                    ":cantidad"       => (int)$detalle["cantidad"],
                    ":precio_unitario"=> (float)$detalle["precio_unitario"],
                    ":subtotal"       => (float)$detalle["subtotal"]
                ];
                $resDetalle = $this->insert($sqlDetalle, $paramsDetalle);
                if (!$resDetalle) throw new Exception("Error al insertar detalle de venta.");
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }
    public function confirmar() {
        try {
            $this->beginTransaction();
            $empleado_id = is_object($this->empleado) ? $this->empleado->getId() : $this->empleado;
            // Confirmar venta
            $sql = "UPDATE venta SET status = 1 WHERE id = :id";
            $res = $this->update($sql, [":id" => $this->id]);
            if (!$res) throw new Exception("No se encontró la venta.", 405);

            // Obtener detalles para registrar inventario
            $detalles = $this->selectAll("SELECT * FROM detalle_venta WHERE venta_id = $this->id");

            foreach ($detalles as $d) {
                $obs = "Salida confirmada por Venta ID {$this->id}";
                $sqlInv = "INSERT INTO inventario (producto_id, tipo_movimiento_id, cantidad, fecha, observaciones, status, empleado_id)
                        VALUES (:producto_id, 2, :cantidad, NOW(), :observaciones, 1, :empleado_id)";
                $paramsInv = [
                    ":producto_id"   => (int)$d["producto_id"],
                    ":cantidad"      => (int)$d["cantidad"],
                    ":observaciones" => $obs,
                    ":empleado_id"   => $empleado_id
                ];
                $resInv = $this->insert($sqlInv, $paramsInv);
                if (!$resInv) throw new Exception("Error al insertar movimiento en inventario.");
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }



    public function getVenta() {
        // Traer los datos completos de la venta junto con joins
        $sql = "SELECT 
                    v.*,
                    c.cliente as cliente_nombre, c.id as cliente_dni, 
                    e.id as empleado_id, e.dni, e.nombre as empleado_nombre,
                    mp.id as metodo_pago_id , mp.metodo_pago as metodo_pago_nombre 
                FROM venta v
                INNER JOIN cliente c ON c.id = v.cliente_id 
                INNER JOIN empleado e ON e.id = v.empleado_id 
                INNER JOIN metodo_pago mp ON mp.id = v.metodo_pago_id
                WHERE v.id = :id 
                LIMIT 1";

        $row = $this->select($sql, [":id" => $this->id]);

        if (!$row) return null;

        // Organizar la venta principal
        $venta = [
            'id' => (int)$row['id'],
            'fecha' => $row['fecha'],
            'total' => $row['total'],
            'status' => (int)$row['status'],
            'empleado' => [
                'id' => (int)$row['empleado_id'],
                'nombre' => $row['empleado_nombre'],
                'dni' => $row['dni'],
            ],
            'cliente' => [
                'nombre' => $row['cliente_nombre'],
                'id' => $row['cliente_dni'],
            ],
            'metodo_pago' => [
                'id' => (int)$row['metodo_pago_id'],
                'nombre' => $row['metodo_pago_nombre'],
            ],
            "es_delivery" => $row["es_delivery"]
        ];

        // Traer los detalles de la venta (unir con productos si deseas más info)
        $sqlDetalles = "SELECT 
                            dv.id,
                            dv.precio_unitario,
                            dv.cantidad,
                            dv.subtotal,
                            p.id AS producto_id,
                            p.nombre AS producto_nombre
                        FROM detalle_venta dv
                        INNER JOIN producto p ON p.id = dv.producto_id
                        WHERE dv.venta_id = $this->id";

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

        $venta['detalles'] = $detalles;

        return $venta;
    }

    // reportes
    public function getVentasMensuales() {
    try {
            $sql = "SELECT MONTH(fecha) AS mes, SUM(total) AS total_ventas
                FROM venta
                WHERE status = 1
                GROUP BY MONTH(fecha)";
        $result = $this->selectAll($sql);

        // Inicializar array con 12 meses
        $ventasPorMes = array_fill(0, 12, 0);

        foreach ($result as $fila) {
            $mes = intval($fila['mes']) - 1; // enero = 0
            $ventasPorMes[$mes] = floatval($fila['total_ventas']);
        }

        return $ventasPorMes;
    } catch (Exception $e) {
        throw $e;
    }
    }
    public function totalVentasDelDia(): array
    {
        try {
            $sql = "
                SELECT
                COUNT(*)            AS cantidad,
                COALESCE(SUM(total), 0) AS total
                FROM venta
                WHERE status = 1
                AND DATE(fecha) = CURRENT_DATE()
            ";

            // select() devuelve un array asociativo con los campos de la fila
            $row = $this->select($sql,[]);
            // Algunos ORMs devuelven fila aplanada, ajusta si es necesario:
            return [
                'cantidad' => (int)($row['cantidad'] ?? 0),
                'total'    => (float)($row['total']    ?? 0),
            ];
            } catch (Exception $e) {
                throw $e;
            }
    }


    public function productosMasVendidos(int $limit ): array
    {
        $sql = "
            SELECT
                p.id                 AS producto_id,
                p.nombre             AS producto,
                SUM(d.cantidad)      AS cantidad
            FROM detalle_venta d
            INNER JOIN producto p ON p.id = d.producto_id
            GROUP BY p.id, p.nombre
            ORDER BY cantidad DESC
            LIMIT  $limit
        ";
        // Muchos motores no permiten bind directo en LIMIT, ajusta si es necesario
        $params = [':limit' => $limit];
        $rows = $this->selectAll($sql);

        // Mapear tipos
        return array_map(function($r) {
            return [
                'producto_id' => (int)$r['producto_id'],
                'producto'    => $r['producto'],
                'cantidad'    => (int)$r['cantidad']
            ];
        }, $rows);
    }
    public function obtenerVentasPorDiaSemana(){
        try {
            // Obtener los datos reales de la base de datos (en inglés)
            $sql = "SELECT 
                        DAYNAME(fecha) AS dia,
                        COUNT(*) AS total
                    FROM venta
                    WHERE DATE(fecha) >= CURDATE() - INTERVAL 15 DAY
                    GROUP BY dia";
            
            $resultados = $this->selectAll($sql);

            // Mapeo de días en inglés a español abreviado
            $dias = [
                'Monday' => 'Lun',
                'Tuesday' => 'Mar',
                'Wednesday' => 'Mié',
                'Thursday' => 'Jue',
                'Friday' => 'Vie',
                'Saturday' => 'Sáb',
                'Sunday' => 'Dom'
            ];

            // Inicializar los 7 días con 0
            $ventas = [];
            foreach ($dias as $diaEn => $diaEs) {
                $ventas[$diaEs] = 0;
            }

            // Llenar con los datos reales
            foreach ($resultados as $fila) {
                $diaEn = $fila['dia'];
                $diaEs = $dias[$diaEn] ?? null;
                if ($diaEs !== null) {
                    $ventas[$diaEs] = (int) $fila['total'];
                }
            }

            // Convertir a formato de array con claves "dia" y "total"
            $resFinal = [];
            foreach ($ventas as $dia => $total) {
                $resFinal[] = ['dia' => $dia, 'total' => $total];
            }

            return $resFinal;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function obtenerVentasPorTipoAtencion() {
        try {
            $sql = "SELECT 
                    DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    CASE 
                        WHEN es_delivery = 1 THEN 'Delivery'
                        ELSE 'Local'
                    END AS canal,
                    COUNT(*) AS total
                FROM venta
                WHERE status = 1
                AND fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY mes, canal
                ORDER BY mes ASC
            ";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }





    
}
