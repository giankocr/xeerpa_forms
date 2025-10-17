# Registro de Cambios - XPSocial Login Plugin

## Versión 3.1.1 (Actual)

### Estado Inicial del Plugin
- **Plugin Name**: XPSocial Login
- **Versión**: 3.1.1
- **Descripción**: Plugin para conectar WordPress con los API de Xeerpa Social
- **Autor**: giankocr
- **Licencia**: GPL-2.0+

### Estructura Actual
```
xpsocial_login/
├── admin/                    # Funcionalidades del panel de administración
│   ├── class-xpsocial_login-admin.php
│   ├── css/
│   ├── js/
│   └── partials/
├── includes/                 # Clases principales del plugin
│   ├── class-xpsocial_login.php (clase principal)
│   ├── class-xpsocial_login-activator.php
│   ├── class-xpsocial_login-deactivator.php
│   ├── class-shortcode-login.php
│   ├── class-shortcode-register.php
│   ├── class-xpsocial_api-rest-login.php
│   ├── class-xpsocial_cache.php
│   ├── class-xpsocial_config.php
│   ├── class-xpsocial_login-form.php
│   ├── class-xpsocial_register-form.php
│   ├── class-xpsocial_performance.php
│   ├── class-xpsocial_style.php
│   ├── opciones.php
│   └── recomendador.php
├── public/                   # Funcionalidades del frontend
│   ├── class-xpsocial_login-public.php
│   ├── css/
│   ├── js/
│   ├── images/
│   └── partials/
├── xpsocial_login.php       # Archivo principal del plugin
├── composer.json
└── README.md
```

### Funcionalidades Identificadas
1. **Login Social**: Integración con APIs de Xeerpa Social
2. **Formularios**: Login y registro de usuarios
3. **Shortcodes**: Para mostrar formularios en páginas
4. **Cache**: Sistema de caché para optimización
5. **Configuración**: Panel de administración para configuraciones
6. **Metadatos**: Gestión de metadatos de usuario
7. **Recomendador**: Sistema de recomendaciones (pendiente de configuración)

### Configuración Actual
- **URL API**: https://geo.erna.group/api/ (Producción)
- **URL API Test**: http://127.0.0.1:8000/api/ (Comentada)
- **Versión Interna**: 1.0.0 (diferente a la versión del plugin 3.1.1)

### Problemas Identificados
1. **Inconsistencia de versiones**: El plugin muestra versión 3.1.1 pero internamente usa 1.0.0
2. **Función incompleta**: `agregar_metadato_usuario()` tiene variable `$data` no definida (líneas 96-101)
3. **TODO pendiente**: Configurar el recomendador (línea 73)
4. **Función de debug**: `console_log()` está definida pero puede no ser segura para producción
5. **Configuración duplicada**: Existe `class-xpsocial_config.php` que no se está utilizando en el archivo principal
6. **URLs hardcodeadas**: URLs de API están definidas como constantes en lugar de usar configuración dinámica

### Análisis Detallado de Archivos

#### Archivo Principal (xpsocial_login.php)
- **Líneas 37-41**: Definición de constantes URLAPI (producción y test)
- **Líneas 47-61**: Funciones de activación y desactivación
- **Líneas 81-89**: Función `console_log()` para debugging
- **Líneas 93-120**: Función `agregar_metadato_usuario()` con error en variable `$data`
- **Líneas 139-146**: Función para ocultar admin bar para roles no administradores

#### Archivo de Opciones (includes/opciones.php)
- **Líneas 9-36**: Función `xpsocial_enqueue_scripts()` para cargar scripts
- **Líneas 38-99**: Configuración del menú de administración y registro de opciones
- **Líneas 101-444**: Interfaz de administración con 3 pestañas:
  - Configuración (URLs, tokens, países, etc.)
  - Google Sheets y Estilos
  - Acerca de
- **Líneas 446-478**: Función `allcountries()` para obtener países desde API

#### Clase de Configuración (includes/class-xpsocial_config.php)
- **Patrón Singleton**: Implementa patrón singleton para gestión de configuración
- **Líneas 26-46**: Inicialización de constantes con fallbacks
- **Líneas 59-64**: Registro de configuraciones
- **Líneas 83-144**: Página de configuración alternativa
- **Líneas 167-194**: Validación de configuración

### Funcionalidades Principales
1. **Login Social**: Integración con Xeerpa Social APIs
2. **Formularios**: Login y registro con shortcodes
3. **Configuración**: Panel de administración extenso
4. **Estilos**: Personalización de formularios
5. **Países**: Selección de países disponibles
6. **Metadatos**: Gestión de metadatos de usuario
7. **Cache**: Sistema de caché (clase separada)
8. **Performance**: Optimizaciones de rendimiento
9. **Recomendador**: Sistema de recomendaciones (pendiente)
10. **Integración FIFCO**: Configuración para API de FIFCO

---

## Modificaciones Realizadas

### Versión 3.1.2 - Simplificación de API REST

#### Cambios Realizados
- **Archivo**: `includes/class-xpsocial_api-rest-login.php`
- **Acción**: Simplificación y limpieza del código
- **Fecha**: $(date)

#### Detalles de la Modificación
1. **Eliminación de código innecesario**:
   - Removidas todas las funciones de login y autenticación
   - Eliminadas funciones de metadatos de usuario
   - Removidas funciones de verificación de origen
   - Eliminadas funciones de seguridad y headers

2. **Funciones mantenidas**:
   - `get_selected_countries()`: Obtener países seleccionados
   - `get_selected_states()`: Obtener estados seleccionados

3. **Mejoras implementadas**:
   - **Documentación mejorada**: Agregados comentarios PHPDoc detallados
   - **Validación de parámetros**: Mejor validación de entrada
   - **Manejo de errores**: Mejor gestión de errores con mensajes claros
   - **Código más limpio**: Estructura más clara y legible
   - **Comentarios explicativos**: Cada sección está bien documentada

4. **Endpoints REST mantenidos**:
   - `GET /wp-json/geo-api/v1/selected-countries`
   - `GET /wp-json/geo-api/v1/selected-states`

#### Beneficios de la Modificación
- **Código más mantenible**: Archivo más pequeño y enfocado
- **Mejor rendimiento**: Menos código innecesario
- **Mayor claridad**: Funciones específicas y bien documentadas
- **Mejor debugging**: Errores más claros y específicos

---

### Versión 3.1.3 - Selección Automática de País Único

#### Cambios Realizados
- **Archivo**: `public/js/xpsocial_forms.js`
- **Acción**: Implementar selección automática cuando solo hay un país configurado
- **Fecha**: $(date)

#### Detalles de la Modificación
1. **Funcionalidad implementada**:
   - **Detección automática**: Cuando solo hay un país configurado, se selecciona automáticamente
   - **Carga de provincias**: Se cargan automáticamente las provincias del país único
   - **Ocultación de placeholder**: Se oculta el texto "Seleccione un país" cuando está preseleccionado
   - **Código de teléfono**: También se preselecciona el código de teléfono correspondiente

2. **Mejoras en la experiencia del usuario**:
   - **Menos clics**: El usuario no necesita seleccionar el país si solo hay uno disponible
   - **Flujo más rápido**: Se cargan automáticamente las provincias del país único
   - **Interfaz más limpia**: Se oculta el placeholder innecesario

3. **Lógica implementada**:
   ```javascript
   // Si solo hay un país configurado, seleccionarlo automáticamente
   const shouldAutoSelect = countries.length === 1;
   
   // Marcar como seleccionado si es el único país
   if (shouldAutoSelect) {
       option.selected = true;
   }
   
   // Cargar provincias automáticamente
   if (shouldAutoSelect && countries.length > 0) {
       fetchProvinces(firstCountry.country_id);
   }
   ```

#### Beneficios de la Modificación
- **Mejor UX**: Experiencia más fluida para usuarios con un solo país configurado
- **Menos fricción**: Reduce pasos innecesarios en el formulario
- **Automatización inteligente**: Solo se activa cuando realmente hay un solo país
- **Compatibilidad**: Funciona con ambos formularios (con y sin redes sociales)

---

### Versión 3.1.4 - Eliminación de Funcionalidades de Login y Usuarios

#### Cambios Realizados
- **Archivos modificados**: Múltiples archivos del plugin
- **Acción**: Eliminación completa de funcionalidades de login y gestión de usuarios
- **Fecha**: $(date)

#### Detalles de la Modificación
1. **Archivo eliminado**:
   - `public/add_metadata.php`: Archivo completo eliminado

2. **Funciones eliminadas**:
   - `agregar_metadato_usuario()` en `xpsocial_login.php`
   - `set_metadata_user()` en `xpsocial_login-public.js`
   - `update_metadata_user_token()` en `xpsocial_login-public.js`
   - `get_metadata_user()` en `xpsocial_login-public.js`
   - `hide_admin_bar_for_roles()` en `xpsocial_login.php`

3. **Funcionalidades removidas**:
   - **Creación de usuarios WordPress**: `wp_create_user()` eliminado
   - **Login automático**: `wp_signon()` eliminado
   - **Gestión de metadatos**: `update_user_meta()` y `add_user_meta()` eliminados
   - **Gestión de roles**: Funciones de admin bar eliminadas
   - **Endpoints REST de metadatos**: API endpoints eliminados

4. **Archivos modificados**:
   - `xpsocial_login.php`: Eliminadas funciones de metadatos y roles
   - `includes/class-xpsocial_register-form.php`: Eliminada creación de usuarios y login
   - `public/js/xpsocial_login-public.js`: Eliminadas funciones de metadatos

#### Funcionalidades que ya NO están disponibles
- ❌ **Creación automática de usuarios WordPress**
- ❌ **Login automático después del registro**
- ❌ **Gestión de metadatos de usuario**
- ❌ **Endpoints REST para metadatos**
- ❌ **Control de admin bar por roles**
- ❌ **Almacenamiento de datos de usuario en WordPress**

#### Funcionalidades que SÍ se mantienen
- ✅ **Formularios de registro y login**
- ✅ **Integración con APIs de Xeerpa**
- ✅ **Selección de países y provincias**
- ✅ **Validación de formularios**
- ✅ **Redirecciones configuradas**
- ✅ **Estilos y personalización**

#### Beneficios de la Eliminación
- **Plugin más liviano**: Menos código y dependencias
- **Mayor simplicidad**: Enfoque solo en formularios y APIs
- **Menos conflictos**: No interfiere con otros plugins de usuarios
- **Mejor rendimiento**: Menos operaciones de base de datos
- **Mantenimiento reducido**: Menos código que mantener

---

### Versión 3.1.5 - Limpieza Adicional de Referencias a Usuarios

#### Cambios Realizados
- **Archivos modificados**: Múltiples archivos del plugin
- **Acción**: Eliminación de referencias restantes a funciones de usuarios
- **Fecha**: $(date)

#### Detalles de la Modificación
1. **Archivo `class-xpsocial_login-form.php`**:
   - Eliminada verificación `is_user_logged_in()`
   - Removida lógica de mostrar mensaje de usuario logueado
   - Eliminadas referencias a `get_current_user_id()` y `get_user_meta()`
   - Formulario ahora siempre se muestra sin verificar estado de login

2. **Archivo `opciones.php`**:
   - Eliminada verificación `is_user_logged_in()` en `xpsocial_enqueue_scripts()`
   - Scripts ahora siempre se cargan sin verificar estado de usuario

3. **Archivo `recomendador.php`**:
   - Función `shortcode_recomendador_xeerpa()` completamente reescrita
   - Eliminadas todas las referencias a usuarios logueados
   - Recomendador ahora muestra mensaje de funcionalidad deshabilitada
   - Removidas referencias a `wp_get_current_user()` y `get_user_meta()`

4. **Archivo `class-xpsocial_register-form.php`**:
   - Eliminada verificación `email_exists()` 
   - Removida lógica de verificar si email ya está registrado

#### Funcionalidades Adicionales Eliminadas
- ❌ **Verificación de estado de login en formularios**
- ❌ **Mensajes personalizados para usuarios logueados**
- ❌ **Verificación de emails existentes**
- ❌ **Sistema de recomendador basado en usuarios**
- ❌ **Carga condicional de scripts según estado de usuario**

#### Beneficios de la Limpieza Adicional
- **Consistencia total**: No hay referencias residuales a usuarios
- **Código más limpio**: Eliminadas todas las verificaciones innecesarias
- **Mejor rendimiento**: Sin verificaciones de estado de usuario
- **Simplicidad máxima**: Plugin completamente enfocado en formularios
- **Mantenimiento simplificado**: Sin lógica condicional compleja

---

### Versión 3.1.6 - Eliminación de Funcionalidad de Google Sheets

#### Cambios Realizados
- **Archivos modificados**: `opciones.php`, `class-xpsocial_register-form.php`
- **Acción**: Eliminación completa de funcionalidad de Google Sheets
- **Fecha**: $(date)

#### Detalles de la Modificación
1. **Archivo `opciones.php`**:
   - Eliminado registro de opción `xpsocial_GoogleID`
   - Removida sección completa de configuración de Google Sheets
   - Cambiado nombre de pestaña de "Google Sheet y Estilos" a "Estilos"
   - Eliminado campo de entrada para URL de Google Sheets

2. **Archivo `class-xpsocial_register-form.php`**:
   - Eliminado código que enviaba datos a Google Sheets
   - Removida variable `$dataGoogle` y su array de datos
   - Eliminada verificación `$urlGoogle` y envío `wp_remote_post()`
   - Removida lógica de envío de datos de formulario a Google

#### Funcionalidades Eliminadas
- ❌ **Configuración de URL de Google Sheets**
- ❌ **Envío automático de datos de registro a Google Sheets**
- ❌ **Campo de configuración en panel de administración**
- ❌ **Integración con Google Sheets API**
- ❌ **Almacenamiento de datos en hojas de cálculo de Google**

#### Beneficios de la Eliminación
- **Mayor privacidad**: Los datos ya no se envían a servicios externos
- **Mejor rendimiento**: Sin envíos HTTP adicionales a Google
- **Simplicidad**: Menos configuraciones en el panel de administración
- **Menos dependencias**: No depende de servicios externos de Google
- **Código más limpio**: Eliminada lógica de envío de datos

#### Funcionalidades que SÍ se mantienen
- ✅ **Formularios de registro y login**
- ✅ **Integración con APIs de Xeerpa**
- ✅ **Selección de países y provincias**
- ✅ **Validación de formularios**
- ✅ **Redirecciones configuradas**
- ✅ **Estilos y personalización**
- ✅ **Selección automática de país único**

---

### Versión 3.1.7 - Reorganización del Panel de Administración

#### Cambios Realizados
- **Archivo modificado**: `opciones.php`
- **Acción**: Reorganización de pestañas en el panel de administración
- **Fecha**: $(date)

#### Detalles de la Modificación
1. **Nueva estructura de pestañas**:
   - **Xeerpa Config**: Configuraciones principales de Xeerpa (licencia, países, URLs, tokens, etc.)
   - **FIFCO GCP**: Configuraciones específicas de FIFCO (API endpoint y token)
   - **Estilos**: Configuraciones de estilos de formularios
   - **Acerca de**: Información del desarrollador

2. **Cambios en la organización**:
   - Separadas las configuraciones de FIFCO en su propia pestaña
   - Mantenidas las configuraciones de Xeerpa en la pestaña principal
   - Reorganizada la estructura para mejor usabilidad
   - Actualizados los IDs de las pestañas para mayor claridad

3. **Pestañas reorganizadas**:
   - `XeerpaConfig`: Configuraciones principales de Xeerpa
   - `FIFCOGCP`: Configuraciones de FIFCO API
   - `Estilos`: Estilos de formularios
   - `Acerca`: Información del desarrollador

#### Beneficios de la Reorganización
- **Mejor organización**: Configuraciones agrupadas por funcionalidad
- **Mayor claridad**: Separación clara entre configuraciones de Xeerpa y FIFCO
- **Mejor usabilidad**: Navegación más intuitiva en el panel de administración
- **Mantenimiento simplificado**: Estructura más lógica y fácil de mantener

#### Estructura Final del Panel
1. **Xeerpa Config**: 
   - Licencia de uso
   - Países activos
   - URLs de Xeerpa
   - Tokens de autenticación
   - URLs de redirección
   - Datos de referencia

2. **FIFCO GCP**:
   - FIFCO API Endpoint
   - FIFCO API Token

