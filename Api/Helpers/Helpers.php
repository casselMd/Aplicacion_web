<?php

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

?>