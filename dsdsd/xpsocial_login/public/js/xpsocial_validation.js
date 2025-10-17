/**
 * XPSocial Form Validation
 * 
 * Este archivo maneja las validaciones en tiempo real del formulario dinámico
 * 
 * @package    Xpsocial_login
 * @subpackage Xpsocial_login/public/js
 * @author     Gianko <gian@gianko.com>
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Configuration - will be set dynamically from form data attributes
    let VALIDATION_CONFIG = {
        debounceDelay: 500, // Delay for debounced validation
        minAge: 18, // Minimum age requirement (will be overridden from form data)
        validateEmail: false, // Will be set from form data
        validateIdNumber: false, // Will be set from form data
        validateAge: false, // Will be set from form data
        apiEndpoints: {
            validateEmail: '/wp-json/geo-api/v1/validate-email',
            validateIdNumber: '/wp-json/geo-api/v1/validate-id-number',
            validateAge: '/wp-json/geo-api/v1/validate-age'
        }
    };

    // Validation state
    let validationState = {
        email: { valid: null, message: '' },
        idNumber: { valid: null, message: '' },
        birthDate: { valid: null, message: '' }
    };

    // Initialize validation configuration from form data attributes
    function initializeValidationConfig() {
        const form = $('.xpsocial-form');
        if (form.length) {
            // Read data attributes correctly (jQuery converts kebab-case to camelCase)
            VALIDATION_CONFIG.validateEmail = form.data('validateEmail') === 1 || form.data('validate-email') === 1;
            VALIDATION_CONFIG.validateIdNumber = form.data('validateIdNumber') === 1 || form.data('validate-id-number') === 1;
            VALIDATION_CONFIG.validateAge = form.data('validateAge') === 1 || form.data('validate-age') === 1;
            VALIDATION_CONFIG.minAge = parseInt(form.data('minAge') || form.data('min-age')) || 18;
            
        }
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Show error message
    function showFieldError(fieldName, message) {
        const field = $(`[name="${fieldName}"]`);
        const fieldContainer = field.closest('div');
        
        // Remove existing error message
        fieldContainer.find('.field-error').remove();
        
        // Add error class to field
        field.addClass('error');
        
        // Add error message
        if (message) {
            fieldContainer.append(`<div class="field-error" style="color: #dc3545; font-size: 12px; margin-top: 5px;">${message}</div>`);
        }
    }

    // Clear field error
    function clearFieldError(fieldName) {
        const field = $(`[name="${fieldName}"]`);
        const fieldContainer = field.closest('div');
        
        // Remove error class and message
        field.removeClass('error');
        fieldContainer.find('.field-error').remove();
    }

    // Show validation errors summary
    function showValidationErrors(errors) {
        // Remove any existing error summary
        $('.validation-errors-summary').remove();
        
        if (errors.length === 0) return;
        
        const errorHtml = `
            <div class="validation-errors-summary" style="
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                padding: 15px;
                margin: 15px 0;
                font-size: 14px;
            ">
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul style="margin: 10px 0 0 20px; padding: 0;">
                    ${errors.map(error => `<li>${error}</li>`).join('')}
                </ul>
            </div>
        `;
        
        // Insert error summary at the top of the form
        $('.xpsocial-form').prepend(errorHtml);
        
        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $('.xpsocial-form').offset().top - 100
        }, 500);
    }

    // Validate email
    function validateEmail(email, source) {
        // Skip validation if not enabled for this campaign
        if (!VALIDATION_CONFIG.validateEmail) {
            clearFieldError('field_email');
            return Promise.resolve(true);
        }

        if (!email || !isValidEmail(email)) {
            validationState.email = { valid: false, message: 'El formato del email no es válido' };
            showFieldError('field_email', validationState.email.message);
            return Promise.resolve(false);
        }

        return fetch(VALIDATION_CONFIG.apiEndpoints.validateEmail, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                source: source
            })
        })
        .then(response => response.json())
        .then(data => {
            validationState.email = { valid: data.valid, message: data.message };
            
            if (data.valid) {
                clearFieldError('field_email');
            } else {
                showFieldError('field_email', data.message);
            }
            
            return data.valid;
        })
        .catch(error => {
            console.error('Email validation error:', error);
            validationState.email = { valid: false, message: 'Error al validar el email' };
            showFieldError('field_email', validationState.email.message);
            return false;
        });
    }

    // Validate ID Number
    function validateIdNumber(idNumber, source) {
        // Skip validation if not enabled for this campaign
        if (!VALIDATION_CONFIG.validateIdNumber) {
            clearFieldError('field_id');
            return Promise.resolve(true);
        }

        if (!idNumber || idNumber.trim() === '') {
            validationState.idNumber = { valid: false, message: 'Número de identificación es requerido' };
            showFieldError('field_id', validationState.idNumber.message);
            return Promise.resolve(false);
        }

        return fetch(VALIDATION_CONFIG.apiEndpoints.validateIdNumber, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id_number: idNumber,
                source: source
            })
        })
        .then(response => response.json())
        .then(data => {
            validationState.idNumber = { valid: data.valid, message: data.message };
            
            if (data.valid) {
                clearFieldError('field_id');
            } else {
                showFieldError('field_id', data.message);
            }
            
            return data.valid;
        })
        .catch(error => {
            console.error('ID Number validation error:', error);
            validationState.idNumber = { valid: false, message: 'Error al validar el número de identificación' };
            showFieldError('field_id', validationState.idNumber.message);
            return false;
        });
    }

    // Validate age
    function validateAge(birthDate) {
        // Skip validation if not enabled for this campaign
        if (!VALIDATION_CONFIG.validateAge) {
            clearFieldError('field_birthday');
            return Promise.resolve(true);
        }

        if (!birthDate) {
            validationState.birthDate = { valid: false, message: 'Fecha de nacimiento es requerida' };
            showFieldError('field_birthday', validationState.birthDate.message);
            return Promise.resolve(false);
        }

        return fetch(VALIDATION_CONFIG.apiEndpoints.validateAge, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                birth_date: birthDate,
                min_age: VALIDATION_CONFIG.minAge
            })
        })
        .then(response => response.json())
        .then(data => {
            validationState.birthDate = { valid: data.valid, message: data.message };
            
            if (data.valid) {
                clearFieldError('field_birthday');
            } else {
                showFieldError('field_birthday', data.message);
            }
            
            return data.valid;
        })
        .catch(error => {
            console.error('Age validation error:', error);
            validationState.birthDate = { valid: false, message: 'Error al validar la edad' };
            showFieldError('field_birthday', validationState.birthDate.message);
            return false;
        });
    }

    // Email format validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Get form source
    function getFormSource() {
        const form = $('.xpsocial-form');
        return form.data('source') || '';
    }

    // Debounced validation functions
    const debouncedEmailValidation = debounce(function(email, source) {
        validateEmail(email, source);
    }, VALIDATION_CONFIG.debounceDelay);

    const debouncedIdNumberValidation = debounce(function(idNumber, source) {
        validateIdNumber(idNumber, source);
    }, VALIDATION_CONFIG.debounceDelay);

    const debouncedAgeValidation = debounce(function(birthDate) {
        validateAge(birthDate);
    }, VALIDATION_CONFIG.debounceDelay);

    // Initialize validation
    function initValidation() {
        const form = $('.xpsocial-form');
        
        if (form.length === 0) {
            return; // No dynamic form found
        }

        // Initialize validation configuration from form data attributes
        initializeValidationConfig();

        const source = getFormSource();

        // Email validation
        $('[name="field_email"]').on('blur input', function() {
            // Clear validation errors summary when user starts typing
            $('.validation-errors-summary').remove();
            
            const email = $(this).val().trim();
            if (email) {
                debouncedEmailValidation(email, source);
            } else {
                clearFieldError('field_email');
                validationState.email = { valid: null, message: '' };
            }
        });

        // ID Number validation
        $('[name="field_id"]').on('blur input', function() {
            // Clear validation errors summary when user starts typing
            $('.validation-errors-summary').remove();
            
            const idNumber = $(this).val().trim();
            if (idNumber) {
                debouncedIdNumberValidation(idNumber, source);
            } else {
                clearFieldError('field_id');
                validationState.idNumber = { valid: null, message: '' };
            }
        });

        // Birth date validation
        $('[name="field_birthday"]').on('blur change', function() {
            // Clear validation errors summary when user changes date
            $('.validation-errors-summary').remove();
            
            const birthDate = $(this).val();
            if (birthDate) {
                debouncedAgeValidation(birthDate);
            } else {
                clearFieldError('field_birthday');
                validationState.birthDate = { valid: null, message: '' };
            }
        });

        // Form submission validation
        form.on('submit', async function(e) {
            e.preventDefault(); // Prevent default submission first
            
            const email = $('[name="field_email"]').val().trim();
            const idNumber = $('[name="field_id"]').val().trim();
            const birthDate = $('[name="field_birthday"]').val();

            let isValid = true;
            let validationErrors = [];

            // Check current validation state first
            if (validationState.email.valid === false) {
                isValid = false;
                validationErrors.push('Email: ' + validationState.email.message);
            }
            if (validationState.idNumber.valid === false) {
                isValid = false;
                validationErrors.push('ID Number: ' + validationState.idNumber.message);
            }
            if (validationState.birthDate.valid === false) {
                isValid = false;
                validationErrors.push('Birth Date: ' + validationState.birthDate.message);
            }

            // If current state is valid, perform fresh validation
            if (isValid) {
                // Validate all fields before submission
                if (email && VALIDATION_CONFIG.validateEmail) {
                    const emailValid = await validateEmail(email, source);
                    if (!emailValid) {
                        isValid = false;
                        validationErrors.push('Email: ' + validationState.email.message);
                    }
                }

                if (idNumber && VALIDATION_CONFIG.validateIdNumber) {
                    const idValid = await validateIdNumber(idNumber, source);
                    if (!idValid) {
                        isValid = false;
                        validationErrors.push('ID Number: ' + validationState.idNumber.message);
                    }
                }

                if (birthDate && VALIDATION_CONFIG.validateAge) {
                    const ageValid = await validateAge(birthDate);
                    if (!ageValid) {
                        isValid = false;
                        validationErrors.push('Birth Date: ' + validationState.birthDate.message);
                    }
                }
            }

            if (!isValid) {
                // Show validation errors
                showValidationErrors(validationErrors);
                return false;
            }

            // If all validations pass, submit the form using our new handler
            form.off('submit'); // Remove event handler to prevent infinite loop
            
            // Use our custom form submission handler if it exists
            if (typeof handleFormSubmit === 'function') {
                // Create a synthetic event object with the correct form element
                const formElement = form[0];
                const syntheticEvent = {
                    target: formElement,
                    preventDefault: function() {}
                };
                
                // Call our handler directly with the form element
                handleFormSubmit(syntheticEvent);
            } else {
                // Fallback to default submission
                form[0].submit();
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initValidation();
    });

    // Re-initialize when new content is loaded (for dynamic forms)
    $(document).on('DOMNodeInserted', function(e) {
        if ($(e.target).hasClass('xpsocial-form') || $(e.target).find('.xpsocial-form').length > 0) {
            setTimeout(initValidation, 100);
        }
    });

})(jQuery);
