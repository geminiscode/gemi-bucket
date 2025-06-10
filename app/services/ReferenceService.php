<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __INTERFACE_RESPONSES;



/*===============================================================================================*/
/* SERVICE --------------------------------------------------------------------------------------*/
/*===============================================================================================*/


 
/**
 * Class ReferenceService
 *
 * Este servicio se encarga de:
 * - Registrar referencias {hash, path} de archivos subidos
 * - Rotación automática de archivos JSON cuando exceden tamaño
 * - Búsqueda y eliminación eficiente de referencias
 */
class ReferenceService
{



    /**
     * Devuelve la ruta del último archivo de referencia disponible
     *
     * @param string $tenantHash Hash del inquilino
     * @return array ['success' => true|false, 'data' => ['file_path', 'references']]
     */
    public static function getLastReferenceFile(string $tenantHash)
    {
        $refDir = __STORAGE_DIR . '/' . $tenantHash . '/references/';

        if (!is_dir($refDir)) {
            return Response::error("Carpeta de referencias no encontrada: $refDir", null, 500);
        }

        $files = glob($refDir . 'ref_*.json');
        usort($files, function ($a, $b) {
            return filemtime($a) < filemtime($b);
        });

        $latestFile = !empty($files) ? end($files) : $refDir . 'ref_001.json';

        // Cargar contenido existente
        $references = is_file($latestFile) ? json_decode(file_get_contents($latestFile), true) ?? [] : [];

        return Response::success('Archivo de referencia cargado correctamente', [
            'file_path' => $latestFile,
            'references' => $references
        ]);
    }

    /**
     * Crea un nuevo archivo de referencia si el actual está lleno
     *
     * @param string $tenantHash Hash del inquilino
     * @param array $currentReferences Contenido actual del archivo de referencia
     * @param string $currentFilePath Ruta del archivo actual
     * @return array ['success' => true|false, 'data' => ['new_file_path', 'references']]
     */
    public static function createNewReferenceFileIfFull(string $tenantHash, array $currentReferences, string $currentFilePath)
    {
        if (filesize($currentFilePath) < MAX_REF_FILE_SIZE) {
            return Response::success('El archivo actual aún tiene espacio', [
                'new_file_path' => $currentFilePath,
                'references' => $currentReferences
            ]);
        }

        $refDir = dirname($currentFilePath);
        $existingFiles = glob($refDir . '/ref_*.json');

        // Encontrar el número siguiente
        $nextNumber = count($existingFiles) + 1;
        $newFileName = sprintf('%s/ref_%03d.json', $refDir, $nextNumber);

        // Guardar el nuevo archivo vacío
        file_put_contents($newFileName, json_encode([], JSON_PRETTY_PRINT));

        return Response::success('Nuevo archivo de referencia creado', [
            'new_file_path' => $newFileName,
            'references' => []
        ]);
    }

    /**
     * Agrega una nueva referencia de archivo ({hash, path})
     *
     * @param string $tenantHash Hash del inquilino
     * @param string $filePath Ruta relativa donde se guardó el archivo
     * @param string $fileHash Hash único del archivo
     * @return array ['success' => true|false, 'message' => string]
     */
    public static function addReference(string $tenantHash, string $filePath, string $fileHash)
    {
        // Obtener el último archivo de referencia
        $lastRef = self::getLastReferenceFile($tenantHash);

        if (!$lastRef['success']) {
            return $lastRef;
        }

        $refData = $lastRef['data'];
        $refPath = $refData['file_path'];
        $references = $refData['references'];

        // Verificar si ya existe el hash
        foreach ($references as $ref) {
            if ($ref['hash'] === $fileHash) {
                return Response::error("El hash '$fileHash' ya existe");
            }
        }

        // Añadir nueva referencia
        $references[] = [
            'hash' => $fileHash,
            'path' => $filePath
        ];

        // Verificar tamaño antes de guardar
        $result = self::createNewReferenceFileIfFull($tenantHash, $references, $refPath);

        if ($result['success']) {
            $newPath = $result['data']['new_file_path'];
            $refsToSave = $result['data']['references'];

            $json = json_encode($refsToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                return Response::error('Error al codificar JSON', json_last_error_msg());
            }

            if (file_put_contents($newPath, $json) === false) {
                return Response::error("No se pudo escribir en el archivo de referencias: $newPath");
            }

            return Response::success('Referencia agregada correctamente', [
                'reference_file' => basename($newPath),
                'file_hash' => $fileHash
            ]);
        }

        return Response::error('No se pudo crear un nuevo archivo de referencia', 'Límite de tamaño alcanzado');
    }

