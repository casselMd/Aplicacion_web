<?php


class ProveedorModel extends Mysql {
    
    private $idProveedor;
    private $nombre;
    private $ruc;
    private $direccion;
    private $telefono;
    private $tipo;
    private $observaciones;
    private $estado;

    public function setIdProveedor($id) { $this->idProveedor = intval($id); }
    public function setNombre($nombre) { $this->nombre = strClean($nombre); }
    public function setRuc($ruc) { $this->ruc = $ruc ? strClean($ruc) : null; }
    public function setDireccion($direccion) { $this->direccion = strClean($direccion); }
    public function setTelefono($telefono) { $this->telefono = strClean($telefono); }
    public function setTipo($tipo) { $this->tipo = $tipo; }
    public function setObservaciones($obs) { $this->observaciones = strClean($obs); }
    public function setEstado($estado) { $this->estado = intval($estado); }

    public function agregar() {
        $sql = "INSERT INTO proveedor (nombre, ruc, direccion, telefono, tipo, observaciones, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $data = [
            $this->nombre,
            $this->ruc,
            $this->direccion,
            $this->telefono,
            $this->tipo,
            $this->observaciones,
            $this->estado
        ];
        return $this->insert($sql, $data);
    }

    public function actualizar() {
        $sql = "UPDATE proveedor SET nombre=?, ruc=?, direccion=?, telefono=?, tipo=?, observaciones=?, estado=?
                WHERE id = ?";
        $data = [
            $this->nombre,
            $this->ruc,
            $this->direccion,
            $this->telefono,
            $this->tipo,
            $this->observaciones,
            $this->estado,
            $this->idProveedor
        ];
        return $this->update($sql, $data);
    }

    public function eliminar() {
        $sql = "UPDATE proveedor SET estado = 0 WHERE id = ?";
        return $this->update($sql, [$this->idProveedor]);
    }

    public function getProveedor() {
        $sql = "SELECT * FROM proveedor WHERE id = ?";
        return $this->select($sql, [$this->idProveedor]);
    }

    public function listar() {
        $sql = "SELECT * FROM proveedor ";
        return $this->selectAll($sql);
    }
}
