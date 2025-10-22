/**
 * Plugin Name:       XPSocial Login
 * Plugin URI:        https://gianko.com/
 * Description:       Plugin para conectar wordpress con los api de Xeerpa Social.
 * Author:            giankocr
 * Author URI:        https://gianko.com/
 */

console.log(
  "Hecho con " +
    String.fromCodePoint(128147) +
    " y " +
    String.fromCodePoint(127850) +
    " por" +
    "  https://gianko.com " +
    String.fromCodePoint(128561, 128640)
);

//**
//Abrir explorador
//
var userAgent = navigator.userAgent || navigator.vendor || window.opera;
var str = navigator.userAgent;
var instagram = str.indexOf("Instagram");
var facebook = str.indexOf("FB");

if (/android/i.test(userAgent) && (instagram != -1 || facebook != -1)) {
  document.write(
    '<a target="_blank" href="https://kernslovers.com" download id="open-browser-url"></a>'
  );
  window.stop();
  let input = document.getElementById("open-browser-url");
  if (input) {
    input.click();
  }
}

if (/iPad|iPhone|iPod/.test(userAgent) && (instagram != -1 || facebook != -1)) {
  window.location.href = "x-safari-https://kernslovers.com";
}

// Get the URL Plugin
let BASE = window.location.origin;
let BASE_URL = BASE + "/wp-content/plugins/xpsocial_login/public/";

/*=============================================
=            Xeerpa Form JS            =
=============================================*/
//Check buttons social network
document.addEventListener("DOMContentLoaded", () => {
  if (document.querySelector(".socials")) {
    const myEvent =
      "ontouchstart" in document.documentElement ? "touchend" : "click";

    if (document.querySelector("#FB")) {
      const facebook = document.querySelector("#FB");
      facebook.addEventListener(myEvent, () => {
        login("FB");
      });
    }
    if (document.querySelector("#GO")) {
      const google = document.querySelector("#GO");
      google.addEventListener(myEvent, () => {
        login("GO");
      });
    }
    if (document.querySelector("#TT")) {
      const tiktok = document.querySelector("#TT");
      tiktok.addEventListener(myEvent, () => {
        login("TT");
      });
    }
  }
});
// check if is register{}
if (document.getElementById("field_email")) {
  const email = document.getElementById("field_email");
}
function load(url, callback) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if (xhr.readyState === 4) {
      callback(xhr.response);
    }
  };
  xhr.open("GET", url, true);
  xhr.send("");
}

window.addEventListener("load", (event) => {
  // This is a hack for when running the site within Facebook's app internal browser. Values are returned through sessionStorage (see COMPLETED).
  // Este es un hack para cuando se lanza la web desde el navegador interno de la app de Facebook. Los valores se pasan mediante sessionStorage (ver COMPLETED).
  if (isFacebookBrowser() && sessionStorage.getItem("x_user_data")) {
    receiveData(sessionStorage.getItem("x_user_data"));
    clearCookie();
  }
});

function login(sn, width, height) {
  clearCookie();
  if (
    navigator.appName == "Microsoft Internet Explorer" ||
    !!(
      navigator.userAgent.match(/Trident/) || navigator.userAgent.match(/rv 11/)
    )
  ) {
    if (checkCookie) clearInterval(checkCookie);
    var checkCookie = setInterval(function () {
      var cookie = readCookie();
      if (cookie) {
        clearInterval(checkCookie);
        receiveData(cookie);
        if (popup) popup.close();
      }
    }, 100);
  }

  // STEP 1 This function initiates the social login calling socialLoginCallback.php in the server and sending the chosen socialNetwork. 'random' tries to avoid cache issues.
  // PASO 1 Esta función inicia el flujo del Social Login llamando a socialLoginCallback y le pasa los parámetros 'socialNetwork' y un 'random' que evita problemas de caché (siguiente paso en socialLoginCallback)
  var url =
    BASE_URL +
    "socialLoginCallback.php?socialNetwork=" +
    sn +
    "&random=" +
    Math.random();
  // In this example we use the email as CRM ID. This will be useful to later link other social networks to the same user even if her/his email is different in them. The first time it will be empty.
  // En este ejemplo usamos el email como CRM ID. Esto será útil para luego asociar al usuario en las otras redes sociales incluso si su email no coincide. La primera vez estará vacío.
  if (document.getElementById("field_email")) {
    if (document.getElementById("field_email").value != "") {
      url += "&id=" + document.getElementById("field_email").value;
    }
  }

  var popup = window.open(
    url,
    "Login",
    "width=" +
      (width || 500) +
      ", height=" +
      (height || 475) +
      ", scrollbars=yes"
  );
}

function clearCookie() {
  localStorage.removeItem("x_user_data");
  sessionStorage.removeItem("x_user_data");
  document.cookie =
    "x_user_data=;path=/;expires=" + new Date(Date.now() - 1000).toGMTString(); // Clear cookie if it exists -- Borra cookie si existe.
}

