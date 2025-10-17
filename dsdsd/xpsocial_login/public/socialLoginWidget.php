<?php

    header("Content-Type: text/html; charset=UTF-8");
    include 'socialLoginSettings.php';

    // In Step 8 we obfuscated ID and TOKEN so they were not directly visible in the browser; We need to recover the real values now. These values were separated by "?".
    // En el Paso 8 habíamos obsfuscado ID y TOKEN para que no fuesen directamente visibles en el navegador; Recuperamos ahora su valor. Estos valores se separaron por "?".
    $cleanIdToken = strtr($_GET['it'], $obfKey2, $obfKey1);
    $arrcleanIdToken = explode("?", $cleanIdToken);

    // Required: In the case of Facebook login, display the Recommendations widget. Note that this widget could be displayed anywhere within the website, typically My Profile page or similar.
    // Obligatorio: En el caso de login con Facebook, se debe mostrar el widget de recomendación de contenidos. Nota que este widget puedes ubicarlo en cualquier lugar de la web, por ejemplo el área de Mi Perfil o similar.
    // For more information please check / Para más información por favor revise https://help.xeerpa.com/Connect-Widgets
    $urlWidget = $xeerpa . '/widgets/summary?id=' . $arrcleanIdToken[0]
                                   . '&token=' . $arrcleanIdToken[1]
                                   . '&lang=es&socialNetwork=' . $_GET['sn'];
    $res = doCurl($urlWidget, null);

    // Uncomment to display widget response, only for testing purposes.
    // Descomentar para ver el resultado devuelto, sólo para tests.
    // echo $urlWidget;
    // echo $res;
   // var_dump(json_decode($res));
   // exit;

    $widget = json_decode($res, true);

if (!isset($widget["html"])) {
    // Manejar el caso en que no hay HTML en la respuesta
    echo "<div style='text-align:center;'>" . htmlspecialchars($widget["type"]) . "<br>" . htmlspecialchars($widget["message"]) . "</div>"; // Usar htmlspecialchars para evitar XSS
} else {
     echo htmlspecialchars($widget['html']); // Asegúrate de que $widget["html"] sea seguro
}
    exit;
