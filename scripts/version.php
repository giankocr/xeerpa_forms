<?php
/**
 * Script para manejar versiones del plugin XPSocial Login
 * 
 * Uso:
 * php scripts/version.php get                    - Obtener versión actual
 * php scripts/version.php set 1.2.3             - Establecer versión específica
 * php scripts/version.php patch                 - Incrementar patch (1.0.0 -> 1.0.1)
 * php scripts/version.php minor                 - Incrementar minor (1.0.0 -> 1.1.0)
 * php scripts/version.php major                 - Incrementar major (1.0.0 -> 2.0.0)
 * php scripts/version.php show                  - Mostrar información detallada
 */

// Verificar que se ejecute desde la línea de comandos
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos.');
}

// Verificar argumentos
if ($argc < 2) {
    showUsage();
    exit(1);
}

$command = $argv[1];
$versionManager = new VersionManager();

switch ($command) {
    case 'get':
        echo $versionManager->getCurrentVersion() . "\n";
        break;
        
    case 'set':
        if ($argc < 3) {
            echo "Error: Debes especificar una versión.\n";
            echo "Uso: php scripts/version.php set 1.2.3\n";
            exit(1);
        }
        $newVersion = $argv[2];
        if ($versionManager->setVersion($newVersion)) {
            echo "Versión actualizada a: $newVersion\n";
        } else {
            echo "Error al actualizar la versión.\n";
            exit(1);
        }
        break;
        
    case 'patch':
        $newVersion = $versionManager->incrementPatch();
        if ($newVersion) {
            echo "Versión actualizada a: $newVersion\n";
        } else {
            echo "Error al incrementar la versión patch.\n";
            exit(1);
        }
        break;
        
    case 'minor':
        $newVersion = $versionManager->incrementMinor();
        if ($newVersion) {
            echo "Versión actualizada a: $newVersion\n";
        } else {
            echo "Error al incrementar la versión minor.\n";
            exit(1);
        }
        break;
        
    case 'major':
        $newVersion = $versionManager->incrementMajor();
        if ($newVersion) {
            echo "Versión actualizada a: $newVersion\n";
        } else {
            echo "Error al incrementar la versión major.\n";
            exit(1);
        }
        break;
        
    case 'show':
        $versionManager->showInfo();
        break;
        
    default:
        echo "Comando no reconocido: $command\n";
        showUsage();
        exit(1);
}

function showUsage() {
    echo "Script de manejo de versiones para XPSocial Login\n\n";
    echo "Comandos disponibles:\n";
    echo "  get                    - Obtener versión actual\n";
    echo "  set <version>          - Establecer versión específica (ej: 1.2.3)\n";
    echo "  patch                  - Incrementar patch (1.0.0 -> 1.0.1)\n";
    echo "  minor                  - Incrementar minor (1.0.0 -> 1.1.0)\n";
    echo "  major                  - Incrementar major (1.0.0 -> 2.0.0)\n";
    echo "  show                   - Mostrar información detallada\n\n";
    echo "Ejemplos:\n";
    echo "  php scripts/version.php get\n";
    echo "  php scripts/version.php set 1.2.3\n";
    echo "  php scripts/version.php patch\n";
    echo "  composer version:patch\n";
}

class VersionManager {
    private $pluginFile;
    private $composerFile;
    private $readmeFile;
    
    public function __construct() {
        $this->pluginFile = __DIR__ . '/../xpsocial_login.php';
        $this->composerFile = __DIR__ . '/../composer.json';
        $this->readmeFile = __DIR__ . '/../readme.txt';
    }
    
    /**
     * Obtener la versión actual del plugin
     */
    public function getCurrentVersion() {
        $content = file_get_contents($this->pluginFile);
        if (preg_match('/Version:\s*([0-9.]+)/i', $content, $matches)) {
            return $matches[1];
        }
        return '1.0.0';
    }
    
    /**
     * Establecer una versión específica
     */
    public function setVersion($version) {
        if (!$this->isValidVersion($version)) {
            echo "Error: Versión inválida. Debe ser en formato X.Y.Z (ej: 1.2.3)\n";
            return false;
        }
        
        $currentVersion = $this->getCurrentVersion();
        echo "Actualizando versión de $currentVersion a $version...\n";
        
        // Actualizar archivo principal del plugin
        if (!$this->updatePluginFile($version)) {
            echo "Error: No se pudo actualizar xpsocial_login.php\n";
            return false;
        }
        
        // Actualizar composer.json
        if (!$this->updateComposerFile($version)) {
            echo "Error: No se pudo actualizar composer.json\n";
            return false;
        }
        
        // Actualizar readme.txt si existe
        $this->updateReadmeFile($version);
        
        // Actualizar constantes en archivos PHP
        $this->updatePhpConstants($version);
        
        echo "✓ Versión actualizada exitosamente en todos los archivos.\n";
        return true;
    }
    
