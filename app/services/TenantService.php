<?php
require_once __DIR__ . '/../interfaces/responses.php';



class TenantService
{
    /**
     * Genera o actualiza el mapa de inquilinos desde tenants.json
     */
    public static function updateTenantMap()
    {
        if (!file_exists(TENANTS_FILE)) {
            return Response::error('Archivo tenants.json no encontrado');
        }

        // Leer lista blanca de dominios
        $raw = file_get_contents(TENANTS_FILE);
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return Response::error(
                'Error al decodificar tenants.json',
                json_last_error_msg()
            );
        }

        // Leer mapa existente (si hay)
        $existingMap = [];
        if (file_exists(TENANT_MAP_FILE)) {
            $rawExisting = file_get_contents(TENANT_MAP_FILE);
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
                $tenantPath = STORAGE_PATH . '/' . $hash;

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
                $tenantPath = STORAGE_PATH . '/' . $hash;

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
        $result = file_put_contents(TENANT_MAP_FILE, json_encode($map, JSON_PRETTY_PRINT));
        if ($result === false) {
            return Response::error('No se pudo guardar tenants_map.json', null, 500);
        }

        return Response::success('Mapa de inquilinos actualizado correctamente', [
            'map' => $map
        ]);
    }

    /**
     * Genera un hash SHA-1 para un dominio
     */
    private static function generateHashForDomain($domain)
    {
        return substr(sha1($domain . uniqid('', true)), 0, 16); // 16 chars
    }

    /**
     * Crea la estructura básica para un nuevo inquilino
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