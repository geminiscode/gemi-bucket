<?php

// Ruta raíz del proyecto (gemi-bucket/)
define('APP_ROOT', dirname(__DIR__));  // Dos niveles arriba desde app/config/

// Rutas importantes
define('STORAGE_PATH', APP_ROOT . '/storage');  // Ahora será: gemi-bucket/storage
define('TENANTS_FILE', APP_ROOT . '/tenants/tenants.json');
define('TENANT_MAP_FILE', APP_ROOT . '/tenants/tenants_map.json');

// Límite por archivo de referencia (1MB)
define('MAX_REF_FILE_SIZE', 1048576);

// Permisos por defecto
define('DEFAULT_PERMISSIONS', [
    'max_file_size' => 10485760, // 10MB
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'video/mp4', 'application/pdf'],
]);
