<?php 
class Auth extends Controller {
    public function __construct(){
        parent::__construct();
    }
    public function recuperar()
    {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method !== "POST") throw new Exception("Método no permitido.");

            $data = json_decode(file_get_contents("php://input"), true);
            $correo = $data["correo"] ?? null;

            if (!$correo) throw new Exception("Correo no enviado");

            $usuario = $this->model->getEmpleadoByCorreo($correo);

            if (!$usuario) throw new Exception("Empleado no encontrado");

            // Generar token de recuperación
            $token = bin2hex(random_bytes(30));
            $this->model->guardarTokenRecuperacion($usuario["id"], $token);

            // Enviar correo
            //require_once 'Helpers/EmailSender.php';
            if (!enviarCorreoRecuperacion($correo, $token)) {
                
                throw new Exception("No se pudo enviar el correo");
            }

            jsonResponse(["status" => true, "msg" => "Correo enviado"], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], 400);
        }
    }

    public function cambiar_password()
    {
        try {
            $method = $_SERVER["REQUEST_METHOD"];
            if ($method !== "POST") throw new Exception("Método no permitido.");

            $data = json_decode(file_get_contents("php://input"), true);
            $token = $data["token"] ?? null;
            $nueva = $data["nueva"] ?? null;

            if (!$token || !$nueva) throw new Exception("Datos incompletos");

            $empleado = $this->model->getEmpleadoByToken($token);
            if (!$empleado) throw new Exception("Token inválido o expirado");

            $hash = hash("SHA256", $nueva);
            $this->model->actualizarPassword($empleado["id"], $hash);

            jsonResponse(["status" => true, "msg" => "Contraseña actualizada"], 200);
        } catch (Exception $e) {
            jsonResponse(["status" => false, "msg" => $e->getMessage()], 400);
        }
    }


}

