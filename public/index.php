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

// Definimos la base URL (lo que viene después del dominio)
$baseUrl = '/gemi-bucket/public'; // ← Esto es importante para que limpie bien las rutas

// Dominios permitidos para CORS (opcional, para desarrollo local puedes dejar vacío)
$allowedDomains = ['http://localhost'];

// Instanciamos el router
$router = new Router($baseUrl, $allowedDomains);

// Definimos algunas rutas de prueba
$router->get('/', function () {
    //echo json_encode(['message' => 'Bienvenido al índice']);
    echo json_encode([
    'server_time' => date('Y-m-d H:i:s'),
    'timezone' => date_default_timezone_get()
]);
});

$router->get('/hello', function () {
    echo json_encode(['message' => 'Hola desde hello']);
});

$router->get('/user/{id}', function ($params) {
    echo json_encode(['message' => 'Usuario ID: ' . $params['id']]);
});

$router->post('/submit', function () {
    echo json_encode(['message' => 'Datos recibidos', 'data' => $_POST]);
});

// Middleware simple de ejemplo
$authMiddleware = function ($params) {
    if ($_SERVER['HTTP_X_API_KEY'] ?? '' === 'secret123') {
        return true;
    }
    return 'Acceso denegado: API Key inválida';
};

$router->get('/secure', function () {
    echo json_encode(['message' => 'Acceso seguro concedido']);
}, $authMiddleware);





$auth = new Auth('tu-clave-secreta-muy-fuerte');
$auth->setTokenTTL(3600); // 1 hora


$router->post('/login', function () use ($auth) {
    // Aquí iría tu lógica de autenticación real
    $user = ['id' => 1, 'username' => 'johndoe'];

    $token = $auth->generate([
        'user' => $user,
        'role' => 'user'
    ]);

    echo json_encode(['token' => $token]);
});

$router->get('/protected', function () {
    $payload = $_SERVER['JWT_PAYLOAD'] ?? [];

    echo json_encode([
        'message' => 'Acceso autorizado',
        'user' => $payload['user']['username'] ?? 'unknown',
        'exp' => date('Y-m-d H:i:s', $_SERVER['JWT_PAYLOAD']['exp'] ?? 0)
    ]);
}, $auth->middleware());




$fs = new Filesystem();

// Crear carpeta
$result = $fs->createFolder(__DIR__ . '/archive/8523ab8065a69338/files/test');
if ($result === true) {
    echo json_encode("Carpeta creada correctamente");
    echo "<br><br>";
} else {
    echo json_encode($result);
    echo "<br><br>";
}


// Subir archivo con opciones
$content = "Contenido de prueba";
$result = $fs->createFile(__DIR__ . '/archive/8523ab8065a69338/files/test/file.txt', $content, 1); // 2: mantener ambos
if ($result === true) {
    echo json_encode("Archivo creado correctamente");
    echo "<br><br>";
} else {
    echo json_encode($result);
    echo "<br><br>";
}

// Mover archivo con opción de conflicto
$result = $fs->moveFile(
    __DIR__ . '/archive/8523ab8065a69338/files/test/file.txt',
    __DIR__ . '/archive/8523ab8065a69338/files/new_folder/file.txt',
    1 // 1: reescribir si ya existe
);

if ($result === true) {
    echo json_encode("Archivo movido correctamente");
    echo "<br><br>";
} else {
    echo json_encode($result);
    echo "<br><br>";
}

// Eliminar archivo
$result = $fs->deleteFile( __DIR__ . '/archive/8523ab8065a69338/files/new_folder/file.txt');
if ($result === true) {
    echo json_encode("Archivo eliminado correctamente");
    echo "<br><br>";
} else {
    echo json_encode($result);
    echo "<br><br>";
}




// Finalmente, lanzamos el router
$router->dispatch();