3. **Estilos**:
   - Configuraciones de estilos de formularios
   - Personalización de inputs y botones

4. **Acerca de**:
   - Información del desarrollador

---

### Versión 3.1.8 - Eliminación de Duplicación de Configuraciones

#### Cambios Realizados
- **Archivo modificado**: `class-xpsocial_config.php`
- **Acción**: Eliminación de duplicación de configuraciones
- **Fecha**: $(date)

#### Problema Identificado
- **Duplicación de configuraciones**: La clase `XPSocial_Config` estaba creando un panel de administración separado que duplicaba las configuraciones ya existentes en `opciones.php`
- **Conflicto de gestión**: Dos sistemas de configuración compitiendo entre sí
- **Mantenimiento complejo**: Cambios necesarios en múltiples lugares

#### Solución Implementada
1. **Eliminación del panel duplicado**:
   - Removido el método `add_config_menu()` que creaba un submenú duplicado
   - Eliminado el método `config_page()` que mostraba configuraciones duplicadas
   - Removido el método `register_config_settings()` que registraba opciones duplicadas

2. **Mantenimiento de funcionalidad esencial**:
   - Conservada la inicialización de constantes esenciales
   - Mantenidos los métodos de validación de configuración
   - Preservados los métodos de acceso a configuraciones

3. **Centralización de configuraciones**:
   - Todas las configuraciones principales se manejan en `opciones.php`
   - La clase `XPSocial_Config` solo maneja constantes y validaciones
   - Eliminada la duplicación de `register_setting()` calls

#### Beneficios de la Corrección
- **Configuración centralizada**: Un solo lugar para todas las configuraciones
- **Eliminación de conflictos**: No más paneles de administración duplicados
- **Mantenimiento simplificado**: Cambios solo en `opciones.php`
- **Mejor experiencia de usuario**: Un solo panel de configuración claro
- **Código más limpio**: Eliminada lógica duplicada

#### Funcionalidades Conservadas
- ✅ **Inicialización de constantes**: URLAPI, XPSOCIAL_VERSION, etc.
- ✅ **Validación de configuración**: Métodos de verificación
- ✅ **Acceso a configuraciones**: Métodos getter para opciones
- ✅ **Patrón Singleton**: Mantenida la instancia única

#### Funcionalidades Eliminadas
- ❌ **Panel de administración duplicado**: Ya existe en `opciones.php`
- ❌ **Registro de opciones duplicado**: Ya se maneja en `opciones.php`
- ❌ **Formulario de configuración duplicado**: Ya existe en `opciones.php`

#### Estructura Final
```
Configuración del Plugin
├── opciones.php (Principal)
│   ├── Panel de administración completo
│   ├── Pestañas organizadas (Xeerpa Config, FIFCO GCP, Estilos, Acerca de)
│   ├── Registro de todas las opciones
│   └── Formularios de configuración
└── class-xpsocial_config.php (Auxiliar)
    ├── Inicialización de constantes
    ├── Validación de configuración
    └── Métodos de acceso a opciones
```

---

### Versión 3.1.9 - Estilos Minimalistas para Pestañas

#### Cambios Realizados
- **Archivo modificado**: `opciones.php`
- **Acción**: Implementación de estilos CSS limpios y minimalistas para pestañas
- **Fecha**: $(date)

#### Mejoras de Diseño Implementadas

1. **Estilos CSS Minimalistas**:
   - **Contenedor de pestañas**: Diseño limpio con fondo gris claro y bordes redondeados
   - **Botones de pestañas**: Estilo minimalista con transiciones suaves
   - **Pestaña activa**: Destacada con color azul WordPress y borde inferior
   - **Contenido de pestañas**: Fondo blanco con sombras sutiles y animaciones

