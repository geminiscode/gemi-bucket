<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __DIR__ . '/JwtValidator.php';
require_once __DIR__ . '/AuthMiddleware.php';
require_once __DIR__ . '/../Exception/AuthException.php';



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



class AuthMiddleware
{
    private $validator;

    public function __construct(JwtValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Middleware que valida un JWT antes de ejecutar la ruta
     *
     * @param array $params Parámetros dinámicos de la ruta
     * @return bool|string Devuelve true si ok, mensaje de error si falla
     */
    public function handle(array $params): bool|string
    {
        try {
            $token = $this->validator->getTokenFromHeader();
            if (!$token) {
                return "Missing token";
            }

            $payload = $this->validator->validateToken($token);
            $_SERVER['JWT_PAYLOAD'] = $payload;

            return true;
        } catch (AuthException $e) {
            return $e->getMessage();
        }
    }
}