<?php
define('WP_USE_THEMES', false);
// Cargar wp-load.php desde el directorio raíz de WordPress
if (!defined('ABSPATH')) {
    $public_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
    include($public_path);
}

/////////////////////////
///////   XEERPA
/////////////////////////
// URL de Xeerpa a la que se accede desde el servidor de la web
$xeerpa = get_option('xpsocial_urlSocial');

// $authToken es el identificador de acceso de cada integracion
$authToken     = get_option('xpsocial_authToken');


// $loginURL es la URL a la que se llama para iniciar el flujo de llamadas para el Social Login. Se le pasa el parámetro 'authToken' y además un parámetro 'info' para que devolvamos la información del usuario desglosada
$loginURL = $xeerpa . '/auth?authToken=' . $authToken . '&info=true';


/////////////////////////
///////   IMPORTANT: SEGURIDAD -- SECURITY
/////////////////////////
/*
        When the authentication popup needs to send user data back to the page that opened it, you must define this variable with the allowed domain only, so no other pages can access it.
        Example: '$postMessageURL = "http://example.org:8080";'
        Leave it blank "" if you don't plan on returnong any data (maybe you just use it server-side).
        ONLY leave it as "*" for testing purposes.

        En el caso de que el popup de autentificación quiera pasar información del usuario a la página que lo abrió, se debe definir esta variable con el domínio permitido para evitar que otras páginas llamen a ese popup
        Ejemplo: '$postMessageURL = "http://example.org:8080";'
        Deja esta variable vacía "" en caso de que la información no vaya a devolverse
        Déjala como "*" solo en entornos de pruebas seguros
    */
$postMessageURL = "*";

// Obfuscation pattern, see socialLoginCompleted.php and socialLoginSaveData.php -- Patrón de obfuscación, ver socialLoginCompleted.php y socialLoginSaveData.php
$obfKey1 = '0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_';
$obfKey2 = '0fg14GHIJ789@ADFvwKLM2eh3NOPQz_RSYZabEcdi56jklmVWXnoTUpqBCrstuxy';


/////////////////////////
//////////   CALLBACK
/////////////////////////
// &callbackURL is the URL to be called when the social network redirects the userafter the login process.
// &callbackURL es la URL a la que se llama cuando la Red Social ha redireccionado al usuario a socialLoginCallback tras el login
$callbackURL = $xeerpa . '/auth/response?';


/////////////////////////////////
//////////   HELPERS -- FUNCIONES DE APOYO
/////////////////////////////////

// Esta función se utiliza en socialLoginCallback para Redes Sociales como LinkedIn -- When using LinkedIn.
function doCurl($url, $fields)
{
    // Only do curl if there are parameters, a call without parameters is not a valid one.
    // Sólo do curl si hay parámetros, si no los hay no será una llamada válida.
    if (!$fields && strpos($url, '?') < 0) {
        $result = '{"errorCode":-35,"message":"No parameters available."}';
        echo 'no fields: ' . $result;
        return $result;
    } else {
        $ch = curl_init($url);
        // Si hay fields significa que la llamada se hace a través de POST  -- For POST requests of the 'fields' parameter.
        if ($fields) {
            curl_setopt($ch, CURLOPT_POST, count(explode("&", $fields)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Se hace la llamada y se devuelve el resultado. -- Make call and return result.
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

// Shows the error that ocurred, feel free to implement your own code here.
// Este es un ejemplo de pantalla de error que puede sustituirse por código propio.
function showError($json)
{
    ?>
    <style>
        body {
            margin-top: 200px;
            text-align: center;
        }
    </style>

    <text width="100%" style=" font-weight: bold; font-family: sans-serif;font-size: small;color:gray"> <?php
                                                                                                        echo ($json->errorCode . ": " . $json->message);
    ?></text>
    <?php
}

// Esta funcion se invoca desde socialLoginCallback para redireccionar usuarios en los casos de login de LinkedIn por ejemplo -- Called for LinkedIn.
function redirect($query, $data)
{
    ?>
    <script>
        var redirectTo = window.location.origin + window.location.pathname + '?<?php echo ($query . "&" . str_replace("\n", "", $data)); ?>';
        var hash = window.location.hash;
        if (hash && hash.length > 1) {
            hash = hash.substr(1);
            if (hash.indexOf("=") === -1) hash = "hash=" + hash;
            redirectTo += "&" + hash;
        }
        window.location = redirectTo.split("redirectHash=true").join("");
    </script><?php
}
?>