<?php
header("Content-Type: text/html; charset=UTF-8");
include 'socialLoginSettings.php';

$json = json_decode($_GET['data']);

/*
Response must be errorCode : 1. Otherwise we call showError defined in socialLoginSettings. Ideally the error will be checked in the server and then forward a more friendly message to the user.
La respuesta debe ser errorCode : 1. Si errorCode NO es 1 llamamos a la funcion showError definida en socialLoginSettings. Lo ideal sería gestionar el error aquí en servidor y enviar a cliente un mensaje más entendible para el usuario.
*/
if ($_GET['errorCode'] != 1){
	showError($json);
}

// If errorCode : 1 then all is correct.
// Si errorCode : 1 es que la respuesta es correcta.
else {
	/*
		STEP 8 - We receive parameter 'data' with a JSON, so we turn it into an object and send to HOME (next step in the client)
		The information in the JSON object may be required to auto-populate the registration form and also to make the subsequent calls to /robinson, /data, /newId and /widgets/summary
		In this example it is sent using postMessage, but it could be sent in any other way.
		$postMessageURL is defined in socialLoginSettings

		PASO 8 - Recibimos un parámetro 'data' con un JSON, así que lo transformamos en objeto y lo enviamos a la HOME (siguiente paso en client)
		La información de este JSON se necesitará para autorellenar el formulario de la HOME y para realizar las llamadas a /newId, /robinson, /data y /widgets/summary
		En esta implementación se envía utilizando postMessage pero se puede sustituir por código propio
		La variable $postMessageURL se define en socialLoginSettings
	*/
	if($postMessageURL != ""){
		// IMPORTANT: Avoid sending sensitive data to the browser. Here we send only the user profile data and obfuscate ID and TOKEN so they are not directly visible. We will need them in the browser for the next step.
	    //			  See an example of data below in this document.
		// IMPORTANTE: Evitemos enviar datos sensibles al navegador. Aquí devolvemos sólo los datos de perfil de usuario y obfuscamos ID y TOKEN para que no se vean sus valores. Luego desde cliente los necesitaremos.
		//            Puedes ver un ejemplo de data debajo en este archivo.

		$arrData = json_decode(stripslashes($_GET['data']),true);
		$idtoken = $arrData["id"]."?".$arrData["token"]; // "?" will be the separator -- "?" será el separador.
		$obfuscatedToken=strtr($idtoken, $obfKey1, $obfKey2);

		// Optional: Search for user in the CRM. With the details from the user you can search her/him in the CRM and load here the CRM ID.
		// Opcional: Con los datos del usuario se le podria buscar en el CRM y cargar aqui el ID CRM.
		// <CALL TO CRM HERE> Example:  $idcrm = getIDCRM(user.email);
		$idcrm = $arrData["user"]["email"];  // In this example we use the email as CRM ID -- en este ejemplo usamos el email como CRM ID

		$resData = array(
			"user" => $arrData["user"],
			"sn" => $arrData["socialNetwork"]["name"],
			"snid" => $arrData["socialNetwork"]["id"],
			// "i" => $arrData["id"],
			// "t" => $arrData["token"],
			"idcrm" => $idcrm,
			"it" => $obfuscatedToken  // id?token
		);

		?>
		<script>
			function isFacebookBrowser() {
	            var ua = navigator.userAgent || navigator.vendor || window.opera;
				return (ua.indexOf("FBAN") > -1) || (ua.indexOf("FBAV") > -1) || (ua.indexOf('FBDV') > -1) || (ua.indexOf('Instagram') > -1);
	        }
			// If the website is being run in IE or within Facebook's In-App browser, data is passed to client.html through sessionStorage. Check addEventListener('load') in client.html to see how this value is used.
			// Si la web se está lanzando desde IE o el navegador interno de la app de Facebook, data se pasa a client.html mediante sessionStorage. Comprueba addEventListener('load') en client.html para ver cómo se usa este valor.
			if(isFacebookBrowser()){
			 	sessionStorage.setItem("x_user_data", JSON.stringify(<?php echo json_encode($resData); ?>));
				window.history.back();
			} else if (navigator.appName == 'Microsoft Internet Explorer' ||  !!(navigator.userAgent.match(/Trident/) || navigator.userAgent.match(/rv 11/))){
				document.cookie = 'x_user_data=<?php echo trim(json_encode($resData), "\""); ?>;path=/;'
			} else {
				var opener = window.opener
				if (opener && opener.postMessage) {
					// IMPORTANT: Avoid sending sensitive data to the browser. Here we send only $resData
					// IMPORTANTE: Evitemos enviar datos sensibles al navegador. Aquí devolvemos sólo $resData
					opener.postMessage('<?php echo json_encode($resData); ?>' ,'<?php echo $postMessageURL; ?>')
				}
			}
		</script><?php
	}
}
?><script>window.close();</script>

<?php
/****
Example of "data":

{
  "socialNetwork": {
    "id": "1219575678124941313",
    "token": "1219575678121231313-jy72ImCNAqnROj1gyqSqbKjirzEiM3",
    "name": "TW",
    "expires": "3000-01-01T00:00:00.000Z",
    "verifier": "6dRfWCsBGWGTN0uRR046QeeleSm0LbPc",
    "secret": "Et0LoPsTxvUjEmaXdf8JzvSONMEAHctburA59uH"
  },
  "user": {
    "name": "Xeerpa Dev",
    "nick": "XeerpaD",
    "email": "support@xeerpa.com",
    "info": {
      "name": {
        "first": "Xeerpa",
        "last": "Dev",
        "full": "Xeerpa Dev",
        "nick": "XeerpaD"
      },
      "email": {
        "value": "support@xeerpa.com",
        "checked": true
      }
    }
  },
  "auth": {
    "refreshToken": "hrH414424b5ac77bcej769f54h8e9f7gcefhj6WPVS3L9Yu2QPJZ79IbijbEYnLbPu8NhDLjfijeijjjj4jbhhcgd8ji6jghcgefhj4adg9b7fgihd57c755addbjefhj13mDmIIvyZDIZe308uDZeeeee02ve03hdf1",
    "expires": "2021-01-26T19:04:23.407Z"
  },
  "errorCode": 1,
  "id": "au_600328556188f105c79e8",
  "token": "elAT16824b5ac77pbjjj67dbhdgj769f54h8e9f7gcefhj6WPVS3L9Yu2QPJZ79IbijbEYnLbPu8NhDLjfijeijjjj4jbhhcgd8ji6jghcgefhj4adg9b7fgihd57c755addbjefhj13uBu44JRxB4xgj082Bxggggg01u03hdf0"
}

***/
?>
