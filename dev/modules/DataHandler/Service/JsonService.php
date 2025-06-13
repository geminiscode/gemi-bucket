<?php

/**
 * Servicio para leer, buscar, actualizar y guardar archivos JSON.
 *
 * Este servicio permite:
 * - Leer contenido de un archivo JSON
 * - Buscar valores recursivamente por clave o valor
 * - Actualizar campos específicos usando rutas en notación de puntos (dot notation)
 * - Eliminar campos por ruta anidada
 * - Insertar elementos en listas (arrays) dentro del JSON
 */
class JsonService
{
    /**
     * Lee un archivo JSON y devuelve su contenido o un valor específico.
     *
     * @param string $filePath Ruta del archivo JSON.
     * @param string|null $path Ruta en notación de puntos (ej: "user.preferences.theme") para obtener un valor específico.
     * @return array|mixed Devuelve el contenido completo como array si no se especifica $path,
     *                    o el valor encontrado en la ruta específica. Retorna false si falla la lectura.
     */
    public function read(string $filePath, ?string $path = null): mixed
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if ($path === null) {
            return $data;
        }

        return $this->getValueByPath($data, $this->parsePath($path));
    }

    /**
     * Guarda datos actualizados en un archivo JSON.
     *
     * @param string $filePath Ruta del archivo donde se guardará el contenido.
     * @param array $data Datos a guardar en formato array asociativo.
     * @param int $onConflict Acción a tomar si ya existe el archivo:
     *                      0 => omitir, 1 => sobreescribir, 2 => mantener ambos con numeración.
     * @return bool|string Devuelve true si se guardó correctamente, o un mensaje de error si falló.
     */
    public function write(string $filePath, array $data, int $onConflict = 1): bool|string
    {
        if (file_exists($filePath)) {
            switch ($onConflict) {
                case 0:
                    return "Operación omitida: El archivo ya existe";
                case 1:
                    break; // Sobrescribir
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

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return "Error codificando JSON: " . json_last_error_msg();
        }

        if (file_put_contents($filePath, $encoded) === false) {
            return "No se pudo escribir el archivo";
        }

        return true;
    }

    /**
     * Busca un valor en el JSON recursivamente por clave.
     *
     * @param array $data Datos donde buscar.
     * @param string $key Clave a buscar.
     * @return mixed|null Valor encontrado o null si no se encontró.
     */
    public function find(array $data, string $key): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        foreach ($data as $value) {
            if (is_array($value)) {
                $result = $this->find($value, $key);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Busca recursivamente un valor específico dentro del JSON.
     *
     * @param array $data Datos donde buscar.
     * @param mixed $value Valor a encontrar.
     * @return array Lista de caminos (como arrays) que apuntan al valor encontrado.
     */
    public function findByValue(array $data, mixed $value): array
    {
        $results = [];
        $this->searchByValueRecursive($data, $value, [], $results);
        return $results;
    }

    /**
     * Función auxiliar recursiva para buscar un valor dentro del JSON.
     *
     * @param array $data Datos actuales.
     * @param mixed $value Valor a encontrar.
     * @param array $path Camino actual hasta este punto.
     * @param array &$results Arreglo donde se almacenan los resultados.
     */
    private function searchByValueRecursive(array $data, mixed $value, array $path, array &$results)
    {
        foreach ($data as $key => $val) {
            $currentPath = array_merge($path, [$key]);

            if ($val === $value) {
                $results[] = $currentPath;
            }

            if (is_array($val)) {
                $this->searchByValueRecursive($val, $value, $currentPath, $results);
            }
        }
    }

    /**
     * Actualiza un campo específico en el JSON usando una ruta anidada.
     *
     * @param array $data Datos actuales.
     * @param string $path Ruta en notación de puntos (ej: "user.preferences.theme").
     * @param mixed $newValue Nuevo valor a establecer.
     * @return array Datos actualizados.
     */
    public function update(array $data, string $path, mixed $newValue): array
    {
        return $this->setValueByPath($data, $this->parsePath($path), $newValue);
    }

    /**
     * Elimina un campo del JSON usando una ruta anidada.
     *
     * @param array $data Datos actuales.
     * @param string $path Ruta en notación de puntos (ej: "user.preferences.theme").
     * @return array Datos actualizados.
     */
    public function delete(array $data, string $path): array
    {
        return $this->deleteValueByPath($data, $this->parsePath($path));
    }

    /**
     * Inserta un elemento en una lista (array) en una ruta específica.
     *
     * @param array $data Datos actuales.
     * @param string $path Ruta en notación de puntos (ej: "user.hobbies").
     * @param mixed $value Valor a insertar.
     * @return array Datos actualizados.
     */
    public function push(array $data, string $path, mixed $value): array
    {
        $pathArray = $this->parsePath($path);
        $key = array_pop($pathArray);

        // Navegar hasta la posición correcta
        $current = &$data;
        foreach ($pathArray as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        if (!isset($current[$key]) || !is_array($current[$key])) {
            $current[$key] = [];
        }

        $current[$key][] = $value;

        return $data;
    }

    /**
     * Convierte una ruta en notación de puntos a un array de claves.
     *
     * @param string $path Ruta en notación de puntos (ej: "user.preferences.theme").
     * @return array Array de claves (ej: ['user', 'preferences', 'theme']).
     */
    private function parsePath(string $path): array
    {
        return explode('.', $path);
    }

    /**
     * Obtiene un valor desde una ruta anidada.
     *
     * @param array $data Datos actuales.
     * @param array $path Array de claves representando la ruta.
     * @return mixed|null Valor encontrado o null si no existe.
     */
    private function getValueByPath(array $data, array $path): mixed
    {
        $key = array_shift($path);

        if ($key === null) {
            return $data;
        }

        if (!isset($data[$key])) {
            return null;
        }

        if (empty($path)) {
            return $data[$key];
        }

        if (!is_array($data[$key])) {
            return null;
        }

        return $this->getValueByPath($data[$key], $path);
    }

    /**
     * Establece un valor en una ruta anidada.
     *
     * @param array $data Datos actuales.
     * @param array $path Array de claves representando la ruta.
     * @param mixed $value Valor a establecer.
     * @return array Datos actualizados.
     */
    private function setValueByPath(array $data, array $path, mixed $value): array
    {
        $key = array_shift($path);

        if ($key === null) {
            return $data;
        }

        if (empty($path)) {
            $data[$key] = $value;
        } else {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                $data[$key] = [];
            }
            $data[$key] = $this->setValueByPath($data[$key], $path, $value);
        }

        return $data;
    }

    /**
     * Elimina un valor en una ruta anidada.
     *
     * @param array $data Datos actuales.
     * @param array $path Array de claves representando la ruta.
     * @return array Datos actualizados.
     */
    private function deleteValueByPath(array $data, array $path): array
    {
        $key = array_shift($path);

        if ($key === null || !isset($data[$key])) {
            return $data;
        }

        if (empty($path)) {
            unset($data[$key]);
        } else {
            if (is_array($data[$key])) {
                $data[$key] = $this->deleteValueByPath($data[$key], $path);
            }
        }

        return $data;
    }
}