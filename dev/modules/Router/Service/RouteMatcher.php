<?php



/*===============================================================================================*/
/* IMPORTS --------------------------------------------------------------------------------------*/
/*===============================================================================================*/



require_once __DIR__ . '/../Exception/RouteNotFoundException.php';



/*===============================================================================================*/
/* MAIN CLASS -----------------------------------------------------------------------------------*/
/*===============================================================================================*/



/**
 * Clase RouteMatcher
 *
 * Se encarga de registrar rutas y compararlas con la URL actual.
 * Soporta métodos HTTP, rutas estáticas y dinámicas (con parámetros {param}).
 */
class RouteMatcher
{
    /**
     * Almacena todas las rutas registradas por método HTTP.
     *
     * @var array
     */
    private $routes = [];

    /**
     * Ruta actual limpia (sin baseUrl ni caracteres extra).
     *
     * @var string
     */
    private $currentPath;

    /**
     * Método HTTP actual (GET, POST, PUT, DELETE).
     *
     * @var string
     */
    private $method;

    /**
     * Base URL del proyecto (ej: /gemi-bucket/public).
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Constructor
     *
     * Inicializa el matcher con la base URL y detecta:
     * - La ruta actual
     * - El método HTTP usado
     *
     * @param string $baseUrl Base URL del proyecto
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Limpiamos la URL quitando el baseUrl
        if (str_starts_with($requestUri, $this->baseUrl)) {
            $this->currentPath = substr($requestUri, strlen($this->baseUrl));
        } else {
            $this->currentPath = $requestUri;
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Registra una nueva ruta
     *
     * @param string   $method    Método HTTP (GET, POST, etc.)
     * @param string   $path      Ruta definida (ej: /user/{id})
     * @param callable|array $handler  Función anónima o [Clase, 'método']
     * @param callable|null $middleware Middleware opcional
     */
    public function addRoute($method, $path, $handler, $middleware = null)
    {
        // Guardamos la ruta original y segmentada
        $segments = explode('/', trim($path, '/'));
        $this->routes[$method][] = [
            'original' => $path,
            'segments' => $segments,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    /**
     * Busca una coincidencia entre la URL actual y las rutas registradas
     *
     * Si encuentra una coincidencia:
     * - Ejecuta middleware si existe
     * - Ejecuta handler correspondiente
     *
     * Si no hay coincidencia:
     * - Lanza RouteNotFoundException
     *
     * @throws RouteNotFoundException
     */
    public function match()
    {
        $requestSegments = explode('/', trim($this->currentPath, '/'));

        if (!isset($this->routes[$this->method])) {
            throw new RouteNotFoundException("Method not supported");
        }

        foreach ($this->routes[$this->method] as $route) {
            $routeSegments = $route['segments'];
            $params = [];

            // Si el número de segmentos no coincide, saltamos
            if (count($requestSegments) !== count($routeSegments)) {
                continue;
            }

            $isMatch = true;

            foreach ($routeSegments as $i => $segment) {
                // Si es parámetro {id}
                if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                    $paramName = substr($segment, 1, -1);
                    $params[$paramName] = $requestSegments[$i];
                } elseif ($segment !== $requestSegments[$i]) {
                    $isMatch = false;
                    break;
                }
            }

            if ($isMatch) {
                // Ejecutar middleware si existe
                if ($route['middleware']) {
                    $middlewareResult = call_user_func($route['middleware'], $params);
                    if ($middlewareResult !== true) {
                        http_response_code(403);
                        echo json_encode(['error' => 'Forbidden', 'message' => $middlewareResult]);
                        return;
                    }
                }

                // Manejar handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    // Caso 1: Handler es una Closure/anónima
                    $handler($params);
                } elseif (is_array($handler) && count($handler) === 2) {
                    // Caso 2: Handler es un array [Clase, 'método']
                    [$controller, $action] = $handler;

                    if (class_exists($controller) && method_exists($controller, $action)) {
                        $controllerInstance = new $controller();
                        $controllerInstance->$action(...array_values($params));
                    } else {
                        throw new RouteNotFoundException("Controller or action not found");
                    }
                } else {
                    throw new RouteNotFoundException("Invalid handler");
                }

                return;
            }
        }

        throw new RouteNotFoundException("Route not found");
    }
}