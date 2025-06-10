<?php

require_once __DIR__ . '/../interfaces/responses.php';
require_once __DIR__ . '/../services/TenantService.php';
require_once __DIR__ . '/../services/ReferenceService.php';

class UploadController
{
    /**
     * Procesa una petición POST para subir un archivo
     */
    public static function handleUpload()
    {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(Response::error('Método no permitido', null, 405));
            return;
        }

        // Verificar si se envió un dominio
        $originDomain = $_SERVER['HTTP_ORIGIN'] ?? $_POST['domain'] ?? null;

        if (!$originDomain) {
            echo json_encode(Response::error('Dominio no especificado', null, 400));
            return;
        }

        // Cargar mapa de inquilinos
        $rawMap = file_get_contents(TENANT_MAP_FILE);
        $tenantMap = json_decode($rawMap, true);

        if (!isset($tenantMap[$originDomain])) {
            echo json_encode(Response::error("Dominio no autorizado: $originDomain", null, 403));
            return;
        }

        $tenantHash = $tenantMap[$originDomain]['hash'];
        $tenantPath = STORAGE_PATH . '/' . $tenantHash;

        // Decisión tomada por el cliente (si aplica)
        $selectedOption = $_POST['selected'] ?? null;
        $requestedPath = trim($_POST['ruta'] ?? '', '/');

        // Si hay una decisión pendiente, procesarla
        if ($selectedOption !== null && !empty($requestedPath)) {
            self::processSelectedOption($tenantHash, $tenantPath, $requestedPath, $selectedOption);
            return;
        }

        // Verificar si hay archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(Response::error('No se recibió un archivo válido', null, 400));
            return;
        }

        $uploadedFile = $_FILES['archivo'];
        $requestedPath = trim($_POST['ruta'] ?? '', '/') ?: uniqid('upload_');

        // Validar tipo y tamaño del archivo
        $permissionsFile = "$tenantPath/config/permissions.json";
        $permissions = json_decode(file_get_contents($permissionsFile), true);

        $maxFileSize = $permissions['max_file_size'] ?? DEFAULT_PERMISSIONS['max_file_size'];
        $allowedMimeTypes = $permissions['allowed_mime_types'] ?? DEFAULT_PERMISSIONS['allowed_mime_types'];

        if ($uploadedFile['size'] > $maxFileSize) {
            echo json_encode(Response::error('Archivo excede el tamaño máximo permitido', null, 413));
            return;
        }

        $fileMimeType = mime_content_type($uploadedFile['tmp_name']);
        if (!in_array($fileMimeType, $allowedMimeTypes)) {
            echo json_encode(Response::error("Tipo de archivo no permitido: $fileMimeType", null, 415));
            return;
        }

        // Buscar si ya existe un archivo en esta ruta
        $existingRef = ReferenceService::findReferenceByPath($tenantHash, $requestedPath);

        if ($existingRef['success']) {
            // Ya existe un archivo en esta ruta → mostrar opciones
            $options = [
                ['id' => 1, 'label' => 'Omitir (no hacer nada)'],
                ['id' => 2, 'label' => 'Sobreescribir (conservar el mismo hash)'],
                ['id' => 3, 'label' => 'Reemplazar (crear nuevo hash)'],
            ];

            echo json_encode(Response::warning(
                "Ya existe un archivo en la ruta '$requestedPath'",
                $options,
                ['current_hash' => $existingRef['data']['hash'], 'path' => $requestedPath]
            ));
            return;
        }

        // Generar hash único para el archivo
        $fileHash = self::generateFileHash($uploadedFile['name']);

        // Crear ruta física final
        $finalPath = "$tenantPath/files/" . ltrim($requestedPath, '/');
        $finalDir = dirname($finalPath);

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0777, true);
        }

        // Mover archivo a destino final
        if (!move_uploaded_file($uploadedFile['tmp_name'], $finalPath)) {
            echo json_encode(Response::error("No se pudo guardar el archivo en: $finalPath", null, 500));
            return;
        }

        // Registrar referencia
        $referenceResult = ReferenceService::addReference($tenantHash, $requestedPath, $fileHash);

        if (!$referenceResult['success']) {
            unlink($finalPath); // Eliminar archivo si falló registro de referencia
            echo json_encode($referenceResult);
            return;
        }

        // Responder con éxito
        echo json_encode(Response::success('Archivo subido correctamente', [
            'hash' => $fileHash,
            'path' => $requestedPath,
            'url' => "/storage/$tenantHash/files/" . basename($finalPath)
        ]));
    }

    /**
     * Genera un hash único para identificar el archivo
     *
     * @param string $originalName Nombre original del archivo
     * @return string Hash SHA-256
     */
    private static function generateFileHash(string $originalName): string
    {
        return hash('sha256', $originalName . uniqid('', true));
    }

    private static function processSelectedOption(string $tenantHash, string $tenantPath, string $requestedPath, string $selectedOption)
    {
        $existingRef = ReferenceService::findReferenceByPath($tenantHash, $requestedPath);

        if (!$existingRef['success']) {
            echo json_encode(Response::error("No se encontró el archivo en la ruta '$requestedPath'"));
            return;
        }

        switch ($selectedOption) {
            case 1:
                // Omitir
                echo json_encode(Response::success('Subida omitida por decisión del usuario'));
                return;

            case 2:
                // Sobreescribir (mismo hash)
                $fileHash = $existingRef['data']['hash'];
                break;

            case 3:
                // Reemplazar (nuevo hash)
                ReferenceService::deleteReference($tenantHash, $existingRef['data']['hash']);
                $fileHash = self::generateFileHash($_FILES['archivo']['name']);
                break;

            default:
                echo json_encode(Response::error("Opción inválida seleccionada"));
                return;
        }

        // Si es sobreescribir o reemplazar, continuar con la subida
        if ($selectedOption === '2' || $selectedOption === '3') {
            $uploadedFile = $_FILES['archivo'];
            $finalPath = "$tenantPath/files/" . ltrim($requestedPath, '/');

            if (!move_uploaded_file($uploadedFile['tmp_name'], $finalPath)) {
                echo json_encode(Response::error("No se pudo guardar el archivo en: $finalPath", null, 500));
                return;
            }

            // Actualizar o crear nueva referencia
            if ($selectedOption === '3') {
                $referenceResult = ReferenceService::addReference($tenantHash, $requestedPath, $fileHash);
            } else {
                $referenceResult = Response::success('Archivo sobrescrito exitosamente');
            }

            echo json_encode([
                ...$referenceResult,
                'selected' => $selectedOption,
                'hash' => $fileHash,
                'path' => $requestedPath
            ]);
        }
    }


    
}