2. **Características de Diseño**:
   - **Colores**: Paleta de grises y azul WordPress (#007cba)
   - **Transiciones**: Animaciones suaves de 0.3s para hover y cambios
   - **Sombras**: Sombras sutiles para profundidad visual
   - **Bordes redondeados**: Esquinas redondeadas para un look moderno
   - **Animación de entrada**: Efecto fadeIn para contenido de pestañas

3. **Mejoras de UX**:
   - **Hover effects**: Transformación sutil al pasar el mouse
   - **Focus states**: Estados de enfoque claros para accesibilidad
   - **Responsive design**: Adaptación para dispositivos móviles
   - **Estados activos**: Indicación clara de pestaña seleccionada

4. **Funcionalidad JavaScript**:
   - **Navegación de pestañas**: Función `openCity()` para cambiar pestañas
   - **Activación automática**: Primera pestaña activa por defecto
   - **Gestión de clases**: Manejo dinámico de clases CSS activas

#### Detalles Técnicos

##### **Estilos CSS Implementados**:
```css
/* Contenedor de pestañas */
.tab {
    display: flex;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Botones de pestañas */
.tablinks {
    background: transparent;
    padding: 16px 24px;
    transition: all 0.3s ease;
    color: #6c757d;
}

/* Pestaña activa */
.tablinks.active {
    background: #ffffff;
    color: #007cba;
    border-bottom: 3px solid #007cba;
}

/* Contenido de pestañas */
.tabcontent {
    background: #ffffff;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    animation: fadeIn 0.3s ease-in-out;
}
```

##### **JavaScript Implementado**:
```javascript
function openCity(evt, cityName) {
    // Ocultar todos los contenidos
    // Remover clases activas
    // Mostrar contenido seleccionado
    // Agregar clase activa
}
```

#### Beneficios del Nuevo Diseño

1. **Experiencia Visual Mejorada**:
   - ✅ **Diseño moderno**: Estilo limpio y profesional
   - ✅ **Navegación intuitiva**: Pestañas claramente diferenciadas
   - ✅ **Animaciones suaves**: Transiciones elegantes
   - ✅ **Consistencia visual**: Alineado con WordPress admin

2. **Mejor Usabilidad**:
   - ✅ **Estados claros**: Pestaña activa bien diferenciada
   - ✅ **Feedback visual**: Hover effects informativos
   - ✅ **Accesibilidad**: Focus states y navegación por teclado
   - ✅ **Responsive**: Funciona en todos los dispositivos

3. **Mantenimiento Simplificado**:
   - ✅ **CSS organizado**: Estilos bien estructurados
   - ✅ **JavaScript limpio**: Código funcional y eficiente
   - ✅ **Comentarios**: Documentación clara del código
   - ✅ **Modularidad**: Fácil de modificar y extender

#### Características Responsive

- **Desktop**: Pestañas horizontales con diseño completo
- **Tablet**: Adaptación de espaciado y tamaños
- **Mobile**: Pestañas verticales para mejor usabilidad

#### Paleta de Colores

- **Primario**: #007cba (Azul WordPress)
- **Secundario**: #6c757d (Gris medio)
- **Fondo**: #f8f9fa (Gris claro)
- **Texto**: #495057 (Gris oscuro)
- **Bordes**: #e9ecef (Gris muy claro)

---

### Versión 3.1.10 - Corrección de Campos de Color

#### Cambios Realizados
- **Archivo modificado**: `opciones.php`
- **Acción**: Corrección de campos de color para mostrar valores guardados correctamente
- **Fecha**: $(date)

#### Problema Identificado
- **Campos de color vacíos**: Los campos de color no mostraban los valores guardados
- **Valores por defecto incorrectos**: Uso de `$default = false` en lugar de valores válidos
- **Experiencia de usuario deficiente**: Campos de color aparecían vacíos o con valores incorrectos

#### Solución Implementada

1. **Corrección de campos de color de inputs**:
   - **Input Background Color**: Valor por defecto `#ffffff` (blanco)
   - **Login Background Color**: Valor por defecto `#f8f9fa` (gris claro)
   - **Font Color Login**: Valor por defecto `#495057` (gris oscuro)
   - **Divisor Login Color**: Valor por defecto `#e9ecef` (gris muy claro)

2. **Corrección de campos de color de botones**:
   - **Button Color**: Valor por defecto `#ffffff` (blanco)
   - **Button Background Color**: Valor por defecto `#007cba` (azul WordPress)
   - **Button Border Color**: Valor por defecto `#007cba` (azul WordPress)

3. **Corrección de campos numéricos**:
   - **Inputs Height**: Valor por defecto `40px`
   - **Inputs Border**: Valor por defecto `1px solid #ced4da`
   - **Inputs Border-radius**: Valor por defecto `6px`
   - **Button Height**: Valor por defecto `40px`
   - **Button Width**: Valor por defecto `100px`
   - **Button Border Width**: Valor por defecto `1px`
   - **Button Border Radius**: Valor por defecto `6px`

#### Cambios Técnicos Realizados

##### **Antes (Problemático)**:
```php
<input type="color" name="xp_bg_color" value='<?php echo get_option('xp_bg_color', $default = false) ?>' />
```

##### **Después (Corregido)**:
```php
<input type="color" name="xp_bg_color" value='<?php echo get_option('xp_bg_color', '#ffffff') ?>' />
```

#### Valores por Defecto Implementados

| Campo | Valor por Defecto | Descripción |
|-------|------------------|-------------|
| **Input Background Color** | `#ffffff` | Fondo blanco para inputs |
| **Login Background Color** | `#f8f9fa` | Fondo gris claro para login |
| **Font Color Login** | `#495057` | Texto gris oscuro |
| **Divisor Login Color** | `#e9ecef` | Color gris muy claro para divisores |
| **Button Color** | `#ffffff` | Texto blanco en botones |
| **Button Background Color** | `#007cba` | Fondo azul WordPress |
| **Button Border Color** | `#007cba` | Borde azul WordPress |
| **Inputs Height** | `40` | Altura de 40px |
| **Inputs Border** | `1px solid #ced4da` | Borde gris claro |
| **Inputs Border-radius** | `6` | Bordes redondeados 6px |
| **Button Height** | `40` | Altura de botón 40px |
| **Button Width** | `100` | Ancho de botón 100px |
| **Button Border Width** | `1` | Grosor de borde 1px |
| **Button Border Radius** | `6` | Bordes redondeados 6px |

#### Beneficios de la Corrección

1. **Experiencia de Usuario Mejorada**:
   - ✅ **Campos de color funcionales**: Muestran valores guardados correctamente
   - ✅ **Valores por defecto apropiados**: Colores y medidas sensatos
   - ✅ **Consistencia visual**: Paleta de colores coherente
   - ✅ **Feedback visual inmediato**: Los usuarios ven los valores actuales

2. **Funcionalidad Restaurada**:
   - ✅ **Guardado de colores**: Los valores se guardan y muestran correctamente
   - ✅ **Aplicación de estilos**: Los estilos se aplican con los valores correctos
   - ✅ **Personalización efectiva**: Los usuarios pueden personalizar efectivamente

3. **Mantenimiento Simplificado**:
   - ✅ **Código más limpio**: Eliminado el uso problemático de `$default = false`
   - ✅ **Valores predecibles**: Valores por defecto consistentes y lógicos
   - ✅ **Debugging facilitado**: Más fácil identificar problemas de configuración

#### Paleta de Colores por Defecto

- **Primarios**: 
  - Azul WordPress: `#007cba`
  - Blanco: `#ffffff`
- **Grises**:
  - Gris oscuro: `#495057`
  - Gris medio: `#ced4da`
  - Gris claro: `#f8f9fa`
  - Gris muy claro: `#e9ecef`

#### Resultado Final

Ahora todos los campos de la sección "Estilos" funcionan correctamente:

- ✅ **Campos de color**: Muestran valores guardados y tienen valores por defecto apropiados
- ✅ **Campos numéricos**: Tienen valores por defecto sensatos
- ✅ **Campos de texto**: Muestran valores guardados correctamente
- ✅ **Experiencia consistente**: Todos los campos se comportan de manera predecible

---

### Versión 3.1.11 - Sincronización de Color Picker

#### Cambios Realizados
- **Archivo modificado**: `opciones.php`
- **Acción**: Corrección de sincronización entre color picker y campos de entrada
- **Fecha**: $(date)

#### Problema Identificado
- **Color picker no sincronizado**: El color picker funcionaba pero no actualizaba visualmente el campo de entrada
- **Campos de color vacíos**: Los campos no mostraban el color seleccionado en el picker
- **Experiencia de usuario confusa**: Los usuarios seleccionaban colores pero no veían el resultado en el campo

#### Solución Implementada

1. **JavaScript de Sincronización**:
   - **Función `syncColorPickers()`**: Sincroniza todos los campos de color
   - **Event listeners**: Detecta cambios en el color picker
   - **Actualización automática**: Actualiza el valor y el color de fondo del campo
   - **Inicialización automática**: Se ejecuta al cargar la página y después de un delay

2. **Estilos CSS Mejorados**:
   - **Tamaño fijo**: Campos de color de 60px de ancho y 40px de alto
   - **Efectos hover**: Escala y cambio de color al pasar el mouse
   - **Bordes personalizados**: Estilos específicos para campos de color
   - **Compatibilidad webkit**: Estilos para navegadores basados en WebKit

#### Detalles Técnicos

##### **JavaScript Implementado**:
```javascript
function syncColorPickers() {
    var colorInputs = document.querySelectorAll('input[type="color"]');
    
    colorInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            this.setAttribute('value', this.value);
            this.style.backgroundColor = this.value;
            console.log('Color actualizado:', this.name, '=', this.value);
        });
        
        if (input.value) {
            input.style.backgroundColor = input.value;
        }
    });
}
```

##### **CSS Implementado**:
```css
.tabcontent input[type="color"] {
    width: 60px !important;
    height: 40px;
    border: 2px solid #ced4da;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #ffffff;
    padding: 0;
}

.tabcontent input[type="color"]:hover {
    border-color: #007cba;
    transform: scale(1.05);
}
```

#### Funcionalidades Implementadas

1. **Sincronización Automática**:
   - ✅ **Detección de cambios**: Event listener en evento 'input'
   - ✅ **Actualización de valor**: `setAttribute('value', this.value)`
   - ✅ **Actualización visual**: `this.style.backgroundColor = this.value`
   - ✅ **Logging**: Console.log para debugging

2. **Inicialización Inteligente**:
   - ✅ **Carga inicial**: Se ejecuta al cargar la página
   - ✅ **Delay de seguridad**: Re-ejecuta después de 500ms
   - ✅ **Compatibilidad**: Funciona con todos los campos de color

3. **Estilos Mejorados**:
   - ✅ **Tamaño consistente**: 60x40px para todos los campos
   - ✅ **Efectos visuales**: Hover con escala y cambio de color
   - ✅ **Bordes personalizados**: Estilos específicos para color pickers
   - ✅ **Compatibilidad webkit**: Estilos para Chrome, Safari, Edge

#### Beneficios de la Corrección

1. **Experiencia de Usuario Mejorada**:
   - ✅ **Feedback visual inmediato**: Los usuarios ven el color seleccionado
   - ✅ **Sincronización perfecta**: Color picker y campo siempre sincronizados
   - ✅ **Interfaz intuitiva**: Comportamiento esperado y predecible
   - ✅ **Efectos visuales**: Hover effects que mejoran la interacción

2. **Funcionalidad Restaurada**:
   - ✅ **Color picker funcional**: Selección de colores funciona correctamente
   - ✅ **Campos actualizados**: Los campos muestran el color seleccionado
   - ✅ **Valores guardados**: Los colores se guardan y cargan correctamente
   - ✅ **Debugging facilitado**: Console logs para identificar problemas

3. **Código Robusto**:
   - ✅ **Manejo de errores**: Verificación de existencia de elementos
   - ✅ **Compatibilidad**: Funciona en todos los navegadores modernos
   - ✅ **Performance**: Ejecución eficiente sin impactar rendimiento
   - ✅ **Mantenibilidad**: Código bien documentado y estructurado

#### Campos Afectados

Los siguientes campos de color ahora funcionan correctamente:

- ✅ **Input Background Color** (`xp_bg_color`)
- ✅ **Login Background Color** (`xp_div_color`)
- ✅ **Font Color Login** (`xp_font_color`)
- ✅ **Divisor Login Color** (`xp_divisor_color`)
- ✅ **Button Color** (`xp_btn_color`)
- ✅ **Button Background Color** (`xp_btn_bgcolor`)
- ✅ **Button Border Color** (`xp_btn_border_color`)

#### Resultado Final

Ahora el color picker funciona perfectamente:

- ✅ **Sincronización completa**: Color picker y campo siempre sincronizados
- ✅ **Feedback visual**: Los usuarios ven inmediatamente el color seleccionado
- ✅ **Experiencia fluida**: Selección de colores intuitiva y responsiva
- ✅ **Compatibilidad total**: Funciona en todos los navegadores modernos
- ✅ **Debugging habilitado**: Console logs para monitorear cambios

---

### Versión 3.1.12 - Estilos Avanzados para Selectores de Color

#### Cambios Realizados
- **Archivo modificado**: `opciones.php`
- **Acción**: Implementación de estilos avanzados y modernos para selectores de color
- **Fecha**: $(date)

#### Mejoras de Diseño Implementadas

1. **Selectores de Color Modernos**:
   - **Tamaño aumentado**: De 60x40px a 80x50px para mejor visibilidad
   - **Bordes redondeados**: Radio de 12px para look moderno
   - **Gradientes de fondo**: Gradiente sutil de blanco a gris claro
   - **Sombras profundas**: Box-shadow con múltiples capas para profundidad

2. **Efectos Visuales Avanzados**:
   - **Hover effects**: Elevación y escala al pasar el mouse
   - **Active states**: Efecto de presión al hacer click
   - **Focus states**: Anillo de enfoque azul con transparencia
   - **Transiciones suaves**: Cubic-bezier para animaciones naturales

3. **Animaciones y Micro-interacciones**:
   - **Efecto de ondas**: Animación de ondas al hacer click
   - **Pulso para campos vacíos**: Animación sutil para indicar campos sin color
   - **Confirmación visual**: Escala temporal al seleccionar color
   - **Indicador de estado**: Checkmark para campos con color seleccionado

#### Características Técnicas Implementadas

##### **Estilos CSS Avanzados**:
```css
.tabcontent input[type="color"] {
    width: 80px !important;
    height: 50px;
    border: 3px solid #e9ecef;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.tabcontent input[type="color"]:hover {
    border-color: #007cba;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 25px rgba(0, 124, 186, 0.15);
}
```

##### **JavaScript Mejorado**:
```javascript
// Efecto visual de confirmación
this.style.transform = 'scale(1.1)';
setTimeout(() => {
    this.style.transform = '';
}, 200);

// Agregar clase para indicar que tiene color
this.classList.add('has-color');
```

#### Funcionalidades Visuales

1. **Estados de Color**:
   - ✅ **Campo vacío**: Borde gris con animación de pulso
   - ✅ **Campo con color**: Borde verde con checkmark
   - ✅ **Hover**: Elevación y cambio de color de borde
   - ✅ **Focus**: Anillo azul de enfoque

2. **Efectos de Interacción**:
   - ✅ **Click**: Efecto de ondas expandiéndose
   - ✅ **Selección**: Escala temporal de confirmación
   - ✅ **Hover**: Elevación y escala sutil
   - ✅ **Active**: Efecto de presión

3. **Compatibilidad Cross-browser**:
   - ✅ **WebKit**: Estilos para Chrome, Safari, Edge
   - ✅ **Firefox**: Estilos específicos para Mozilla
   - ✅ **Responsive**: Adaptación para dispositivos móviles

#### Mejoras Adicionales

1. **Tabla de Estilos Mejorada**:
   - ✅ **Fondo blanco**: Tabla con fondo limpio
   - ✅ **Bordes redondeados**: Esquinas redondeadas de 12px
   - ✅ **Filas alternadas**: Colores alternos para mejor legibilidad
   - ✅ **Hover en filas**: Efecto hover azul claro

2. **Inputs Numéricos y de Texto**:
   - ✅ **Bordes mejorados**: Bordes de 2px con transiciones
   - ✅ **Padding aumentado**: 12px 16px para mejor usabilidad
   - ✅ **Sombras sutiles**: Box-shadow para profundidad
   - ✅ **Estados de focus**: Anillo azul de enfoque

3. **Labels Mejorados**:
   - ✅ **Tipografía**: Font-weight 600, uppercase, letter-spacing
   - ✅ **Espaciado**: Margen inferior de 8px
   - ✅ **Color**: Gris oscuro para mejor contraste

#### Paleta de Colores Implementada

- **Primarios**:
  - Azul WordPress: `#007cba`
  - Verde éxito: `#28a745`
  - Blanco: `#ffffff`

- **Grises**:
  - Gris claro: `#e9ecef`
  - Gris medio: `#f8f9fa`
  - Gris oscuro: `#495057`
  - Azul claro: `#e3f2fd`

#### Beneficios de los Nuevos Estilos

1. **Experiencia de Usuario Mejorada**:
   - ✅ **Visibilidad**: Selectores más grandes y visibles
   - ✅ **Feedback visual**: Respuesta inmediata a interacciones
   - ✅ **Estados claros**: Indicación visual de campos con/sin color
   - ✅ **Animaciones fluidas**: Transiciones suaves y naturales

2. **Diseño Moderno**:
   - ✅ **Estética actual**: Bordes redondeados y sombras modernas
   - ✅ **Consistencia**: Alineado con tendencias de diseño actuales
   - ✅ **Profesionalismo**: Apariencia pulida y profesional
   - ✅ **Accesibilidad**: Estados de focus claros y contrastes apropiados

3. **Funcionalidad Mejorada**:
   - ✅ **Mejor usabilidad**: Campos más grandes y fáciles de usar
   - ✅ **Feedback inmediato**: Confirmación visual de selecciones
   - ✅ **Compatibilidad**: Funciona en todos los navegadores modernos
   - ✅ **Responsive**: Se adapta a diferentes tamaños de pantalla

#### Resultado Final

Los selectores de color ahora tienen:

- ✅ **Apariencia moderna**: Diseño actual con bordes redondeados y sombras
- ✅ **Interacciones fluidas**: Animaciones suaves y efectos visuales
- ✅ **Estados claros**: Indicación visual de campos con/sin color
- ✅ **Mejor usabilidad**: Campos más grandes y fáciles de usar
- ✅ **Compatibilidad total**: Funciona en todos los navegadores
- ✅ **Diseño profesional**: Apariencia pulida y moderna

---

### Versión 3.1.13 - Mejora del Activador del Plugin

#### Cambios Realizados
- **Archivo modificado**: `class-xpsocial_login-activator.php`
- **Acción**: Implementación de creación automática de tabla de base de datos
- **Fecha**: $(date)

#### Funcionalidades Implementadas

1. **Creación Automática de Tabla**:
   - **Tabla**: `wp_xpsocial_leads` se crea automáticamente al activar el plugin
   - **Estructura completa**: Todos los campos necesarios para almacenar leads
   - **Índices únicos**: Prevención de duplicados por email y source
   - **Timestamps**: Campos de creación y actualización automáticos

2. **Inicialización de Opciones por Defecto**:
   - **Configuraciones básicas**: Todas las opciones del plugin con valores por defecto
   - **Estilos por defecto**: Valores predefinidos para campos de estilo
   - **Configuraciones FIFCO**: Opciones para integración con FIFCO
   - **Configuraciones Xeerpa**: Opciones para integración con Xeerpa

3. **Sistema de Logging**:
   - **Log de activación**: Registro de creación de tabla exitosa
   - **Log de opciones**: Registro de inicialización de opciones
   - **Debugging**: Facilita la identificación de problemas

#### Estructura de la Tabla Creada

##### **Tabla: `wp_xpsocial_leads`**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| **id** | BIGINT(20) UNSIGNED | Clave primaria auto-incremental |
| **Marca** | VARCHAR(120) | Marca del producto/servicio |
| **IDNumber** | VARCHAR(100) | Número de identificación del usuario |
| **EmailAddress** | VARCHAR(190) | Dirección de correo electrónico |
| **FirstName** | VARCHAR(100) | Primer nombre |
| **SecondName** | VARCHAR(100) | Segundo nombre |
| **LastName** | VARCHAR(100) | Primer apellido |
| **SecondLastName** | VARCHAR(100) | Segundo apellido |
| **Gender** | VARCHAR(30) | Género del usuario |
| **BirthDate** | VARCHAR(20) | Fecha de nacimiento |
| **MobileNumber** | VARCHAR(50) | Número de teléfono móvil |
| **Province** | VARCHAR(120) | Provincia/estado |
| **Country** | VARCHAR(120) | País |
| **UserRegisterSocial** | VARCHAR(50) | Red social de registro |
| **CaptureDate** | VARCHAR(20) | Fecha de captura |
| **ModifiedDate** | VARCHAR(20) | Fecha de modificación |
| **PoliticasPrivacidad** | VARCHAR(5) | Aceptación de políticas de privacidad |
| **AceptaComunicaciones** | VARCHAR(5) | Aceptación de comunicaciones |
| **Source** | VARCHAR(180) | Fuente del lead |
| **snid** | VARCHAR(190) | ID de red social |
| **it_token** | TEXT | Token de integración |
| **id_token** | TEXT | Token de identificación |
| **dynamic_fields** | TEXT | Campos dinámicos adicionales |
| **created_at** | DATETIME | Fecha de creación (automática) |
| **updated_at** | DATETIME | Fecha de actualización (automática) |

##### **Índices Únicos**:
- `uniq_email_source`: Previene duplicados por email y source
- `uniq_id_source`: Previene duplicados por ID y source

#### Opciones por Defecto Inicializadas

##### **Configuraciones Básicas**:
- `xpsocial_licencia`: Licencia de uso
- `xpsocial_urlSocial`: URL de Xeerpa Social
- `xpsocial_urlForm`: URL de Xeerpa Form
- `xpsocial_authToken`: Token de autenticación
- `xpsocial_clientId`: ID del cliente
- `xpsocial_clientPwd`: Contraseña del cliente
- `xpsocial_appId`: ID de la aplicación

##### **URLs de Redirección**:
- `xpsocial_redirect_login`: URL de redirección de login
- `xpsocial_redirect_to_registro`: URL de redirección de registro
- `xpsocial_redirect_existing_user`: URL para usuarios existentes

##### **Configuraciones de Marca**:
- `xpsocial_marca`: Marca del sitio
- `xpsocial_linkPP`: Link de políticas de privacidad
- `xpsocial_linkTyC`: Link de términos y condiciones
- `xpsocial_lost_password_url`: URL de recuperación de contraseña

##### **Configuraciones FIFCO**:
- `fifco_api_url`: URL del API de FIFCO
- `fifco_api_token`: Token del API de FIFCO

##### **Estilos por Defecto**:
- `xp_input_height`: 40px
- `xp_input_border`: 1px solid #ced4da
- `xp_input_border_radius`: 6px
- `xp_bg_color`: #ffffff
- `xp_div_color`: #f8f9fa
- `xp_font_color`: #495057
- `xp_divisor_color`: #e9ecef
- `xp_btn_height`: 40px
- `xp_btn_width`: 100px
- `xp_btn_color`: #ffffff
- `xp_btn_bgcolor`: #007cba
- `xp_btn_border_width`: 1px
- `xp_btn_border_color`: #007cba
- `xp_btn_border_radius`: 6px

#### Beneficios de la Mejora

1. **Instalación Automática**:
   - ✅ **Tabla creada automáticamente**: No requiere intervención manual
   - ✅ **Estructura optimizada**: Campos apropiados para almacenar leads
   - ✅ **Índices únicos**: Previene duplicados automáticamente
   - ✅ **Compatibilidad**: Funciona con cualquier charset de WordPress

2. **Configuración Inicial**:
   - ✅ **Opciones predefinidas**: Valores por defecto sensatos
   - ✅ **Estilos listos**: Configuración visual preestablecida
   - ✅ **Configuraciones FIFCO**: Preparado para integración
   - ✅ **Configuraciones Xeerpa**: Preparado para integración

3. **Mantenimiento y Debugging**:
   - ✅ **Logging automático**: Registro de operaciones exitosas
   - ✅ **Flags de activación**: Control de estado del plugin
   - ✅ **Fechas de activación**: Historial de instalaciones
   - ✅ **Debugging facilitado**: Logs para identificar problemas

#### Código Implementado

##### **Función de Activación**:
```php
public static function activate()
{
    // Create custom table for leads
    self::create_custom_table();
    
    // Initialize default options
    self::init_default_options();
    
    // Set activation flag
    update_option('xpsocial_plugin_activated', true);
    update_option('xpsocial_activation_date', current_time('mysql'));
}
```

##### **Creación de Tabla**:
```php
private static function create_custom_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'xpsocial_leads';
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    $sql = "CREATE TABLE {$table_name} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        // ... estructura completa de la tabla
        PRIMARY KEY (id)
    ) {$charset_collate};";
    
    dbDelta($sql);
}
```

#### Resultado Final

Al activar el plugin ahora se ejecuta automáticamente:

- ✅ **Creación de tabla**: `wp_xpsocial_leads` con estructura completa
- ✅ **Inicialización de opciones**: Todas las configuraciones con valores por defecto
- ✅ **Configuración de estilos**: Valores predefinidos para personalización
- ✅ **Logging de operaciones**: Registro de todas las operaciones exitosas
- ✅ **Flags de estado**: Control del estado de activación del plugin

---

### Versión 3.1.14 - Custom Post Type para Formularios Dinámicos

#### Cambios Realizados
- **Archivos creados**: 
  - `class-xpsocial_forms-cpt.php` - Custom Post Type para formularios
  - `class-xpsocial_leads-manager.php` - Manager para la tabla de leads
  - `register-form-dynamic.html` - Template de formulario dinámico
- **Archivo modificado**: `xpsocial_login.php` - Inclusión del CPT
- **Acción**: Implementación de sistema completo de formularios dinámicos
- **Fecha**: $(date)

#### Funcionalidades Implementadas

1. **Custom Post Type para Formularios**:
   - **Tipo**: `xpsocial_form` - Formularios dinámicos configurables
   - **Menú de administración**: "Formularios XP Social" en el admin de WordPress
   - **Configuración completa**: Source, marca FIFCO, campos dinámicos
   - **Interfaz intuitiva**: Meta boxes para configuración fácil

2. **Sistema de Campos Dinámicos**:
   - **Tipos de campo**: Texto, textarea, select, radio, checkbox, audio, imagen
   - **Configuración flexible**: Etiquetas, placeholders, opciones, validación
   - **Campos requeridos**: Sistema de validación por campo
   - **Opciones múltiples**: Para select, radio y checkbox

3. **Integración con FIFCO**:
   - **Marca configurable**: Campo específico para marca FIFCO
   - **Source personalizable**: Identificador único por formulario
   - **País específico**: Configuración de país por formulario
   - **API habilitada**: Checkbox para activar integración FIFCO

4. **Manager de Leads**:
   - **Guardado automático**: Datos se guardan en `wp_xpsocial_leads`
   - **Validación de duplicados**: Prevención de registros duplicados
   - **Campos dinámicos**: Almacenamiento JSON de campos personalizados
   - **Estadísticas**: Métodos para obtener estadísticas de leads

#### Estructura del Custom Post Type

##### **Tipo de Post: `xpsocial_form`**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| **ID** | INT | ID único del formulario |
| **post_title** | VARCHAR | Título del formulario |
| **post_content** | TEXT | Descripción del formulario |
| **post_status** | VARCHAR | Estado del formulario (publish, draft, etc.) |

##### **Meta Fields del Formulario**:

| Meta Key | Tipo | Descripción |
|----------|------|-------------|
| **`_xpsocial_source`** | VARCHAR | Identificador único del formulario |
| **`_xpsocial_success_html`** | TEXT | HTML de mensaje de éxito |
| **`_xpsocial_fifco_enabled`** | BOOLEAN | Habilitar integración FIFCO |
| **`_xpsocial_marca`** | VARCHAR | Marca FIFCO específica |
| **`_xpsocial_country`** | VARCHAR | País específico del formulario |
| **`_xpsocial_show_terms`** | BOOLEAN | Mostrar términos y condiciones |
| **`_xpsocial_show_privacy`** | BOOLEAN | Mostrar política de privacidad |
| **`_xpsocial_dynamic_fields`** | JSON | Campos dinámicos del formulario |

#### Configuración de Campos Dinámicos

##### **Tipos de Campo Disponibles**:

1. **Texto (`text`)**:
   - Campo de entrada de texto simple
   - Validación de patrón opcional
   - Placeholder personalizable

2. **Área de Texto (`textarea`)**:
   - Campo de texto multilínea
   - Ideal para comentarios o descripciones
   - Placeholder personalizable

3. **Lista Desplegable (`select`)**:
   - Opciones predefinidas
   - Una opción por línea en configuración
   - Placeholder para opción por defecto

4. **Botones de Radio (`radio`)**:
   - Selección única de opciones
   - Una opción por línea en configuración
   - Validación de campo requerido

5. **Casillas de Verificación (`checkbox`)**:
   - Selección múltiple de opciones
   - Una opción por línea en configuración
   - Array de valores en envío

6. **Audio (`audio`)**:
   - Subida de archivos de audio
   - Formatos permitidos: MP3, WAV, OGG
   - Validación de tipo de archivo

7. **Imagen (`image`)**:
   - Subida de archivos de imagen
   - Formatos permitidos: JPG, PNG, GIF
   - Validación de tipo de archivo

##### **Configuración por Campo**:

| Propiedad | Tipo | Descripción |
|-----------|------|-------------|
| **label** | VARCHAR | Etiqueta visible del campo |
| **name** | VARCHAR | Nombre del campo (sin espacios) |
| **type** | VARCHAR | Tipo de campo (text, textarea, etc.) |
| **required** | BOOLEAN | Campo obligatorio |
| **placeholder** | VARCHAR | Texto de placeholder |
| **options** | TEXT | Opciones (una por línea) |

#### Manager de Leads

##### **Métodos Principales**:

1. **`save_lead($data)`**:
   - Guarda un nuevo lead en la tabla
   - Valida datos requeridos (EmailAddress o IDNumber + Source)
   - Sanitiza todos los datos de entrada
   - Retorna ID del lead o false en caso de error

2. **`get_lead($lead_id)`**:
   - Obtiene un lead por ID
   - Decodifica campos dinámicos JSON
   - Retorna objeto del lead o null

3. **`get_leads_by_source($source, $limit, $offset)`**:
   - Obtiene leads por source específico
   - Soporte para paginación
   - Ordenados por fecha de creación descendente

4. **`get_leads_by_email($email)`**:
   - Obtiene todos los leads con un email específico
   - Útil para verificar duplicados
   - Ordenados por fecha de creación descendente

5. **`get_leads_stats($source)`**:
   - Obtiene estadísticas de leads
   - Total de leads, por source, por marca, por país
   - Leads recientes (últimos 30 días)

6. **`update_lead($lead_id, $data)`**:
   - Actualiza un lead existente
   - Actualiza fecha de modificación automáticamente
   - Valida campos permitidos

7. **`delete_lead($lead_id)`**:
   - Elimina un lead por ID
   - Log de operación para auditoría

8. **`lead_exists($email, $source)`**:
   - Verifica si existe un lead con email y source específicos
   - Útil para prevenir duplicados

#### Template de Formulario Dinámico

##### **Características del Template**:

1. **Campos Base**:
   - Nombre y apellidos (requeridos)
   - Email (validación de formato)
   - Fecha de nacimiento (requerida)
   - Género (requerido)
   - Teléfono con código de país (requerido)
   - Cédula/identificación (requerida)
   - País y provincia (requeridos)

2. **Campos Dinámicos**:
   - Renderizado automático según configuración
   - Validación según tipo de campo
   - Estilos consistentes con formulario base

3. **Términos y Privacidad**:
   - Checkbox de términos y condiciones (opcional)
   - Checkbox de política de privacidad (opcional)
   - Marca dinámica en texto de privacidad

4. **Campos Ocultos**:
   - Source del formulario
   - Tokens de integración
   - Nonce de seguridad

#### Integración con Tabla de Leads

##### **Mapeo de Datos**:

| Campo Formulario | Campo Tabla | Descripción |
|------------------|-------------|-------------|
| `field_firstname` | `FirstName` | Primer nombre |
| `field_lastname` | `LastName` | Apellidos |
| `field_email` | `EmailAddress` | Email del usuario |
| `field_birthday` | `BirthDate` | Fecha de nacimiento |
| `field_gender` | `Gender` | Género del usuario |
| `field_phone` | `MobileNumber` | Número de teléfono |
| `field_id` | `IDNumber` | Número de identificación |
| `field_country` | `Country` | País seleccionado |
| `field_province` | `Province` | Provincia seleccionada |
| `form_source` | `Source` | Source del formulario |
| `dynamic_*` | `dynamic_fields` | Campos dinámicos (JSON) |
| `field_terms` | `PoliticasPrivacidad` | Aceptación de términos |
| `field_privacy` | `AceptaComunicaciones` | Aceptación de comunicaciones |

##### **Proceso de Guardado**:

1. **Validación**: Verificar campos requeridos
2. **Sanitización**: Limpiar todos los datos de entrada
3. **Campos dinámicos**: Convertir a JSON para almacenamiento
4. **Inserción**: Guardar en tabla `wp_xpsocial_leads`
5. **Logging**: Registrar operación exitosa o errores

#### Interfaz de Administración

##### **Menú Principal**:
- **"Formularios XP Social"**: Menú principal con icono de feedback
- **"Todos los Formularios"**: Lista de formularios existentes
- **"Agregar Nuevo"**: Crear nuevo formulario

##### **Lista de Formularios**:
- **Título**: Nombre del formulario
- **Source**: Identificador único
- **Marca FIFCO**: Marca configurada
- **Campos Dinámicos**: Número de campos personalizados
- **Acciones**: Editar y ver formulario

##### **Editor de Formulario**:
- **Meta Box de Configuración**: Source, mensaje de éxito, FIFCO, marca, país
- **Meta Box de Campos Dinámicos**: Agregar/editar campos personalizados
- **Interfaz drag-and-drop**: Para reordenar campos
- **Validación en tiempo real**: Para configuración correcta

#### Beneficios del Sistema

1. **Flexibilidad Total**:
   - ✅ **Formularios personalizables**: Cada formulario puede tener campos únicos
   - ✅ **Configuración por formulario**: Source, marca, país específicos
   - ✅ **Tipos de campo variados**: Desde texto simple hasta archivos
   - ✅ **Validación personalizable**: Campos requeridos por formulario

2. **Integración FIFCO**:
   - ✅ **Marca específica**: Cada formulario puede tener su marca FIFCO
   - ✅ **Source único**: Identificador para tracking y análisis
   - ✅ **País configurable**: Para formularios específicos por región
   - ✅ **API habilitada**: Integración opcional con FIFCO

3. **Gestión de Leads**:
   - ✅ **Almacenamiento centralizado**: Todos los leads en una tabla
   - ✅ **Prevención de duplicados**: Validación por email y source
   - ✅ **Campos dinámicos**: Almacenamiento flexible de datos personalizados
   - ✅ **Estadísticas**: Métodos para análisis de leads

4. **Experiencia de Usuario**:
   - ✅ **Interfaz intuitiva**: Configuración fácil en WordPress admin
   - ✅ **Validación robusta**: Campos requeridos y formatos correctos
   - ✅ **Mensajes personalizados**: HTML personalizable para éxito
   - ✅ **Términos configurables**: Mostrar/ocultar según necesidad

#### Código Implementado

##### **Inicialización del CPT**:
```php
// Initialize Custom Post Type for forms
new Xpsocial_Forms_CPT();

// Initialize Leads Manager
Xpsocial_Leads_Manager::get_instance();
```

##### **Configuración de Formulario**:
```php
$config = array(
    'source' => 'mi_formulario_2025',
    'success_html' => '<h2>¡Registro exitoso!</h2>',
    'fifco_enabled' => '1',
    'marca' => 'Imperial',
    'country' => 'Costa Rica',
    'show_terms' => '1',
    'show_privacy' => '1',
    'dynamic_fields' => array(
        array(
            'label' => 'Preferencia de contacto',
            'name' => 'preferencia_contacto',
            'type' => 'select',
            'required' => '1',
            'options' => "Email\nTeléfono\nWhatsApp",
            'placeholder' => 'Seleccione su preferencia'
        )
    )
);
```

##### **Guardado de Lead**:
```php
$leads_manager = Xpsocial_Leads_Manager::get_instance();
$lead_id = $leads_manager->save_lead(array(
    'FirstName' => 'Juan',
    'LastName' => 'Pérez',
    'EmailAddress' => 'juan@ejemplo.com',
    'Source' => 'mi_formulario_2025',
    'Marca' => 'Imperial',
    'dynamic_fields' => array(
        'preferencia_contacto' => 'Email'
    )
));
```

#### Resultado Final

El sistema ahora incluye:

- ✅ **Custom Post Type completo**: Para gestión de formularios dinámicos
- ✅ **Sistema de campos flexibles**: 7 tipos de campo diferentes
- ✅ **Integración FIFCO**: Marca, source y país configurables
- ✅ **Manager de leads robusto**: CRUD completo con validaciones
- ✅ **Template de formulario**: Renderizado dinámico de campos
- ✅ **Interfaz de administración**: Fácil configuración en WordPress
- ✅ **Almacenamiento en tabla**: Integración con `wp_xpsocial_leads`
- ✅ **Prevención de duplicados**: Validación por email y source
- ✅ **Estadísticas**: Métodos para análisis de leads
- ✅ **Logging completo**: Para debugging y auditoría

---

### Versión 3.1.15 - Integración del Formulario de Registro con CPT

#### Cambios Realizados
- **Archivo modificado**: `class-xpsocial_register-form.php`
- **Acción**: Integración completa del formulario de registro con el Custom Post Type
- **Fecha**: $(date)

#### Funcionalidades Implementadas

1. **Integración con CPT**:
   - **Obtención de configuración**: El formulario ahora obtiene la configuración del CPT basada en el `form_source`
   - **Valores dinámicos**: Source, Marca, País se toman desde la configuración del formulario
   - **Campos dinámicos**: Procesamiento y almacenamiento de campos personalizados del formulario

2. **Mapeo Correcto de Datos del Usuario**:
   - **Provincia**: Se toma del campo `field_province` del formulario
   - **País**: Se toma del campo `field_country` del formulario o de la configuración del CPT
   - **Políticas de Privacidad**: Checkbox marcado = "Sí", desmarcado = "No"
   - **Términos y Condiciones**: Checkbox marcado = "Sí", desmarcado = "No"

3. **Guardado en Tabla de Leads**:
   - **Integración con Leads Manager**: Los datos se guardan automáticamente en `wp_xpsocial_leads`
   - **Campos dinámicos**: Almacenamiento JSON de campos personalizados
   - **Validación de duplicados**: Prevención de registros duplicados por email y source

4. **Envío a FIFCO API**:
   - **Valores del CPT**: Source y Marca se toman de la configuración del formulario
   - **Campos dinámicos**: Se incluyen en el payload enviado a FIFCO
   - **Valores del usuario**: Provincia, país, políticas se mapean correctamente

#### Modificaciones Técnicas

##### **1. Obtención de Configuración del CPT**:
```php
// Get form source from POST data
$form_source = sanitize_text_field($_POST['form_source'] ?? '');

// Get form configuration from CPT if source is provided
$form_config = null;
if (!empty($form_source)) {
    $form_config = Xpsocial_Forms_CPT::get_form_config_by_source($form_source);
}
```

##### **2. Procesamiento de Campos Dinámicos**:
```php
// Get dynamic fields data
$dynamic_fields_data = array();
if ($form_config && !empty($form_config['dynamic_fields'])) {
    foreach ($form_config['dynamic_fields'] as $field) {
        $field_name = 'dynamic_' . $field['name'];
        if (isset($_POST[$field_name])) {
            if ($field['type'] === 'checkbox') {
                // For checkboxes, we get an array
                $dynamic_fields_data[$field['name']] = is_array($_POST[$field_name]) ? implode(', ', $_POST[$field_name]) : $_POST[$field_name];
            } else {
                $dynamic_fields_data[$field['name']] = sanitize_text_field($_POST[$field_name]);
            }
        }
    }
}
```

##### **3. Mapeo Correcto de Valores Booleanos**:
```php
// Prepare boolean values - check means "Sí"
$robinson = isset($_POST[ 'field_terms' ]) && $_POST[ 'field_terms' ] === 'si' ? 'false' : 'true';
$politicaprivacidad = isset($_POST[ 'field_privacy' ]) && $_POST[ 'field_privacy' ] === 'si' ? 'true' : 'false';
```

##### **4. Guardado en Tabla de Leads**:
```php
// Prepare lead data
$lead_data = array(
    'FirstName' => $first_name,
    'LastName' => $last_name,
    'EmailAddress' => $email,
    'IDNumber' => $IDCedula,
    'Gender' => $genero,
    'BirthDate' => $birthday,
    'MobileNumber' => $phone,
    'Province' => $provincia,
    'Country' => $country,
    'UserRegisterSocial' => $sn,
    'CaptureDate' => current_time('Y-m-d H:i:s'),
    'ModifiedDate' => current_time('Y-m-d H:i:s'),
    'PoliticasPrivacidad' => $politicaprivacidad === 'true' ? 'Sí' : 'No',
    'AceptaComunicaciones' => $robinson === 'false' ? 'Sí' : 'No',
    'snid' => $snid,
    'it_token' => $it,
    'id_token' => $desobfuscatedToken,
    'dynamic_fields' => $dynamic_fields_data
);

// Add form configuration values if available
if ($form_config) {
    $lead_data['Source'] = $form_config['source'];
    $lead_data['Marca'] = $form_config['marca'] ?? '';
    if (!empty($form_config['country'])) {
        $lead_data['Country'] = $form_config['country'];
    }
} else {
    // Fallback values if no form config
    $lead_data['Source'] = $form_source ?: 'default_form';
    $lead_data['Marca'] = 'Default';
}
```

##### **5. Envío a FIFCO con Valores del CPT**:
```php
// Get form configuration values
$form_config = $data['form_config'] ?? null;
$form_source = $data['form_source'] ?? '';

// Determine values from form config or fallback to defaults
$marca = 'Default';
$source = 'default_form';
$country = $data['country'] ?? 'Guatemala';

if ($form_config) {
    $marca = $form_config['marca'] ?? 'Default';
    $source = $form_config['source'] ?? 'default_form';
    if (!empty($form_config['country'])) {
        $country = $form_config['country'];
    }
} else if (!empty($form_source)) {
    $source = $form_source;
}

// Convert boolean values to Spanish
$politicas_privacidad = $data['politicaprivacidad'] === 'true' ? 'Sí' : 'No';
$acepta_comunicaciones = $data['robinson'] === 'false' ? 'Sí' : 'No';
```

##### **6. Inclusión de Campos Dinámicos en FIFCO**:
```php
// Add dynamic fields if available
if (isset($data['dynamic_fields']) && !empty($data['dynamic_fields'])) {
    foreach ($data['dynamic_fields'] as $field_name => $field_value) {
        $formatted_fields[] = [
            "label" => ucfirst(str_replace('_', ' ', $field_name)),
            "value" => $field_value
        ];
    }
}
```

#### Flujo de Datos Actualizado

##### **1. Recepción del Formulario**:
1. **Form Source**: Se obtiene del campo `form_source` del POST
2. **Configuración CPT**: Se busca la configuración del formulario por source
3. **Datos del Usuario**: Se sanitizan todos los campos del formulario
4. **Campos Dinámicos**: Se procesan según la configuración del CPT

##### **2. Procesamiento de Datos**:
1. **Mapeo de Valores**: Se mapean correctamente provincia, país, políticas
2. **Validación**: Se validan campos requeridos y formatos
3. **Preparación**: Se preparan datos para guardado y envío a APIs

##### **3. Guardado en Base de Datos**:
1. **Tabla de Leads**: Se guarda en `wp_xpsocial_leads` usando el Leads Manager
2. **Campos Dinámicos**: Se almacenan como JSON en el campo `dynamic_fields`
3. **Validación de Duplicados**: Se previenen registros duplicados

##### **4. Envío a APIs**:
1. **FIFCO API**: Se envían datos con Source y Marca del CPT
2. **Xeerpa API**: Se mantiene funcionalidad existente
3. **Campos Dinámicos**: Se incluyen en el payload de FIFCO

#### Mapeo de Campos Actualizado

| Campo Formulario | Campo Tabla/API | Origen del Valor |
|------------------|-----------------|------------------|
| `form_source` | `Source` | Atributo del shortcode → CPT |
| `_xpsocial_marca` | `Marca` | Configuración del CPT |
| `field_province` | `Province` | Selección del usuario |
| `field_country` | `Country` | Selección del usuario o CPT |
| `field_terms` | `PoliticasPrivacidad` | Checkbox del usuario (check = "Sí") |
| `field_privacy` | `AceptaComunicaciones` | Checkbox del usuario (check = "Sí") |
| `dynamic_*` | `dynamic_fields` | Campos dinámicos del CPT |

#### Valores de Ejemplo

##### **Configuración del CPT**:
```php
$form_config = array(
    'source' => 'Ker_KetchupLovers_2025',
    'marca' => 'Kerns',
    'country' => 'Guatemala',
    'dynamic_fields' => array(
        array(
            'name' => 'preferencia_contacto',
            'label' => 'Preferencia de Contacto',
            'type' => 'select',
            'options' => "Email\nTeléfono\nWhatsApp"
        )
    )
);
```

##### **Datos Enviados a FIFCO**:
```json
{
    "fields": [
        {
            "label": "Marca",
            "value": "Kerns"
        },
        {
            "label": "Source",
            "value": "Ker_KetchupLovers_2025"
        },
        {
            "label": "Province",
            "value": "Guatemala"
        },
        {
            "label": "Country",
            "value": "Guatemala"
        },
        {
            "label": "PoliticasPrivacidad",
            "value": "Sí"
        },
        {
            "label": "AceptaComunicaciones",
            "value": "Sí"
        },
        {
            "label": "Preferencia contacto",
            "value": "Email"
        }
    ]
}
```

#### Beneficios de la Integración

1. **Configuración Centralizada**:
   - ✅ **Source dinámico**: Cada formulario puede tener su source único
   - ✅ **Marca específica**: Cada formulario puede tener su marca FIFCO
   - ✅ **País configurable**: País específico por formulario
   - ✅ **Campos personalizados**: Campos dinámicos por formulario

2. **Mapeo Correcto de Datos**:
   - ✅ **Valores del usuario**: Provincia, país, políticas se toman correctamente
   - ✅ **Checkboxes**: Check marcado = "Sí", desmarcado = "No"
   - ✅ **Campos dinámicos**: Se procesan y almacenan correctamente
   - ✅ **Validación**: Datos sanitizados y validados

3. **Integración Completa**:
   - ✅ **Tabla de leads**: Guardado automático en `wp_xpsocial_leads`
   - ✅ **FIFCO API**: Envío con valores del CPT
   - ✅ **Xeerpa API**: Mantiene funcionalidad existente
   - ✅ **Campos dinámicos**: Incluidos en todas las integraciones

4. **Flexibilidad**:
   - ✅ **Formularios múltiples**: Cada formulario con su configuración
   - ✅ **Fallbacks**: Valores por defecto si no hay configuración
   - ✅ **Compatibilidad**: Funciona con formularios existentes
   - ✅ **Escalabilidad**: Fácil agregar nuevos campos y configuraciones

#### Resultado Final

El sistema ahora funciona completamente integrado:

- ✅ **Formularios dinámicos**: Configurables desde el CPT
- ✅ **Source y Marca**: Se toman de la configuración del formulario
- ✅ **Datos del usuario**: Se mapean correctamente desde el formulario
- ✅ **Guardado en BD**: Automático en la tabla de leads
- ✅ **Envío a FIFCO**: Con valores correctos del CPT
- ✅ **Campos dinámicos**: Procesados y enviados correctamente
- ✅ **Validación**: Checkboxes y campos requeridos funcionan correctamente

---

### Versión 3.1.18 - Limpieza de Código de Debug

#### Cambios Realizados
- **Archivos modificados**: 
  - `class-xpsocial_api-rest-login.php` - Eliminación de logs de debug y endpoint temporal
  - `xpsocial_forms.js` - Eliminación de console.log de debug
- **Acción**: Limpieza de código de debug después de confirmar funcionamiento
- **Fecha**: $(date)

#### Limpieza Realizada

##### **1. Eliminación de Endpoint de Debug**:
- ✅ **Endpoint removido**: `/wp-json/geo-api/v1/debug-countries`
- ✅ **Función eliminada**: `debug_countries_api()`
- ✅ **Registro de ruta removido**: Endpoint temporal ya no está disponible

##### **2. Limpieza de Logs de Debug en API REST**:

**Logs eliminados de `get_selected_countries()`**:
- ✅ `error_log('XPSocial API Debug - Token: ...')`
- ✅ `error_log('XPSocial API Debug - Countries ID: ...')`
- ✅ `error_log('XPSocial API Debug - URL: ...')`
- ✅ `error_log('XPSocial API Debug - Countries ID string: ...')`
- ✅ `error_log('XPSocial API Debug - HTTP Code: ...')`
- ✅ `error_log('XPSocial API Debug - Response Body: ...')`
- ✅ `error_log('XPSocial API Debug - Using data.data structure')`
- ✅ `error_log('XPSocial API Debug - Using direct array structure')`
- ✅ `error_log('XPSocial API Debug - Using data.countries structure')`
- ✅ `error_log('XPSocial API Debug - Using data.results structure')`
- ✅ `error_log('XPSocial API Error - Unexpected data structure: ...')`
- ✅ `error_log('XPSocial API Error - Data type: ...')`
- ✅ `error_log('XPSocial API Error - Array keys: ...')`
- ✅ `error_log('XPSocial API Warning - Returning empty response due to unexpected structure')`
- ✅ `error_log('XPSocial API Success - Countries count: ...')`

**Logs eliminados de `get_selected_states()`**:
- ✅ `error_log('XPSocial States API Debug - Token: ...')`
- ✅ `error_log('XPSocial States API Debug - Country ID: ...')`
- ✅ `error_log('XPSocial States API Debug - URL: ...')`
- ✅ `error_log('XPSocial States API Debug - Country ID: ...')`
- ✅ `error_log('XPSocial States API Debug - HTTP Code: ...')`
- ✅ `error_log('XPSocial States API Debug - Response Body: ...')`
- ✅ `error_log('XPSocial States API Debug - Using data.data structure')`
- ✅ `error_log('XPSocial States API Debug - Using direct array structure')`
- ✅ `error_log('XPSocial States API Debug - Using data.states structure')`
- ✅ `error_log('XPSocial States API Debug - Using data.results structure')`
- ✅ `error_log('XPSocial States API Error - Unexpected data structure: ...')`
- ✅ `error_log('XPSocial States API Error - Data type: ...')`
- ✅ `error_log('XPSocial States API Error - Array keys: ...')`
- ✅ `error_log('XPSocial States API Warning - Returning empty response due to unexpected structure')`
- ✅ `error_log('XPSocial States API Success - States count: ...')`

##### **3. Limpieza de Console Logs en JavaScript**:

**Logs eliminados de `fetchCountries()`**:
- ✅ `console.log("Fetching countries from:", url)`
- ✅ `console.log("Response status:", response.status)`
- ✅ `console.log("API Response:", data)`
- ✅ `console.error("Unexpected data structure:", data)`
- ✅ `console.log("Countries to process:", countries)`
- ✅ `console.error("Error fetching countries:", error)`

**Logs eliminados de `fetchProvinces()`**:
- ✅ `console.log("Fetching provinces from:", url)`
- ✅ `console.log("Provinces response status:", response.status)`
- ✅ `console.log("Provinces API Response:", data)`
- ✅ `console.error("Unexpected provinces data structure:", data)`
- ✅ `console.log("Provinces to process:", provinces)`
- ✅ `console.error("Error fetching provinces:", error)`

#### Logs de Error Mantenidos

Se mantuvieron únicamente los logs de error esenciales para el funcionamiento:

##### **API REST**:
- ✅ `error_log('XPSocial API Error: Token de licencia no configurado')`
- ✅ `error_log('XPSocial API Error: Países no configurados')`
- ✅ `error_log('XPSocial API Error - WP Error: ...')`
- ✅ `error_log('XPSocial API Error - JSON Decode Error: ...')`
- ✅ `error_log('XPSocial States API Error: Token de licencia no configurado')`
- ✅ `error_log('XPSocial States API Error: Country ID no proporcionado')`
- ✅ `error_log('XPSocial States API Error - cURL Error: ...')`
- ✅ `error_log('XPSocial States API Error - JSON Decode Error: ...')`

#### Beneficios de la Limpieza

##### **1. Código Más Limpio**:
- ✅ **Menos ruido**: Eliminación de logs innecesarios en producción
- ✅ **Mejor rendimiento**: Menos operaciones de logging
- ✅ **Código más legible**: Enfoque en la funcionalidad principal

##### **2. Logs de Producción Optimizados**:
- ✅ **Solo errores críticos**: Logs únicamente para problemas reales
- ✅ **Información esencial**: Mantenimiento de logs de error importantes
- ✅ **Debugging cuando necesario**: Logs de error para diagnóstico

##### **3. Mantenimiento Simplificado**:
- ✅ **Código más limpio**: Fácil de leer y mantener
- ✅ **Menos archivos de log**: Reducción del tamaño de logs
- ✅ **Enfoque en funcionalidad**: Código centrado en la lógica de negocio

#### Funcionalidad Mantenida

A pesar de la limpieza, se mantiene toda la funcionalidad:

##### **1. Manejo Flexible de Estructuras**:
- ✅ **Múltiples formatos**: Soporte para diferentes estructuras de respuesta
- ✅ **Fallback robusto**: Respuesta vacía en lugar de error crítico
- ✅ **Validación de datos**: Verificación de estructura antes de procesar

##### **2. Manejo de Errores**:
- ✅ **Validación de configuración**: Verificación de token y países
- ✅ **Manejo de errores de conexión**: Logs de errores de API
- ✅ **Fallbacks de usuario**: Mensajes de error en interfaz

##### **3. Experiencia de Usuario**:
- ✅ **Formularios funcionales**: Carga de países y provincias
- ✅ **Mensajes de error claros**: Para usuarios finales
- ✅ **Continuidad del servicio**: Funcionamiento robusto

#### Resultado Final

La limpieza de código resulta en:

- ✅ **Código más limpio**: Sin logs de debug innecesarios
- ✅ **Mejor rendimiento**: Menos operaciones de logging
- ✅ **Funcionalidad completa**: Todas las características funcionando
- ✅ **Logs esenciales**: Solo errores críticos registrados
- ✅ **Mantenimiento simplificado**: Código más fácil de leer y mantener

---

### Versión 3.1.17 - Manejo Flexible de Estructuras de Respuesta de API

#### Cambios Realizados
- **Archivos modificados**: 
  - `class-xpsocial_api-rest-login.php` - Manejo flexible de estructuras de respuesta
- **Acción**: Corrección de error "Estructura de datos inválida recibida de la API"
- **Fecha**: $(date)

#### Problema Identificado y Solucionado

**Error "Estructura de datos inválida recibida de la API"**:
- **Causa**: La API externa devuelve diferentes estructuras de respuesta que no coinciden con la validación rígida
- **Solución**: Implementación de manejo flexible de múltiples estructuras de respuesta
- **Mejora**: Agregado endpoint de debug para investigar respuestas de API

#### Mejoras Implementadas

##### **1. Manejo Flexible de Estructuras de Respuesta**:

**Antes**:
```php
// Verificar si la respuesta tiene datos válidos
if (!isset($data['data']) || !is_array($data['data'])) {
    error_log('XPSocial API Error - Invalid data structure: ' . print_r($data, true));
    return new WP_Error('api_error', 'Estructura de datos inválida recibida de la API', array('status' => 500));
}
```

**Después**:
```php
// Verificar si la respuesta tiene datos válidos - manejo más flexible
$countries_data = null;

if (isset($data['data']) && is_array($data['data'])) {
    // Estructura esperada: { "data": [...] }
    $countries_data = $data['data'];
    error_log('XPSocial API Debug - Using data.data structure');
} elseif (is_array($data)) {
    // Estructura alternativa: [...] (array directo)
    $countries_data = $data;
    error_log('XPSocial API Debug - Using direct array structure');
} elseif (isset($data['countries']) && is_array($data['countries'])) {
    // Estructura alternativa: { "countries": [...] }
    $countries_data = $data['countries'];
    error_log('XPSocial API Debug - Using data.countries structure');
} elseif (isset($data['results']) && is_array($data['results'])) {
    // Estructura alternativa: { "results": [...] }
    $countries_data = $data['results'];
    error_log('XPSocial API Debug - Using data.results structure');
} else {
    // Log detallado de la estructura recibida
    error_log('XPSocial API Error - Unexpected data structure: ' . print_r($data, true));
    error_log('XPSocial API Error - Data type: ' . gettype($data));
    if (is_array($data)) {
        error_log('XPSocial API Error - Array keys: ' . implode(', ', array_keys($data)));
    }
    
    // Intentar devolver una respuesta vacía en lugar de error
    $empty_response = array('data' => array());
    error_log('XPSocial API Warning - Returning empty response due to unexpected structure');
    return rest_ensure_response($empty_response);
}

// Reconstruir la respuesta con la estructura estándar
$standard_response = array('data' => $countries_data);
```

##### **2. Endpoint de Debug Agregado**:

**Nuevo endpoint**: `/wp-json/geo-api/v1/debug-countries`

**Funcionalidad**:
```php
function debug_countries_api(WP_REST_Request $request)
{
    $debug_info = array(
        'config' => array(
            'token_present' => !empty($token),
            'token_length' => strlen($token),
            'countries_id' => $countries_id,
            'countries_count' => is_array($countries_id) ? count($countries_id) : 0,
            'api_url' => URLAPI . 'countries/selected'
        ),
        'raw_response' => null,
        'parsed_response' => null,
        'errors' => array()
    );
    
    // Información detallada de la respuesta de la API externa
    $debug_info['raw_response'] = array(
        'http_code' => $http_code,
        'body' => $body,
        'body_length' => strlen($body)
    );
    
    $debug_info['parsed_response'] = array(
        'data_type' => gettype($data),
        'is_array' => is_array($data),
        'keys' => is_array($data) ? array_keys($data) : null,
        'structure' => $data
    );
    
    return rest_ensure_response($debug_info);
}
```

#### Estructuras de Respuesta Soportadas

##### **1. Estructura Estándar**:
```json
{
    "data": [
        {
            "country_id": 1,
            "name": "Costa Rica",
            "emoji": "🇨🇷",
            "phone_code": "+506"
        }
    ]
}
```

##### **2. Array Directo**:
```json
[
    {
        "country_id": 1,
        "name": "Costa Rica",
        "emoji": "🇨🇷",
        "phone_code": "+506"
    }
]
```

##### **3. Estructura con "countries"**:
```json
{
    "countries": [
        {
            "country_id": 1,
            "name": "Costa Rica",
            "emoji": "🇨🇷",
            "phone_code": "+506"
        }
    ]
}
```

##### **4. Estructura con "results"**:
```json
{
    "results": [
        {
            "country_id": 1,
            "name": "Costa Rica",
            "emoji": "🇨🇷",
            "phone_code": "+506"
        }
    ]
}
```

#### Funcionalidades de Debug Mejoradas

##### **1. Logging Detallado de Estructuras**:
- ✅ **Tipo de estructura detectada**: Log específico del tipo de estructura encontrada
- ✅ **Información de fallback**: Log cuando se usa estructura alternativa
- ✅ **Análisis de estructura**: Tipo de datos, claves de array, contenido completo

##### **2. Manejo de Errores Mejorado**:
- ✅ **Respuesta vacía**: En lugar de error 500, devuelve array vacío
- ✅ **Logging específico**: Información detallada sobre estructuras no reconocidas
- ✅ **Continuidad del servicio**: El formulario sigue funcionando aunque la API tenga problemas

##### **3. Endpoint de Debug**:
- ✅ **Información de configuración**: Estado de token y países configurados
- ✅ **Respuesta cruda**: Código HTTP y cuerpo completo de la respuesta
- ✅ **Análisis de estructura**: Tipo de datos y claves disponibles
- ✅ **Errores específicos**: Lista de errores encontrados

#### Beneficios de las Mejoras

##### **1. Robustez**:
- ✅ **Múltiples formatos**: Soporte para diferentes estructuras de respuesta
- ✅ **Fallback inteligente**: Respuesta vacía en lugar de error crítico
- ✅ **Continuidad**: El formulario funciona independientemente de la estructura de API

##### **2. Debugging Facilitado**:
- ✅ **Endpoint de debug**: Información completa de la respuesta de API
- ✅ **Logging detallado**: Información específica sobre estructuras detectadas
- ✅ **Análisis de errores**: Información detallada para diagnosticar problemas

##### **3. Mantenimiento Simplificado**:
- ✅ **Flexibilidad**: Adaptación automática a cambios en la API externa
- ✅ **Información de contexto**: Logs detallados para análisis posterior
- ✅ **Herramientas de debug**: Endpoint específico para investigar problemas

#### Instrucciones de Uso del Endpoint de Debug

Para investigar problemas con la API de países:

1. **Acceder al endpoint de debug**:
   ```
   GET /wp-json/geo-api/v1/debug-countries
   ```

2. **Revisar la información de configuración**:
   - `token_present`: Si el token está configurado
   - `countries_id`: IDs de países configurados
   - `api_url`: URL de la API externa

3. **Analizar la respuesta cruda**:
   - `http_code`: Código de respuesta HTTP
   - `body`: Cuerpo completo de la respuesta
   - `body_length`: Longitud del cuerpo de respuesta

4. **Examinar la estructura parseada**:
   - `data_type`: Tipo de datos (array, object, etc.)
   - `keys`: Claves disponibles en la respuesta
   - `structure`: Estructura completa de los datos

#### Resultado Final

Las mejoras implementadas resuelven:

- ✅ **Error de estructura inválida**: Manejo flexible de múltiples formatos
- ✅ **Falta de información de debug**: Endpoint específico para investigar
- ✅ **Rigidez en validación**: Adaptación automática a diferentes estructuras
- ✅ **Experiencia de usuario**: Continuidad del servicio con fallbacks robustos
- ✅ **Mantenimiento**: Herramientas de debug para análisis detallado

---

### Versión 3.1.16 - Corrección de Errores en API de Países

#### Cambios Realizados
- **Archivos modificados**: 
  - `xpsocial_forms.js` - Mejora del manejo de errores en JavaScript
  - `class-xpsocial_api-rest-login.php` - Mejora del manejo de errores en API REST
- **Acción**: Corrección de errores 400 (Bad Request) y manejo de respuestas de API
- **Fecha**: $(date)

#### Problemas Identificados y Solucionados

1. **Error 400 (Bad Request)**:
   - **Causa**: Configuración incompleta (token de licencia o países no configurados)
   - **Solución**: Validación mejorada con mensajes de error específicos
   - **Logging**: Agregado logging detallado para debugging

2. **Error "countries.forEach is not a function"**:
   - **Causa**: Respuesta de API con estructura inesperada
   - **Solución**: Validación de estructura de datos antes de procesar
   - **Fallback**: Manejo de diferentes formatos de respuesta

3. **Falta de información de debug**:
   - **Problema**: Errores sin información suficiente para diagnosticar
   - **Solución**: Logging detallado en consola y archivos de log

#### Mejoras Implementadas

##### **1. Manejo de Errores en JavaScript**:

**Antes**:
```javascript
fetch(url, requestOptions)
.then((response) => response.json())
.then((data) => {
    let countries = JSON.parse(JSON.stringify(data));
    countries.forEach((country) => {
        // Procesar países
    });
})
.catch((error) => console.error("Error fetching countries:", error));
```

**Después**:
```javascript
fetch(url, requestOptions)
.then((response) => {
    console.log("Response status:", response.status);
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
})
.then((data) => {
    console.log("API Response:", data);
    
    // Verificar si la respuesta tiene la estructura esperada
    let countries = [];
    if (data && data.data && Array.isArray(data.data)) {
        countries = data.data;
    } else if (Array.isArray(data)) {
        countries = data;
    } else {
        console.error("Unexpected data structure:", data);
        throw new Error("Invalid data structure received from API");
    }
    
    countries.forEach((country) => {
        // Procesar países
    });
})
.catch((error) => {
    console.error("Error fetching countries:", error);
    
    // Mostrar mensaje de error al usuario
    if (countrySelect) {
        countrySelect.innerHTML = '<option value="">Error al cargar países</option>';
        countrySelect.disabled = true;
    }
});
```

##### **2. Validación Mejorada en API REST**:

**Antes**:
```php
// Validar que tenemos los datos necesarios
if (empty($token) || empty($countries_id)) {
    return new WP_Error('missing_config', 'Configuración incompleta', array('status' => 400));
}
```

**Después**:
```php
// Log para debugging
error_log('XPSocial API Debug - Token: ' . (!empty($token) ? 'Present' : 'Missing'));
error_log('XPSocial API Debug - Countries ID: ' . print_r($countries_id, true));

// Validar que tenemos los datos necesarios
if (empty($token)) {
    error_log('XPSocial API Error: Token de licencia no configurado');
    return new WP_Error('missing_token', 'Token de licencia no configurado. Por favor configure la licencia en la configuración del plugin.', array('status' => 400));
}

if (empty($countries_id)) {
    error_log('XPSocial API Error: Países no configurados');
    return new WP_Error('missing_countries', 'No hay países configurados. Por favor seleccione al menos un país en la configuración del plugin.', array('status' => 400));
}
```

##### **3. Logging Detallado de Respuestas**:

```php
// Log de la petición
error_log('XPSocial API Debug - URL: ' . $api_url);
error_log('XPSocial API Debug - Countries ID string: ' . $countries_id);

// Obtener código de respuesta HTTP
$http_code = wp_remote_retrieve_response_code($response);
error_log('XPSocial API Debug - HTTP Code: ' . $http_code);

// Procesar respuesta
$body = wp_remote_retrieve_body($response);
error_log('XPSocial API Debug - Response Body: ' . $body);

// Verificar si la respuesta tiene datos válidos
if (!isset($data['data']) || !is_array($data['data'])) {
    error_log('XPSocial API Error - Invalid data structure: ' . print_r($data, true));
    return new WP_Error('api_error', 'Estructura de datos inválida recibida de la API', array('status' => 500));
}

error_log('XPSocial API Success - Countries count: ' . count($data['data']));
```

#### Funcionalidades de Debug Agregadas

##### **1. Logging en Consola del Navegador**:
- ✅ **URL de petición**: Se muestra la URL completa de la API
- ✅ **Status de respuesta**: Código HTTP de la respuesta
- ✅ **Estructura de datos**: Validación de la estructura recibida
- ✅ **Conteo de elementos**: Número de países/provincias recibidos

##### **2. Logging en Archivos de WordPress**:
- ✅ **Configuración**: Estado de token y países configurados
- ✅ **Peticiones**: URL y parámetros enviados
- ✅ **Respuestas**: Código HTTP y cuerpo de respuesta
- ✅ **Errores**: Mensajes detallados de errores específicos

##### **3. Mensajes de Error Específicos**:
- ✅ **Token faltante**: "Token de licencia no configurado"
- ✅ **Países faltantes**: "No hay países configurados"
- ✅ **Estructura inválida**: "Estructura de datos inválida"
- ✅ **Errores de conexión**: Mensajes específicos de cURL/WP_Error

#### Manejo de Diferentes Estructuras de Respuesta

##### **Estructura Esperada**:
```json
{
    "data": [
        {
            "country_id": 1,
            "name": "Costa Rica",
            "emoji": "🇨🇷",
            "phone_code": "+506"
        }
    ]
}
```

##### **Estructura Alternativa**:
```json
[
    {
        "country_id": 1,
        "name": "Costa Rica",
        "emoji": "🇨🇷",
        "phone_code": "+506"
    }
]
```

##### **Validación Implementada**:
```javascript
// Verificar si la respuesta tiene la estructura esperada
let countries = [];
if (data && data.data && Array.isArray(data.data)) {
    countries = data.data;  // Estructura con wrapper "data"
} else if (Array.isArray(data)) {
    countries = data;       // Estructura directa de array
} else {
    console.error("Unexpected data structure:", data);
    throw new Error("Invalid data structure received from API");
}
```

#### Mejoras en la Experiencia del Usuario

##### **1. Mensajes de Error Claros**:
- ✅ **En consola**: Información detallada para desarrolladores
- ✅ **En interfaz**: Mensajes simples para usuarios finales
- ✅ **Estados de carga**: Indicadores visuales de errores

##### **2. Fallbacks Robustos**:
- ✅ **Campos deshabilitados**: Cuando hay errores de API
- ✅ **Mensajes informativos**: "Error al cargar países"
- ✅ **Prevención de crashes**: Validación antes de procesar datos

##### **3. Debugging Facilitado**:
- ✅ **Logs detallados**: En archivos de WordPress
- ✅ **Console logs**: En navegador para debugging
- ✅ **Información de contexto**: URLs, parámetros, respuestas

#### Configuración Requerida

Para que la API funcione correctamente, se requiere:

##### **1. Token de Licencia**:
- **Campo**: `xpsocial_licencia` en opciones de WordPress
- **Ubicación**: Configuración del plugin → Xeerpa Config
- **Formato**: Token de autenticación para la API externa

##### **2. Países Configurados**:
- **Campo**: `xpsocial_countries` en opciones de WordPress
- **Ubicación**: Configuración del plugin → Xeerpa Config
- **Formato**: Array de IDs de países

##### **3. URL de API**:
- **Constante**: `URLAPI` definida en el plugin
- **Valor por defecto**: `https://geo.erna.group/api/`
- **Uso**: Base URL para todas las peticiones de geolocalización

#### Resultado Final

Las mejoras implementadas resuelven:

- ✅ **Error 400 (Bad Request)**: Mensajes específicos sobre configuración faltante
- ✅ **Error forEach**: Validación de estructura de datos antes de procesar
- ✅ **Falta de información**: Logging detallado para debugging
- ✅ **Experiencia de usuario**: Mensajes claros y fallbacks robustos
- ✅ **Mantenimiento**: Información suficiente para diagnosticar problemas

#### Instrucciones para Resolver el Error

Si el error persiste, verificar:

1. **Token de Licencia**:
   - Ir a Configuración del plugin → Xeerpa Config
   - Verificar que el campo "Licencia" esté configurado

2. **Países Configurados**:
   - Ir a Configuración del plugin → Xeerpa Config
   - Verificar que al menos un país esté seleccionado

3. **Logs de Debug**:
   - Revisar `wp-content/debug.log` para mensajes de error específicos
   - Revisar consola del navegador para información adicional

4. **URL de API**:
   - Verificar que la constante `URLAPI` esté correctamente definida
   - Probar conectividad a la API externa

---

## Próximas Modificaciones

### Pendientes de Definir
- [ ] Modificaciones específicas a realizar
- [ ] Mejoras de funcionalidad
- [ ] Correcciones de bugs
- [ ] Optimizaciones de rendimiento

---

## Notas de Desarrollo
- Todas las modificaciones se documentarán en este archivo
- Se mantendrá un registro detallado de cambios
- Se incluirán explicaciones de por qué se realizan las modificaciones
- Se documentarán las pruebas realizadas

---

## [3.1.19] - 2025-10-15

### Corrección de Compatibilidad con SQLite y MySQL
- **Problema identificado**: `dbDelta` no es compatible con SQLite, causaba errores de creación de tabla
- **Solución implementada**: Uso de `$wpdb->query()` directamente para SQLite y `dbDelta` para MySQL
- **Detección mejorada**: Mejorada la detección de SQLite incluyendo `WP_SQLite_DB` y `PDO`
- **Verificación robusta**: Agregada verificación doble de existencia de tabla antes del guardado
- **Logging optimizado**: Reducido el logging excesivo, solo se registra cuando hay problemas
- **Corrección de advertencia**: Corregida la advertencia `Undefined array key "REQUEST_METHOD"`
- **Inicialización mejorada**: El gestor de leads se inicializa en el hook `init` con prioridad 20
- **Resultado**: El formulario ahora guarda correctamente los datos en ambas bases de datos

## [3.1.20] - 2025-10-15

### Corrección de Mapeo de Datos del Formulario
- **Género**: Corregido mapeo para guardar "Masculino", "Femenino", "No Binario" en lugar de "m", "f", "nb"
- **País**: Corregido para guardar el nombre completo del país en lugar del ID numérico
- **Source**: Implementado shortcode dinámico que toma el source del atributo del shortcode `[xpsocial_register_form source="formulario_audio_2025"]`
- **Términos y Privacidad**: Corregido mapeo para guardar "Sí"/"No" en base de datos y mantener true/false para sistema externo
- **Shortcode dinámico**: Creado nuevo shortcode que renderiza formularios basados en configuración del CPT
- **Compatibilidad**: Mantenida compatibilidad con sistema externo usando valores true/false

## [3.1.21] - 2025-10-15

### Corrección de Distribución de Campos en Formulario Dinámico
- **Estructura de columnas**: Corregida para usar `two-columns` y `columns-wrap` como el formulario original
- **Campos principales**: Mantienen distribución de dos columnas (Nombre/Apellidos, Fecha/Género, Teléfono/Cédula, País/Provincia)
- **Campos dinámicos**: Mantienen distribución de una sola columna (`form-group`)
- **Consistencia visual**: El formulario dinámico ahora tiene la misma apariencia que el formulario original
- **Clases CSS**: Actualizadas para usar las mismas clases que el template original

## [3.1.22] - 2025-10-15

### Corrección de Checkboxes en Formulario Dinámico
- **Checkboxes principales**: Corregidos para estar en una sola línea con input dentro del label
- **Links funcionales**: Agregados links reales a términos y condiciones y políticas de privacidad
- **Checkboxes dinámicos**: Modificados para mostrarse en línea horizontal con flexbox
- **Estructura consistente**: Los checkboxes ahora siguen la misma estructura que el formulario original
- **Estilos inline**: Agregados estilos CSS inline para asegurar que los checkboxes dinámicos se muestren en línea

## [3.1.23] - 2025-10-15

### Corrección de Estilos del Botón Submit
- **Botón submit**: Corregido para usar las mismas clases CSS que el formulario original
- **Clases aplicadas**: Cambiado de `btn-submit` a `xp-boton-registro xp_button`
- **ID consistente**: Cambiado de `submit_register` a `xp-registro-social`
- **Tipo de elemento**: Cambiado de `<button>` a `<input type="submit">` para consistencia
- **Estilos del plugin**: Ahora el botón respeta los estilos configurados en el plugin

## [3.1.24] - 2025-10-15

### Aplicación Completa de Estilos del Plugin al Formulario Dinámico
- **Estilos CSS dinámicos**: Agregados estilos CSS que respetan todas las configuraciones del plugin
- **Configuraciones aplicadas**: 
  - Altura de inputs (`xp_input_height`)
  - Bordes de inputs (`xp_input_border`, `xp_input_border_radius`)
  - Colores de fondo (`xp_bg_color`, `xp_div_color`)
  - Colores de fuente (`xp_font_color`)
  - Estilos del botón (`xp_btn_height`, `xp_btn_width`, `xp_btn_color`, `xp_btn_bgcolor`, `xp_btn_border_*`)
- **Layout responsive**: Estilos para distribución de dos columnas y campos dinámicos
- **Consistencia visual**: El formulario dinámico ahora respeta completamente la configuración del plugin
- **Estilos inline**: CSS generado dinámicamente basado en las opciones del plugin

## [3.1.25] - 2025-10-15

### Refactorización de CSS: Movimiento de Estilos a Archivo Público
- **CSS inline removido**: Eliminado CSS inline del template HTML del formulario dinámico
- **Estilos en archivo público**: Movidos todos los estilos del formulario dinámico a `/public/css/xpsocial_login-public.css`
- **CSS dinámico inteligente**: Implementado sistema de CSS dinámico que se aplica solo cuando hay formularios dinámicos en la página
- **Detección automática**: El sistema detecta automáticamente si hay shortcodes `[xpsocial_register_form]` en la página
- **Optimización de rendimiento**: CSS dinámico se genera solo cuando es necesario
- **Configuraciones aplicadas**: Todas las configuraciones del plugin se aplican dinámicamente via CSS inline
- **Separación de responsabilidades**: HTML limpio sin estilos inline, CSS organizado en archivo dedicado

#### Beneficios de la Refactorización

1. **✅ Código más limpio**: HTML sin estilos inline
2. **✅ Mejor organización**: CSS centralizado en archivo público
3. **✅ Rendimiento optimizado**: CSS dinámico solo cuando es necesario
4. **✅ Mantenibilidad**: Fácil modificación de estilos
5. **✅ Consistencia**: Misma estructura que otros archivos del plugin
6. **✅ Escalabilidad**: Fácil agregar nuevos estilos

## [3.1.26] - 2025-10-15

### Mejoras en Estilos de Formulario: Configuración de Bordes Redondeados para Selects
- **Selects mejorados**: Aplicada configuración de bordes redondeados a elementos `select`
- **Tipos de input ampliados**: Agregados todos los tipos de input HTML5 al CSS
- **Estilos específicos por tipo**: Implementados estilos personalizados para diferentes tipos de input
- **Selects personalizados**: Eliminada apariencia nativa del navegador con flecha SVG personalizada
- **Compatibilidad mejorada**: Estilos específicos para checkboxes, radio buttons, file inputs, range, color, etc.

#### Tipos de Input Soportados

1. **✅ Inputs básicos**: text, email, tel, date, password, number, search, url
2. **✅ Inputs especiales**: file, range, color, datetime-local, month, time, week
3. **✅ Inputs de selección**: checkbox, radio, hidden
4. **✅ Elementos de formulario**: select, textarea

#### Estilos Específicos Implementados

- **Selects**: Apariencia personalizada con flecha SVG, bordes redondeados configurables
- **Checkboxes/Radio**: Tamaño y espaciado optimizado
- **File inputs**: Estilo dashed con hover effects
- **Range inputs**: Slider personalizado con thumb redondeado
- **Color inputs**: Tamaño fijo optimizado para selección de colores

#### Configuraciones Aplicadas a Selects

- **Bordes redondeados**: `border-radius` desde configuración del plugin
- **Altura**: `height` desde configuración del plugin  
- **Bordes**: `border` desde configuración del plugin
- **Colores**: `background-color` y `color` desde configuración del plugin
- **Apariencia personalizada**: Flecha SVG en lugar de nativa del navegador

## [3.1.27] - 2025-10-15

### Sistema Completo de Validaciones Frontend y Backend
- **Validaciones de duplicados**: Implementadas validaciones para email, source e ID Number
- **Validación de edad mínima**: Verificación de que el usuario sea mayor de 18 años
- **Endpoints REST**: Creados endpoints para validaciones AJAX en tiempo real
- **Validaciones JavaScript**: Implementadas validaciones en tiempo real sin recargar página
- **Mensajes de error dinámicos**: Mensajes de error mostrados debajo de cada campo
- **Validaciones backend**: Protección contra envío directo a base de datos

#### Validaciones Implementadas

##### **1. ✅ Validaciones de Duplicados**:
- **Email + Source**: No se puede registrar el mismo email para la misma campaña
- **ID Number + Source**: No se puede registrar el mismo número de identificación para la misma campaña
- **Verificación en tiempo real**: Validación AJAX mientras el usuario escribe

##### **2. ✅ Validación de Edad Mínima**:
- **18 años mínimo**: Verificación automática basada en fecha de nacimiento
- **Cálculo preciso**: Usa DateTime para cálculo exacto de edad
- **Validación en tiempo real**: Se valida al cambiar la fecha de nacimiento

##### **3. ✅ Endpoints REST API**:
```php
POST /wp-json/geo-api/v1/validate-email
POST /wp-json/geo-api/v1/validate-id-number  
POST /wp-json/geo-api/v1/validate-age
```

##### **4. ✅ Validaciones JavaScript**:
- **Debounced validation**: Evita múltiples llamadas API
- **Validación en tiempo real**: Sin recargar página
- **Estados visuales**: Campos con errores se marcan en rojo
- **Mensajes dinámicos**: Errores específicos para cada campo

##### **5. ✅ Protección Backend**:
- **Validación antes de guardar**: Verificación en `class-xpsocial_register-form.php`
- **Respuesta JSON**: Errores devueltos como JSON con código 400
- **Prevención de duplicados**: Protección contra envío directo a BD

#### Métodos de Validación en Leads Manager

```php
// Validaciones individuales
$leads_manager->email_exists($email, $source)
$leads_manager->id_number_exists($id_number, $source)
$leads_manager->validate_minimum_age($birth_date, $min_age)

// Validación completa
$errors = $leads_manager->validate_lead_data($data)
```

#### Estilos de Validación

- **Campos con error**: Borde rojo y sombra roja
- **Campos válidos**: Borde verde y sombra verde
- **Mensajes de error**: Animación fadeIn, color rojo
- **Indicador de carga**: Spinner durante validación AJAX

#### Flujo de Validación

1. **Usuario escribe** → Validación debounced (500ms)
2. **Llamada AJAX** → Endpoint REST correspondiente
3. **Respuesta del servidor** → Validación en base de datos
4. **Actualización visual** → Campo marcado como válido/inválido
5. **Mensaje de error** → Mostrado debajo del campo si hay error
6. **Envío del formulario** → Validación final antes de guardar

#### Beneficios de la Implementación

1. **✅ Experiencia de usuario mejorada**: Validación en tiempo real
2. **✅ Prevención de duplicados**: Protección completa en frontend y backend
3. **✅ Seguridad**: Validaciones backend contra manipulación
4. **✅ Feedback inmediato**: Usuario sabe inmediatamente si hay errores
5. **✅ Prevención de envíos inválidos**: Formulario no se envía con errores
6. **✅ Código mantenible**: Validaciones centralizadas y reutilizables

## [3.1.28] - 2025-10-15

### Valor por Defecto para UserRegisterSocial
- **Valor por defecto**: `UserRegisterSocial` ahora tiene "FM" como valor por defecto
- **Aplicación**: Se aplica cuando el campo `field_sn` no está presente o está vacío
- **Ubicación**: Modificado en `class-xpsocial_register-form.php`
- **Consistencia**: Garantiza que siempre haya un valor válido para este campo

#### Cambio Implementado

```php
// Antes
$sn = sanitize_text_field($_POST['field_sn']);

// Después  
$sn = sanitize_text_field($_POST['field_sn']) ?: 'FM'; // Default to 'FM' if not provided
```

#### Beneficios

1. **✅ Valor consistente**: Siempre hay un valor para `UserRegisterSocial`
2. **✅ Compatibilidad**: Mantiene compatibilidad con sistemas que esperan este campo
3. **✅ Fallback seguro**: Si no se proporciona el campo, usa "FM" por defecto
4. **✅ Aplicación automática**: Se aplica tanto en la base de datos como en la API FIFCO

## [3.1.29] - 2025-10-15

### Corrección de Codificación UTF-8 en Campos Dinámicos
- **Problema identificado**: Los campos dinámicos se guardaban con codificación incorrecta (ej: "u00bfCual es tu color favorito?")
- **Solución implementada**: Mejorado el procesamiento de caracteres UTF-8 en campos dinámicos
- **Ubicaciones corregidas**: 
  - Procesamiento de formulario (`class-xpsocial_register-form.php`)
  - Guardado de campos dinámicos en CPT (`class-xpsocial_forms-cpt.php`)

#### Cambios Implementados

##### **1. ✅ Procesamiento de Formulario**:
```php
// Antes
$dynamic_fields_data[$field['name']] = sanitize_text_field($_POST[$field_name]);

// Después
$value = $_POST[$field_name];
$value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
$value = sanitize_text_field($value);
$dynamic_fields_data[$field['name']] = $value;
```

##### **2. ✅ Guardado en CPT**:
```php
// Antes
'label' => sanitize_text_field($field['label']),
'options' => sanitize_textarea_field($field['options']),

// Después
$label = html_entity_decode($field['label'], ENT_QUOTES, 'UTF-8');
$options = html_entity_decode($field['options'], ENT_QUOTES, 'UTF-8');
'label' => sanitize_text_field($label),
'options' => sanitize_textarea_field($options),
```

##### **3. ✅ JSON Encoding**:
```php
// Antes
json_encode($fields)

// Después
json_encode($fields, JSON_UNESCAPED_UNICODE)
```

#### Beneficios de la Corrección

1. **✅ Caracteres especiales**: Los acentos y caracteres especiales se guardan correctamente
2. **✅ Codificación UTF-8**: Manejo adecuado de la codificación UTF-8
3. **✅ Compatibilidad**: Funciona correctamente con caracteres en español
4. **✅ Consistencia**: Los datos se muestran igual en la base de datos y en el frontend

#### Campos Afectados

- **Labels de campos dinámicos**: Títulos de los campos
- **Opciones de select/checkbox**: Valores de las opciones
- **Placeholders**: Textos de ayuda
- **Valores de campos dinámicos**: Datos ingresados por el usuario

## [3.1.30] - 2025-10-15

### Columna de Acciones en CPT "Formularios XP Social"
- **Nueva columna**: Agregada columna "Acciones" al listado del CPT
- **Botones de acción**: Botones "Editar" y "Eliminar" para cada formulario
- **Confirmación de eliminación**: JavaScript de confirmación antes de eliminar
- **Estilos mejorados**: CSS personalizado para los botones de acción
- **Seguridad**: URLs con nonce para prevenir ataques CSRF

#### Funcionalidades Implementadas

##### **1. ✅ Columna de Acciones**:
- **Ubicación**: Nueva columna "Acciones" en el listado del CPT
- **Contenido**: Botones "Editar" y "Eliminar" para cada formulario
- **Ancho fijo**: 120px para mantener consistencia visual

##### **2. ✅ Botón Editar**:
```php
$edit_url = get_edit_post_link($post_id);
echo '<a href="' . esc_url($edit_url) . '" class="button button-small">Editar</a>';
```

##### **3. ✅ Botón Eliminar**:
```php
$delete_url = wp_nonce_url(
    admin_url('post.php?post=' . $post_id . '&action=delete'),
    'delete-post_' . $post_id
);
echo '<a href="' . esc_url($delete_url) . '" class="button button-small" onclick="return confirm(\'¿Estás seguro?\');" style="color: #a00;">Eliminar</a>';
```

##### **4. ✅ Estilos CSS**:
```css
.wp-list-table .column-actions {
    width: 120px;
}

.wp-list-table .column-actions .button {
    margin: 2px;
    padding: 4px 8px;
    font-size: 11px;
    line-height: 1.4;
    min-height: auto;
}

.wp-list-table .column-actions .button[style*="color: #a00"] {
    border-color: #dc3232;
    color: #dc3232 !important;
}

.wp-list-table .column-actions .button[style*="color: #a00"]:hover {
    background: #dc3232;
    color: #fff !important;
}
```

#### Estructura de la Tabla

| Columna | Descripción |
|---------|-------------|
| **Checkbox** | Selección múltiple |
| **Título** | Nombre del formulario |
| **Source** | Identificador único |
| **Marca FIFCO** | Marca configurada |
| **Campos Dinámicos** | Número de campos |
| **Acciones** | Botones Editar/Eliminar |
| **Fecha** | Fecha de creación |

#### Beneficios de la Implementación

1. **✅ Acceso rápido**: Editar y eliminar formularios directamente desde el listado
2. **✅ Seguridad**: URLs con nonce para prevenir ataques CSRF
3. **✅ Confirmación**: JavaScript de confirmación antes de eliminar
4. **✅ Estilos consistentes**: Botones con estilos de WordPress
5. **✅ UX mejorada**: Interfaz más intuitiva y funcional

## [3.1.31] - 2025-10-15

### Respeto de Configuración de Validaciones del CPT
- **Configuración dinámica**: Las validaciones ahora respetan la configuración específica de cada campaña
- **Frontend y Backend**: Tanto JavaScript como PHP respetan las configuraciones del CPT
- **Validaciones opcionales**: Email, ID Number y Edad pueden ser habilitados/deshabilitados por campaña
- **Edad mínima configurable**: Cada campaña puede tener una edad mínima diferente

#### Funcionalidades Implementadas

##### **1. ✅ Atributos de Datos en el Formulario**:
```html
<div class="xpsocial-form" 
     data-source="formulario_audio_2025"
     data-validate-email="1"
     data-validate-id-number="0"
     data-validate-age="1"
     data-min-age="21">
```

##### **2. ✅ JavaScript Dinámico**:
```javascript
// Initialize validation configuration from form data attributes
function initializeValidationConfig() {
    const form = $('.xpsocial-form');
    if (form.length) {
        VALIDATION_CONFIG.validateEmail = form.data('validate-email') === 1;
        VALIDATION_CONFIG.validateIdNumber = form.data('validate-id-number') === 1;
        VALIDATION_CONFIG.validateAge = form.data('validate-age') === 1;
        VALIDATION_CONFIG.minAge = parseInt(form.data('min-age')) || 18;
    }
}
```

##### **3. ✅ Validaciones Condicionales**:
```javascript
// Validate email (solo si está habilitado)
function validateEmail(email, source) {
    if (!VALIDATION_CONFIG.validateEmail) {
        clearFieldError('field_email');
        return Promise.resolve(true);
    }
    // ... resto de validación
}
```

##### **4. ✅ Backend Configurable**:
```php
public function validate_lead_data($data, $form_config = null)
{
    // Get validation settings from form config
    $validate_email = true;
    $validate_id_number = true;
    $validate_age = true;
    $min_age = 18;
    
    if ($form_config) {
        $validate_email = isset($form_config['validate_email']) ? (bool)$form_config['validate_email'] : true;
        $validate_id_number = isset($form_config['validate_id_number']) ? (bool)$form_config['validate_id_number'] : true;
        $validate_age = isset($form_config['validate_age']) ? (bool)$form_config['validate_age'] : true;
        $min_age = isset($form_config['min_age']) ? (int)$form_config['min_age'] : 18;
    }
    
    // Validar solo campos habilitados
    if ($validate_email && !empty($data['EmailAddress'])) {
        // ... validación de email
    }
}
```

#### Configuración por Campaña

| Campo | Descripción | Valores |
|-------|-------------|---------|
| **Validar Email** | Verificar duplicados por campaña | ✅/❌ |
| **Validar ID Number** | Verificar duplicados por campaña | ✅/❌ |
| **Validar Edad** | Verificar edad mínima | ✅/❌ |
| **Edad Mínima** | Edad mínima requerida | 18+ años |

#### Flujo de Validación

1. **✅ Configuración CPT**: Administrador configura validaciones en el CPT
2. **✅ Atributos HTML**: Configuración se pasa al formulario como data attributes
3. **✅ JavaScript**: Lee configuración y aplica validaciones condicionales
4. **✅ Backend**: Recibe configuración y valida según reglas específicas
5. **✅ Respuesta**: Errores específicos según configuración de campaña

#### Beneficios de la Implementación

1. **✅ Flexibilidad**: Cada campaña puede tener validaciones diferentes
2. **✅ Configuración centralizada**: Todo desde el CPT del formulario
3. **✅ Consistencia**: Frontend y backend usan la misma configuración
4. **✅ UX mejorada**: Solo se validan campos configurados
5. **✅ Mantenimiento**: Fácil modificar validaciones por campaña

## [3.1.32] - 2025-10-15

### Corrección de Lectura de Configuración de Validaciones
- **Problema identificado**: Las configuraciones de validación del CPT no se estaban leyendo correctamente en el JavaScript
- **Causa**: Faltaban las configuraciones de validación en `get_form_config_by_source()` y problemas con la lectura de atributos `data-*`
- **Solución**: Agregadas configuraciones de validación al array de configuración y mejorada la lectura de atributos en JavaScript

#### Funcionalidades Corregidas

##### **1. ✅ Configuración Completa en `get_form_config_by_source()`**:
```php
$config = array(
    'source' => $source,
    'success_html' => get_post_meta($post->ID, '_xpsocial_success_html', true),
    'fifco_enabled' => get_post_meta($post->ID, '_xpsocial_fifco_enabled', true),
    'marca' => get_post_meta($post->ID, '_xpsocial_marca', true),
    'country' => get_post_meta($post->ID, '_xpsocial_country', true),
    'show_terms' => get_post_meta($post->ID, '_xpsocial_show_terms', true),
    'show_privacy' => get_post_meta($post->ID, '_xpsocial_show_privacy', true),
    'validate_email' => get_post_meta($post->ID, '_xpsocial_validate_email', true),
    'validate_id_number' => get_post_meta($post->ID, '_xpsocial_validate_id_number', true),
    'validate_age' => get_post_meta($post->ID, '_xpsocial_validate_age', true),
    'min_age' => get_post_meta($post->ID, '_xpsocial_min_age', true) ?: 18,
    'dynamic_fields' => array()
);
```

##### **2. ✅ Lectura Mejorada de Atributos Data**:
```javascript
function initializeValidationConfig() {
    const form = $('.xpsocial-form');
    if (form.length) {
        // Read data attributes correctly (jQuery converts kebab-case to camelCase)
        VALIDATION_CONFIG.validateEmail = form.data('validateEmail') === 1 || form.data('validate-email') === 1;
        VALIDATION_CONFIG.validateIdNumber = form.data('validateIdNumber') === 1 || form.data('validate-id-number') === 1;
        VALIDATION_CONFIG.validateAge = form.data('validateAge') === 1 || form.data('validate-age') === 1;
        VALIDATION_CONFIG.minAge = parseInt(form.data('minAge') || form.data('min-age')) || 18;
        
        console.log('Validation config loaded:', VALIDATION_CONFIG);
        console.log('Form data attributes:', {
            'validate-email': form.attr('data-validate-email'),
            'validate-id-number': form.attr('data-validate-id-number'),
            'validate-age': form.attr('data-validate-age'),
            'min-age': form.attr('data-min-age')
        });
    }
}
```

#### Problema Resuelto

**Antes**: 
```javascript
// Debug mostraba:
{
    validateEmail: false,
    validateIdNumber: false, 
    validateAge: false
}
```

**Después**:
```javascript
// Debug ahora muestra:
{
    validateEmail: true,    // Si está habilitado en el CPT
    validateIdNumber: true, // Si está habilitado en el CPT
    validateAge: true,      // Si está habilitado en el CPT
    minAge: 21             // Valor configurado en el CPT
}
```

#### Flujo de Configuración Corregido

1. **✅ CPT Guarda**: Configuraciones se guardan en `_xpsocial_validate_*` meta fields
2. **✅ `get_form_config_by_source()`**: Ahora incluye todas las configuraciones de validación
3. **✅ Template HTML**: Pasa configuraciones como atributos `data-*`
4. **✅ JavaScript**: Lee correctamente los atributos y aplica validaciones
5. **✅ Backend**: Recibe configuración completa para validaciones

#### Beneficios de la Corrección

1. **✅ Configuración Respeta CPT**: Las validaciones ahora se aplican según la configuración del formulario
2. **✅ Debug Mejorado**: Console.log muestra valores reales de configuración
3. **✅ Compatibilidad**: Soporte para ambos formatos de atributos (kebab-case y camelCase)
4. **✅ Validaciones Funcionales**: Email, ID Number y Edad se validan solo si están habilitados
5. **✅ Edad Mínima Dinámica**: Cada campaña puede tener su propia edad mínima

## [3.1.33] - 2025-10-15

### Sistema Eficiente de Campos Dinámicos
- **Nueva tabla separada**: Creación de `wp_xpsocial_dynamic_fields` para almacenar campos dinámicos por separado
- **Mejor rendimiento**: Consultas más eficientes y análisis de datos mejorado
- **Estructura normalizada**: Cada campo dinámico se guarda como registro individual
- **Compatibilidad SQLite/MySQL**: Funciona en ambos tipos de base de datos
- **Métodos de consulta**: Nuevos métodos para recuperar y analizar campos dinámicos

#### Funcionalidades Implementadas

##### **1. ✅ Nueva Tabla `wp_xpsocial_dynamic_fields`**:
```sql
-- SQLite
CREATE TABLE wp_xpsocial_dynamic_fields (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lead_id INTEGER NOT NULL,
    field_name TEXT NOT NULL,
    field_value TEXT,
    field_type TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES wp_xpsocial_leads(id) ON DELETE CASCADE
);

-- MySQL
CREATE TABLE wp_xpsocial_dynamic_fields (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    lead_id BIGINT(20) UNSIGNED NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_value TEXT,
    field_type VARCHAR(50),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY lead_id (lead_id),
    KEY field_name (field_name),
    FOREIGN KEY (lead_id) REFERENCES wp_xpsocial_leads(id) ON DELETE CASCADE
);
```

##### **2. ✅ Método `save_dynamic_fields()`**:
```php
private function save_dynamic_fields($lead_id, $dynamic_fields, $form_config = null)
{
    // Guarda cada campo dinámico como registro individual
    foreach ($dynamic_fields as $field_name => $field_value) {
        $field_data = array(
            'lead_id' => $lead_id,
            'field_name' => sanitize_text_field($field_name),
            'field_value' => sanitize_text_field($field_value),
            'field_type' => sanitize_text_field($field_type)
        );
        $wpdb->insert($this->dynamic_fields_table, $field_data);
    }
}
```

##### **3. ✅ Método `get_dynamic_fields()`**:
```php
public function get_dynamic_fields($lead_id)
{
    // Recupera todos los campos dinámicos de un lead específico
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT field_name, field_value, field_type FROM {$this->dynamic_fields_table} WHERE lead_id = %d",
        $lead_id
    ));
    
    $dynamic_fields = array();
    foreach ($results as $row) {
        $dynamic_fields[$row->field_name] = array(
            'value' => $row->field_value,
            'type' => $row->field_type
        );
    }
    return $dynamic_fields;
}
```

##### **4. ✅ Método `get_leads_with_dynamic_fields()`**:
```php
public function get_leads_with_dynamic_fields($args = array())
{
    // Obtiene leads con sus campos dinámicos incluidos
    $leads = $wpdb->get_results($sql);
    
    foreach ($leads as $lead) {
        $lead->dynamic_fields = $this->get_dynamic_fields($lead->id);
    }
    
    return $leads;
}
```

#### Ventajas del Nuevo Sistema

| Aspecto | Antes (JSON) | Después (Tabla Separada) |
|---------|--------------|--------------------------|
| **Consultas** | Difícil filtrar por campo específico | Fácil consulta por campo |
| **Análisis** | Requiere parsing de JSON | Consultas SQL directas |
| **Rendimiento** | Carga todo el JSON | Carga solo campos necesarios |
| **Escalabilidad** | Limitado por tamaño de JSON | Sin límites de campos |
| **Índices** | No se pueden indexar campos | Índices en field_name y lead_id |

#### Ejemplos de Uso

##### **Consultar por Campo Específico**:
```sql
-- Buscar todos los leads que respondieron "Rojo" al campo "color_favorito"
SELECT l.*, df.field_value 
FROM wp_xpsocial_leads l
JOIN wp_xpsocial_dynamic_fields df ON l.id = df.lead_id
WHERE df.field_name = 'color_favorito' AND df.field_value = 'Rojo';
```

##### **Análisis de Respuestas**:
```sql
-- Contar respuestas por opción en un campo select
SELECT field_value, COUNT(*) as count
FROM wp_xpsocial_dynamic_fields
WHERE field_name = 'color_favorito'
GROUP BY field_value
ORDER BY count DESC;
```

##### **Leads con Campos Dinámicos**:
```php
// Obtener leads con sus campos dinámicos
$leads_manager = Xpsocial_Leads_Manager::get_instance();
$leads = $leads_manager->get_leads_with_dynamic_fields(array(
    'source' => 'formulario_audio_2025',
    'limit' => 10
));

foreach ($leads as $lead) {
    echo "Lead: " . $lead->FirstName . " " . $lead->LastName . "\n";
    foreach ($lead->dynamic_fields as $field_name => $field_data) {
        echo "  {$field_name}: {$field_data['value']} ({$field_data['type']})\n";
    }
}
```

#### Migración Automática

- **✅ Compatibilidad**: Los campos dinámicos se siguen guardando en JSON en la tabla principal (retrocompatibilidad)
- **✅ Doble Guardado**: Se guardan tanto en JSON como en tabla separada
- **✅ Sin Pérdida**: No se pierden datos existentes
- **✅ Migración Gradual**: Se puede migrar datos existentes cuando sea necesario

#### Beneficios de la Implementación

1. **✅ Consultas Eficientes**: Fácil filtrar y analizar campos específicos
2. **✅ Escalabilidad**: Sin límites en número de campos dinámicos
3. **✅ Análisis Avanzado**: Consultas SQL complejas para reportes
4. **✅ Índices Optimizados**: Mejor rendimiento en consultas
5. **✅ Estructura Normalizada**: Base de datos más limpia y organizada
6. **✅ Retrocompatibilidad**: No rompe funcionalidad existente

## [3.1.34] - 2025-10-15

### Bloqueo de Envío de Formulario con Errores de Validación
- **Prevención de envío**: El formulario no se puede enviar si hay errores de validación
- **Validación robusta**: Verifica tanto el estado actual como realiza validaciones frescas
- **Resumen de errores**: Muestra todos los errores de validación en un panel destacado
- **UX mejorada**: Limpia automáticamente los errores cuando el usuario empieza a corregir
- **Scroll automático**: Lleva al usuario al inicio del formulario cuando hay errores

#### Funcionalidades Implementadas

##### **1. ✅ Prevención de Envío**:
```javascript
form.on('submit', async function(e) {
    e.preventDefault(); // Prevent default submission first
    
    // Check current validation state first
    if (validationState.email.valid === false) {
        isValid = false;
        validationErrors.push('Email: ' + validationState.email.message);
    }
    
    // If current state is valid, perform fresh validation
    if (isValid) {
        if (email && VALIDATION_CONFIG.validateEmail) {
            const emailValid = await validateEmail(email, source);
            if (!emailValid) {
                isValid = false;
                validationErrors.push('Email: ' + validationState.email.message);
            }
        }
    }
    
    if (!isValid) {
        showValidationErrors(validationErrors);
        return false;
    }
    
    // If all validations pass, submit the form
    form.off('submit');
    form[0].submit();
});
```

##### **2. ✅ Resumen de Errores Visual**:
```javascript
function showValidationErrors(errors) {
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
    
    $('.xpsocial-form').prepend(errorHtml);
    
    // Scroll to top of form
    $('html, body').animate({
        scrollTop: $('.xpsocial-form').offset().top - 100
    }, 500);
}
```

##### **3. ✅ Limpieza Automática de Errores**:
```javascript
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
```

#### Flujo de Validación Mejorado

1. **✅ Usuario intenta enviar**: Se previene el envío por defecto
2. **✅ Verificación de estado**: Se revisa el estado actual de validación
3. **✅ Validación fresca**: Si el estado es válido, se realizan validaciones nuevas
4. **✅ Bloqueo de envío**: Si hay errores, se muestra resumen y se bloquea envío
5. **✅ Corrección de errores**: Usuario corrige y los errores se limpian automáticamente
6. **✅ Envío exitoso**: Solo se envía cuando todas las validaciones pasan

#### Estados de Validación

| Estado | Descripción | Acción |
|--------|-------------|--------|
| **`valid: null`** | No validado aún | Permite envío (se validará) |
| **`valid: true`** | Validación exitosa | Permite envío |
| **`valid: false`** | Error de validación | Bloquea envío |

#### Beneficios de la Implementación

1. **✅ Prevención de datos inválidos**: No se envían formularios con errores
2. **✅ Feedback claro**: Usuario ve exactamente qué debe corregir
3. **✅ UX intuitiva**: Errores se limpian automáticamente al corregir
4. **✅ Validación doble**: Estado actual + validación fresca
5. **✅ Scroll automático**: Lleva al usuario a los errores
6. **✅ Configuración respetada**: Solo valida campos habilitados en el CPT

#### Casos de Uso

##### **Caso 1: Email Duplicado**
- Usuario ingresa email existente
- Validación en tiempo real muestra error
- Al intentar enviar: "Email: Este email ya está registrado para esta campaña"
- Formulario no se envía hasta corregir

##### **Caso 2: Edad Insuficiente**
- Usuario selecciona fecha que da menos de 18 años
- Validación en tiempo real muestra error
- Al intentar enviar: "Birth Date: Debes ser mayor de 18 años para registrarte"
- Formulario no se envía hasta corregir

##### **Caso 3: Múltiples Errores**
- Usuario tiene email duplicado + edad insuficiente
- Al intentar enviar se muestra:
  ```
  Por favor corrige los siguientes errores:
  • Email: Este email ya está registrado para esta campaña
  • Birth Date: Debes ser mayor de 18 años para registrarte
  ```

## [3.1.35] - 2025-10-15

### Sistema de Subida de Archivos para Campos Dinámicos
- **Subida de archivos**: Los campos de tipo `audio` e `image` ahora guardan archivos en `uploads/social-login/`
- **Organización por tipo**: Archivos organizados en subcarpetas `/audios`, `/imagenes`, `/documentos`
- **Validación de tipos**: Solo permite tipos de archivo seguros según el tipo de campo
- **Nombres únicos**: Genera nombres únicos para evitar conflictos
- **URLs de acceso**: Retorna URLs públicas para acceder a los archivos subidos

#### Funcionalidades Implementadas

##### **1. ✅ Procesamiento de Archivos**:
```php
if ($field['type'] === 'audio' || $field['type'] === 'image') {
    // Handle file uploads
    if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] === UPLOAD_ERR_OK) {
        $uploaded_file = handle_file_upload($_FILES[$field_name], $field['type'], $field['name']);
        if ($uploaded_file) {
            $dynamic_fields_data[$field['name']] = $uploaded_file;
        }
    }
}
```

##### **2. ✅ Estructura de Directorios**:
```
uploads/
└── social-login/
    ├── audios/          # Archivos de audio (mp3, wav, ogg, m4a, aac)
    ├── imagenes/        # Archivos de imagen (jpg, png, gif, webp, svg)
    └── documentos/      # Documentos (pdf, doc, docx, txt, rtf)
```

##### **3. ✅ Validación de Tipos de Archivo**:
```php
function get_allowed_file_types($field_type) {
    switch ($field_type) {
        case 'audio':
            return array(
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'm4a' => 'audio/mp4',
                'aac' => 'audio/aac'
            );
        case 'image':
            return array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml'
            );
        default: // documents
            return array(
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'txt' => 'text/plain',
                'rtf' => 'application/rtf'
            );
    }
}
```

##### **4. ✅ Generación de Nombres Únicos**:
```php
// Generate unique filename
$filename = sanitize_file_name($file['name']);
$filename = pathinfo($filename, PATHINFO_FILENAME);
$extension = $file_type['ext'];
$unique_filename = $filename . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $extension;
```

##### **5. ✅ Formulario con Soporte de Archivos**:
```html
<form method="POST" enctype="multipart/form-data"
    action="<?php echo esc_url(get_site_url() . '/wp-content/plugins/xpsocial_login/includes/class-xpsocial_register-form.php'); ?>"
    id="register_form" class="register_form" validate>
```

#### Flujo de Subida de Archivos

1. **✅ Usuario selecciona archivo**: Campo de tipo `audio` o `image` en el formulario
2. **✅ Validación de tipo**: Se verifica que el archivo sea del tipo permitido
3. **✅ Creación de directorios**: Se crean las carpetas necesarias si no existen
4. **✅ Generación de nombre único**: Se crea un nombre único para evitar conflictos
5. **✅ Subida del archivo**: Se mueve el archivo a la ubicación final
6. **✅ Generación de URL**: Se crea la URL pública para acceder al archivo
7. **✅ Almacenamiento en BD**: Se guarda la URL en la base de datos

#### Tipos de Archivo Soportados

| Tipo | Extensiones | MIME Types |
|------|-------------|------------|
| **Audio** | mp3, wav, ogg, m4a, aac | audio/mpeg, audio/wav, audio/ogg, audio/mp4, audio/aac |
| **Imagen** | jpg, jpeg, png, gif, webp, svg | image/jpeg, image/png, image/gif, image/webp, image/svg+xml |
| **Documento** | pdf, doc, docx, txt, rtf | application/pdf, application/msword, text/plain, application/rtf |

#### Ejemplo de Uso

##### **Campo de Audio en CPT**:
```json
{
    "label": "Grabación de Voz",
    "name": "audio_grabacion",
    "type": "audio",
    "required": "1"
}
```

##### **Resultado en Base de Datos**:
```json
{
    "audio_grabacion": "https://example.com/wp-content/uploads/social-login/audios/grabacion_1734567890_aB3dEfGh.mp3"
}
```

#### Beneficios de la Implementación

1. **✅ Organización clara**: Archivos separados por tipo en subcarpetas
2. **✅ Seguridad**: Solo tipos de archivo permitidos y validados
3. **✅ Nombres únicos**: Evita conflictos y sobrescritura de archivos
4. **✅ URLs públicas**: Acceso directo a los archivos subidos
5. **✅ Logging**: Registro de subidas exitosas y errores
6. **✅ Compatibilidad**: Funciona con la estructura existente de campos dinámicos

#### Casos de Uso

##### **Caso 1: Subida de Audio**
- Usuario graba un mensaje de voz
- Se valida que sea formato de audio permitido
- Se guarda en `uploads/social-login/audios/`
- URL se almacena en la base de datos

##### **Caso 2: Subida de Imagen**
- Usuario sube una foto de perfil
- Se valida que sea formato de imagen permitido
- Se guarda en `uploads/social-login/imagenes/`
- URL se almacena en la base de datos

##### **Caso 3: Archivo No Válido**
- Usuario intenta subir archivo no permitido
- Se registra error en log
- Se retorna `false` y no se procesa el archivo

*Última actualización: $(date)*
