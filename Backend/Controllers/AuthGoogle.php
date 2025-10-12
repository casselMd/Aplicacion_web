<?php
require_once('Libraries/Core/Controller.php');

class AuthGoogle extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // Redirigido por Google
    public function callback()
    {
        session_start();

        if (!isset($_GET['code'])) {
            echo "No se recibió código de autenticación.";
            return;
        }

        $code = $_GET['code'];
        $client_id = GOOGLE_CLIENT_ID;
        $client_secret = GOOGLE_CLIENT_SECRET;
        $redirect_uri = 'http://localhost/Delicias/Backend/AuthGoogle/callback';

        $data = http_build_query([
            'code' => $code,
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ]);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded",
                'content' => $data
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents('https://oauth2.googleapis.com/token', false, $context);
        $result = json_decode($response, true);

        $access_token = $result['access_token'] ?? null;
        if (!$access_token) {
            echo "No se pudo obtener el token.";
            return;
        }

        $userInfo = file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=$access_token");
        $user = json_decode($userInfo, true);
        $user_imagen = $user['picture'] ?? null;
        $correo = $user['email'] ?? null;

        if (!$correo) {
            echo "Correo no recibido.";
            return;
        }

        require_once 'Models/AuthGoogleModel.php';
        $model = new AuthGoogleModel();
        $empleado = $model->getEmpleadoByCorreo($correo);
        $model->guardarFotoPerfil($correo, $user_imagen);

        if (!$empleado) {
            header("Location: http://localhost:4200/login?error=no_registrado");
            exit;
        }

        $req_tokenJWT = getTokenApi();
        $req_tokenEmpleado = fnGenerateTokenEmpleado($empleado);

        $arrAuth = $req_tokenJWT["data"];
        $arrAuth["token_empleado"] = $req_tokenEmpleado;

        $tempAuthID = bin2hex(random_bytes(16));
        $_SESSION["oauth_tokens_$tempAuthID"] = $arrAuth;

        header("Location: http://localhost:4200/auth/google/callback?auth_id=$tempAuthID");
        exit;

    }

    // Endpoint para Angular (obtener tokens por auth_id)
    public function session()
    {
        session_start();
        $auth_id = $_GET['auth_id'] ?? null;

        if (!$auth_id) {
            jsonResponse(["status" => false, "msg" => "Identificador no recibido."], 400);
            return;
        }

        $tokens = $_SESSION["oauth_tokens_$auth_id"] ?? null;

        if (!$tokens) {
            jsonResponse(["status" => false, "msg" => "Sesión no encontrada o expirada."], 404);
            return;
        }

        unset($_SESSION["oauth_tokens_$auth_id"]);
        jsonResponse(["status" => true, "data" => $tokens], 200);
    }
}
