/**
 * XPSocial Audio Recorder - Sistema de grabación de audio para campos dinámicos
 * Versión refactorizada con mejor estructura y mantenibilidad
 */

class XPSocialAudioRecorder {
    constructor(container, options = {}) {
        this.container = container;
        this.fieldId = container.getAttribute('data-field-id');
        this.fieldName = container.getAttribute('data-field-name');
        
        // Configuración por defecto
        this.config = {
            allowedFormats: ['mp3', 'wav', 'm4a'],
            maxDuration: 300, // 5 minutos
            maxSizeMB: 10,
            allowRecording: true,
            allowFileUpload: true,
            ...options
        };
        
        // Estado del grabador
        this.state = {
            mediaRecorder: null,
            audioChunks: [],
            isRecording: false,
            recordingStartTime: null,
            recordingTimer: null,
            audioBlob: null,
            audioContext: null,
            analyser: null,
            microphone: null,
            animationFrame: null,
            boundUpdateAudioInfo: null,
            boundHandleAudioError: null
        };
        
        // Elementos DOM
        this.elements = {};
        
        this.init();
    }
    
    // ==================== INICIALIZACIÓN ====================
    
    init() {
        this.createUI();
        this.bindEvents();
        this.checkBrowserSupport();
        this.logDiagnosticInfo();
    }
    
    logDiagnosticInfo() {
        // Diagnostic logging disabled for production
    }
    
    createUI() {
        const fieldId = this.fieldId;
        const fieldName = this.fieldName;
        
        this.container.innerHTML = this.generateHTML(fieldId, fieldName);
        this.getElements();
    }
    
    generateHTML(fieldId, fieldName) {
        return `
            <div class="xpsocial-audio-recorder" id="recorder_${fieldId}">
                ${this.generateHeader()}
                ${this.generateControls()}
                ${this.generateStatus(fieldId)}
                ${this.generatePreview(fieldId)}
                ${this.generateError(fieldId)}
                ${this.generateHiddenInputs(fieldName)}
            </div>
        `;
    }
    
    generateHeader() {
        return `
                <div class="recorder-header">
                    <h4>Grabador de Audio</h4>
                </div>
        `;
    }
    
    generateControls() {
        const recordBtn = this.config.allowRecording ? `
            <button type="button" class="record-btn" id="record_${this.fieldId}">
                        <span class="icon">🎤</span>
                        <span class="text">Grabar</span>
                    </button>
        ` : '';
                    
        const uploadBtn = this.config.allowFileUpload ? `
            <button type="button" class="upload-btn" id="upload_${this.fieldId}">
                        <span class="icon">📁</span>
                        <span class="text">Subir Archivo</span>
                    </button>
        ` : '';
        
        return `
            <div class="recorder-controls">
                ${recordBtn}
                ${uploadBtn}
                </div>
        `;
    }
                
    generateStatus(fieldId) {
        return `
                <div class="recorder-status" id="status_${fieldId}" style="display: none;">
                    <div class="status-content">
                        <button type="button" class="stop-btn" id="stop_${fieldId}">
                            <span class="icon">⏹️</span>
                            <span class="text">Detener</span>
                        </button>
                    ${this.generateWaveAnimation()}
                        <div class="recording-indicator">
                            <div class="recording-dot"></div>
                            <span class="timer" id="timer_${fieldId}">00:00.00</span>
                        </div>
                    </div>
                </div>
        `;
    }
    
    generateWaveAnimation() {
        const waveBars = Array.from({length: 15}, () => '<div class="wave-bar"></div>').join('');
        return `<div class="audio-wave" id="wave_${this.fieldId}">${waveBars}</div>`;
    }
    
