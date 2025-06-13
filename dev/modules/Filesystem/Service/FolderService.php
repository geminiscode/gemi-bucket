<?php



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Clase que maneja todas las operaciones relacionadas con carpetas.
 */
class FolderService
{
    /**
     * Crea una carpeta si no existe.
     *
     * @param string $path Ruta de la carpeta a crear.
     * @return bool|string true si se creó correctamente, mensaje de error si falló.
     */
    public function create(string $path): bool|string
    {
        if (file_exists($path)) {
            return "La carpeta ya existe: $path";
        }

        if (!mkdir($path, 0777, true)) {
            return "No se pudo crear la carpeta: $path";
        }

        return true;
    }

    /**
     * Elimina una carpeta vacía.
     *
     * @param string $path Ruta de la carpeta a eliminar.
     * @return bool|string true si se eliminó correctamente, mensaje de error si falló.
     */
    public function delete(string $path): bool|string
    {
        if (!is_dir($path)) {
            return "Carpeta no encontrada: $path";
        }

        if (count(scandir($path)) > 2) {
            return "La carpeta no está vacía: $path";
        }

        if (!rmdir($path)) {
            return "No se pudo eliminar la carpeta: $path";
        }

        return true;
    }

    /**
     * Lista todos los elementos dentro de una carpeta.
     *
     * @param string $path Ruta de la carpeta.
     * @return array|false Lista de nombres de archivos/carpetas o false si falló.
     */
    public function listAll(string $path): array|false
    {
        if (!is_dir($path)) {
            return false;
        }

        $items = scandir($path);
        return array_values(array_filter($items, fn($item) => !in_array($item, ['.', '..'])));
    }

    /**
     * Busca archivos o carpetas que coincidan con un término dentro de una carpeta.
     *
     * @param string $path Ruta de la carpeta donde buscar.
     * @param string $term Término de búsqueda.
     * @return array Lista de resultados encontrados.
     */
    public function find(string $path, string $term): array
    {
        $results = [];
        foreach ($this->listAll($path) as $item) {
            if (stripos($item, $term) !== false) {
                $fullPath = "$path/$item";
                $results[] = [
                    'name' => $item,
                    'type' => is_dir($fullPath) ? 'folder' : 'file',
                    'path' => $fullPath
                ];
            }
        }
        return $results;
    }
}