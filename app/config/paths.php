<?php



/*===============================================================================================*/
/* ENVIROMENTS PATHS ----------------------------------------------------------------------------*/
/*===============================================================================================*/



// constrollers ---------------------------------------------------------------------------------*/
define('__CONTROLLER_UPLOAD', APP_ROOT . '/app/controllers/UploadController.php');  // Ahora será: gemi-bucket/storage



// interfaces -----------------------------------------------------------------------------------*/
define('__INTERFACE_RESPONSES', APP_ROOT . '/app/interfaces/responses.php');



// services -----------------------------------------------------------------------------------*/
define('__SERVICE_REFERENCE', APP_ROOT . '/app/services/ReferenceService.php');
define('__SERVICE_TENANT', APP_ROOT . '/app/services/TenantService.php');
define('__SERVICE_HASH', APP_ROOT . '/app/services/HashService.php');
define('__SERVICE_METADATA', APP_ROOT . '/app/services/MetadataService.php');


// tenants -----------------------------------------------------------------------------------*/
define('__TENANTS_TENANTS', APP_ROOT . '/app/tenants/tenants.json');
define('__TENANTS_TENANTS_MAP', APP_ROOT . '/app/tenants/tenants_map.json');



// tenants -----------------------------------------------------------------------------------*/
define('__STORAGE_DIR', APP_ROOT . '/app/storage');  // Ahora será: gemi-bucket/storage


