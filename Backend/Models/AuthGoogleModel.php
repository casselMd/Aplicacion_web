<?php
class AuthGoogleModel extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEmpleadoByCorreo($correo)
    {
        try{    
            $sql = "SELECT * FROM empleado WHERE email = ?";
            $data = $this->select($sql, [$correo]);
            return !empty($data) ? $data : null;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function guardarFotoPerfil(string $correo, string $fotoUrl)
{
    try {
        $sql = "UPDATE empleado SET imagen_url = :imagen_url WHERE email = :email";
        $arrData = [":imagen_url" => $fotoUrl, 
                    ":email" =>$correo];
        $request = $this->update($sql, $arrData);
        return $request;
    } catch (Exception $e) {
        throw $e;
    }
}

}
