<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Routes\ApiRoutes;
use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Tuupola\Middleware\JwtAuthentication;
use Tuupola\Middleware\JwtAuthentication\RequestMethodRule;
use Tuupola\Middleware\JwtAuthentication\RuleInterface;

$app = AppFactory::create();

// Configurar base path
$basePath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')) ?: '/';
$GLOBALS['basePath'] = $basePath;
$basePath !== '/' && $app->getRouteCollector()->setBasePath($basePath);

// Middleware para parsear body 
$bodyParsingMiddleware = $app->addBodyParsingMiddleware();
$bodyParsingMiddleware->registerBodyParser('multipart/form-data', function ($input) {
    return $_POST; // PHP parsea automáticamente en $_POST
});

// Configurar manejo de errores (JSON para APIs)
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->getDefaultErrorHandler()->forceContentType('application/json');

// Cargar .env
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    try {
        $dotenv->load();
    } catch (Exception $e) {
        error_log('Error cargando .env: ' . $e->getMessage());
    }
}

$jwtSecret = isset($_ENV['JWT_SECRET']) ? trim($_ENV['JWT_SECRET'], '"\'') : '';

$pdo = Database::obtenerInstancia()->obtenerPdo();

$tzId = $_ENV['APP_TZ'] ?? 'America/Argentina/Buenos_Aires';
date_default_timezone_set($tzId);

// Configurar rutas (antes del middleware JWT)
$apiRoutes = new ApiRoutes($pdo, $jwtSecret);
$apiRoutes->definirRutas($app);

// Middleware JWT (después de definir rutas)
if (empty($jwtSecret)) {
    error_log('ADVERTENCIA: JWT_SECRET está vacío. El middleware JWT no se configurará.');
} else {
    // Rutas públicas (no requieren autenticación)
    $ignorePaths = [
        "/api/auth/login",
        "/api/auth/register",
        "/api/auth/recuperar",
        "/api/auth/verificar-codigo",
        "/api/auth/cambiar-password",
        "/verificar",
        "/Login",
        "/Inicio",
        "/Carrito",
        "/Pasarela",
        "/favicon.ico",
        "/Uploads",
        "/api/puntos-retiro"
    ];
    
    // Agregar rutas con base path
    $basePath !== '/' && $ignorePaths = array_merge($ignorePaths, array_map(fn($p) => $basePath . $p, $ignorePaths));
    
    // Reglas personalizadas para JWT
    $customRules = [
        new RequestMethodRule(["ignore" => ["OPTIONS"]]),
        new class($ignorePaths, $basePath) implements RuleInterface {
            private $ignorePaths;
            private $basePath;
            public function __construct($ignorePaths, $basePath) {
                $this->ignorePaths = $ignorePaths;
                $this->basePath = $basePath;
            }
            public function __invoke(\Psr\Http\Message\ServerRequestInterface $request): bool {
                $path = $request->getUri()->getPath();
                $method = $request->getMethod();
                
                // Verificar rutas ignoradas
                foreach ($this->ignorePaths as $ignore) {
                    if (preg_match("@^" . preg_quote(rtrim($ignore, "/"), "@") . "(/.*)?$@", $path)) {
                        return false;
                    }
                }
                
                // Rutas exactas públicas solo para GET
                $exactPublicPaths = [
                    '/api/productos', $this->basePath . '/api/productos',
                    '/api/categorias', $this->basePath . '/api/categorias',
                    '/api/tematicas', $this->basePath . '/api/tematicas'
                ];
                if (in_array($path, $exactPublicPaths, true) && $method === 'GET') {
                    return false;
                }
                
                return true;
            }
        }
    ];
    
    $app->add(new JwtAuthentication([
        "secret"    => $jwtSecret,
        "algorithm" => ["HS256"],
        "rules" => $customRules,
        "attribute" => "token",
        "secure" => false,
        "error" => function (Response $response, array $args) {
            $message = $args["message"] ?? "Token inválido";
            $response->getBody()->write(json_encode([
                "error" => "Unauthorized",
                "message" => $message
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader("Content-Type", "application/json")->withStatus(401);
        }
    ]));
}


$app->get('/', function (Request $request, Response $response) {
    $baseUrl = dirname($_SERVER['SCRIPT_NAME']);
    return $response->withHeader('Location', $baseUrl . '/Login')->withStatus(302);
});


$app->run();
