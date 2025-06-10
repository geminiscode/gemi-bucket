<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __INTERFACE_RESPONSES;



/*===============================================================================================*/
/* SERVICE --------------------------------------------------------------------------------------*/
/*===============================================================================================*/


 
/**
 * Class TenantService
 *
 * Este servicio se encarga de gestionar:
 * - Registro de nuevos inquilinos
 * - Asignación de identificadores únicos (hash)
 * - Creación de carpetas base por cliente
 * - Actualización automática del mapa de inquilinos
 */
class TenantService
{



    /**
     * Genera o actualiza el mapa de inquilinos desde tenants.json
     *
     * @return array Respuesta estandarizada con éxito/error
     */
    public static function updateTenantMap()
    {
        if (!file_exists(__TENANTS_TENANTS)) {
            return Response::error('Archivo tenants.json no encontrado');
        }

        // Leer lista blanca de dominios
        $raw = file_get_contents(__TENANTS_TENANTS);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return Response::error(
                'Error al decodificar tenants.json',
                json_last_error_msg()
            );
        }

        // Leer mapa existente (si hay)
        $existingMap = [];
        if (file_exists(__TENANTS_TENANTS_MAP)) {
            $rawExisting = file_get_contents(__TENANTS_TENANTS_MAP);
            $existingMap = json_decode($rawExisting, true) ?? [];

            if (json_last_error() !== JSON_ERROR_NONE) {
                return Response::error(
                    'Error al decodificar tenants_map.json',
                    json_last_error_msg()
                );
            }
        }

        $map = [];

        foreach ($data['dominios'] as $domain) {
            // Si ya existe un hash para este dominio, reutilízalo
            if (isset($existingMap[$domain])) {
                $hash = $existingMap[$domain]['hash'];
                $tenantPath = __STORAGE_DIR . '/' . $hash;

                // Asegurarse de que la carpeta aún exista
                if (!file_exists($tenantPath)) {
                    $mkdirResult = mkdir($tenantPath, 0777, true);
                    if (!$mkdirResult) {
                        return Response::error(
                            "No se pudo crear la carpeta: $tenantPath",
                            null,
                            500
                        );
                    }

                    $structureResult = self::createTenantStructure($tenantPath);
                    if (!$structureResult['success']) {
                        return $structureResult;
                    }
                }
            } else {
                // Si no existe, genera uno nuevo
                $hash = self::generateHashForDomain($domain);
                $tenantPath = __STORAGE_DIR . '/' . $hash;

                $mkdirResult = mkdir($tenantPath, 0777, true);
                if (!$mkdirResult) {
                    return Response::error(
                        "No se pudo crear la carpeta: $tenantPath",
                        null,
                        500
                    );
                }

                $structureResult = self::createTenantStructure($tenantPath);
                if (!$structureResult['success']) {
                    return $structureResult;
                }
            }

            $map[$domain] = [
                'hash' => $hash,
                'path' => $tenantPath,
                'activo' => true
            ];
        }

        // Guardar el mapa actualizado
        $result = file_put_contents(__TENANTS_TENANTS_MAP, json_encode($map, JSON_PRETTY_PRINT));
        if ($result === false) {
            return Response::error('No se pudo guardar tenants_map.json', null, 500);
        }

        return Response::success('Mapa de inquilinos actualizado correctamente', [
            'map' => $map
        ]);
    }

    /**
     * Genera un hash SHA-1 único para un dominio
     *
     * @param string $domain Dominio del cliente
     * @return string Hash SHA-1 reducido a 16 caracteres
     */
    private static function generateHashForDomain($domain)
    {
        return substr(sha1($domain . uniqid('', true)), 0, 16); // 16 chars
    }

    /**
     * Crea la estructura base para un nuevo inquilino
     *
     * @param string $path Ruta donde crear la estructura
     * @return array Respuesta estandarizada con éxito/error
     */
    private static function createTenantStructure($path)
    {
        $dirs = [
            "$path/config",
            "$path/references",
            "$path/files"
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0777, true)) {
                    return Response::error("No se pudo crear la carpeta: $dir", null, 500);
                }
            }
        }

        // Archivo de configuración por defecto
        $permissionsFile = "$path/config/permissions.json";
        $tenantConfigFile = "$path/config/tenant_config.json";

        if (!file_put_contents($permissionsFile, json_encode(DEFAULT_PERMISSIONS, JSON_PRETTY_PRINT))) {
            return Response::error("No se pudo escribir en: $permissionsFile", null, 500);
        }

        $tenantConfigData = json_encode([
            'nombre_cliente' => 'Cliente Local',
            'fecha_registro' => date('c'),
            'activo' => true
        ], JSON_PRETTY_PRINT);

        if (!file_put_contents($tenantConfigFile, $tenantConfigData)) {
            return Response::error("No se pudo escribir en: $tenantConfigFile", null, 500);
        }

        return Response::success("Estructura creada correctamente en: $path");
    }
}