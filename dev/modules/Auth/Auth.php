<?php



require_once __DIR__ . '/Service/JwtValidator.php';
require_once __DIR__ . '/Service/AuthMiddleware.php';



class Auth
{
    private JwtValidator $validator;

    public function __construct(string $secretKey)
    {
        $this->validator = new JwtValidator($secretKey);
    }

    /**
     * Establece el tiempo de vida del token (en segundos)
     */
    public function setTokenTTL(int $seconds): self
    {
        $this->validator->setTokenTTL($seconds);
        return $this;
    }

    /**
     * Devuelve un middleware listo para usar en rutas protegidas
     */
    public function middleware(): callable
    {
        $middleware = new AuthMiddleware($this->validator);
        return [$middleware, 'handle'];
    }

    /**
     * Genera un nuevo token JWT
     */
    public function generate(array $payload): string
    {
        return $this->validator->generateToken($payload);
    }

    /**
     * Valida un token manualmente
     */
    public function validate(string $token): array
    {
        return $this->validator->validateToken($token);
    }
}