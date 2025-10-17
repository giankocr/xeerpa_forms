<?php

    include 'socialLoginSettings.php';

    // STEP 12: The user has completed the form in the browser and all the values are received here so they can be sent to Xeerpa as part of the user profile.
    //          In this example the data validation has been done in the browser, but it would be advisable to validate again here.
    //          NOTE: This call is very important because it helps save important data for the user.
    // PASO 12: El usuario ha completado los campos del formulario, que se reciben aquí y ahora se envían a Xeerpa como parte del perfil de usuario.
    //          En este ejemplo la validación de datos se hizo en el navegador, pero sería recomendable volver a validar aquí.
    //          NOTA: Esta llamada es muy importante, pues permite grabar información muy relevante del usuario.

    // Build the PROFILE parameter with the basic profile information: See https://help.xeerpa.com/Connect-BackAuth
    // Formamos el parámetro PROFILE con los datos básicos de usuario: Ver https://help.xeerpa.com/Connect-BackAuth


if ($_GET[ 'sn' ] ===  'TT') {
    $profile = '"profile":{
			"birthday":"' . $_GET[ 'birthday' ] .
        '","location":"' . $_GET[ 'provincia' ] . 
        '","gender":"' . $_GET[ 'genero' ] .
        '","email":"' . $_GET[ 'email' ] .
        '","first_name":"' . $_GET[ 'first_name' ] .
        '","last_name":"' . $_GET[ 'last_name' ] .
    '"}';
    // Build the DATA parameter with the fields from your form -- Formamos el parámetro DATA con los campos del formulario
    $data = '{' . $profile .
                ',"phone":"' . $_GET[ 'phone' ] .
                '","IDCedula":"' . $_GET[ 'IDCedula' ] .
                '","birthday":"' . $_GET[ 'birthday' ] .
                '","robinson":"' . $_GET[ 'robinson' ] .
                '"}';
} else {
    $profile = '"profile":{
			"birthday":"' . $_GET[ 'birthday' ] .
        '","location":"' . $_GET[ 'provincia' ] . 
        '","gender":"' . $_GET[ 'genero' ] .
    '"}';
    // Build the DATA parameter with the fields from your form -- Formamos el parámetro DATA con los campos del formulario
    $data = '{' . $profile .
    ',"phone":"' . $_GET[ 'phone' ] .
    '","email":"' . $_GET[ 'email' ] .
    '","first_name":"' . $_GET[ 'first_name' ] .
    '","last_name":"' . $_GET[ 'last_name' ] .
    '","IDCedula":"' . $_GET[ 'IDCedula' ] .
    '","birthday":"' . $_GET[ 'birthday' ] .
    '","robinson":"' . $_GET[ 'robinson' ] .
    '"}';
}

    // In Step 8 we obfuscated ID and TOKEN so they were not directly visible in the browser; We need to recover the real values now. These values were separated by "?".
    // En el Paso 8 habíamos obsfuscado ID y TOKEN para que no fuesen directamente visibles en el navegador; Recuperamos ahora su valor. Estos valores se separaron por "?".

    $cleanIdToken = strtr($_GET['it'], $obfKey2, $obfKey1);
    $arrcleanIdToken = explode("?", $cleanIdToken);

    // IMPORTANT: In this example we use the email as CRM ID, but if you know the real CRM ID please use it as newid.
    //            The following call to /auth/saveData sends the information for CRM ID (newid), ROBINSON and additional data (DATA).
    // IMPORTANTE: En este ejemplo usamos el email como id de CRM, pero si sabes el id de CRM real por favor úsalo en newid.
    //             La llamada a /auth/saveData combina las llamadas para enviar el CRM ID (newid), ROBINSON y datos adicionales (DATA).
    $url =  'https://auth-fifco.xeerpa.com/auth/saveData?id=' . $arrcleanIdToken[0]      // $_GET['i']
                                   . '&token=' . $arrcleanIdToken[1] // $_GET['t']
                                   . '&idCRM=' . $_GET['idcrm']
                                   . '&robinson=' . $_GET['robinson']  // ROBINSON=TRUE: does NOT accept communications; ROBINSON=FALSE: accepts communications
                                   . '&data=' . rawurlencode($data); // Soluciona problema de carractares en los parametros gianko.com
//console_log('SAVEDATA2: '.$url);
                $result = doCurl($url, null); // Declared in socialLoginSettings.php -- Declarada en socialLoginSettings.php
                return $result;