    /**
     * Incrementar versión patch (1.0.0 -> 1.0.1)
     */
    public function incrementPatch() {
        $currentVersion = $this->getCurrentVersion();
        $parts = explode('.', $currentVersion);
        $parts[2] = (int)$parts[2] + 1;
        $newVersion = implode('.', $parts);
        return $this->setVersion($newVersion) ? $newVersion : false;
    }
    
    /**
     * Incrementar versión minor (1.0.0 -> 1.1.0)
     */
    public function incrementMinor() {
        $currentVersion = $this->getCurrentVersion();
        $parts = explode('.', $currentVersion);
        $parts[1] = (int)$parts[1] + 1;
        $parts[2] = 0; // Reset patch
        $newVersion = implode('.', $parts);
        return $this->setVersion($newVersion) ? $newVersion : false;
    }
    
    /**
     * Incrementar versión major (1.0.0 -> 2.0.0)
     */
    public function incrementMajor() {
        $currentVersion = $this->getCurrentVersion();
        $parts = explode('.', $currentVersion);
        $parts[0] = (int)$parts[0] + 1;
        $parts[1] = 0; // Reset minor
        $parts[2] = 0; // Reset patch
        $newVersion = implode('.', $parts);
        return $this->setVersion($newVersion) ? $newVersion : false;
    }
    
    /**
     * Mostrar información detallada
     */
    public function showInfo() {
        $version = $this->getCurrentVersion();
        echo "=== Información de Versión ===\n";
        echo "Versión actual: $version\n";
        echo "Archivos que se actualizan:\n";
        echo "  - xpsocial_login.php (header del plugin)\n";
        echo "  - composer.json (versión del paquete)\n";
        echo "  - readme.txt (si existe)\n";
        echo "  - Archivos PHP con constantes de versión\n\n";
        
        echo "Comandos disponibles:\n";
        echo "  composer version:get     - Obtener versión actual\n";
        echo "  composer version:set 1.2.3 - Establecer versión específica\n";
        echo "  composer version:patch   - Incrementar patch\n";
        echo "  composer version:minor   - Incrementar minor\n";
        echo "  composer version:major   - Incrementar major\n";
    }
    
    /**
     * Validar formato de versión
     */
    private function isValidVersion($version) {
        return preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $version);
    }
    
    /**
     * Actualizar archivo principal del plugin
     */
    private function updatePluginFile($version) {
        $content = file_get_contents($this->pluginFile);
        $content = preg_replace('/Version:\s*[0-9.]+/i', "Version: $version", $content);
        return file_put_contents($this->pluginFile, $content) !== false;
    }
    
    /**
     * Actualizar composer.json
     */
    private function updateComposerFile($version) {
        $content = file_get_contents($this->composerFile);
        $content = preg_replace('/"version":\s*"[^"]+"/', "\"version\": \"$version\"", $content);
        return file_put_contents($this->composerFile, $content) !== false;
    }
    
    /**
     * Actualizar readme.txt si existe
     */
    private function updateReadmeFile($version) {
        if (!file_exists($this->readmeFile)) {
            return true;
        }
        
        $content = file_get_contents($this->readmeFile);
        $content = preg_replace('/Stable tag:\s*[0-9.]+/i', "Stable tag: $version", $content);
        return file_put_contents($this->readmeFile, $content) !== false;
    }
    
    /**
     * Actualizar constantes de versión en archivos PHP
     */
    private function updatePhpConstants($version) {
        $files = [
            __DIR__ . '/../includes/class-xpsocial_login.php',
            __DIR__ . '/../public/class-xpsocial_login-public.php',
            __DIR__ . '/../admin/class-xpsocial_login-admin.php'
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                // Buscar y reemplazar constantes de versión
                $content = preg_replace('/XPSOCIAL_LOGIN_VERSION[^;]*;\s*\/\/\s*[0-9.]+/', "XPSOCIAL_LOGIN_VERSION', '$version'); // $version", $content);
                $content = preg_replace('/\$this->version\s*=\s*[\'"][^\'"]+[\'"]/', "\$this->version = '$version'", $content);
                file_put_contents($file, $content);
            }
        }
    }
}
