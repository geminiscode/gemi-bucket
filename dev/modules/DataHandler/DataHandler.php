<?php


require_once __DIR__ . '/Service//JsonService.php';


/**
 * Clase principal del módulo DataHandler.
 * Proporciona acceso a diferentes servicios de manipulación de datos (JSON, XML, etc.)
 */
class DataHandler
{
    private ?JsonService $jsonService = null;

    /**
     * Devuelve una instancia del servicio de JSON
     */
    public function json(): JsonService
    {
        if (!$this->jsonService) {
            $this->jsonService = new JsonService();
        }
        return $this->jsonService;
    }

    // Aquí puedes agregar métodos similares para XML, HTML, etc. cuando lo necesites.
}