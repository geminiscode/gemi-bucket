<?php



/*===============================================================================================*/
/* ENVIROMENTS PATHS (FOLDERS) ------------------------------------------------------------------*/
/*===============================================================================================*/



// DEV ----------------------------------------*/



define('__DEV_INTERFACES', APP_ROOT . '/dev/interfaces');
define('__DEV_MODULES', APP_ROOT . '/dev/modules');
define('__DEV_MODULES_ROUTER', __DEV_MODULES . '/Router');



// APP ----------------------------------------*/




/*===============================================================================================*/
/* ENVIROMENTS PATHS (FILES) --------------------------------------------------------------------*/
/*===============================================================================================*/



// DEV ----------------------------------------*/



define('__DEV_FILE_MODULES_ROUTER', __DEV_MODULES_ROUTER . '/Router.php');
define('__DEV_FILE_MODULES_MATCHER', __DEV_MODULES_ROUTER . '/Service/RouteMatcher.php');
define('__DEV_FILE_MODULES_EXCEPTION', __DEV_MODULES_ROUTER . '/Exception/RouteNotFoundException.php');

