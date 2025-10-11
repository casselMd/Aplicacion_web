<?php
require_once ("Libraries/vendor/autoload.php");
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
function base_url() {
    return BASE_URL;
}

function url_assets() {
    return BASE_URL . "/Assets";
}

function debug($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

function strClean($strCadena) {
    $string = preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], $strCadena);
    $string = trim($string); //Elimina espacios en blanco al inicio y al final
    $string = stripslashes($string); // Elimina las \ invertidas
    $string = str_ireplace("<script>","",$string);
    $string = str_ireplace("</script>","",$string);
    $string = str_ireplace("<script src>","",$string);
    $string = str_ireplace("<script type=>","",$string);
    $string = str_ireplace("SELECT * FROM","",$string);
    $string = str_ireplace("DELETE FROM","",$string);
    $string = str_ireplace("INSERT INTO","",$string);
    $string = str_ireplace("SELECT COUNT(*) FROM","",$string);
    $string = str_ireplace("DROP TABLE","",$string);
    $string = str_ireplace("OR '1'='1","",$string);
    $string = str_ireplace('OR "1"="1"',"",$string);
    $string = str_ireplace('OR ´1´=´1´',"",$string);
    $string = str_ireplace("is NULL; --","",$string);
    $string = str_ireplace("is NULL; --","",$string);
    $string = str_ireplace("LIKE '","",$string);
    $string = str_ireplace('LIKE "',"",$string);
    $string = str_ireplace("LIKE ´","",$string);
    $string = str_ireplace("OR 'a'='a","",$string);
    $string = str_ireplace('OR "a"="a',"",$string);
    $string = str_ireplace("OR ´a´=´a","",$string);
    $string = str_ireplace("OR ´a´=´a","",$string);
    $string = str_ireplace("--","",$string);
    $string = str_ireplace("^","",$string);
    $string = str_ireplace("[","",$string);
    $string = str_ireplace("]","",$string);
    $string = str_ireplace("==","",$string);
    return $string;
}

function jsonResponse(array $arrData, int $code) {
    if( is_array($arrData) ) {
        header("HTTP/1.1 " . $code);
        header("Content-Type: application/json");
        echo json_encode($arrData, true);
    }
}

function is_correct_text(string $strCadena) {
    $re = '/[a-zA-ZÑñÁáÉéÍíÓóÚúÜü\s]+$/m';
    $isValid = preg_match($re, $strCadena);
    return boolval($isValid);
}

function is_correct_number(string $strCadena) {
    $re = '/[0-9]+$/m';
    $isValid = preg_match($re, $strCadena);
    return boolval($isValid);
}

function is_correct_email(string $strCadena) {
    $re = '/[a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,3})$/m';
    $isValid = preg_match($re, $strCadena);
    return boolval($isValid);
}



function fnAuthorization(array $headers) {
    try {
        if(empty($headers) || !isset($headers["Authorization"])) throw new Exception("Autorización ...requerida.");
        
        $tokenBearer = $headers["Authorization"];
        $arrToken = explode(" ", $tokenBearer); 
        if($arrToken[0] !== "Bearer") throw new Exception("Error en la autorización");
        $tokenJWT = $arrToken[1];
        $decoded = JWT::decode($tokenJWT, new Key(API_KEY, 'HS512'));
        return $decoded;
    } catch (Exception $e ) {
        throw $e;
    }
}
function fnGetEmpleadoIdToken(array $headers):int {
    try {
        if (!isset($headers['Authorization-Empleado']) || empty($headers) )  throw new Exception("Token del empleado no recibido");

        $token = $headers['Authorization-Empleado'];
        $decoded = JWT::decode($token, new Key(API_KEY, 'HS512'));
        //debug($decoded);
        if (!isset($decoded->data->id) ) throw new Exception("Token de empleado no válido: ID no encontrado.");
        return $decoded->data->id;
    } catch (Exception $e) {
        throw $e;
    }
}

function getTokenApi() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_AUTH_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, API_CLIENT.":".API_KEY);

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if($err) {
        $request = "CURL Error : " . $err;
    } else {
        $request = json_decode($result, true);
    }
    return $request;
}
function fnValidateToken($token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, API_VALIDATE_TOKEN_URL. urlencode($token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    //debug($result);exit;
    if($err) {
        $request = "CURL Error : " . $err;
    } else {
        $request = json_decode($result, true);
    }
    return $request;
}

function guardarTokenClienteJWT($tokenData) {
    $jsonData = json_encode($tokenData, JSON_PRETTY_PRINT);
    file_put_contents(__DIR__ . "/../key/token.json", $jsonData);
}
function obtenerTokenClienteJWT() {
    $ruta = __DIR__ . "/../key/token.json";

    if (!file_exists($ruta)) {
        return ["status" => false, "msg" => "No existe token guardado"];
    }

    $data = json_decode(file_get_contents($ruta), true);

    if (empty($data["access_token"])) {
        return ["status" => false, "msg" => "Token inválido"];
    }

    if (isset($data["expires_at"])) {
        $exp = strtotime($data["expires_at"]);
        $now = time();

        if ($exp < $now) {
            return ["status" => false, "msg" => "Token expirado"];
        }
    }
    return ["status" => true, "token" => $data["access_token"]];
}

function fnGenerateTokenEmpleado($request) {
    try {
        // Clave secreta para firmar el token (mejor usar variable de entorno o config segura)
        $secret_key = API_KEY;

        $issuedAt   = time();
        $expire     = $issuedAt + (60*60*6); // Token válido 1 hora

        $payload = [
            "iat" => $issuedAt,
            "exp" => $expire,
            "data"=> [
                        "id"       => $request["id"],
                        "rol"      => $request["rol"] ?? "invitado",
                        "nombre_completo"   => $request["nombre"]." ".$request["apellidos"]
                    ]
        ];

        // Generar el token firmado
        $jwt = JWT::encode($payload, $secret_key, 'HS512');

        return $jwt;
    } catch (Exception $e) {
        throw $e;
    }
}

function fnValidateTokenEmpleado($jwt) {
    $secret_key = API_KEY;

    try {
        // Decodifica y valida el token con la clave y algoritmo HS256
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS512'));

        // $decoded es un objeto, convertimos a array para usar más fácil
        $decoded_array = (array) $decoded;

        return [
            "status" => true,
            "data" => $decoded_array['data'], // datos del empleado dentro del token
            "msg" => "Token válido"
        ];

    } catch (Exception $e) {
        return [
            "status" => false,
            "msg" => "Token inválido: " . $e->getMessage()
        ];
    }
}





/**
 * msg por correo para recuper contraseña
 */
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'Libraries/vendor/autoload.php';
require_once 'Config/config.php';
function enviarCorreoRecuperacion($correoDestino, $token)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = USER_EMAIL;
        $mail->Password   = PASSWORD_EMAIL;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom(USER_EMAIL, 'Panaderia Delicias');
        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Recupera tu contraseña';
        $mail->Body    = "Haz clic en el siguiente enlace para recuperar tu contraseña: 
        <a href='http://localhost:4200/auth/reset-password?token=$token'>Recuperar contraseña</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Muestra el error real
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        throw $e;
    }
}




?>