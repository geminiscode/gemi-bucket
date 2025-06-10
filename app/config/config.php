<?php



/*===============================================================================================*/
/* ENVIROMENTS VARIABLES ------------------------------------------------------------------------*/
/*===============================================================================================*/



/* ruta raiz del proyecto (gemi-bucket/) --------------------------------------------------------*/
define('APP_ROOT', dirname(__DIR__, 2));  // Dos niveles arriba: gemi-bucket/



// Límite por archivo de referencia (1MB) -------------------------------------------------------*/
define('MAX_REF_FILE_SIZE', 1048576);



// Permisos por defecto -------------------------------------------------------------------------*/
define('DEFAULT_PERMISSIONS', [
    'max_file_size' => 10485760, // 10MB
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'video/mp4', 'application/pdf'],
]);