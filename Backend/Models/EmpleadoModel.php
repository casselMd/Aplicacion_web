<?php


class EmpleadoModel extends Mysql {
    private $id;
    private $nombre;
    private $apellidos;
    private $username;
    private $password;
    private $status;
    private $fechaCreado;
    private $dni;
    private $rol;

    public function __construct($id = 0, $nombre = '', $apellidos = '', $username = '', $password = '', $status = '', $fechaCreado = '', $dni = '', $rol = '') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->username = $username;
        $this->password = $password;
        $this->status = $status;
        $this->fechaCreado = $fechaCreado;
        $this->dni = $dni;
        $this->rol = $rol;
        parent::__construct();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getUsername() { return $this->username; }
    public function getPassword() { return $this->password; }
    public function getStatus() { return $this->status; }
    public function getFechaCreado() { return $this->fechaCreado; }
    public function getDni() { return $this->dni; }
    public function getRol() { return $this->rol; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setUsername($username) { $this->username = $username; }
    public function setPassword($password) { $this->password = $password; }
    public function setStatus($status) { $this->status = $status; }
    public function setFechaCreado($fechaCreado) { $this->fechaCreado = $fechaCreado; }
    public function setDni($dni) { $this->dni = $dni; }
    public function setRol($rol) { $this->rol = $rol; }

    public function agregar() {
        try {
            $sql = "INSERT INTO empleado (nombre, apellidos, username, password, status, dni, rol) 
                    VALUES (:nombre, :apellidos, :username, :password, :status, :dni, :rol)";
            $arrData = [
                ":nombre"    => $this->nombre,
                ":apellidos" => $this->apellidos,
                ":username"  => $this->username,
                ":password"  => $this->password,
                ":status"    => $this->status,
                ":dni"       => $this->dni,
                ":rol"       => $this->rol
            ];
            return $this->insert($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function actualizar() {
        try {
            $sql = "UPDATE empleado 
                    SET nombre = :nombre, apellidos = :apellidos,  status = :status, dni = :dni, rol = :rol
                    WHERE id = :id";
            $arrData = [
                ":nombre"    => $this->nombre,
                ":apellidos" => $this->apellidos,
               // ":username"  => $this->username,
                //":password"  => $this->password,
                ":status"    => $this->status,
                ":dni"       => $this->dni,
                ":rol"       => $this->rol,
                ":id"        => $this->id
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function actualizarDatosPersonales() {
        try {
            $sql = "UPDATE empleado 
                    SET nombre = :nombre, apellidos = :apellidos,  status = :status, dni = :dni
                    WHERE id = :id";
            $arrData = [
                ":nombre"    => $this->nombre,
                ":apellidos" => $this->apellidos,
                ":status"    => $this->status,
                ":dni"       => $this->dni,
                ":id"        => $this->id
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function actualizarCredenciales() {
        try {
            $sql = "UPDATE empleado 
                    SET username = :username, password = :password
                    WHERE id = :id";
            $arrData = [
                ":username"  => $this->username,
                ":password"  => $this->password,
                ":id"        => $this->id
            ];
            return $this->update($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function eliminar() {
        try {
            $sql = "UPDATE empleado SET status = 0 WHERE id = :id";
            return $this->update($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getEmpleado() {
        try {
            $sql = "SELECT id, nombre, apellidos, username, dni , rol, imagen_url  FROM empleado WHERE id = :id  ";
            return $this->select($sql, [":id" => $this->id]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getEmpleadosActivosCount()
    {
        try {
            $sql = "SELECT COUNT(DISTINCT dni) AS total FROM empleado 
            WHERE status = 1;
            ";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getEmpleadoByUserName(){
        try {
            $sql = "SELECT id, username FROM empleado WHERE username = :username AND status = 1";
            return $this->select($sql, [":username" => $this->username]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar() {
        try {
            $sql = "SELECT * FROM empleado ORDER BY id ASC";
            return $this->selectAll($sql);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function login() {
        try {
            $sql = "SELECT id, dni, username, rol,nombre,apellidos ,status
                    FROM empleado
                    WHERE username = :username 
                        AND password = :password
                        AND status = 1";
            $arrData = [
                ":username" => $this->username,
                ":password" => $this->password
            ];
            return $this->select($sql, $arrData);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
