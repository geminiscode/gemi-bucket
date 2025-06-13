<?php



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Servicio para generar y verificar hashes seguros (SHA-256, UUID, etc.)
 */
class HashService
{
    /**
     * Genera un hash SHA-256 de una cadena
     *
     * @param string $input Cadena original
     * @param string|null $salt Salt opcional para mayor seguridad
     * @return string Hash generado
     */
    public function sha256(string $input, ?string $salt = null): string
    {
        return hash('sha256', $salt . $input);
    }

    /**
     * Verifica si un valor coincide con su hash SHA-256
     *
     * @param string $input Valor a comprobar
     * @param string $storedHash Hash almacenado
     * @param string|null $salt Salt usado al generar el hash
     * @return bool true si coinciden, false si no
     */
    public function verifySha256(string $input, string $storedHash, ?string $salt = null): bool
    {
        return hash_equals($storedHash, $this->sha256($input, $salt));
    }

    /**
     * Genera un UUID versión 4 aleatorio (estándar RFC 4122)
     *
     * @return string UUID v4
     */
    public function uuid4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Versión 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant 10xx
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Genera un "salt" aleatorio seguro criptográficamente
     *
     * @param int $length Longitud del salt
     * @return string Salt generado
     */
    public function generateSalt(int $length = 16): string
    {
        return bin2hex(random_bytes($length));
    }
}