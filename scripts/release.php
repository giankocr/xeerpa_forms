<?php
/**
 * Script para crear releases del plugin XPSocial Login
 * 
 * Uso:
 * php scripts/release.php patch    - Crear release patch (1.0.0 -> 1.0.1)
 * php scripts/release.php minor    - Crear release minor (1.0.0 -> 1.1.0)
 * php scripts/release.php major    - Crear release major (1.0.0 -> 2.0.0)
 * php scripts/release.php 1.2.3    - Crear release con versión específica
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

$versionType = $argv[1];
$releaseManager = new ReleaseManager();

// Determinar si es una versión específica o un tipo de incremento
if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $versionType)) {
    // Es una versión específica
    $newVersion = $versionType;
    $releaseManager->createRelease($newVersion);
} else {
    // Es un tipo de incremento
    switch ($versionType) {
        case 'patch':
            $releaseManager->createPatchRelease();
            break;
        case 'minor':
            $releaseManager->createMinorRelease();
            break;
        case 'major':
            $releaseManager->createMajorRelease();
            break;
        default:
            echo "Error: Tipo de release no válido: $versionType\n";
            showUsage();
            exit(1);
    }
}

function showUsage() {
    echo "Script de release para XPSocial Login\n\n";
    echo "Uso:\n";
    echo "  php scripts/release.php patch        - Crear release patch (1.0.0 -> 1.0.1)\n";
    echo "  php scripts/release.php minor        - Crear release minor (1.0.0 -> 1.1.0)\n";
    echo "  php scripts/release.php major        - Crear release major (1.0.0 -> 2.0.0)\n";
    echo "  php scripts/release.php 1.2.3        - Crear release con versión específica\n\n";
    echo "Ejemplos:\n";
    echo "  php scripts/release.php patch\n";
    echo "  composer release:patch\n";
}

class ReleaseManager {
    private $versionManager;
    private $pluginDir;
    
    public function __construct() {
        $this->versionManager = new VersionManager();
        $this->pluginDir = __DIR__ . '/..';
    }
    
    /**
     * Crear release patch
     */
    public function createPatchRelease() {
        $currentVersion = $this->versionManager->getCurrentVersion();
        $newVersion = $this->versionManager->incrementPatch();
        
        if ($newVersion) {
            $this->createRelease($newVersion, 'patch');
        }
    }
    
    /**
     * Crear release minor
     */
    public function createMinorRelease() {
        $currentVersion = $this->versionManager->getCurrentVersion();
        $newVersion = $this->versionManager->incrementMinor();
        
        if ($newVersion) {
            $this->createRelease($newVersion, 'minor');
        }
    }
    
    /**
     * Crear release major
     */
    public function createMajorRelease() {
        $currentVersion = $this->versionManager->getCurrentVersion();
        $newVersion = $this->versionManager->incrementMajor();
        
        if ($newVersion) {
            $this->createRelease($newVersion, 'major');
        }
    }
    
    /**
     * Crear release completo
     */
    public function createRelease($version, $type = 'custom') {
        echo "=== Creando Release $version ===\n";
        
        // 1. Actualizar versión
        echo "1. Actualizando versión...\n";
        if (!$this->versionManager->setVersion($version)) {
            echo "Error: No se pudo actualizar la versión.\n";
            exit(1);
        }
        
        // 2. Ejecutar tests (si existen)
        echo "2. Ejecutando tests...\n";
        $this->runTests();
        
        // 3. Ejecutar linting
        echo "3. Ejecutando linting...\n";
        $this->runLinting();
        
        // 4. Crear changelog
        echo "4. Actualizando changelog...\n";
        $this->updateChangelog($version, $type);
        
        // 5. Crear commit de release
        echo "5. Creando commit de release...\n";
        $this->createReleaseCommit($version);
        
        // 6. Crear tag de Git
        echo "6. Creando tag de Git...\n";
        $this->createGitTag($version);
        
        // 7. Crear archivo ZIP
        echo "7. Creando archivo ZIP...\n";
        $this->createZipFile($version);
        
        echo "\n✓ Release $version creado exitosamente!\n";
        echo "Archivos generados:\n";
        echo "  - xpsocial_login-v$version.zip\n";
        echo "  - Git tag: v$version\n";
        echo "  - Commit: Release $version\n\n";
        
        echo "Próximos pasos:\n";
        echo "  1. Revisar los cambios: git log --oneline -5\n";
        echo "  2. Subir el tag: git push origin v$version\n";
        echo "  3. Subir los cambios: git push origin develop\n";
        echo "  4. Crear release en GitHub con el archivo ZIP\n";
    }
    
    /**
     * Ejecutar tests
     */
    private function runTests() {
        $output = [];
        $returnCode = 0;
        exec('composer test 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "⚠️  Tests fallaron, pero continuando...\n";
            echo implode("\n", $output) . "\n";
        } else {
            echo "✓ Tests pasaron correctamente.\n";
        }
    }
    
    /**
     * Ejecutar linting
     */
    private function runLinting() {
        $output = [];
        $returnCode = 0;
        exec('composer lint 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "⚠️  Linting encontró problemas, pero continuando...\n";
            echo implode("\n", $output) . "\n";
        } else {
            echo "✓ Linting pasó correctamente.\n";
        }
    }
    
    /**
     * Actualizar changelog
     */
    private function updateChangelog($version, $type) {
        $changelogFile = $this->pluginDir . '/CHANGELOG.md';
        $date = date('Y-m-d');
        
        $entry = "\n## [$version] - $date\n\n";
        
        switch ($type) {
            case 'patch':
                $entry .= "### Fixed\n";
                $entry .= "- Bug fixes and minor improvements\n\n";
                break;
            case 'minor':
                $entry .= "### Added\n";
                $entry .= "- New features and enhancements\n\n";
                break;
            case 'major':
                $entry .= "### Changed\n";
                $entry .= "- Breaking changes and major updates\n\n";
                break;
            default:
                $entry .= "### Changed\n";
                $entry .= "- Version $version release\n\n";
        }
        
        if (file_exists($changelogFile)) {
            $content = file_get_contents($changelogFile);
            $content = str_replace('# Changelog', '# Changelog' . $entry, $content);
            file_put_contents($changelogFile, $content);
        } else {
            $content = "# Changelog\n\nAll notable changes to this project will be documented in this file.\n" . $entry;
            file_put_contents($changelogFile, $content);
        }
        
        echo "✓ Changelog actualizado.\n";
    }
    
    /**
     * Crear commit de release
     */
    private function createReleaseCommit($version) {
        $output = [];
        $returnCode = 0;
        
        // Agregar archivos modificados
        exec('git add .', $output, $returnCode);
        
        // Crear commit
        exec("git commit -m \"Release $version\"", $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "⚠️  No se pudo crear el commit (puede que no haya cambios).\n";
        } else {
            echo "✓ Commit de release creado.\n";
        }
    }
    
    /**
     * Crear tag de Git
     */
    private function createGitTag($version) {
        $output = [];
        $returnCode = 0;
        
        exec("git tag -a v$version -m \"Release $version\"", $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "⚠️  No se pudo crear el tag (puede que ya exista).\n";
        } else {
            echo "✓ Tag v$version creado.\n";
        }
    }
    
    /**
     * Crear archivo ZIP
     */
    private function createZipFile($version) {
        $zipFile = "xpsocial_login-v$version.zip";
        $excludeFiles = [
            '.git',
            '.gitignore',
            'node_modules',
            'vendor',
            '*.log',
            '.DS_Store',
            'Thumbs.db',
            'scripts',
            'tests',
            'composer.json',
            'composer.lock',
            'phpunit.xml',
            '.phpunit.result.cache'
        ];
        
        $excludePattern = '';
        foreach ($excludeFiles as $file) {
            $excludePattern .= " --exclude='$file'";
        }
        
        $output = [];
        $returnCode = 0;
        
        exec("cd .. && zip -r $zipFile xpsocial_login $excludePattern", $output, $returnCode);
        
        if ($returnCode !== 0) {
            echo "⚠️  No se pudo crear el archivo ZIP.\n";
        } else {
            echo "✓ Archivo ZIP creado: $zipFile\n";
        }
    }
}

// Incluir la clase VersionManager
require_once __DIR__ . '/version.php';
