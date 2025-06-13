<?php



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Clase que maneja todas las operaciones relacionadas con archivos.
 */
class FileService
{
    /**
     * Lee el contenido de un archivo.
     *
     * @param string $filePath Ruta del archivo a leer.
     * @return string|bool Contenido del archivo o mensaje de error.
     */
    public function read(string $filePath): string|bool
    {
        if (!file_exists($filePath)) {
            return "Archivo no encontrado: $filePath";
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return "No se pudo leer el archivo: $filePath";
        }

        return $content;
    }

    /**
     * Escribe o crea un archivo.
     *
     * @param string $filePath Ruta del archivo a crear o sobrescribir.
     * @param string $content Contenido a escribir.
     * @param int $onConflict Acción a tomar si el archivo ya existe:
     *                        0 => omitir, 1 => sobreescribir, 2 => mantener ambos.
     * @return bool|string true si se escribió correctamente, mensaje de error si falló.
     */
    public function write(string $filePath, string $content, int $onConflict = 1): bool|string
    {
        if (file_exists($filePath)) {
            switch ($onConflict) {
                case 0:
                    return "Operación omitida: El archivo ya existe en $filePath";

                case 1:
                    if (!unlink($filePath)) {
                        return "No se pudo sobreescribir el archivo en $filePath";
                    }
                    break;

                case 2:
                    $dir = dirname($filePath);
                    $filename = pathinfo($filePath, PATHINFO_FILENAME);
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    $i = 1;
                    $newFilePath = "$dir/$filename($i).$extension";
                    while (file_exists($newFilePath)) {
                        $i++;
                        $newFilePath = "$dir/$filename($i).$extension";
                    }
                    $filePath = $newFilePath;
                    break;
            }
        }

        if (file_put_contents($filePath, $content) === false) {
            return "No se pudo escribir el archivo en $filePath";
        }

        return true;
    }

    /**
     * Elimina un archivo.
     *
     * @param string $filePath Ruta del archivo a eliminar.
     * @return bool|string true si se eliminó correctamente, mensaje de error si falló.
     */
    public function delete(string $filePath): bool|string
    {
        if (!file_exists($filePath)) {
            return "Archivo no encontrado: $filePath";
        }

        if (!unlink($filePath)) {
            return "No se pudo eliminar el archivo: $filePath";
        }

        return true;
    }

    /**
     * Mueve o renombra un archivo.
     *
     * @param string $from Ruta original del archivo.
     * @param string $to Nueva ruta destino del archivo.
     * @param int $onConflict Acción a tomar si ya existe un archivo en destino:
     *                      0 => omitir, 1 => reemplazar, 2 => mantener ambos.
     * @return bool|string true si se movió correctamente, mensaje de error si falló.
     */
    public function move(string $from, string $to, int $onConflict = 1): bool|string
    {
        if (!file_exists($from)) {
            return "Archivo no encontrado: $from";
        }

        if (file_exists($to)) {
            switch ($onConflict) {
                case 0:
                    return "Operación omitida: Ya existe un archivo en $to";

                case 1:
                    if (!unlink($to)) {
                        return "No se pudo reemplazar el archivo en $to";
                    }
                    break;

                case 2:
                    $dir = dirname($to);
                    $filename = pathinfo($to, PATHINFO_FILENAME);
                    $extension = pathinfo($to, PATHINFO_EXTENSION);
                    $i = 1;
                    $newTo = "$dir/$filename($i).$extension";
                    while (file_exists($newTo)) {
                        $i++;
                        $newTo = "$dir/$filename($i).$extension";
                    }
                    $to = $newTo;
                    break;
            }
        }

        // Asegurarse de que la carpeta destino exista
        $dirTo = dirname($to);
        if (!is_dir($dirTo)) {
            if (!mkdir($dirTo, 0777, true)) {
                return "No se pudo crear la carpeta destino: $dirTo";
            }
        }

        if (!rename($from, $to)) {
            return "No se pudo mover el archivo desde $from hasta $to";
        }

        return true;
    }

    /**
     * Obtiene información sobre un archivo.
     *
     * @param string $filePath Ruta del archivo.
     * @return array|false Información del archivo (nombre, tamaño, tipo, etc.) o false si no existe.
     */
    public function info(string $filePath): array|false
    {
        if (!file_exists($filePath)) {
            return false;
        }

        return [
            'filename' => basename($filePath),
            'path' => $filePath,
            'size' => filesize($filePath),
            'modified' => filemtime($filePath),
            'type' => mime_content_type($filePath) ?: 'unknown'
        ];
    }
}