/**
 * Imperial Audio Transfer
 * Maneja la transferencia de audio del grabador independiente al formulario dinámico
 */

(function() {
    'use strict';

    const DEBUG = false;
    const dlog = () => {}; // Disabled for production

    // Esperar a que el DOM esté listo
    document.addEventListener('DOMContentLoaded', function() {
        dlog('DOM ready');
        initializeAudioTransfer();
    });

    function initializeAudioTransfer() {
        // Buscar el grabador independiente
        const independentRecorder = document.querySelector('#recorder_independent_field');
        const formRecorder = document.querySelector('#recorder_dynamic_trabalenguas');
        const registerForm = document.getElementById('register_form');
        const formHiddenInput = document.querySelector('#hidden_input_dynamic_trabalenguas');
        const indepHiddenInput = document.querySelector('#hidden_input_independent_field');
        
        if (!independentRecorder || !formRecorder) {
            dlog('Recorders not found', { independentRecorder: !!independentRecorder, formRecorder: !!formRecorder });
            return;
        }

        dlog('Recorders found');
        dlog('Hidden inputs presence', { formHidden: !!formHiddenInput, indepHidden: !!indepHiddenInput });

        // Configurar listeners inmediatamente, no esperar al recorder
        setupAudioTransfer();
    }

    function setupAudioTransfer() {
        const independentRecorder = document.querySelector('#recorder_independent_field');
        const formRecorder = document.querySelector('#recorder_dynamic_trabalenguas');
        
        dlog('Setting up audio transfer listeners');

        // Verificar elementos antes de configurar listeners
        const formHiddenInput = document.querySelector('#hidden_input_dynamic_trabalenguas');
        const indepHiddenInput = document.querySelector('#hidden_input_independent_field');
        const registerForm = document.getElementById('register_form');
        
        dlog('Elements check', {
            formHidden: !!formHiddenInput,
            indepHidden: !!indepHiddenInput,
            registerForm: !!registerForm,
            independentRecorder: !!independentRecorder
        });

        // Escuchar cuando se graba audio en el grabador independiente (a nivel de documento por si se recrean nodos)
        document.addEventListener('audioRecorded', function(event) {
            dlog('Audio recorded event received', event?.detail);
            transferAudioToForm(event.detail);
        });
        
        // También escuchar directamente en el grabador independiente por si el evento no burbujea
        if (independentRecorder) {
            independentRecorder.addEventListener('audioRecorded', function(event) {
                dlog('Audio recorded event received (direct)', event?.detail);
                transferAudioToForm(event.detail);
            });
        }

        // También escuchar cambios en el campo oculto del grabador independiente
        const independentHiddenInput = document.querySelector('#hidden_input_independent_field');
        if (independentHiddenInput) {
            independentHiddenInput.addEventListener('change', function() {
                dlog('Independent hidden input changed', { value: this.value });
                transferAudioToForm({ filename: this.value });
            });
        } else {
            dlog('Independent hidden input not found');
        }

        // Fallback en submit: si el campo del formulario está vacío, tomarlo del independiente
        if (registerForm) {
            registerForm.addEventListener('submit', function(event) {
                const formHiddenInput = document.querySelector('#hidden_input_dynamic_trabalenguas');
                const indepHiddenInput = document.querySelector('#hidden_input_independent_field');
                const currentFormVal = formHiddenInput ? formHiddenInput.value : '';
                const currentIndepVal = indepHiddenInput ? indepHiddenInput.value : '';
                dlog('On submit values before fallback', { formHidden: currentFormVal, indepHidden: currentIndepVal });

                // Si no hay audio grabado, bloquear el envío
                if (!currentFormVal && !currentIndepVal) {
                    event.preventDefault();
                    alert('Por favor, graba tu audio antes de enviar el formulario.');
                    dlog('Form submission blocked: No audio recorded');
                    return false;
                }

                if (formHiddenInput && !formHiddenInput.value) {
                    const candidate = currentIndepVal;
                    if (candidate && /recording_.*\.(webm|wav|mp3|m4a|ogg)$/i.test(candidate)) {
                        dlog('Fallback applied on submit', candidate);
                        formHiddenInput.value = candidate;
                    } else {
                        dlog('Fallback skipped: candidate invalid or empty');
                        // Si no hay candidato válido, bloquear el envío
                        event.preventDefault();
                        alert('Por favor, graba tu audio antes de enviar el formulario.');
                        return false;
                    }
                }

                const finalVal = formHiddenInput ? formHiddenInput.value : '';
                dlog('On submit values after fallback', { finalFormHidden: finalVal });
                
                // Verificar que finalmente tenemos audio
                if (!finalVal) {
                    event.preventDefault();
                    alert('Por favor, graba tu audio antes de enviar el formulario.');
                    dlog('Form submission blocked: No final audio value');
                    return false;
                }
            });
        } else {
            dlog('Register form not found');
        }
        
        // Fallback periódico: verificar cada 2 segundos si hay audio en el independiente y transferirlo
        setInterval(() => {
            const formHiddenInput = document.querySelector('#hidden_input_dynamic_trabalenguas');
            const indepHiddenInput = document.querySelector('#hidden_input_independent_field');
            
            if (formHiddenInput && indepHiddenInput && !formHiddenInput.value && indepHiddenInput.value) {
                const candidate = indepHiddenInput.value;
                if (/recording_.*\.(webm|wav|mp3|m4a|ogg)$/i.test(candidate)) {
                    dlog('Periodic fallback applied', candidate);
                    formHiddenInput.value = candidate;
                }
            }
            
            // Actualizar indicador visual del estado de grabación
            updateRecordingStatus();
        }, 2000);
        
        // Función para actualizar el indicador visual
        function updateRecordingStatus() {
            const formHiddenInput = document.querySelector('#hidden_input_dynamic_trabalenguas');
            const indepHiddenInput = document.querySelector('#hidden_input_independent_field');
            const submitButton = document.querySelector('#xp-registro-social');
            
            if (formHiddenInput && submitButton) {
                const hasAudio = formHiddenInput.value || (indepHiddenInput && indepHiddenInput.value);
                
                if (hasAudio) {
                    submitButton.style.backgroundColor = '#000';
                    submitButton.value = 'Enviar';
                } else {
                    submitButton.style.backgroundColor = '#c70609';
                    submitButton.value = '(Grabá tu audio primero)';
                }
            }
        }
    }

    function transferAudioToForm(audioData) {
        dlog('Transferring audio to form', audioData);
        
        const formRecorder = document.querySelector('#recorder_dynamic_trabalenguas');
        const formHiddenInput = document.querySelector('#hidden_input_dynamic_trabalenguas');
        const formAudioElement = document.querySelector('#audio_dynamic_trabalenguas');
        const formPreview = document.querySelector('#preview_dynamic_trabalenguas');
        const formFilename = document.querySelector('#filename_dynamic_trabalenguas');
        const formFilesize = document.querySelector('#filesize_dynamic_trabalenguas');
        const formDuration = document.querySelector('#duration_dynamic_trabalenguas');
        
        if (!formRecorder || !formHiddenInput) {
            dlog('Form elements not found', { formRecorder: !!formRecorder, formHiddenInput: !!formHiddenInput });
            return;
        }

        // Transferir el nombre del archivo
        if (audioData.filename) {
            formHiddenInput.value = audioData.filename;
            dlog('Filename transferred:', audioData.filename);
        } else {
            dlog('No filename in audioData');
        }

        // Transferir la URL del audio si está disponible
        if (audioData.url && formAudioElement) {
            formAudioElement.src = audioData.url;
            dlog('Audio URL transferred:', audioData.url);
        }

        // Transferir información del archivo
        if (audioData.filename) {
            formFilename.textContent = audioData.filename;
        }
        
        if (audioData.size) {
            formFilesize.textContent = formatFileSize(audioData.size);
        }
        
        if (audioData.duration) {
            formDuration.textContent = formatDuration(audioData.duration);
        }

        // Mostrar la vista previa del formulario
        formPreview.style.display = 'block';
        
        // Ocultar los controles de grabación del formulario
        const formControls = formRecorder.querySelector('.recorder-controls');
        if (formControls) {
            formControls.style.display = 'none';
        }

        // SUBIR EL ARCHIVO FÍSICAMENTE AL SERVIDOR
        if (audioData.filename) {
            // Si no hay URL, usar el blob del grabador independiente
            let blobUrl = audioData.url;
            if (!blobUrl) {
                const independentAudio = document.querySelector('#audio_independent_field');
                if (independentAudio && independentAudio.src) {
                    blobUrl = independentAudio.src;
                    dlog('Using independent recorder audio URL:', blobUrl);
                }
            }
            
            if (blobUrl) {
                uploadAudioFile(audioData.filename, blobUrl);
            } else {
                dlog('No audio URL available for upload');
            }
        }

        dlog('Audio transfer completed', { valueNow: formHiddenInput.value });
    }
    
    function uploadAudioFile(filename, blobUrl) {
        dlog('Starting audio file upload', { filename, blobUrl });
        
        // Verificar que tenemos los datos necesarios
        if (!window.xpsocial_ajax) {
            dlog('xpsocial_ajax not available');
            return;
        }
        
        dlog('AJAX config', { 
            ajaxurl: window.xpsocial_ajax.ajaxurl, 
            nonce: window.xpsocial_ajax.nonce 
        });
        
        // Convertir blob URL a blob
        fetch(blobUrl)
            .then(response => {
                dlog('Fetch response received', response);
                return response.blob();
            })
            .then(blob => {
                dlog('Blob created', { size: blob.size, type: blob.type });
                
                // Crear FormData para subir el archivo
                const formData = new FormData();
                formData.append('audio_file', blob, filename);
                formData.append('action', 'xpsocial_upload_audio');
                formData.append('nonce', window.xpsocial_ajax.nonce);
                
                dlog('FormData created, uploading file to server', { filename, size: blob.size });
                
                // Subir al servidor
                return fetch(window.xpsocial_ajax.ajaxurl, {
                    method: 'POST',
                    body: formData
                });
            })
            .then(response => {
                dlog('Upload response received', { status: response.status, ok: response.ok });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    dlog('Audio file uploaded successfully', data);
                } else {
                    dlog('Audio file upload failed', data);
                }
            })
            .catch(error => {
                dlog('Audio file upload error', error);
            });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatDuration(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }

    // Exponer funciones globalmente para debugging
    window.imperialAudioTransfer = {
        transferAudioToForm: transferAudioToForm,
        setupAudioTransfer: setupAudioTransfer
    };

})();
