<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __INTERFACE_RESPONSES;
require_once __SERVICE_TENANT;
require_once __SERVICE_REFERENCE;
require_once __SERVICE_METADATA;



/*===============================================================================================*/
/* CONTROLLER -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



class FileController
{
    /**
     * Maneja las peticiones GET, PUT y DELETE basadas en hash
     */
    public static function handleRequest()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if (!isset($_GET['hash'])) {
            echo json_encode(Response::error('Hash no especificado', null, 400));
            return;
        }

        $fileHash = $_GET['hash'];

        if (!HashService::isValidHash($fileHash)) {
            echo json_encode(Response::error("Formato de hash inválido", null, 400));
            return;
        }

        // Buscar a qué inquilino pertenece este hash
        $tenantResult = TenantService::findTenantByHash($fileHash);

        if (!$tenantResult['success']) {
            echo json_encode(Response::error("Archivo no encontrado", null, 404));
            return;
        }

        $tenantHash = $tenantResult['data']['tenant_hash'];
        $filePath = "$tenantResult[data][tenant_path]/files/$fileHash";

        switch ($method) {
            case 'GET':
                self::getFile($filePath, $fileHash, $tenantHash);
                break;

            case 'DELETE':
                self::deleteFile($filePath, $fileHash, $tenantHash);
                break;

            case 'PUT':
                parse_str(file_get_contents("php://input"), $putData);
                $newPath = $putData['ruta'] ?? null;
                self::updateFile($filePath, $fileHash, $tenantHash, $newPath);
                break;

            default:
                echo json_encode(Response::error("Método no permitido", null, 405));
                break;
        }
    }

    /**
     * Devuelve el archivo físico asociado al hash
     */
    private static function getFile(string $filePath, string $fileHash, string $tenantHash)
    {
        if (!file_exists($filePath)) {
            echo json_encode(Response::error("Archivo no encontrado", null, 404));
            return;
        }

        // Obtener metadatos
        $metadataResult = MetadataService::loadFileMetadata($tenantHash, $fileHash);
        $metadata = $metadataResult['success'] ? $metadataResult['data'] : [];

        // Enviar encabezados para descarga
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: inline; filename="' . ($metadata['original_name'] ?? $fileHash) . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
    }

    /**
     * Elimina el archivo y su referencia
     */
    private static function deleteFile(string $filePath, string $fileHash, string $tenantHash)
    {
        if (!file_exists($filePath)) {
            echo json_encode(Response::error("Archivo no encontrado", null, 404));
            return;
        }

        // Eliminar archivo físico
        if (!unlink($filePath)) {
            echo json_encode(Response::error("No se pudo eliminar el archivo", null, 500));
            return;
        }

        // Eliminar referencia
        $referenceResult = ReferenceService::deleteReference($tenantHash, $fileHash);
        if (!$referenceResult['success']) {
            echo json_encode($referenceResult);
            return;
        }

        echo json_encode(Response::success("Archivo eliminado correctamente"));
    }

    /**
     * Actualiza el contenido del archivo o su ubicación
     */
    private static function updateFile(string $filePath, string $fileHash, string $tenantHash, ?string $newPath)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            echo json_encode(Response::error("Método no permitido", null, 405));
            return;
        }

        if (!file_exists($filePath)) {
            echo json_encode(Response::error("Archivo no encontrado", null, 404));
            return;
        }

        // Reemplazar archivo
        $uploadedFile = $_FILES['archivo'] ?? null;

        if ($uploadedFile && $uploadedFile['error'] === UPLOAD_ERR_OK) {
            if (!move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                echo json_encode(Response::error("No se pudo actualizar el archivo", null, 500));
                return;
            }

            echo json_encode(Response::success("Archivo actualizado exitosamente"));
            return;
        }

        // Cambiar ruta del archivo
        if ($newPath) {
            $newPath = trim($newPath, '/');
            $newFilePath = dirname($filePath) . '/' . basename($newPath);

            if (!rename($filePath, $newFilePath)) {
                echo json_encode(Response::error("No se pudo mover el archivo", null, 500));
                return;
            }

            // Actualizar referencia
            $referenceResult = ReferenceService::updateReferencePath($tenantHash, $fileHash, $newPath);
            if (!$referenceResult['success']) {
                echo json_encode($referenceResult);
                return;
            }

            echo json_encode(Response::success("Ruta del archivo actualizada", [
                'old_path' => $filePath,
                'new_path' => $newFilePath
            ]));
            return;
        }

        echo json_encode(Response::error("No se especificó una acción válida", null, 400));
    }
}
