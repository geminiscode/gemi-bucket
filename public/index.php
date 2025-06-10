<?php

// Cargar configuración global
require_once '../app/config/config.php';
require_once '../app/config/paths.php';
require_once __TENANTS_TENANTS;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload') {
        require_once __CONTROLLER_UPLOAD;
        UploadController::handleUpload();
        exit;
    }
}

echo "<pre>" . json_encode(TenantService::updateTenantMap()) . "</pre>";// Actualizar mapa de inquilinos al inicio

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gemi-Bucket - Prueba de Subida</title>
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

        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 15px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            margin-top: 15px;
            padding: 12px 20px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #218838;
        }

        .hash-list {
            max-width: 500px;
            margin: auto;
            text-align: left;
            margin-top: 20px;
        }

        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            overflow-x: auto;
            text-align: left;
            border-radius: 6px;
        }

        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .response {
            margin-top: 20px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            text-align: left;
        }

        .response pre {
            margin: 0;
        }
    </style>
</head>

<body>

    <h1>👋 Gemi-Bucket - Pruebas Locales</h1>

    <!-- Formulario de subida -->
    <form id="uploadForm" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">

        <label for="ruta">Ruta deseada (ej: /imagenes/foto.png)</label>
        <input type="text" id="ruta" name="ruta" placeholder="/imagenes/foto.png" required>

        <label for="archivo">Selecciona un archivo</label>
        <input type="file" id="archivo" name="archivo" required>

        <button type="submit">📤 Subir archivo</button>
    </form>

    <!-- Sección de hash almacenados -->
    <div class="hash-list">
        <h3>📜 Hashes guardados</h3>
        <select id="hashList" size="5" style="width: 100%;"></select>
        <p><small>Estos hashes están guardados en el localStorage del navegador.</small></p>
    </div>

    <!-- Respuesta del backend -->
    <div class="response" id="serverResponse"></div>

    <!-- Dialogo de decisión -->
    <div class="decision" id="decisionBox" style="display: none;">
        <h3>⚠️ Ya existe un archivo en esta ruta</h3>
        <p>¿Qué deseas hacer?</p>
        <form id="decisionForm">
            <input type="hidden" name="ruta" id="decisionRuta">
            <input type="hidden" name="tenant_hash" id="decisionTenantHash" value="d1292a5312c69108">
            <label><input type="radio" name="selected" value="1"> Omitir</label><br>
            <label><input type="radio" name="selected" value="2"> Sobreescribir</label><br>
            <label><input type="radio" name="selected" value="3"> Reemplazar (nuevo hash)</label><br><br>
            <button type="submit">Aceptar</button>
        </form>
    </div>

    <script>
        const form = document.getElementById('uploadForm');
        const serverResponse = document.getElementById('serverResponse');
        const decisionBox = document.getElementById('decisionBox');
        const decisionForm = document.getElementById('decisionForm');
        const decisionRuta = document.getElementById('decisionRuta');
        let currentFilePath = '';
        let currentTenantHash = '';

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            serverResponse.innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            if (data.status === 2 && data.options) {
                currentFilePath = formData.get('ruta');
                currentTenantHash = formData.get('tenant_hash') || 'd1292a5312c69108';
                decisionRuta.value = currentFilePath;
                decisionBox.style.display = 'block';
            } else if (data.success && data.data?.hash) {
                const stored = JSON.parse(localStorage.getItem('gemiBucketHashes') || '[]');
                stored.push({
                    hash: data.data.hash,
                    path: data.data.path
                });
                localStorage.setItem('gemiBucketHashes', JSON.stringify(stored));
                loadHashes();
            }
        });

        decisionForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const selected = document.querySelector('input[name="selected"]:checked').value;
            const formData = new FormData();
            formData.append('action', 'upload');
            formData.append('selected', selected);
            formData.append('ruta', currentFilePath);
            formData.append('domain', 'http://localhost'); // Simulamos dominio
            formData.append('archivo', form.querySelector('[type="file"]').files[0]);

            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            serverResponse.innerHTML = '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
            decisionBox.style.display = 'none';

            if (result.selected === '2' || result.selected === '3') {
                const stored = JSON.parse(localStorage.getItem('gemiBucketHashes') || '[]');
                stored.push({
                    hash: result.hash,
                    path: result.path
                });
                localStorage.setItem('gemiBucketHashes', JSON.stringify(stored));
                loadHashes();
            }
        });

        function loadHashes() {
            const stored = JSON.parse(localStorage.getItem('gemiBucketHashes') || '[]');
            const hashList = document.getElementById('hashList');
            hashList.innerHTML = '';
            stored.forEach((hashData, index) => {
                const option = document.createElement('option');
                option.value = hashData.hash;
                option.textContent = `${hashData.hash} → ${hashData.path}`;
                hashList.appendChild(option);
            });
        }

        loadHashes();
    </script>

</body>

</html>