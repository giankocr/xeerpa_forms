function togglePassword() {
        const passwordInput = document.getElementById("field_password");
        const togglePassword = document.querySelector("span.toggle-password");
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            togglePassword.classList.remove('eye-closed');
            togglePassword.classList.add('eye-open');
        } else {
            passwordInput.type = 'password';
            togglePassword.classList.remove('eye-open');
            togglePassword.classList.add('eye-closed');
        }
}
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector(".socials")) {
        const countrySelect = document.getElementById("field_country");
        const countrySelectCode = document.getElementById("field_countryPhoneCode");
        const provinceSelect = document.getElementById("field_province");
        let loginmessage = document.getElementById("login-message");

        if (document.getElementById("field_phone")) {
            document
            .getElementById("field_phone")
            .addEventListener("input", function (e) {
                this.value = this.value.replace(/[^0-9]/g, "");
            });
            document
            .getElementById("field_id")
            .addEventListener("input", function (e) {
                this.value = this.value.replace(/[^0-9]/g, "");
            });
            fetchCountries();

            countrySelect.addEventListener("change", function () {
                  provinceSelect.disabled = true;
                  provinceSelect.value = null;
                  console.log(this.value);
                  fetchProvinces(this.value);
            });
        }

      // Función para obtener los parámetros de la URL
        function getQueryParams()
        {
            const params = new URLSearchParams(window.location.search);
            return {
                email: params.get("email"),
                firstname: params.get("first_name"),
                lastname: params.get("last_name"),
                birthday: params.get("birthday"),
                it: params.get("it"),
                sn: params.get("sn"),
                idcrm: params.get("email"),
                snid: params.get("snid"),
                nickname: params.get("nickname"),
            };
        }

      // Obtener los valores de los parámetros de la URL
        const queryParams = getQueryParams();
        if (queryParams.sn) {
            loginmessage.innerHTML =
            "<div class='holanickname'><h1>¡Hola! " +
            queryParams.nickname +
            "</h1> <span> termina de completar tu registro.</span></div>";
            loginmessage.style.display = "block";
         // Asignar valores a los campos de entrada si existen
            if (queryParams.email) {
                document.getElementById("field_email").value = queryParams.email;
            }
            if (queryParams.firstname) {
                document.getElementById("field_firstname").value =
                queryParams.firstname;
            }
            if (queryParams.lastname) {
                document.getElementById("field_lastname").value = queryParams.lastname;
            }
            if (queryParams.birthday) {
                document.getElementById("field_birthday").value = queryParams.birthday;
            }
            if (queryParams.it) {
                document.getElementById("field_it").value = queryParams.it;
            }
            if (queryParams.crmid) {
                document.getElementById("field_idcrm").value = queryParams.email;
            }
            if (queryParams.snid) {
                document.getElementById("field_snid").value = queryParams.snid;
            }
            if (queryParams.sn) {
                document.getElementById("field_sn").value = queryParams.sn;
            }
        }

        function fetchCountries()
        {
            const host = window.location.origin;
            const url = host + "/wp-json/geo-api/v1/selected-countries";
            const requestOptions = {
                method: "GET",
                redirect: "follow",
            };
            fetch(url, requestOptions)
            .then((response) => response.json())
            .then((data) => {
                let countries = JSON.parse(JSON.stringify(data));
              // console.log(JSON.parse(JSON.stringify(countries)));
              // Ordenar los países por phonecode

                countrySelect.disabled = false;
                countrySelectCode.disabled = false;
                countries.forEach((country) => {
                    let option = document.createElement("option");
                    option.value = country.country_id;
                    option.text = country.emoji + " " + country.name;
                    countrySelect.add(option);

                    let optioncode = document.createElement("option");
                    optioncode.value = country.phone_code;
                    optioncode.text = country.emoji + " " + country.phone_code;
                    countrySelectCode.add(optioncode);
                });
            })
            .catch((error) => console.error("Error fetching countries:", error));
        }

        function fetchProvinces(countryCode)
        {
            const requestOptions = {
                method: "GET",
                redirect: "follow",
            };
            const host = window.location.origin;
            const url =
            host + "/wp-json/geo-api/v1/selected-states?country_id=" + countryCode;
            fetch(url, requestOptions)
            .then((response) => response.json())
            .then((data) => {
                let provinces = data;
                provinceSelect.disabled = false;
                provinceSelect.innerHTML =
                '<option value="">Seleccione una provincia</option>';
                provinces.forEach((province) => {
                    let option = document.createElement("option");
                    option.value = province.name;
                    option.text = province.name;
                    provinceSelect.add(option);
                });
            })
            .catch((error) => console.error("Error fetching provinces:", error));
        }
    }
});

/**
 * Realiza una llamada a una API.
 * @param {string} url - La URL de la API.
 * @param {string} method - El método HTTP a utilizar (GET, POST, PUT, DELETE, etc.).
 * @param {Object} [headers={}] - Los encabezados de la solicitud.
 * @param {Object} [body=null] - El cuerpo de la solicitud (para POST, PUT, etc.).
 * @returns {Promise} - Una promesa que se resuelve con la respuesta de la API.
 */
async function callApi(url, method = "GET", headers = {}, body = null)
{
    const options = {
        method: method,
        headers: {
            "Content-Type": "application/json",
            ...headers,
        },
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`Error: ${response.status} ${response.statusText}`);
        }
        return await response.json();
    } catch (error) {
        console.error("Error al realizar la llamada a la API:", error);
        throw error;
    }
}

// Ejemplos de uso

//   // Hacer una solicitud GET
//   callApi("https://api.example.com/data")
//     .then((data) => console.log("Datos recibidos:", data))
//     .catch((error) => console.error("Error:", error));
