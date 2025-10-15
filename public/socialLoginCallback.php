<?php
    header("Content-Type: text/html; charset=UTF-8");
    // Included to access configuration settings.
    // Incluimos socialLoginSettings ya que es donde están definidas algunas variables y funciones que se utilizan en este código.
    include 'socialLoginSettings.php';


    ////// BEFORE STEP 2 - PREPARING CALLS FLOW //////
    ////// ANTES DEL PASO 2 - PREPARACIÓN DEL FLUJO DE LLAMADAS //////
    /*
        Both GET and POST are considered because different social networks use one or the other.
        Possible received parameters are:
        For STEP 2: 'socialNetwork' and 'random' sent by client.html
        For STEP 5: 'code', 'state' returned by Facebook (GET) or 'fields' returned by LinkedIn (POST)

        Se recogen GET y POST porque hay redes sociales que utilizan ambos métodos
        Los parámetros recibidos, entre otros pueden ser:
        PASO 2: 'socialNetwork' y 'random' que nos pasa el HTML
        PASO 5: o 'code' y 'state' devueltos por GET en caso de Facebook, o 'fields' por POST en caso de LinkedIn
    */
    $getString = http_build_query($_GET); // Se recogen todos los parámetros que lleguen por GET  -- All GET params
    $postString = http_build_query($_POST);// Se recogen todos los parámetros que lleguen por POST  -- All POST params
    $doubleResponse = $postString != "" && $getString != "" ? "&" : ""; // Se decide si vamos a necesitar un "&" entre el posts y los gets. -- Checks which separator we need.
    $queryVars = $getString . $doubleResponse . $postString; // Se junta la información recibida por GET y POST -- Join both GET and POST information.

    // socialNetwork chosen by the user. Supported values are FB (facebook), LI (linkedin), TW (twitter), GO (google), IG (instagram)
    // socialNetwork - La red social con la que el usuario quiere autentificarse. Actualmente los valores admitidos son: FB (facebook), LI (linkedin), TW (twitter), GO (google), IG (instagram)
    
    // Helper function to sanitize strings (replaces deprecated FILTER_SANITIZE_STRING)
    // Función auxiliar para limpiar strings (reemplaza FILTER_SANITIZE_STRING deprecado)
    function sanitizeString($input) {
        if ($input === null) return null;
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    $socialNetwork = sanitizeString($_GET["socialNetwork"] ?? null); // Detectamos 'socialNetwork y eliminamos caracteres extraños -- Detect social network and remove unwanted characters
    $redirectHash = sanitizeString($_GET["redirectHash"] ?? null); // Detectamos '#' y eliminamos caracteres extraños -- Detect social network and remove unwanted characters

    ////// CALLING FLOW //////
    ////// FLUJO DE LLAMADAS //////
    /*
        $loginURL and $callbackURL are defined in socialLoginSettings
        STEP 2 - If 'socialNetwork' exists that is because it was received in STEP 1 from client.html - Call &loginURL with &queryVars
        STEP 5 - The social network redirects to socialLoginCallback sending parameters that update $queryVars and remove 'socialNetwork', so &callbackURL is called sending the new &queryVars. At this point &queryVars may contain 'code' and 'state', as returned by Facebook (GET), or 'fields' for LinkedIn (POST).

        $loginURL y $callbackURL están definidas en socialLoginSettings
        PASO 2 - Si 'socialNetwork' existe porque se ha recibido en el PASO 1 desde el HTML - se llama a &loginURL pasando &queryVars
        PASO 5 - La Red Social redirecciona a socialLoginCallback y pasa unos parámetros que actualizan $queryVars entre los que no existe 'socialNetwork' así que se llama a &callbackURL pasando el nuevo &queryVars. En este punto &queryVars puede contener 'code' y 'state' devueltos por GET en caso de Facebook, o 'fields' por POST en caso de LinkedIn
    */

if ($redirectHash) {
    // The social network uses # to send variables to client. We need these in the server.
    // la red social utiliza # para pasar las variables a cliente. Necesitamos acceder a esas variables desde servidor.
    redirect($queryVars, "");
} else {
    // $loginURL and $callbackURL are defined in socialLoginSettings.php
    //  socialNetwork - Network chosen by the user: FB (facebook), LI (linkedin), TW (twitter), GO (google), IG (instagram)

    // $loginURL y $callbackURL están creadas en socialLoginSettings.php
    //  socialNetwork - La red social con la que el usuario quiere autentificarse.
    //  Actualmente los valores admitidos son: FB (facebook), LI (linkedin), TW (twitter), GO (google), IG (instagram)
    $url = "";
    if ($socialNetwork && strlen($socialNetwork) != 2) {
        header("HTTP/1.1 404 Not Found");
    } else {
        /*
            'doCurl' is defined in socialLoginSettings
            STEP 3 - We receive response from &loginURL. Example: '{"errorCode":1, "url":"https://www.facebook.com/...&redirect_uri=./../socialLogin/socialLoginCallback&state=rjeoghoighrpigposfjgñshjgñs"}' and we transform into an object.
            STEP 6 - We receive response from &callbackURL. Example: '{"errorCode":1, "url":"https://..." "data":"someVar=someValue"}' and we transform into an object.

            La función 'doCurl' está definida en socialLoginSettings
            PASO 3 - Recibimos la respuesta de &loginURL. Ejemplo: '{"errorCode":1, "url":"https://www.facebook.com/...&redirect_uri=./../socialLogin/socialLoginCallback&state=rjeoghoighrpigposfjgñshjgñs"}' y lo transformamos en objeto
            PASO 6 - Recibimos la respuesta de &callbackURL. Ejemplo: '{"errorCode":1, "url":"https://..." "data":"someVar=someValue"}' y lo transformamos en objeto
        */
        $url = (($socialNetwork) ? $loginURL : $callbackURL) . "&" . $queryVars;

        $dataFromXeerpa = doCurl($url, null);

        $fromXeerpa = json_decode($dataFromXeerpa); // Transformamos la respuesta en un objeto -- Transform to object.

        // Response must be errorCode : 1. Otherwise there has been an error and we call 'showError', defined in socialLoginSettings
        // La respuesta debe ser errorCode : 1. Si errorCode NO es 1 llamamos a la funcion 'showError' definida en socialLoginSettings
        if ($fromXeerpa -> errorCode != 1) {
            showError($fromXeerpa);
        }

        // If errorCode : 1 the response is successful.
        // Si errorCode : 1 es que la respuesta es correcta.
        elseif (!property_exists($fromXeerpa, "fields")) {
            /*
                STEP 4 - If field 'fields' doesn't exist in &fromXeerpa we do a redirection from the popup to the URL, in this case the social network
                STEP 7 - If field 'fields' doesn't exist in $fromXeerpa we do a redirection from the popup to the URL, in this case socialLoginCompleted

                PASO 4 - Si no existe campo 'fields' en &fromXeerpa hacemos una redirección del popup a la URL, que en este punto es la de la Red Social
                PASO 7 - Si no existe campo 'fields' en $fromXeerpa hacemos una redirección del popup a la URL, que en este punto es la de socialLoginCompleted (siguiente paso en socialLoginCompleted)
            */
            ?><script>window.location = "<?php echo ($fromXeerpa -> url); ?>"</script><?php
        } else {
            /*
                'doCurl' and 'redirect' are defined in socialLoginSettings
                If 'fields' exists then we do a POST to the url with the variable in the body message.
                For Facebook the field doesn't exist, so if Facebook is the only social network this step can be omitted.

                Las funciones 'doCurl' y 'redirect' están definidas en socialLoginSettings
                Si existe 'fields' hacemos un POST a la url indicada pasándole esa variable en el cuerpo del mensaje
                En el caso de Facebook no existe este campo y si sólo se va a integrar esa red social, puede obviarse esta parte
            */
            $dataFromNetwork = doCurl($fromXeerpa -> url, $fromXeerpa -> fields);
            redirect($queryVars, "data=" . $dataFromNetwork);
        }
    }
}
?>