    generatePreview(fieldId) {
        return `
                <div class="audio-preview" id="preview_${fieldId}" style="display: none;">
                    <div class="preview-header">
                        <h5>Audio Grabado</h5>
                        <button type="button" class="delete-btn" id="delete_${fieldId}">
                            <span class="icon">🗑️</span>
                        </button>
                    </div>
                    <div class="audio-player">
                    <audio controls id="audio_${fieldId}" preload="metadata"></audio>
                </div>
                ${this.generateAudioInfo(fieldId)}
                    </div>
        `;
    }
    
    
    generateAudioInfo(fieldId) {
        return `
                    <div class="audio-info">
                        <span class="file-name" id="filename_${fieldId}"></span>
                        <span class="file-size" id="filesize_${fieldId}"></span>
                        <span class="file-duration" id="duration_${fieldId}"></span>
                    </div>
        `;
    }
    
    generateError(fieldId) {
        return `<div class="error-message" id="error_${fieldId}" style="display: none;"></div>`;
    }
    
    generateHiddenInputs(fieldName) {
        return `
            <input type="file" id="file_input_${this.fieldId}" name="${fieldName}" accept="audio/*" style="display: none;">
            <input type="hidden" id="hidden_input_${this.fieldId}" name="${fieldName}_filename">
        `;
    }
    
    getElements() {
        const fieldId = this.fieldId;
        this.elements = {
            recordBtn: document.getElementById(`record_${fieldId}`),
            uploadBtn: document.getElementById(`upload_${fieldId}`),
            stopBtn: document.getElementById(`stop_${fieldId}`),
            deleteBtn: document.getElementById(`delete_${fieldId}`),
            fileInput: document.getElementById(`file_input_${fieldId}`),
            hiddenInput: document.getElementById(`hidden_input_${fieldId}`),
            status: document.getElementById(`status_${fieldId}`),
            preview: document.getElementById(`preview_${fieldId}`),
            timer: document.getElementById(`timer_${fieldId}`),
            audio: document.getElementById(`audio_${fieldId}`),
            filename: document.getElementById(`filename_${fieldId}`),
            filesize: document.getElementById(`filesize_${fieldId}`),
            duration: document.getElementById(`duration_${fieldId}`),
            error: document.getElementById(`error_${fieldId}`),
        };
    }
    
    // ==================== EVENTOS ====================
    
    bindEvents() {
        this.bindControlEvents();
        this.bindAudioEvents();
    }
    
    bindControlEvents() {
        const events = [
            { element: 'recordBtn', event: 'click', handler: () => this.startRecording() },
            { element: 'uploadBtn', event: 'click', handler: () => this.elements.fileInput?.click() },
            { element: 'stopBtn', event: 'click', handler: () => this.stopRecording() },
            { element: 'deleteBtn', event: 'click', handler: () => this.deleteRecording() },
            { element: 'fileInput', event: 'change', handler: (e) => this.handleFileUpload(e) },
        ];
        
        events.forEach(({ element, event, handler }) => {
            if (this.elements[element]) {
                this.elements[element].addEventListener(event, handler);
            }
        });
    }
    
    bindAudioEvents() {
        // Los event listeners del audio se manejan dinámicamente
    }
    
    // ==================== GRABACIÓN ====================
    
    async startRecording() {
        try {
            this.showError('');
            
            // Verificar compatibilidad antes de intentar grabar
            if (!this.checkBrowserSupport()) {
                return;
            }
            
            const stream = await this.getUserMedia();
            this.setupMediaRecorder(stream);
            this.startRecordingProcess(stream);
        } catch (error) {
            this.handleRecordingError(error);
        }
    }
    
