<?php
/**
 * Imperial Audio Recorder Shortcode
 * Handles independent audio recording and file transfer to main form
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode para incrustar el grabador independiente y el botón de activación.
 *
 * Uso: [imperial_grabador_independiente]
 */
function imperial_recorder_shortcode() {
    ob_start();
    ?>
   <div id="independent_audio_recorder">
        <div class="xpsocial-audio-field" data-field-name="independent_field" data-field-id="independent_field" data-allowed-formats="webm" data-max-duration="60" data-allow-recording="1" data-allow-file-upload="0">
            <div class="xpsocial-audio-recorder" id="recorder_independent_field">
                <div class="recorder-controls">
                    <button type="button" class="record-btn" id="record_independent_field"> </button>
                    </div>
                
                <div class="recorder-status" id="status_independent_field" style="display: none;"></div>
                <div class="audio-preview" id="preview_independent_field" style="display: none;">
                    <div class="preview-header">
                        <button type="button" class="delete-btn" id="delete_independent_field">🗑️</button>
                    </div>
                    <div class="audio-player">
                        <audio controls="" id="audio_independent_field"></audio>
                    </div>
                </div>
                
                <input type="file" id="file_input_independent_field" name="independent_field" accept="audio/*" style="display: none;">
                <input type="hidden" id="hidden_input_independent_field" name="independent_field_filename">
            </div>
        </div>
    </div>
    
    <script type="text/javascript">
    window.addEventListener('load', function() {
        
        // IDs de los elementos
        const triggerElement = document.getElementById('id_record'); 
        const independentRecordButton = document.getElementById('record_independent_field'); 
        const registroSection = document.getElementById('registro');

        // --- 1. Enlace del botón visible con el grabador oculto ---
        if (triggerElement && independentRecordButton) {
            triggerElement.addEventListener('click', function(e) {
                e.preventDefault(); 
                
                // Simula el clic en el botón de grabación real (oculto)
                independentRecordButton.click();
                
                // Desplazar la vista al formulario de registro
                if (registroSection) {
                    registroSection.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }
            });
        }
        
        // --- 2. Store reference to independent recorder for file transfer ---
        setTimeout(() => {
            const independentRecorder = document.querySelector('#recorder_independent_field');
            if (independentRecorder && independentRecorder.xpsocialRecorder) {
                window.imperialAudioRecorder = independentRecorder.xpsocialRecorder;
                console.log('Imperial Audio Transfer: Independent recorder reference stored');
            }
        }, 1000);
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('imperial_grabador_independiente', 'imperial_recorder_shortcode');

/**
 * Agrega estilos CSS para el botón y ocultar el grabador independiente.
 */
function imperial_recorder_styles() {
    ?>
    <style type="text/css">
        /* 1. Oculta la interfaz del grabador auxiliar que solo usamos para la función */
        #independent_audio_recorder {
        }

        /* 2. Estiliza el botón de Divi para que tenga la imagen de fondo */
        #id_record {

        }

        /* 3. Estilo al pasar el ratón */
        #id_record:hover {
            opacity: 0.9;
        }
    </style>
    <?php
}
// Usa un hook tardío para asegurar que el CSS se carga al final.
add_action('wp_head', 'imperial_recorder_styles');

/**
 * Enqueue the audio transfer script
 */
function imperial_enqueue_audio_transfer_script() {
 /*  wp_enqueue_script(
        'imperial-audio-transfer',
        plugin_dir_url(__FILE__) . '../public/js/imperial-audio-transfer.js',
        array('jquery'),
        '1.0.0',
        true
    );*/
}
add_action('wp_enqueue_scripts', 'imperial_enqueue_audio_transfer_script');
?>
