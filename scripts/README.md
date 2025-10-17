# Scripts de Versión y Release

Este directorio contiene scripts para automatizar el manejo de versiones y la creación de releases del plugin XPSocial Login.

## Scripts Disponibles

### 1. Script de Versión (`version.php`)

Maneja las versiones del plugin en todos los archivos relevantes.

#### Comandos Básicos:
```bash
# Obtener versión actual
composer version:get
php scripts/version.php get

# Establecer versión específica
composer version:set 1.2.3
php scripts/version.php set 1.2.3

# Incrementar versión patch (1.0.0 -> 1.0.1)
composer version:patch
php scripts/version.php patch

# Incrementar versión minor (1.0.0 -> 1.1.0)
composer version:minor
php scripts/version.php minor

# Incrementar versión major (1.0.0 -> 2.0.0)
composer version:major
php scripts/version.php major

# Mostrar información detallada
composer version:show
php scripts/version.php show
```

#### Archivos que se Actualizan:
- `xpsocial_login.php` - Header del plugin
- `composer.json` - Versión del paquete
- `readme.txt` - Versión estable (si existe)
- Archivos PHP con constantes de versión

### 2. Script de Release (`release.php`)

Crea releases completos con todas las tareas necesarias.

#### Comandos de Release:
```bash
# Crear release patch (1.0.0 -> 1.0.1)
composer release:patch
php scripts/release.php patch

# Crear release minor (1.0.0 -> 1.1.0)
composer release:minor
php scripts/release.php minor

# Crear release major (1.0.0 -> 2.0.0)
composer release:major
php scripts/release.php major

# Crear release con versión específica
composer release 1.2.3
php scripts/release.php 1.2.3
```

#### Proceso de Release:
1. **Actualizar versión** en todos los archivos
2. **Ejecutar tests** (si están configurados)
3. **Ejecutar linting** para verificar código
4. **Actualizar changelog** con la nueva versión
5. **Crear commit** de release
6. **Crear tag** de Git
7. **Crear archivo ZIP** para distribución

## Ejemplos de Uso

### Escenario 1: Bug Fix (Patch Release)
```bash
# Hacer cambios en el código
# Luego crear release patch
composer release:patch
```

### Escenario 2: Nueva Funcionalidad (Minor Release)
```bash
# Agregar nuevas características
# Luego crear release minor
composer release:minor
```

### Escenario 3: Cambios Importantes (Major Release)
```bash
# Hacer cambios que rompen compatibilidad
# Luego crear release major
composer release:major
```

### Escenario 4: Versión Específica
```bash
# Establecer una versión específica
composer release 2.1.0
```

## Archivos Generados

Después de crear un release, se generan:

- **Archivo ZIP**: `xpsocial_login-vX.X.X.zip` (en el directorio padre)
- **Git Tag**: `vX.X.X`
- **Git Commit**: `Release X.X.X`
- **Changelog**: Actualizado en `CHANGELOG.md`

## Próximos Pasos Después del Release

1. **Revisar cambios**:
   ```bash
   git log --oneline -5
   ```

2. **Subir tag a Git**:
   ```bash
   git push origin vX.X.X
   ```

3. **Subir cambios**:
   ```bash
   git push origin develop
   ```

4. **Crear release en GitHub**:
   - Ir a GitHub → Releases → Create a new release
   - Seleccionar el tag `vX.X.X`
   - Subir el archivo ZIP generado
   - Agregar notas de release

## Configuración

### Personalizar Archivos a Actualizar

Para agregar más archivos a la actualización de versión, edita el método `updatePhpConstants()` en `version.php`:

```php
private function updatePhpConstants($version) {
    $files = [
        __DIR__ . '/../includes/class-xpsocial_login.php',
        __DIR__ . '/../public/class-xpsocial_login-public.php',
        __DIR__ . '/../admin/class-xpsocial_login-admin.php',
        // Agregar más archivos aquí
    ];
    // ...
}
```

### Personalizar Exclusiones del ZIP

Para excluir más archivos del ZIP de release, edita el array `$excludeFiles` en `release.php`:

```php
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
    // Agregar más exclusiones aquí
];
```

## Troubleshooting

### Error: "No se pudo actualizar la versión"
- Verifica que los archivos existan y tengan permisos de escritura
- Asegúrate de que el formato de versión sea correcto (X.Y.Z)

### Error: "No se pudo crear el commit"
- Verifica que estés en un repositorio Git
- Asegúrate de que haya cambios para commitear

### Error: "No se pudo crear el tag"
- Verifica que el tag no exista ya
- Asegúrate de tener permisos para crear tags

## Integración con CI/CD

Estos scripts pueden integrarse fácilmente con sistemas de CI/CD:

```yaml
# Ejemplo para GitHub Actions
- name: Create Release
  run: |
    composer release:patch
    git push origin v${{ github.ref_name }}
```

## Soporte

Para problemas o sugerencias, contacta a gian@gianko.com
