<?php
// public/index.php

require_once '../dev/config/config.php';
require_once '../dev/config/paths.php';


// Incluye autoloader o require directos si no usas Composer
require_once '../dev/modules/Router/Router.php';
require_once '../dev/modules/Router/Exception/RouteNotFoundException.php';
require_once '../dev/modules/Router/Service/RouteMatcher.php';
require_once '../dev/modules/Auth/Auth.php';
require_once '../dev/modules/Filesystem/Service/FileService.php';
require_once '../dev/modules/Filesystem/Service/FolderService.php';
require_once '../dev/modules/Filesystem/Filesystem.php';
require_once '../dev/modules/DataHandler/DataHandler.php';
require_once '../dev/modules/Hasher/Hasher.php';



function setClientTimezone()
{
    $tzHeader = $_SERVER['HTTP_X_TIMEZONE'] ?? null;

    if ($tzHeader && in_array($tzHeader, DateTimeZone::listIdentifiers())) {
        date_default_timezone_set($tzHeader);
    } else {
        // Fallback a UTC o a la del servidor
        date_default_timezone_set(APP_TIME_ZONE);
    }
}
setClientTimezone();



// Inicializar hasher
$hasher = new Hasher();

// Generar hash simple
$password = 'miContraseñaSegura';
$hash = $hasher->hash()->sha256($password);
echo "Hash SHA-256: $hash\n<br><br>";

// Generar hash con salt
$salt = $hasher->hash()->generateSalt();
$hashWithSalt = $hasher->hash()->sha256($password, $salt);
echo "Hash con salt: $hashWithSalt\n<br><br>";

// Verificar hash
$valid = $hasher->hash()->verifySha256($password, $hashWithSalt, $salt);
echo "Verificación: " . ($valid ? "✅ Válido" : "❌ Inválido") . "\n<br><br>";

// Generar UUID v4
$uuid = $hasher->hash()->uuid4();
echo "UUID v4 generado: $uuid\n<br><br>";