    /**
     * Busca un archivo por su hash en todas las referencias del inquilino
     *
     * @param string $tenantHash Hash del inquilino
     * @param string $fileHash Hash del archivo buscado
     * @return array ['success' => true|false, 'data' => ['path', 'hash'], ...]
     */
    public static function findReferenceByHash(string $tenantHash, string $fileHash)
    {
        $refDir = __STORAGE_DIR . '/' . $tenantHash . '/references/';
        $files = glob($refDir . 'ref_*.json');

        if (empty($files)) {
            return Response::error('No hay archivos de referencia disponibles');
        }

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true) ?? [];
            foreach ($content as $ref) {
                if ($ref['hash'] === $fileHash) {
                    return Response::success('Archivo encontrado', $ref);
                }
            }
        }

        return Response::error("Archivo con hash '$fileHash' no encontrado");
    }

    /**
     * Elimina una referencia de archivo
     *
     * @param string $tenantHash Hash del inquilino
     * @param string $fileHash Hash del archivo a eliminar
     * @return array ['success' => true|false, 'message' => string]
     */
    public static function deleteReference(string $tenantHash, string $fileHash)
    {
        $refDir = __STORAGE_DIR . '/' . $tenantHash . '/references/';
        $files = glob($refDir . 'ref_*.json');

        $found = false;

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true) ?? [];
            $newContent = [];

            foreach ($content as $ref) {
                if ($ref['hash'] === $fileHash) {
                    $found = true;
                    continue;
                }
                $newContent[] = $ref;
            }

            if ($found) {
                $json = json_encode($newContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                file_put_contents($file, $json);
                break;
            }
        }

        if (!$found) {
            return Response::error("Referencia con hash '$fileHash' no encontrada");
        }

        return Response::success("Referencia con hash '$fileHash' eliminada");
    }

    /**
     * Busca una referencia de archivo por su ruta dentro del espacio de un inquilino.
     *
     * Este método recorre todos los archivos JSON en la carpeta "references/" del inquilino
     * y busca si alguno contiene una entrada con el campo 'path' igual al proporcionado.
     *
     * @param string $tenantHash Hash único del inquilino (cliente)
     * @param string $filePath   Ruta relativa del archivo a buscar (ej. /imagenes/foto.jpg)
     *
     * @return array Devuelve una respuesta estandarizada:
     *               - Si se encontró el archivo:
     *                   [
     *                       'success' => true,
     *                       'message' => 'Archivo encontrado',
     *                       'data'    => ['hash' => 'abc123...', 'path' => '/imagenes/foto.jpg']
     *                   ]
     *               - Si no se encontró:
     *                   [
     *                       'success' => false,
     *                       'message' => "No se encontró ningún archivo en la ruta: $filePath"
     *                   ]
     *               - Si no hay archivos de referencia disponibles:
     *                   [
     *                       'success' => false,
     *                       'message' => 'No hay archivos de referencia disponibles'
     *                   ]
     */
    public static function findReferenceByPath(string $tenantHash, string $filePath)
    {
        $refDir = __STORAGE_DIR . '/' . $tenantHash . '/references/';
        $files = glob($refDir . 'ref_*.json');

        if (empty($files)) {
            return Response::error("No hay archivos de referencia disponibles");
        }

        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true) ?? [];
            foreach ($content as $ref) {
                if ($ref['path'] === $filePath) {
                    return Response::success("Archivo encontrado", $ref);
                }
            }
        }

        return Response::error("No se encontró ningún archivo en la ruta: $filePath");
    }
}
