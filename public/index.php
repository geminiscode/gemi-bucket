<?php

// Cargar configuración global
require_once '../app/config/config.php';
require_once '../app/interfaces/responses.php';

// Solo ejecutar si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../app/services/TenantService.php';

    $result = TenantService::updateTenantMap();

    echo '<pre>';
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo '</pre>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gemi-Bucket - Inicio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #f4f4f4;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        button {
            padding: 12px 24px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        pre {
            margin-top: 30px;
            background-color: #272822;
            color: #f8f8f2;
            padding: 20px;
            border-radius: 8px;
            max-width: 90%;
            overflow-x: auto;
            text-align: left;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <h1>👋 Bienvenido a Gemi-Bucket</h1>
    <p>Haz clic en el botón para generar o actualizar el mapa de inquilinos.</p>

    <form method="post">
        <button type="submit">🔄 Generar/Actualizar Mapa de Inquilinos</button>
    </form>

</body>

</html>