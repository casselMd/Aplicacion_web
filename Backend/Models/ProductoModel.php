<?php



class ProductoModel extends Mysql {
    private $id;
    private $nombre;
    private $descripcion;
    private $precio;
    private $precio_venta;
    private $stock;
    private $status;
    private $categoria; // Instancia de CategoriaModel
    private $unidad_medida;
    private $es_existente;
    private $margen_ganancia;
    public function __construct($id = 0, $nombre = '', $descripcion = '', $precio = 0.0, $precio_venta = null, $status = '', $categoria = null, $unidad_medida = null, $es_existente= null, $margen_ganancia = null) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->precio_venta = $precio_venta;
        $this->status = $status;
        $this->categoria = $categoria;
        $this->unidad_medida = $unidad_medida;
        $this->es_existente = $es_existente;
        $this->margen_ganancia = $margen_ganancia;
        parent::__construct();
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getPrecio() {
        return $this->precio;
    }
    public function getPrecioVenta() {
        return $this->precio_venta;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getCategoria() {
        return $this->categoria;
    }
    public function getMargenGanancia() {
        return $this->margen_ganancia;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }
    public function setPrecioVenta($precio_venta) {
        $this->precio_venta = $precio_venta;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setCategoria($categoria) {
        $this->categoria = $categoria;
    }
    public function setMargenGanancia($margen_ganancia) {
        $this->margen_ganancia = $margen_ganancia;
    }
    public function agregar() {
        try {
            $sql = "INSERT INTO producto (nombre, descripcion, precio, precio_venta, categoria_id, unidad_medida_id,stock,es_existente, margen_ganancia)
                    VALUES (:nombre, :descripcion, :precio,:precio_venta, :categoria_id, :unidad_medida_id, :stock,:es_existente, :margen_ganancia)";
            $arrData = [
                ":nombre"            => $this->nombre,
                ":descripcion"       => $this->descripcion,
                ":precio"            => $this->precio,
                ":precio_venta"      => $this->precio +  $this->precio*$this->margen_ganancia,
                ":categoria_id"      => $this->categoria->getId(),
                ":unidad_medida_id"  => $this->unidad_medida->getId(),
                ":stock"  => ($this->es_existente) ? 1 : 0  ,
                ":margen_ganancia" => $this->margen_ganancia,
                ":es_existente"  => $this->es_existente 
            ];

            return $this->insert($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizar() {
        try {
            $sql = "UPDATE producto 
                    SET nombre = :nombre, descripcion = :descripcion, precio = :precio, 
                        status = :status, 
                        categoria_id = :categoria_id,
                        unidad_medida_id = :unidad_medida_id,
                        
                        es_existente = :es_existente,
                        margen_ganancia = :margen_ganancia
                    WHERE id = :id";

            $arrData = [
                ":nombre"           => $this->nombre,
                ":descripcion"      => $this->descripcion,
                ":precio"           => $this->precio,
                ":status"           => $this->status,
                ":categoria_id"     => $this->categoria->getId(),
                ":unidad_medida_id" => $this->unidad_medida->getId(),
                //":stock" => $this->es_existente ? 1 : 0,
                ":es_existente" => $this->es_existente ,
                ":margen_ganancia" => $this->margen_ganancia,
                ":id"               => $this->id
            ];

            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function eliminar() {
        try {
            $sql = "UPDATE producto SET status = 0 WHERE id = :id";
            return $this->update($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getProducto() {
        try {
            $sql = "SELECT * FROM producto WHERE id = :id AND status = 1";
            return $this->select($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT 
                        p.id,
                        p.nombre,
                        p.descripcion,
                        p.precio,
                        p.precio_venta,
                        p.stock,
                        p.status,
                        p.es_existente,
                        p.margen_ganancia,
                        c.id AS categoria_id,
                        c.categoria AS categoria_nombre,
                        u.id AS unidad_medida_id,
                        u.nombre AS unidad_medida_nombre
                    FROM producto p
                    JOIN categoria c ON p.categoria_id = c.id
                    JOIN unidad_medida u ON p.unidad_medida_id = u.id
                    ORDER BY p.id DESC";

            $productosDB = $this->selectAll($sql);
            $productos = [];

            foreach ($productosDB as $row) {//linea 156
                $productos[] = [
                    "id" => (int) $row["id"],
                    "nombre" => $row["nombre"],
                    "descripcion" => $row["descripcion"],
                    "precio" => (float) $row["precio"],
                    "precio_venta" => (float) $row["precio_venta"],
                    "stock" => (int) $row["stock"],
                    "status" => (int) $row["status"],
                    "categoria" => [
                        "id" => (int) $row["categoria_id"],
                        "nombre" => $row["categoria_nombre"]
                    ],
                    "unidad_medida" => [
                        "id" => (int) $row["unidad_medida_id"],
                        "nombre" => $row["unidad_medida_nombre"]
                    ],
                    "es_existente" => $row["es_existente"],
                    "margen_ganancia" => $row["margen_ganancia"]
                ];
            }

            return $productos;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCantidadProductosStockMinimo(){
        try {
            $sql = "SELECT COUNT(*) AS total FROM producto WHERE stock < 10";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getProductosStockMinimo(){
        try {
            $sql = "SELECT id,nombre, stock FROM producto WHERE stock < 10 and es_existente = 0 and status != 0 ";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the value of unidad_medida
     */ 
    public function getUnidadMedidaId()
    {
        return $this->unidad_medida;
    }

    public function setUnidadMedidaId($unidad_medida)
    {
        $this->unidad_medida = $unidad_medida;

    }

    /**
     * Get the value of es_existente
     */ 
    public function isExistente()
    {
        return $this->es_existente;
    }
    public function setExistente($es_existente)
    {
        $this->es_existente = $es_existente;
    }

        public function productosBajoStock(int $limit = 10): array
        {
            $sql = "
                SELECT
                    p.id                 AS id,
                    p.nombre             AS nombre,
                    p.stock              AS stock,
                    p.precio             AS precio
                FROM producto p
                WHERE p.stock > 0
                ORDER BY p.stock ASC
                LIMIT $limit
            ";
            
            $rows = $this->selectAll($sql);

            // Mapear tipos
            return array_map(function($r) {
                return [
                    'id'     => (int)$r['id'],
                    'nombre' => $r['nombre'],
                    'stock'  => (int)$r['stock'],
                    'precio' => (float)$r['precio']
                ];
            }, $rows);
        }
}