    async getUserMedia() {
        return await navigator.mediaDevices.getUserMedia({ 
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                autoGainControl: true,
                sampleRate: 44100,
                channelCount: 1,
                volume: 1.0
            } 
        });
    }
    
    setupMediaRecorder(stream) {
        const options = { mimeType: this.getSupportedMimeType() };
        this.state.mediaRecorder = new MediaRecorder(stream, options);
        this.state.audioChunks = [];
        
        this.state.mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                this.state.audioChunks.push(event.data);
                }
            };
            
        this.state.mediaRecorder.onstop = () => {
                this.processRecording();
                this.stopStream(stream);
            };
            
        this.state.mediaRecorder.onerror = (event) => {
                this.showError('Error durante la grabación: ' + event.error);
                this.stopStream(stream);
            };
    }
    
    startRecordingProcess(stream) {
        this.state.mediaRecorder.start(1000);
        this.state.isRecording = true;
        this.state.recordingStartTime = Date.now();
        
            this.setupAudioAnalysis(stream);
            this.updateUI('recording');
            this.startTimer();
            this.startWaveAnimation();
    }
    
    stopRecording() {
        if (this.state.mediaRecorder && this.state.isRecording) {
            this.state.mediaRecorder.stop();
            this.state.isRecording = false;
            this.stopTimer();
            this.stopWaveAnimation();
            this.cleanupAudioAnalysis();
            this.updateUI('stopped');
        }
    }
    
    // ==================== PROCESAMIENTO DE AUDIO ====================
    
    processRecording() {
        
        if (this.state.audioChunks.length === 0) {
            this.showError('No se grabó ningún audio');
            return;
        }
        
        const mimeType = this.getSupportedMimeType();
        this.state.audioBlob = new Blob(this.state.audioChunks, { type: mimeType });
        
        if (!this.validateAudioSize()) return;
        
        this.convertToCompatibleFormat(this.state.audioBlob, mimeType);
    }
    
    validateAudioSize() {
        const sizeMB = this.state.audioBlob.size / (1024 * 1024);
        if (sizeMB > this.config.maxSizeMB) {
            this.showError(`El archivo es demasiado grande (${sizeMB.toFixed(2)}MB). Máximo permitido: ${this.config.maxSizeMB}MB`);
            return false;
        }
        return true;
    }
    
    async convertToCompatibleFormat(originalBlob, originalMimeType) {
        try {
            
            const audioBuffer = await this.decodeAudioData(originalBlob);
            
            const wavBlob = this.audioBufferToWav(audioBuffer);
            const fileName = `recording_${Date.now()}.wav`;
            const audioFile = new File([wavBlob], fileName, { type: 'audio/wav' });
            
            this.finalizeAudioProcessing(audioFile);
            
        } catch (error) {
            // Audio conversion failed, using original format
            this.fallbackToOriginalFormat(originalBlob, originalMimeType);
        }
    }
    
    async decodeAudioData(originalBlob) {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const arrayBuffer = await originalBlob.arrayBuffer();
        return await audioContext.decodeAudioData(arrayBuffer);
    }
    
    fallbackToOriginalFormat(originalBlob, originalMimeType) {
        const fileName = `recording_${Date.now()}.${this.getFileExtension()}`;
        const audioFile = new File([originalBlob], fileName, { type: originalMimeType });
        
        this.finalizeAudioProcessing(audioFile);
    }
    
    finalizeAudioProcessing(audioFile) {
        const recordingDuration = (Date.now() - this.state.recordingStartTime) / 1000;
        
        this.updateAudioPreview(audioFile);
        this.updateHiddenInput(audioFile);
        this.elements.duration.textContent = this.formatDuration(recordingDuration);
        
        this.elements.preview.style.display = 'block';
        this.elements.status.style.display = 'none';
    }
    
    // ==================== SUBIDA DE ARCHIVOS ====================
    
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (!this.validateFile(file)) return;
        
        this.showError('');
        this.updateAudioPreview(file);
        this.updateHiddenInput(file);
        this.elements.preview.style.display = 'block';
    }
    
    validateFile(file) {
        if (!file.type.startsWith('audio/')) {
            this.showError('Por favor, selecciona un archivo de audio válido');
            return false;
        }
        
        const sizeMB = file.size / (1024 * 1024);
        if (sizeMB > this.config.maxSizeMB) {
            this.showError(`El archivo es demasiado grande (${sizeMB.toFixed(2)}MB). Máximo permitido: ${this.config.maxSizeMB}MB`);
            return false;
        }
        
        const extension = file.name.split('.').pop().toLowerCase();
        if (!this.config.allowedFormats.includes(extension)) {
            this.showError(`Formato no permitido. Formatos válidos: ${this.config.allowedFormats.join(', ')}`);
            return false;
        }
        
        if (file.size === 0) {
            this.showError('El archivo está vacío o corrupto');
            return false;
        }
        
        return true;
    }
    
    // ==================== PREVIEW DE AUDIO ====================
    
    updateAudioPreview(file) {
        
        this.cleanupPreviousAudio();
        this.setupAudioEventListeners();
        this.loadAudioFile(file);
        this.setupAudioTimeout();
    }
    
    cleanupPreviousAudio() {
        if (this.elements.audio.src && this.elements.audio.src.startsWith('blob:')) {
            URL.revokeObjectURL(this.elements.audio.src);
        }
        
        if (this.state.boundUpdateAudioInfo) {
            this.elements.audio.removeEventListener('loadedmetadata', this.state.boundUpdateAudioInfo);
        }
        if (this.state.boundHandleAudioError) {
            this.elements.audio.removeEventListener('error', this.state.boundHandleAudioError);
        }
    }
    
    setupAudioEventListeners() {
        this.state.boundUpdateAudioInfo = this.updateAudioInfo.bind(this);
        this.state.boundHandleAudioError = this.handleAudioError.bind(this);
        
        this.elements.audio.addEventListener('loadedmetadata', this.state.boundUpdateAudioInfo);
        this.elements.audio.addEventListener('error', this.state.boundHandleAudioError);
        this.elements.audio.addEventListener('canplaythrough', () => {
        });
    }
    
    loadAudioFile(file) {
        const url = URL.createObjectURL(file);
        
        this.elements.audio.src = url;
        this.elements.filename.textContent = file.name;
        this.elements.filesize.textContent = this.formatFileSize(file.size);
        this.elements.duration.textContent = 'Cargando...';
        
        this.elements.audio.load();
    }
    
    setupAudioTimeout() {
        setTimeout(() => {
            if (this.elements.duration.textContent === 'Cargando...') {
                console.warn('Audio loading timeout, trying fallback approach');
                this.handleAudioLoadingTimeout();
            }
        }, 5000);
    }
    
    
    // ==================== UTILIDADES DE AUDIO ====================
    
    audioBufferToWav(buffer) {
        const length = buffer.length;
        const sampleRate = buffer.sampleRate;
        const numberOfChannels = buffer.numberOfChannels;
        const arrayBuffer = new ArrayBuffer(44 + length * numberOfChannels * 2);
        const view = new DataView(arrayBuffer);
        
        this.writeWavHeader(view, length, sampleRate, numberOfChannels);
        this.writeWavData(view, buffer, numberOfChannels);
        
        return new Blob([arrayBuffer], { type: 'audio/wav' });
    }
    
    writeWavHeader(view, length, sampleRate, numberOfChannels) {
        const writeString = (offset, string) => {
            for (let i = 0; i < string.length; i++) {
                view.setUint8(offset + i, string.charCodeAt(i));
            }
        };
        
        writeString(0, 'RIFF');
        view.setUint32(4, 36 + length * numberOfChannels * 2, true);
        writeString(8, 'WAVE');
        writeString(12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true);
        view.setUint16(22, numberOfChannels, true);
        view.setUint32(24, sampleRate, true);
        view.setUint32(28, sampleRate * numberOfChannels * 2, true);
        view.setUint16(32, numberOfChannels * 2, true);
        view.setUint16(34, 16, true);
        writeString(36, 'data');
        view.setUint32(40, length * numberOfChannels * 2, true);
    }
    
    writeWavData(view, buffer, numberOfChannels) {
        let offset = 44;
        for (let i = 0; i < buffer.length; i++) {
            for (let channel = 0; channel < numberOfChannels; channel++) {
                const sample = Math.max(-1, Math.min(1, buffer.getChannelData(channel)[i]));
                view.setInt16(offset, sample < 0 ? sample * 0x8000 : sample * 0x7FFF, true);
                offset += 2;
            }
        }
    }
    
    // ==================== ANÁLISIS DE AUDIO ====================
    
    setupAudioAnalysis(stream) {
        try {
            this.state.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.state.analyser = this.state.audioContext.createAnalyser();
            this.state.microphone = this.state.audioContext.createMediaStreamSource(stream);
            
            this.state.analyser.fftSize = 256;
            this.state.analyser.smoothingTimeConstant = 0.8;
            
            this.state.microphone.connect(this.state.analyser);
            this.verifyMicrophoneInput();
        } catch (error) {
            console.warn('No se pudo configurar el análisis de audio:', error);
        }
    }
    
    verifyMicrophoneInput() {
        if (!this.state.analyser) return;
        
        const bufferLength = this.state.analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        
        setTimeout(() => {
            this.state.analyser.getByteFrequencyData(dataArray);
            const hasAudio = dataArray.some(value => value > 0);
            
            if (!hasAudio) {
                this.showError('No se detecta entrada de audio del micrófono. Verifica que el micrófono esté conectado y funcionando.');
            }
        }, 2000);
    }
    
    startWaveAnimation() {
        if (!this.state.analyser) return;
        
        const waveBars = this.container.querySelectorAll('.wave-bar');
        const bufferLength = this.state.analyser.frequencyBinCount;
        const dataArray = new Uint8Array(bufferLength);
        
        const animate = () => {
            if (!this.state.isRecording) return;
            
            this.state.analyser.getByteFrequencyData(dataArray);
            
            waveBars.forEach((bar, index) => {
                const dataIndex = Math.floor((index / waveBars.length) * bufferLength);
                const value = dataArray[dataIndex] / 255;
                const height = Math.max(8, value * 40);
                bar.style.height = `${height}px`;
            });
            
            this.state.animationFrame = requestAnimationFrame(animate);
        };
        
        animate();
    }
    
    stopWaveAnimation() {
        if (this.state.animationFrame) {
            cancelAnimationFrame(this.state.animationFrame);
            this.state.animationFrame = null;
        }
        
        const waveBars = this.container.querySelectorAll('.wave-bar');
        waveBars.forEach(bar => {
            bar.style.height = '8px';
        });
    }
    
    cleanupAudioAnalysis() {
        if (this.state.audioContext) {
            this.state.audioContext.close();
            this.state.audioContext = null;
        }
        this.state.analyser = null;
        this.state.microphone = null;
    }
    
    // ==================== TIMER ====================
    
    startTimer() {
        this.state.recordingTimer = setInterval(() => {
            if (this.state.recordingStartTime) {
                const elapsed = Date.now() - this.state.recordingStartTime;
                this.elements.timer.textContent = this.formatDuration(elapsed / 1000);
                
                if (elapsed / 1000 >= this.config.maxDuration) {
                    this.stopRecording();
                }
            }
        }, 10);
    }
    
    stopTimer() {
        if (this.state.recordingTimer) {
            clearInterval(this.state.recordingTimer);
            this.state.recordingTimer = null;
        }
    }
    
    // ==================== UI ====================
    
    updateUI(state) {
        const controls = this.container.querySelector('.recorder-controls');
        const status = this.elements.status;
        
        const states = {
            'idle': () => {
                if (controls) controls.style.display = 'flex';
                status.style.display = 'none';
            },
            'recording': () => {
                if (controls) controls.style.display = 'none';
                status.style.display = 'block';
            },
            'stopped': () => {
                if (controls) controls.style.display = 'none';
                status.style.display = 'block';
            }
        };
        
        if (states[state]) {
            states[state]();
        }
    }
    
    
    updateAudioInfo() {
        if (this.elements.audio.duration && !isNaN(this.elements.audio.duration) && isFinite(this.elements.audio.duration)) {
            this.elements.duration.textContent = this.formatDuration(this.elements.audio.duration);
        }
    }
    
    updateHiddenInput(file) {
        if (this.elements.fileInput) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            this.elements.fileInput.files = dataTransfer.files;
        }
        
        if (this.elements.hiddenInput) {
            this.elements.hiddenInput.value = file.name;

            // Production mode - debug disabled

            // Emitir evento personalizado para transferencia de audio (con burbujeo)
            const audioRecordedEvent = new CustomEvent('audioRecorded', {
                bubbles: true,
                composed: true,
                detail: {
                    filename: file.name,
                    url: this.state.audioUrl,
                    size: file.size,
                    duration: this.state.audioDuration
                }
            });
            this.elements.hiddenInput.dispatchEvent(audioRecordedEvent);

            // Disparar evento change para listeners existentes
            const changeEvent = new Event('change', { bubbles: true, composed: true });
            this.elements.hiddenInput.dispatchEvent(changeEvent);
        } else {
            // Hidden input not found - silent fail in production
        }
    }
    
    deleteRecording() {
        this.cleanupAudioResources();
        this.resetUI();
        this.updateUI('idle');
    }
    
    cleanupAudioResources() {
        if (this.elements.audio.src && this.elements.audio.src.startsWith('blob:')) {
            URL.revokeObjectURL(this.elements.audio.src);
        }
        
        if (this.state.audioBlob) {
            this.state.audioBlob = null;
        }
        
        if (this.state.boundUpdateAudioInfo) {
            this.elements.audio.removeEventListener('loadedmetadata', this.state.boundUpdateAudioInfo);
        }
        if (this.state.boundHandleAudioError) {
            this.elements.audio.removeEventListener('error', this.state.boundHandleAudioError);
        }
        
        this.state.boundUpdateAudioInfo = null;
        this.state.boundHandleAudioError = null;
    }
    
    resetUI() {
        this.elements.audio.src = '';
        this.elements.audio.load();
        this.elements.filename.textContent = '';
        this.elements.filesize.textContent = '';
        this.elements.duration.textContent = '';
        this.elements.hiddenInput.value = '';
        this.elements.fileInput.value = '';
        this.showError('');
        this.elements.preview.style.display = 'none';
        this.elements.status.style.display = 'none';
    }
    
    // ==================== MANEJO DE ERRORES ====================
    
    handleRecordingError(error) {
        console.error('Error starting recording:', error);
        
        let errorMessage = 'Error al acceder al micrófono: ';
        
        if (error.name === 'NotAllowedError') {
            errorMessage = 'Acceso al micrófono denegado. Por favor, permite el acceso al micrófono y recarga la página.';
        } else if (error.name === 'NotFoundError') {
            errorMessage = 'No se encontró ningún micrófono. Verifica que tengas un micrófono conectado.';
        } else if (error.name === 'NotReadableError') {
            errorMessage = 'El micrófono está siendo usado por otra aplicación. Cierra otras aplicaciones que puedan estar usando el micrófono.';
        } else if (error.name === 'OverconstrainedError') {
            errorMessage = 'El micrófono no cumple con los requisitos técnicos necesarios.';
        } else if (error.name === 'SecurityError') {
            errorMessage = 'Error de seguridad. Asegúrate de usar HTTPS y permite el acceso al micrófono.';
        } else {
            errorMessage += error.message;
        }
        
        this.showError(errorMessage);
    }
    
    handleAudioError(event) {
        console.error('Error loading audio:', event);
        console.error('Audio element src:', this.elements.audio.src);
        console.error('Audio element error:', this.elements.audio.error);
        
        this.elements.duration.textContent = 'Error al cargar';
        
        const errorMessages = {
            1: 'El archivo fue abortado.',
            2: 'Error de red al cargar el archivo.',
            3: 'Error al decodificar el archivo de audio.',
            4: 'Formato de archivo no soportado.'
        };
        
        let errorMessage = 'Error al cargar el archivo de audio. ';
        if (this.elements.audio.error) {
            errorMessage += errorMessages[this.elements.audio.error.code] || 'Error desconocido.';
        } else {
            errorMessage += 'Verifica que el formato sea compatible.';
        }
        
        this.showError(errorMessage);
    }
    
    handleAudioLoadingTimeout() {
        this.elements.duration.textContent = 'Duración no disponible';
        this.showError('No se pudo cargar la información del archivo de audio, pero el archivo se guardará correctamente.');
    }
    
    
    // ==================== UTILIDADES ====================
    
    checkBrowserSupport() {
        
        const issues = [];
        
        // Verificar MediaDevices
        if (!navigator.mediaDevices) {
            issues.push('navigator.mediaDevices no está disponible');
        }
        
        // Verificar getUserMedia
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            issues.push('getUserMedia no está disponible');
        }
        
        // Verificar MediaRecorder
        if (!window.MediaRecorder) {
            issues.push('MediaRecorder no está disponible');
        }
        
        // Verificar HTTPS (requerido para getUserMedia)
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            issues.push('Se requiere HTTPS para grabación de audio');
        }
        
        if (issues.length > 0) {
            console.error('Browser support issues:', issues);
            this.showDetailedError(issues);
            if (this.elements.recordBtn) {
                this.elements.recordBtn.disabled = true;
            }
            return false;
        }
        
        return true;
    }
    
    showDetailedError(issues) {
        let errorMessage = 'Tu navegador no soporta grabación de audio. Problemas detectados:\n\n';
        issues.forEach((issue, index) => {
            errorMessage += `${index + 1}. ${issue}\n`;
        });
        
        errorMessage += '\nSoluciones:\n';
        errorMessage += '• Usa Chrome, Firefox, Safari o Edge actualizados\n';
        errorMessage += '• Asegúrate de usar HTTPS (no HTTP)\n';
        errorMessage += '• Verifica que JavaScript esté habilitado\n';
        errorMessage += '• Permite el acceso al micrófono cuando se solicite';
        
        this.showError(errorMessage);
    }
    
    getSupportedMimeType() {
        const types = [
            'audio/wav',
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/mp4;codecs=mp4a.40.2',
            'audio/mp4',
            'audio/ogg;codecs=opus',
            'audio/ogg'
        ];
        
        for (const type of types) {
            if (MediaRecorder.isTypeSupported(type)) {
                return type;
            }
        }
        
        return 'audio/webm';
    }
    
    getFileExtension() {
        const mimeType = this.getSupportedMimeType();
        if (mimeType.includes('webm')) return 'webm';
        if (mimeType.includes('mp4')) return 'm4a';
        if (mimeType.includes('wav')) return 'wav';
        return 'webm';
    }
    
    formatDuration(seconds) {
        if (!isFinite(seconds) || isNaN(seconds) || seconds < 0) {
            return '00:00.00';
        }
        
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        const centiseconds = Math.floor((seconds % 1) * 100);
        return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}.${centiseconds.toString().padStart(2, '0')}`;
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    showError(message) {
        if (message) {
            this.elements.error.textContent = message;
            this.elements.error.style.display = 'block';
        } else {
            this.elements.error.style.display = 'none';
        }
    }
    
    stopStream(stream) {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    }
}

// ==================== INICIALIZACIÓN GLOBAL ====================

document.addEventListener('DOMContentLoaded', function() {
    const audioContainers = document.querySelectorAll('.xpsocial-audio-field');
    
    audioContainers.forEach(container => {
        const fieldId = container.getAttribute('data-field-id');
        const allowedFormats = container.getAttribute('data-allowed-formats')?.split(',') || ['mp3', 'wav', 'm4a'];
        const maxDuration = parseInt(container.getAttribute('data-max-duration')) || 300;
        const maxSizeMB = parseInt(container.getAttribute('data-max-size-mb')) || 10;
        const allowRecording = container.getAttribute('data-allow-recording') === '1';
        const allowFileUpload = container.getAttribute('data-allow-file-upload') === '1';
        
        if (allowRecording || allowFileUpload) {
            new XPSocialAudioRecorder(container, {
                allowedFormats,
                maxDuration,
                maxSizeMB,
                allowRecording,
                allowFileUpload
            });
        }
    });
});

// Exportar para uso global
window.XPSocialAudioRecorder = XPSocialAudioRecorder;