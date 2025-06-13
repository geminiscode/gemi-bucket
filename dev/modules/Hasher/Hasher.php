<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __DIR__ . '/Service/HashService.php';



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Clase principal del módulo Hasher.
 * Proporciona acceso a servicios especializados de hashing seguro.
 */
class Hasher
{
    private ?HashService $hashService = null;

    /**
     * Devuelve una instancia del servicio de hashing
     */
    public function hash(): HashService
    {
        if (!$this->hashService) {
            $this->hashService = new HashService();
        }
        return $this->hashService;
    }

    // Aquí puedes añadir métodos futuros para HMAC, UUIDs avanzados, etc.
}