/**
 * Audio Recording Functionality for XPSocial Login Plugin
 * Handles audio recording, playback, and file management for dynamic audio fields
 */

(function() {
    'use strict';
    
    // Global variables for audio recording
    let mediaRecorder = null;
    let audioChunks = [];
    let recordingStartTime = null;
    let recordingTimer = null;
    let currentField = null;
    
    // Initialize audio functionality when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeAudioFields();
    });
    
    /**
     * Initialize all audio fields on the page
     */
    function initializeAudioFields() {
        // Add event listeners for recording controls
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('start-recording')) {
                e.preventDefault();
                startRecording(e.target);
            } else if (e.target.classList.contains('stop-recording')) {
                e.preventDefault();
                stopRecording(e.target);
            } else if (e.target.classList.contains('play-recording')) {
                e.preventDefault();
                playRecording(e.target);
            }
        });
        
        // Add event listener for file input changes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('audio-file-input')) {
                handleFileInput(e.target);
            }
        });
    }
    
    /**
     * Start audio recording
     */
    async function startRecording(button) {
        const fieldName = button.getAttribute('data-field');
        currentField = fieldName;
        
        try {
            // Request microphone access
            const stream = await navigator.mediaDevices.getUserMedia({ 
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    sampleRate: 44100
                } 
            });
            
            // Create MediaRecorder
            mediaRecorder = new MediaRecorder(stream, {
                mimeType: getSupportedMimeType()
            });
            
            // Prepare audio chunks array
            audioChunks = [];
            
            // Set up event handlers
            mediaRecorder.ondataavailable = function(event) {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };
            
            mediaRecorder.onstop = function() {
                const audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType });
                handleRecordingComplete(audioBlob, fieldName);
                
                // Stop all tracks to release microphone
                stream.getTracks().forEach(track => track.stop());
            };
            
            // Start recording
            mediaRecorder.start(1000); // Collect data every second
            recordingStartTime = Date.now();
            
            // Update UI
            updateRecordingUI(fieldName, true);
            
            // Start timer
            startRecordingTimer(fieldName);
            
            console.log('Recording started for field:', fieldName);
            
        } catch (error) {
            console.error('Error starting recording:', error);
            showError('No se pudo acceder al micrófono. Verifica los permisos del navegador.');
        }
    }
    
    /**
     * Stop audio recording
     */
    function stopRecording(button) {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            updateRecordingUI(currentField, false);
            stopRecordingTimer();
        }
    }
    
    /**
     * Play recorded audio
     */
    function playRecording(button) {
        const fieldName = button.getAttribute('data-field');
        const audioPreview = document.querySelector(`[data-field="${fieldName}"]`).closest('.audio-field-container').querySelector('.audio-preview');
        
        if (audioPreview && audioPreview.src) {
            audioPreview.play();
        }
    }
    
    /**
     * Handle file input change
     */
    function handleFileInput(input) {
        const file = input.files[0];
        if (file) {
            const fieldName = input.id.replace('dynamic_', '');
            const audioPreview = input.closest('.audio-field-container').querySelector('.audio-preview');
            
            // Validate file type
            if (!isValidAudioFile(file)) {
                showError('Formato de archivo no válido. Por favor, selecciona un archivo de audio válido.');
                input.value = '';
                return;
            }
            
            // Show preview
            const url = URL.createObjectURL(file);
            audioPreview.src = url;
            audioPreview.style.display = 'block';
            
            // Show play button
            const playButton = input.closest('.audio-field-container').querySelector('.play-recording');
            playButton.style.display = 'inline-block';
            
            console.log('Audio file selected:', file.name, 'Size:', file.size, 'Type:', file.type);
        }
    }
    
    /**
     * Handle recording completion
     */
    function handleRecordingComplete(audioBlob, fieldName) {
        const container = document.querySelector(`[data-field="${fieldName}"]`).closest('.audio-field-container');
        const fileInput = container.querySelector('.audio-file-input');
        const audioPreview = container.querySelector('.audio-preview');
        const playButton = container.querySelector('.play-recording');
        
        // Create file from blob
        const fileName = `recording_${fieldName}_${Date.now()}.${getFileExtension(audioBlob.type)}`;
        const audioFile = new File([audioBlob], fileName, { type: audioBlob.type });
        
        // Create a new FileList-like object
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(audioFile);
        fileInput.files = dataTransfer.files;
        
        // Show preview
        const url = URL.createObjectURL(audioBlob);
        audioPreview.src = url;
        audioPreview.style.display = 'block';
        playButton.style.display = 'inline-block';
        
        console.log('Recording completed:', fileName, 'Size:', audioBlob.size, 'Type:', audioBlob.type);
    }
    
    /**
     * Update recording UI state
     */
    function updateRecordingUI(fieldName, isRecording) {
        const container = document.querySelector(`[data-field="${fieldName}"]`).closest('.audio-field-container');
        const startButton = container.querySelector('.start-recording');
        const stopButton = container.querySelector('.stop-recording');
        const recordingStatus = container.querySelector('.recording-status');
        
        if (isRecording) {
            startButton.style.display = 'none';
            stopButton.style.display = 'inline-block';
            recordingStatus.style.display = 'flex';
        } else {
            startButton.style.display = 'inline-block';
            stopButton.style.display = 'none';
            recordingStatus.style.display = 'none';
        }
    }
    
    /**
     * Start recording timer
     */
    function startRecordingTimer(fieldName) {
        const container = document.querySelector(`[data-field="${fieldName}"]`).closest('.audio-field-container');
        const timerElement = container.querySelector('.recording-timer');
        
        recordingTimer = setInterval(function() {
            const elapsed = Date.now() - recordingStartTime;
            const minutes = Math.floor(elapsed / 60000);
            const seconds = Math.floor((elapsed % 60000) / 1000);
            
            timerElement.textContent = 
                (minutes < 10 ? '0' : '') + minutes + ':' + 
                (seconds < 10 ? '0' : '') + seconds;
        }, 1000);
    }
    
    /**
     * Stop recording timer
     */
    function stopRecordingTimer() {
        if (recordingTimer) {
            clearInterval(recordingTimer);
            recordingTimer = null;
        }
    }
    
    /**
     * Get supported MIME type for recording
     */
    function getSupportedMimeType() {
        const types = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/mp4',
            'audio/ogg;codecs=opus',
            'audio/wav'
        ];
        
        for (let type of types) {
            if (MediaRecorder.isTypeSupported(type)) {
                return type;
            }
        }
        
        return 'audio/webm'; // Fallback
    }
    
    /**
     * Get file extension from MIME type
     */
    function getFileExtension(mimeType) {
        const extensions = {
            'audio/webm': 'webm',
            'audio/mp4': 'm4a',
            'audio/ogg': 'ogg',
            'audio/wav': 'wav',
            'audio/mpeg': 'mp3'
        };
        
        return extensions[mimeType] || 'webm';
    }
    
    /**
     * Validate audio file
     */
    function isValidAudioFile(file) {
        const validTypes = [
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
            'audio/mp4',
            'audio/aac',
            'audio/webm',
            'audio/flac'
        ];
        
        return validTypes.includes(file.type);
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        // Create or update error message
        let errorDiv = document.querySelector('.audio-error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'audio-error-message';
            errorDiv.style.cssText = `
                background: #f8d7da;
                color: #721c24;
                padding: 10px;
                border: 1px solid #f5c6cb;
                border-radius: 4px;
                margin: 10px 0;
                display: block;
            `;
            
            // Insert at the beginning of the form
            const form = document.querySelector('.xpsocial-form');
            if (form) {
                form.insertBefore(errorDiv, form.firstChild);
            }
        }
        
        errorDiv.textContent = message;
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }, 5000);
    }
    
    /**
     * Check if browser supports audio recording
     */
    function checkAudioSupport() {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.warn('Audio recording not supported in this browser');
            return false;
        }
        
        if (!window.MediaRecorder) {
            console.warn('MediaRecorder not supported in this browser');
            return false;
        }
        
        return true;
    }
    
    // Check support on load
    if (!checkAudioSupport()) {
        console.warn('Audio recording functionality may not work in this browser');
    }
    
})();