function readCookie() {
  if (sessionStorage.getItem("x_user_data")) {
    return sessionStorage.getItem("x_user_data");
  } else if (localStorage.getItem("x_user_data")) {
    // <<<< AÑADIR ESTAS LINEA
    return localStorage.getItem("x_user_data"); // <<<< AÑADIR ESTA LINEA
  } else {
    if (!document.cookie) return;
    var cookies = document.cookie.split("; ");
    for (var i = 0; cookies.length; i++) {
      var cookie = cookies[i].split("=");
      if (cookie[0] === "x_user_data") {
        var value = cookie[1];
        //clearCookie();                            // <<<< COMENTAR ESTA LINEA
        return value;
      }
    }
  }
}

function isFacebookBrowser() {
  var ua = navigator.userAgent || navigator.vendor || window.opera;
  return (
    ua.indexOf("FBAN") > -1 ||
    ua.indexOf("FBAV") > -1 ||
    ua.indexOf("FBDV") > -1 ||
    ua.indexOf("Instagram") > -1
  );
}

function listen(obj, evt, func) {
  "addEventListener" in window
    ? obj.addEventListener(evt, func, false)
    : obj.attachEvent("on" + evt, func);
}

listen(window, "message", function (event) {
  receiveData(event.data);
});

function isValidJSON(jsonString) {
  try {
    JSON.parse(jsonString);
    return true; // If parsing succeeds, it's valid JSON
  } catch (e) {
    return false; // If parsing fails, it's not valid JSON
  }
}

async function receiveData(data) {
  /*
    STEP 10 - Here we process the JSON returned by Completed, you can modify as required.
    PASO 10 - Esta función desglosa el JSON que devuelve Completed. Este código puede sustituirse por uno propio si se va a tratar el JSON de otra forma.
   */

  if (isValidJSON(data)) {
    data = await JSON.parse(data);

    // Fill form in -- Rellena el formulario.
    let birthday = ""; // Initialize birthday variable
    if (data.user.info.birthday) {
      birthday =
        data.user.info.birthday.year +
        "/" +
        data.user.info.birthday.month +
        "/" +
        data.user.info.birthday.day;
    }
    const uid = data.id;
    const token = data.token;
    const firstname = data.user.info.name.first;
    const lastname = data.user.info.name.last;
    const email = data.user.email;
    const snid = data.snid;
    const it = data.it;
    const sn = data.sn;
    const idcrm = data.user.email;
    const nickname = data.user.name;
    if (sn !== "TT") {
      if (document.getElementById("field_firstname")) {
        document.getElementById("field_firstname").value = firstname;
        document.getElementById("field_lastname").value = lastname;
        document.getElementById("field_email").value = email;
        // Only set birthday if it has a value
        if (birthday && document.getElementById("field_birthday")) {
          document.getElementById("field_birthday").value = birthday;
        }
        document.getElementById("field_idcrm").value = email;
        document.getElementById("field_it").value = it;
        document.getElementById("field_snid").value = snid;
        document.getElementById("field_sn").value = sn;
      }
    } else {

        const tt_text = document.getElementById("field_tt_text");
        tt_text.style.display = "block";

      if (document.getElementById("field_firstname")) {
        document.getElementById("field_snid").value = snid;
        document.getElementById("field_it").value = it;
        document.getElementById("field_sn").value = sn;
      }
    }
    localStorage.setItem("xeerpa_it", data.it);
    if (document.querySelector(".recomendador")) {
      showWidget(data.it, data.sn);
    }

  }
}

/*
 * =====  End of Xeerpa Form JS  ======
 */


/*
 * ================Recomendador===================
 */
async function showWidget(it, sn) {
  try {
    function decodeHtmlEntities(text) {
      const parser = new DOMParser();
      const doc = parser.parseFromString(text, "text/html");
      return doc.documentElement.textContent;
    }

    // Insertar el contenido en el DOM
    const widgetContainer = document.getElementById("divWidget");
    const loadingElement = document.getElementById("loading");
    const widgetWrapper = document.getElementById("divWidgetWrapper");

    // Mostrar el contenedor del loadingElement
    if (loadingElement) {
      loadingElement.style.display = "block";
    }

    // Construir la URL
    const params = new URLSearchParams({
      it: it,
      lang: "es",
      sn: sn || "GO", // Usa el parámetro 'sn' pasado, o 'GO' por defecto
    });
    const url = new URL("socialLoginWidget.php", BASE_URL);
    url.search = params.toString();

    // Realizar la solicitud
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }
    const data = await response.text();
    // Decodificar entidades HTML
    const decodedData = decodeHtmlEntities(data); // Decodificar aquí
    if (widgetContainer) {
      widgetContainer.innerHTML = decodedData; // Usar el contenido decodificado
    }

    // Ocultar el elemento de carga

    if (loadingElement) {
      loadingElement.style.display = "none";
    }

    // Ejecutar scripts si es necesario
    const scripts = widgetContainer.getElementsByTagName("script");
    for (let script of scripts) {
      const newScript = document.createElement("script");
      newScript.textContent = script.innerHTML; // Asignar el contenido del script
      document.body.appendChild(newScript); // Agregar el nuevo script al DOM
      document.body.removeChild(newScript); // Opcional: eliminar el script después de ejecutarlo
    }
  } catch (error) {
    console.error("Error al iniciar la carga del widget:", error);
  }
}
