
document.addEventListener("DOMContentLoaded", function () {
    if (document.querySelector(".socials")) {
        const countrySelect = document.getElementById("field_country");
        const countrySelectCode = document.getElementById("field_countryPhoneCode");
        const provinceSelect = document.getElementById("field_province");
        let loginmessage = document.getElementById("login-message");
        
        // Manejar el envío del formulario directamente
        // El event listener se maneja desde xpsocial_validation.js
        // No necesitamos agregar otro aquí para evitar duplicación

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
                document.getElementById("field_firstname").value = queryParams.firstname;
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
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                
                // Verificar si la respuesta tiene la estructura esperada
                let countries = [];
                if (data && data.data && Array.isArray(data.data)) {
                    countries = data.data;
                } else if (Array.isArray(data)) {
                    countries = data;
                } else {
                    throw new Error("Invalid data structure received from API");
                }

                countrySelect.disabled = false;
                countrySelectCode.disabled = false;
                
                // Si solo hay un país configurado, seleccionarlo automáticamente
                const shouldAutoSelect = countries.length === 1;
                
                countries.forEach((country) => {
                    let option = document.createElement("option");
                    option.value = country.name;
                    option.text = country.emoji + " " + country.name;
                    // Si solo hay un país, marcarlo como seleccionado
                    if (shouldAutoSelect) {
                        option.selected = true;
                    }
                    countrySelect.add(option);

                    let optioncode = document.createElement("option");
                    optioncode.value = country.phone_code;
                    optioncode.text = country.emoji + " " + country.phone_code;
                    // Si solo hay un país, marcar también el código como seleccionado
                    if (shouldAutoSelect) {
                        optioncode.selected = true;
                    }
                    countrySelectCode.add(optioncode);
                });
                
                // Si solo hay un país, cargar automáticamente las provincias
                if (shouldAutoSelect && countries.length > 0) {
                    const firstCountry = countries[0];
                    fetchProvinces(firstCountry.country_id);
                    
                    // Ocultar el placeholder del país ya que está seleccionado
                    const placeholderOption = countrySelect.querySelector('option.placeholder');
                    if (placeholderOption) {
                        placeholderOption.style.display = 'none';
                    }
                }
            })
            .catch((error) => {
                // Mostrar mensaje de error al usuario
                if (countrySelect) {
                    countrySelect.innerHTML = '<option value="">Error al cargar países</option>';
                    countrySelect.disabled = true;
                }
                if (countrySelectCode) {
                    countrySelectCode.innerHTML = '<option value="">Error al cargar códigos</option>';
                    countrySelectCode.disabled = true;
                }
            });
        }

        function fetchProvinces(countryCode)
        {
            const requestOptions = {
                method: "GET",
                redirect: "follow",
            };
            const host = window.location.origin;
            const url = host + "/wp-json/geo-api/v1/selected-states?country_id=" + countryCode;
            
            fetch(url, requestOptions)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then((data) => {
                
                // Verificar si la respuesta tiene la estructura esperada
                let provinces = [];
                if (data && data.data && Array.isArray(data.data)) {
                    provinces = data.data;
                } else if (Array.isArray(data)) {
                    provinces = data;
                } else {
                    throw new Error("Invalid data structure received from provinces API");
                }
                
                provinceSelect.disabled = false;
                provinceSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
                
                provinces.forEach((province) => {
                    let option = document.createElement("option");
                    option.value = province.name;
                    option.text = province.name;
                    provinceSelect.add(option);
                });
            })
            .catch((error) => {
                // Mostrar mensaje de error al usuario
                if (provinceSelect) {
                    provinceSelect.innerHTML = '<option value="">Error al cargar provincias</option>';
                    provinceSelect.disabled = true;
                }
            });
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


// Variable para evitar múltiples ejecuciones
let isSubmitting = false;

// Función para manejar el envío del formulario (disponible globalmente)
window.handleFormSubmit = async function handleFormSubmit(event) {
    // Prevenir múltiples ejecuciones
    if (isSubmitting) {
        console.log('Form submission already in progress, ignoring duplicate call');
        return;
    }
    
    event.preventDefault(); // Prevenir el envío normal del formulario
    
    // Marcar como en proceso
    isSubmitting = true;
    
    // Asegurar que tenemos un HTMLFormElement
    let form = event.target;
    if (form && form.tagName !== 'FORM') {
        // Si no es un form, buscar el form padre
        form = form.closest('form');
    }
    
    // Si aún no tenemos un form, buscar en el documento
    if (!form || form.tagName !== 'FORM') {
        // Buscar el primer formulario que contenga campos de registro
        form = document.querySelector('form[id*="register"], form[class*="register"], form[class*="xpsocial"]');
    }
    
    if (!form || form.tagName !== 'FORM') {
        console.error('No se pudo encontrar el formulario válido', {
            target: event.target,
            targetTagName: event.target ? event.target.tagName : 'undefined',
            foundForm: form
        });
        return;
    }
    
    const formData = new FormData(form);
    const submitButton = form.querySelector('input[type="submit"], button[type="submit"]');
    const originalButtonText = submitButton ? (submitButton.value || submitButton.textContent) : 'Enviar';
    
    // Asegurar que el nonce esté incluido
    const nonceField = form.querySelector('input[name="register_form_nonce"]');
    if (nonceField && !formData.has('register_form_nonce')) {
        formData.append('register_form_nonce', nonceField.value);
    }
    
    // Agregar action para AJAX de WordPress
    formData.append('action', 'xpsocial_register_form');
    
    // Debug: verificar que el nonce esté incluido (temporal)
    // console.log('FormData contents:');
    // for (let [key, value] of formData.entries()) {
    //     console.log(key, value);
    // }
    
    // Mostrar estado de carga
    if (submitButton) {
        if (submitButton.tagName === 'INPUT') {
            submitButton.value = "Enviando...";
        } else {
            submitButton.textContent = "Enviando...";
        }
        submitButton.disabled = true;
    }
    
        try {
            // Usar la URL de AJAX de WordPress
            const ajaxUrl = (typeof xpsocial_ajax !== 'undefined') ? xpsocial_ajax.ajaxurl : '/wp-admin/admin-ajax.php';
            
            // Enviar datos al servidor con headers de seguridad
            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
        
        const result = await response.json();
        
        if (result.success) {
            // Verificar si hay redirect configurado
            if (result.redirect) {
                // Redirigir a la URL configurada
                window.location.href = result.redirect;
            } else if (result.success_html) {
                // Mostrar mensaje de éxito si no hay redirect
                showSuccessMessage(result.success_html);
            } else {
                // Fallback: mostrar mensaje genérico
                showSuccessMessage('<div class="xpsocial-success-message"><h3>¡Registro exitoso!</h3><p>Gracias por registrarte. Tu información ha sido guardada correctamente.</p></div>');
            }
        } else {
            // Mostrar errores de validación
            showValidationErrors(result.errors || {});
        }
        
    } catch (error) {
        console.error('Error al enviar el formulario:', error);
        showErrorMessage('Ocurrió un error al procesar tu solicitud. Por favor, inténtalo de nuevo.');
    } finally {
        // Restaurar el botón
        if (submitButton) {
            if (submitButton.tagName === 'INPUT') {
                submitButton.value = originalButtonText;
            } else {
                submitButton.textContent = originalButtonText;
            }
            submitButton.disabled = false;
        }
        
        // Resetear flag de envío
        isSubmitting = false;
    }
}

// Función para mostrar el mensaje de éxito (disponible globalmente)
window.showSuccessMessage = function showSuccessMessage(successHtml) {
    const formContainer = document.getElementById('xpsocial-form-container');
    const successContainer = document.getElementById('xpsocial-success-container');
    const successContent = successContainer.querySelector('.xpsocial-success-content');
    const socialsLogin = document.querySelector('.socials.login-social');
    
    if (formContainer && successContainer && successContent) {
        // Ocultar el formulario con animación
        formContainer.classList.add('hidden');
        
        // Ocultar también los botones de login social si existen
        if (socialsLogin) {
            socialsLogin.classList.add('hidden');
        }
        
        // Cargar el contenido de éxito
        successContent.innerHTML = successHtml;
        
        // Mostrar el contenedor de éxito
        setTimeout(() => {
            successContainer.style.display = 'block';
        }, 300);
        
        // Scroll suave hacia el mensaje de éxito
        setTimeout(() => {
            successContainer.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }, 600);
    }
}

// Función para mostrar errores de validación (disponible globalmente)
window.showValidationErrors = function showValidationErrors(errors) {
    // Limpiar errores anteriores
    clearValidationErrors();
    
    // Mostrar nuevos errores
    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.classList.add('error');
            
            // Crear o actualizar mensaje de error
            let errorElement = field.parentNode.querySelector('.field-error');
            if (!errorElement) {
                errorElement = document.createElement('span');
                errorElement.className = 'field-error';
                field.parentNode.appendChild(errorElement);
            }
            errorElement.textContent = errors[fieldName];
        }
    });
}

// Función para limpiar errores de validación (disponible globalmente)
window.clearValidationErrors = function clearValidationErrors() {
    const errorFields = document.querySelectorAll('.error');
    const errorMessages = document.querySelectorAll('.field-error');
    
    errorFields.forEach(field => field.classList.remove('error'));
    errorMessages.forEach(message => message.remove());
}

// Función para mostrar mensaje de error general (disponible globalmente)
window.showErrorMessage = function showErrorMessage(message) {
    const loginMessage = document.getElementById('login-message');
    if (loginMessage) {
        loginMessage.innerHTML = `<p style="color: #dc3545; background-color: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;">${message}</p>`;
        loginMessage.style.display = 'block';
    }
}
