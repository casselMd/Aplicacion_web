<?php

class AuthjwtModel extends Mysql{

    private $nombre;
    private $apellido;
    private $username;
    private $password;

    private $scope;
    private $client_id;
    private $secret_key;
    private $id_cliente_jwt;
    
    private $id_scope_jwt;
    private $access_token;
    private $expires_in;

    public function __construct() {
        parent::__construct();
    }

    public function set(string $campo, $valor) { $this->$campo = $valor; }
    public function get(string $campo) { return $this->$campo; }

    public function setCliente() {
        try {
            $sql_email_exists = "SELECT * FROM cliente_jwt WHERE username = '{$this->username}' AND status != 0";
            $request_sql_exists = $this->selectAll($sql_email_exists);

            if( empty($request_sql_exists) ) {
                $sql_insert = "INSERT INTO cliente_jwt(nombre, apellido, username, password)
                                VALUE (:nom, :ape, :username, :pass)";
                $arrData = [
                    ":nom" => $this->nombre,
                    ":ape" => $this->apellido,
                    ":username" => $this->username,
                    ":pass" => $this->password
                ];

                $request_insert = $this->insert($sql_insert, $arrData);
                return $request_insert;
            } else {
                throw new Exception("El nombre de usuario ya existe.\n");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCliente() {
        try {
            $sql = "SELECT id, nombre, apellido, username
                    FROM cliente_jwt WHERE username = :username AND status != 0";
            $arrData = [":username" => $this->username];
            $request = $this->select($sql, $arrData);
            return $request;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function setScope() {
        try {
            $sql = "SELECT * FROM scope_jwt WHERE scope = '{$this->scope}' AND id_cliente_jwt = {$this->id_cliente_jwt} AND status !=0 ";
            $request = $this->selectAll($sql);

            if(count($request) == 0) {
                $sql_insert = "INSERT INTO scope_jwt(scope, client_id, secret_key, id_cliente_jwt) VALUES (:scope, :cli, :key, :idcli)";
                $arrData = [
                    ":scope" => $this->scope,
                    ":cli" => $this->client_id,
                    ":key" => $this->secret_key,
                    ":idcli" => $this->id_cliente_jwt
                ];
                $req_insert = $this->insert($sql_insert, $arrData);
                return $req_insert;
            } else {
                throw new Exception(": El Scope ya existe.\n");
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function getScope()
{
    try {
        $sql = "SELECT s.id AS id_scope_jwt, s.scope, c.id AS id_cliente_jwt, c.nombre, c.username
                FROM scope_jwt s
                INNER JOIN cliente_jwt c ON s.id_cliente_jwt = c.id
                WHERE s.client_id = BINARY :idCli
                AND s.secret_key = BINARY :pass
                AND s.status != 0";

        $arrData = [
            ":idCli" => $this->client_id,
            ":pass"  => $this->secret_key
        ];

        $request = $this->select($sql, $arrData);
        return $request;
    } catch (Exception $e) {
        // Manejo de la excepción
    }
    
}

public function setToken()
{   

    try {
        $sql = "INSERT INTO token_jwt(id_cliente_jwt, id_scope_jwt, access_token, expires_in)
                VALUES(:idcli, :idsco, :token, :exp)";

        $arrData = [
            ":idcli" => $this->id_cliente_jwt,
            ":idsco" => $this->id_scope_jwt,
            ":token" => $this->access_token,
            ":exp"   => $this->expires_in
        ];

        $request = $this->insert($sql, $arrData);
        return $request;
    } catch (Exception $e) {
        throw $e;
    }
}
public function getToken()
{
    try {
        $sql = "SELECT * 
                FROM token_jwt t
                INNER JOIN scope_jwt s ON t.id_scope_jwt = s.id
                WHERE t.access_token = BINARY :token
                AND s.status != 0";

        $arrData = [
            ":token" => $this->access_token
        ];

        

        $request = $this->select($sql, $arrData);
        //debug($request);exit;
        return $request;
    } catch (Exception $e) {
        throw $e;
    }
}
}