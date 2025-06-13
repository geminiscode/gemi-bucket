<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __DIR__ . '/../Exception/AuthException.php';



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



class JwtValidator
{
    private $secretKey;
    private $validAlgorithms = ['HS256'];
    private $tokenTTL = 3600; // 1 hora por defecto

    /**
     * Esquema del payload esperado (clave => [type, description])
     */
    private $payloadSchema = [
        'iat' => ['integer', 'Timestamp de emisión'],
        'exp' => ['integer', 'Timestamp de expiración'],
        'user' => ['array', 'Datos del usuario autenticado']
    ];

    /**
     * Payload base usado para generar tokens
     */
    private $basePayload = [];





    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * Valida un JWT y devuelve el payload si es válido.
     *
     * @param string $token JWT a validar
     * @return array Payload decodificado
     * @throws AuthException Si el token no es válido
     */
    public function validateToken(string $token): array
    {
        list($header64, $payload64, $signature64) = explode('.', $token);

        $header = json_decode(base64_decode($header64), true);
        $payload = json_decode(base64_decode($payload64), true);
        $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $signature64));

        if (!isset($header['alg']) || !in_array($header['alg'], $this->validAlgorithms)) {
            throw new AuthException("Unsupported algorithm: " . ($header['alg'] ?? 'none'));
        }

        $expectedSignature = hash_hmac('sha256', "$header64.$payload64", $this->secretKey, true);

        if (!hash_equals($signature, $expectedSignature)) {
            throw new AuthException("Invalid signature");
        }

        if (isset($payload['exp']) && time() > $payload['exp']) {
            throw new AuthException("Token has expired");
        }

        return $payload;
    }

    /**
     * Genera un nuevo JWT
     *
     * @param array $payload Datos del usuario u otros claims
     * @return string Token generado
     */
    public function generateToken(array $payload): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = array_merge([
            'iat' => time(),
            'exp' => time() + $this->tokenTTL
        ], $this->basePayload, $payload);

        $header64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $payload64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', "$header64.$payload64", $this->secretKey, true);
        $signature64 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$header64.$payload64.$signature64";
    }

    /**
     * Obtiene el token desde el encabezado Authorization
     *
     * @param string $headerName Nombre del encabezado (por defecto 'Authorization')
     * @return string|null Token o null si no existe
     */
    public function getTokenFromHeader(string $headerName = 'Authorization'): ?string
    {
        $headers = getallheaders();
        if (!isset($headers[$headerName])) {
            return null;
        }

        $authHeader = $headers[$headerName];
        if (stripos($authHeader, 'Bearer ') === 0) {
            return trim(substr($authHeader, 7));
        }

        return null;
    }

    /**
     * Establece el tiempo de vida del token (en segundos)
     */
    public function setTokenTTL(int $seconds): self
    {
        $this->tokenTTL = $seconds;
        return $this;
    }

    /**
     * Devuelve el esquema del payload esperado
     *
     * @return array Clave => [tipo, descripción]
     */
    public function getPayloadSchema(): array
    {
        return $this->payloadSchema;
    }

    /**
     * Establece valores adicionales o reemplaza partes del payload base
     *
     * @param array $newPayload Valores a agregar o actualizar
     * @return self
     */
    public function setPayload(array $newPayload): self
    {
        foreach ($newPayload as $key => $value) {
            $this->basePayload[$key] = $value;
        }
        return $this;
    }
}