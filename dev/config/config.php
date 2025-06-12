<?php



/*===============================================================================================*/
/* ENVIROMENTS VARIABLES ------------------------------------------------------------------------*/
/*===============================================================================================*/



/* ruta raiz del proyecto (gemi-bucket/) --------------------------------------------------------*/
define('APP_NAME', '/gemi-bucket'); //: /gemi-bucket
define('APP_DEPLOY', APP_NAME . '/public'); //: /gemi-bucket/public
define('APP_ROOT', dirname(__DIR__, 3) . APP_NAME);  //: ../htdocs/gemi-bucket
define('APP_ROOT_PUBLIC', dirname(__DIR__, 3) . APP_NAME . APP_DEPLOY);  //: ../htdocs/gemi-bucket/public
define('APP_DOMAIN', 'localhost');
define('APP_TIME_ZONE', 'America/El_Salvador'); //: America/El_Salvador



/* Define la URL raíz de la aplicación ----------------------------------------------------------*/
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
define('URL_ROOT', $scheme . '://' . APP_DOMAIN . APP_DEPLOY); //: http://localhost/gemi-bucket/public