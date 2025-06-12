<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __DIR__ . '/Service/FileService.php';
require_once __DIR__ . '/Service/FolderService.php';



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Clase principal del módulo Filesystem.
 * Proporciona una interfaz limpia para operaciones de archivos y carpetas.
 */
class Filesystem
{
    private FileService $fileService;
    private FolderService $folderService;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->folderService = new FolderService();
    }

    /**
     * Crea un archivo con contenido dado.
     *
     * @param string $filePath Ruta completa del archivo a crear.
     * @param string $content Contenido a escribir en el archivo.
     * @param int $onConflict Acción a tomar si el archivo ya existe:
     *                        0 => omitir, 1 => sobreescribir, 2 => mantener ambos.
     * @return bool|string true si se creó correctamente, mensaje de error si falló.
     */
    public function createFile(string $filePath, string $content, int $onConflict = 1): bool|string
    {
        return $this->fileService->write($filePath, $content, $onConflict);
    }

    /**
     * Lee el contenido de un archivo.
     *
     * @param string $filePath Ruta del archivo a leer.
     * @return string|bool Contenido del archivo o mensaje de error.
     */
    public function readFile(string $filePath): string|bool
    {
        return $this->fileService->read($filePath);
    }

    /**
     * Elimina un archivo.
     *
     * @param string $filePath Ruta del archivo a eliminar.
     * @return bool|string true si se eliminó correctamente, mensaje de error si falló.
     */
    public function deleteFile(string $filePath): bool|string
    {
        return $this->fileService->delete($filePath);
    }

    /**
     * Mueve un archivo de una ubicación a otra.
     *
     * @param string $from Ruta original del archivo.
     * @param string $to Nueva ruta destino del archivo.
     * @param int $onConflict Acción a tomar si ya existe un archivo en destino:
     *                      0 => omitir, 1 => reemplazar, 2 => mantener ambos.
     * @return bool|string true si se movió correctamente, mensaje de error si falló.
     */
    public function moveFile(string $from, string $to, int $onConflict = 1): bool|string
    {
        return $this->fileService->move($from, $to, $onConflict);
    }

    /**
     * Obtiene información sobre un archivo.
     *
     * @param string $filePath Ruta del archivo.
     * @return array|false Información del archivo (nombre, tamaño, tipo, etc.) o false si no existe.
     */
    public function fileInfo(string $filePath): array|false
    {
        return $this->fileService->info($filePath);
    }

    // --- Métodos para carpetas ---

    /**
     * Crea una carpeta si no existe.
     *
     * @param string $path Ruta de la carpeta a crear.
     * @return bool|string true si se creó correctamente, mensaje de error si falló.
     */
    public function createFolder(string $path): bool|string
    {
        return $this->folderService->create($path);
    }

    /**
     * Elimina una carpeta vacía.
     *
     * @param string $path Ruta de la carpeta a eliminar.
     * @return bool|string true si se eliminó correctamente, mensaje de error si falló.
     */
    public function deleteFolder(string $path): bool|string
    {
        return $this->folderService->delete($path);
    }

    /**
     * Lista todos los elementos dentro de una carpeta.
     *
     * @param string $path Ruta de la carpeta.
     * @return array|false Lista de nombres de archivos/carpetas o false si falló.
     */
    public function listAll(string $path): array|false
    {
        return $this->folderService->listAll($path);
    }

    /**
     * Busca archivos o carpetas que coincidan con un término dentro de una carpeta.
     *
     * @param string $path Ruta de la carpeta donde buscar.
     * @param string $term Término de búsqueda.
     * @return array Lista de resultados encontrados.
     */
    public function findInFolder(string $path, string $term): array
    {
        return $this->folderService->find($path, $term);
    }
}