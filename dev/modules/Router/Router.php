<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __DIR__ . '/Service/RouteMatcher.php';
require_once __DIR__ . '/Exception/RouteNotFoundException.php';



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Clase Router
 *
 * Es el punto de entrada del sistema de rutas. Se encarga de:
 * - Registrar rutas (GET, POST, PUT, DELETE)
 * - Configurar CORS
 * - Lanzar el proceso de matching de rutas
 */
class Router
{
    /**
     * Instancia del RouteMatcher que se usa para comparar rutas.
     *
     * @var RouteMatcher
     */
    private $routeMatcher;

    /**
     * Lista de dominios permitidos para CORS.
     *
     * @var array
     */
    private $allowedDomains;

    /**
     * Handler opcional para manejar rutas no encontradas (404).
     *
     * @var callable|null
     */
    private $notFoundHandler;



    /**
     * Constructor
     *
     * Inicializa el router con la base URL y dominios permitidos.
     * Configura automáticamente las cabeceras CORS.
     *
     * @param string $baseUrl       Base URL del proyecto (ej: /gemi-bucket/public)
     * @param array  $allowedDomains Dominios permitidos para CORS
     */
    public function __construct(string $baseUrl, array $allowedDomains = [])
    {
        $this->allowedDomains = $allowedDomains;
        $this->configureCors();
        $this->routeMatcher = new RouteMatcher($baseUrl);
    }

    /**
     * Registra una ruta GET
     *
     * @param string   $path      Ruta definida (ej: /users/{id})
     * @param callable|array $handler Función anónima o [Clase, 'método']
     * @param callable|null $middleware Middleware opcional
     */
    public function get($path, $handler, $middleware = null)
    {
        $this->routeMatcher->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Registra una ruta POST
     *
     * @param string   $path      Ruta definida (ej: /submit)
     * @param callable|array $handler Función anónima o [Clase, 'método']
     * @param callable|null $middleware Middleware opcional
     */
    public function post($path, $handler, $middleware = null)
    {
        $this->routeMatcher->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Registra una ruta PUT
     *
     * @param string   $path      Ruta definida (ej: /update/{id})
     * @param callable|array $handler Función anónima o [Clase, 'método']
     * @param callable|null $middleware Middleware opcional
     */
    public function put($path, $handler, $middleware = null)
    {
        $this->routeMatcher->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Registra una ruta DELETE
     *
     * @param string   $path      Ruta definida (ej: /delete/{id})
     * @param callable|array $handler Función anónima o [Clase, 'método']
     * @param callable|null $middleware Middleware opcional
     */
    public function delete($path, $handler, $middleware = null)
    {
        $this->routeMatcher->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Define un handler personalizado para cuando no se encuentra una ruta.
     *
     * @param callable $handler Función anónima que maneja el error 404
     */
    public function setNotFoundHandler(callable $handler)
    {
        $this->notFoundHandler = $handler;
    }

    /**
     * Lanza el proceso de matching de rutas
     *
     * Si no encuentra coincidencia:
     * - Captura RouteNotFoundException
     * - Envía respuesta JSON 404
     */
    public function dispatch()
    {
        try {
            $this->routeMatcher->match();
        } catch (RouteNotFoundException $e) {
            if ($this->notFoundHandler) {
                // Usar handler personalizado
                call_user_func($this->notFoundHandler, $e->getMessage());
            } else {
                // Respuesta por defecto
                http_response_code(404);
                echo json_encode([
                    'error' => 'Not Found',
                    'message' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Configura las cabeceras CORS si el origen está permitido
     *
     * Si es una solicitud OPTIONS, responde inmediatamente con 200 OK.
     */
    private function configureCors()
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, $this->allowedDomains)) {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key");

            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
        }
    }
}