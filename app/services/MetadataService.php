<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __INTERFACE_RESPONSES;



/*===============================================================================================*/
/* SERVICE --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Class MetadataService
 *
 * Este servicio se encarga de:
 * - Registrar metadatos originales de los archivos subidos
 * - Almacenarlos en la carpeta /metadata/ del inquilino
 * - Recuperarlos cuando sea necesario
 */
class MetadataService
{
    /**
     * Obtiene los metadatos básicos de un archivo desde $_FILES
     *
     * @param array $file Archivo recibido desde $_FILES
     * @return array Array con metadatos: nombre original, MIME type, tamaño, fecha
     */
    public static function getFileMetadata(array $file): array
    {
        return [
            'original_name' => $file['name'],
            'mime_type' => mime_content_type($file['tmp_name']),
            'size_bytes' => $file['size'],
            'uploaded_at' => date('c'), // Formato ISO 8601
        ];
    }

    /**
     * Guarda los metadatos de un archivo en su carpeta correspondiente
     *
     * @param string $tenantHash Hash del inquilino
     * @param string $fileHash   Hash del archivo
     * @param array  $metadata   Metadatos a guardar
     * @return array Respuesta estandarizada
     */
    public static function saveFileMetadata(string $tenantHash, string $fileHash, array $metadata): array
    {
        $metaDir = __STORAGE_DIR . '/' . $tenantHash . '/metadata/';
        if (!is_dir($metaDir)) {
            mkdir($metaDir, 0777, true);
        }

        $metaFile = $metaDir . "$fileHash.json";
        $json = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($metaFile, $json) === false) {
            return Response::error("No se pudo guardar metadata en: $metaFile");
        }

        return Response::success("Metadata guardada correctamente", ['file' => $metaFile]);
    }

    /**
     * Carga los metadatos asociados a un archivo
     *
     * @param string $tenantHash Hash del inquilino
     * @param string $fileHash   Hash del archivo
     * @return array Respuesta con metadata o error
     */
    public static function loadFileMetadata(string $tenantHash, string $fileHash): array
    {
        $metaFile = __STORAGE_DIR . '/' . $tenantHash . '/metadata/' . "$fileHash.json";

        if (!file_exists($metaFile)) {
            return Response::error("No se encontró metadata para el hash: $fileHash");
        }

        $content = json_decode(file_get_contents($metaFile), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return Response::error("Error al leer metadata del archivo: $metaFile");
        }

        return Response::success("Metadata cargada correctamente", $content);
    }
}
