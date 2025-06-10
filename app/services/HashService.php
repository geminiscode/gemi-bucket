<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __INTERFACE_RESPONSES;



/*===============================================================================================*/
/* SERVICE --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Class HashService
 *
 * Este servicio se encarga de:
 * - Generar identificadores únicos basados en SHA-256
 * - Validar que una cadena sea un hash válido
 * - Proveer hashes cortos para uso rápido
 */
class HashService
{
    /**
     * Genera un hash único SHA-256 usando el nombre original + uniqid()
     *
     * @param string $originalName Nombre original del archivo
     * @return string Hash SHA-256 (64 caracteres hexadecimales)
     */
    public static function generateUniqueHash(string $originalName): string
    {
        return hash('sha256', $originalName . uniqid('', true));
    }

    /**
     * Valida si una cadena tiene formato de hash SHA-256 válido
     *
     * @param string $hash Cadena a validar
     * @return bool True si es válido, false en caso contrario
     */
    public static function isValidHash(string $hash): bool
    {
        return preg_match('/^[a-f0-9]{64}$/', $hash) === 1;
    }

    /**
     * Genera un hash corto de 16 caracteres (útil para identificación rápida)
     *
     * @param string $input Cadena base para generar el hash
     * @return string Hash corto de 16 caracteres
     */
    public static function generateShortHash(string $input): string
    {
        return substr(sha1($input . uniqid('', true)), 0, 16);
    }
}
