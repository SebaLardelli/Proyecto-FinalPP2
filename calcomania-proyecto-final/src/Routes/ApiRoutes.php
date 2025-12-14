<?php

namespace App\Routes;

use Slim\App;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controllers\AuthController;
use App\Controllers\CarritoController;
use App\Controllers\ProductosController;
use App\Controllers\CategoriasController;
use App\Controllers\TematicaController;
use App\Controllers\MetodosPagoController;
use App\Controllers\PuntosVentaController;
use App\Controllers\PuntosRetiroController;
use App\Controllers\VentasController;
use App\Middleware\RoleMiddleware;

class ApiRoutes
{
    public function __construct(
        private PDO $pdo,
        private string $jwtSecret
    ) {}

    public function definirRutas(App $app): void
    {
        // Favicon
        $app->get('/favicon.ico', fn($req, $res) => $res->withStatus(204));

        // AUTENTICACIÓN
        $authController = new AuthController();
        
        // Login
        $app->post('/api/auth/login', function (Request $request, Response $response) use ($authController) {
            return $authController->iniciarSesion($request, $response);
        });

        // Registrar usuario
        $app->post('/api/auth/register', function (Request $request, Response $response) use ($authController) {
            return $authController->registrar($request, $response);
        });

        // Cerrar sesión
        $app->post('/api/auth/logout', function (Request $request, Response $response) use ($authController) {
            return $authController->cerrarSesion($request, $response);
        });

        // Recuperar contraseña
        $app->post('/api/auth/recuperar', function (Request $request, Response $response) use ($authController) {
            return $authController->recuperarPassword($request, $response);
        });

        // Verificar código OTP
        $app->post('/api/auth/verificar-codigo', function (Request $request, Response $response) use ($authController) {
            return $authController->verificarCodigo($request, $response);
        });

        // Cambiar contraseña
        $app->post('/api/auth/cambiar-password', function (Request $request, Response $response) use ($authController) {
            return $authController->cambiarPassword($request, $response);
        });

        // Obtener usuario
        $app->get('/api/auth/usuario', function (Request $request, Response $response) use ($authController) {
            return $authController->obtenerUsuario($request, $response);
        });

        // Verificar email
        $app->get('/api/auth/verificar-email', function (Request $request, Response $response) use ($authController) {
            return $authController->verificarEmail($request, $response);
        });
        
        // Verificación email
        $app->get('/verificar', function (Request $request, Response $response) use ($authController) {
            return $authController->mostrarVerificacion($request, $response);
        });

        // Verificar sesión
        $app->get('/api/auth/verificar-sesion', function (Request $request, Response $response) use ($authController) {
            return $authController->verificarSesion($request, $response);
        });

        // PRODUCTOS
        try {
            $productosController = new ProductosController();
        } catch (\Exception $e) {
            error_log("Error creando ProductosController: " . $e->getMessage());
            throw $e;
        }
        
        // Listar productos (público)
        $app->get('/api/productos', function (Request $request, Response $response) use ($productosController) {
            $productosController->listar($request);
            return $response;
        });

        // Listar productos admin
        $app->get('/api/productos/admin', function (Request $request, Response $response) use ($productosController) {
            return $productosController->listarAdmin($request, $response);
        })->add(new RoleMiddleware([1]));

        // Obtener producto
        $app->get('/api/productos/{id}', function (Request $request, Response $response, $args) use ($productosController) {
            $productosController->obtener($request, $args['id']);
        });

        // Crear producto
        $app->post('/api/productos', function (Request $request, Response $response) use ($productosController) {
            $productosController->crear($request);
        })->add(new RoleMiddleware([1]));

        // Actualizar producto
        $app->put('/api/productos/{id}', function (Request $request, Response $response, $args) use ($productosController) {
            $productosController->actualizar($request, $args['id']);
        })->add(new RoleMiddleware([1]));
        
        // Actualizar producto con FormData
        $app->post('/api/productos/{id}', function (Request $request, Response $response, $args) use ($productosController) {
            // Verificar que tenga _method=PUT en el body para distinguir de crear
            $body = $request->getParsedBody();
            if (isset($body['_method']) && $body['_method'] === 'PUT') {
                return $productosController->actualizar($request, $args['id']);
            }
            // Si no tiene _method=PUT, es un error (no se puede crear con POST /api/productos/{id})
            $response->getBody()->write(json_encode(['error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(405);
        })->add(new RoleMiddleware([1]));

        // Eliminar producto
        $app->delete('/api/productos/{id}', function (Request $request, Response $response, $args) use ($productosController) {
            $productosController->eliminar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // CATEGORÍAS
        $categoriasController = new CategoriasController();
        
        // Listar categorías
        $app->get('/api/categorias', function (Request $request, Response $response) use ($categoriasController) {
            $categoriasController->listar($request);
        });

        // Obtener categoría
        $app->get('/api/categorias/{id}', function (Request $request, Response $response, $args) use ($categoriasController) {
            $categoriasController->obtener($request, $args['id']);
        });

        // Crear categoría
        $app->post('/api/categorias', function (Request $request, Response $response) use ($categoriasController) {
            $categoriasController->crear($request);
        })->add(new RoleMiddleware([1]));

        // Actualizar categoría
        $app->put('/api/categorias/{id}', function (Request $request, Response $response, $args) use ($categoriasController) {
            $categoriasController->actualizar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // Eliminar categoría
        $app->delete('/api/categorias/{id}', function (Request $request, Response $response, $args) use ($categoriasController) {
            $categoriasController->eliminar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // TEMÁTICAS
        $tematicaController = new TematicaController();
        
        // Listar temáticas
        $app->get('/api/tematicas', function (Request $request, Response $response) use ($tematicaController) {
            $tematicaController->listar($request);
            return $response;
        });

        // Obtener temática
        $app->get('/api/tematicas/{id}', function (Request $request, Response $response, $args) use ($tematicaController) {
            $tematicaController->obtener($request, $args['id']);
        });

        // Crear temática
        $app->post('/api/tematicas', function (Request $request, Response $response) use ($tematicaController) {
            $tematicaController->crear($request);
        })->add(new RoleMiddleware([1]));

        // Actualizar temática
        $app->put('/api/tematicas/{id}', function (Request $request, Response $response, $args) use ($tematicaController) {
            $tematicaController->actualizar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // Eliminar temática
        $app->delete('/api/tematicas/{id}', function (Request $request, Response $response, $args) use ($tematicaController) {
            $tematicaController->eliminar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // CARRITO
        $carritoController = new CarritoController();
        
        // Obtener carrito
        $app->get('/api/carrito', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->obtener($request);
        })->add(new RoleMiddleware([1, 2]));

        // Agregar al carrito
        $app->post('/api/carrito/agregar', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->agregar($request);
        })->add(new RoleMiddleware([1, 2]));

        // Actualizar cantidad carrito
        $app->put('/api/carrito/actualizar', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->actualizar($request);
        })->add(new RoleMiddleware([1, 2]));

        // Procesar compra
        $app->post('/api/carrito/procesar', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->procesarCompra($request);
        })->add(new RoleMiddleware([1, 2]));

        // Limpiar carritos vacíos
        $app->post('/api/carrito/limpiar', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->limpiarCarritosVacios($request);
        })->add(new RoleMiddleware([1, 2]));

        // Probar stock
        $app->post('/api/carrito/probar-stock', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->probarStock($request);
        })->add(new RoleMiddleware([1, 2]));

        // Eliminar producto del carrito
        $app->delete('/api/carrito/eliminar', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->eliminar($request);
        })->add(new RoleMiddleware([1, 2]));

        // Vaciar carrito
        $app->delete('/api/carrito', function (Request $request, Response $response) use ($carritoController) {
            $carritoController->vaciar($request);
        })->add(new RoleMiddleware([1, 2]));

        // VENTAS
        $ventasController = new VentasController();
        
        // Listar ventas
        $app->get('/api/ventas', function (Request $request, Response $response) use ($ventasController) {
            $ventasController->listar($request);
        })->add(new RoleMiddleware([1, 2]));

        // Obtener venta
        $app->get('/api/ventas/{id}', function (Request $request, Response $response, $args) use ($ventasController) {
            $ventasController->obtenerDetalle($request, $args['id']);
        })->add(new RoleMiddleware([1, 2]));

        // Crear venta
        $app->post('/api/ventas', function (Request $request, Response $response) use ($ventasController) {
            $ventasController->crear($request);
        })->add(new RoleMiddleware([1, 2]));

        // MÉTODOS DE PAGO
        $metodosPagoController = new MetodosPagoController();
        
        // Listar métodos de pago
        $app->get('/api/metodos-pago', function (Request $request, Response $response) use ($metodosPagoController) {
            $metodosPagoController->listar($request);
        });

        // Obtener método de pago
        $app->get('/api/metodos-pago/{id}', function (Request $request, Response $response, $args) use ($metodosPagoController) {
            $metodosPagoController->obtener($request, $args['id']);
        });

        // Crear método de pago
        $app->post('/api/metodos-pago', function (Request $request, Response $response) use ($metodosPagoController) {
            $metodosPagoController->crear($request);
        })->add(new RoleMiddleware([1]));

        // Actualizar método de pago
        $app->put('/api/metodos-pago/{id}', function (Request $request, Response $response, $args) use ($metodosPagoController) {
            $metodosPagoController->actualizar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // Eliminar método de pago
        $app->delete('/api/metodos-pago/{id}', function (Request $request, Response $response, $args) use ($metodosPagoController) {
            $metodosPagoController->eliminar($request, $args['id']);
        })->add(new RoleMiddleware([1]));

        // PUNTOS DE VENTA
        $puntosVentaController = new PuntosVentaController();
        
        // Listar puntos de venta
        $app->get('/api/puntos-venta', function (Request $request, Response $response) use ($puntosVentaController) {
            return $puntosVentaController->listar($request, $response);
        })->add(new RoleMiddleware([1]));

        // Obtener punto de venta
        $app->get('/api/puntos-venta/{id}', function (Request $request, Response $response, $args) use ($puntosVentaController) {
            return $puntosVentaController->obtener($request, $response, $args['id']);
        })->add(new RoleMiddleware([1]));

        // Crear punto de venta
        $app->post('/api/puntos-venta', function (Request $request, Response $response) use ($puntosVentaController) {
            return $puntosVentaController->crear($request, $response);
        })->add(new RoleMiddleware([1]));

        // Actualizar punto de venta
        $app->put('/api/puntos-venta/{id}', function (Request $request, Response $response, $args) use ($puntosVentaController) {
            return $puntosVentaController->actualizar($request, $response, $args['id']);
        })->add(new RoleMiddleware([1]));

        // Eliminar punto de venta
        $app->delete('/api/puntos-venta/{id}', function (Request $request, Response $response, $args) use ($puntosVentaController) {
            return $puntosVentaController->eliminar($request, $response, $args['id']);
        })->add(new RoleMiddleware([1]));

        // PUNTOS DE RETIRO
        $puntosRetiroController = new PuntosRetiroController();
        
        // Listar puntos de retiro
        $app->get('/api/puntos-retiro', function (Request $request, Response $response) use ($puntosRetiroController) {
            $puntosRetiroController->listar($request);
        });

        // Obtener punto de retiro
        $app->get('/api/puntos-retiro/{id}', function (Request $request, Response $response, $args) use ($puntosRetiroController) {
            $puntosRetiroController->obtener($request, $args['id']);
        });

        // Servir archivos estáticos (debe ir al final para no capturar rutas específicas)
        // TEMPORALMENTE COMENTADO PARA DEBUGGING
        // $this->definirRutasEstaticas($app);
    }

    private function jsonError(Response $response, string $mensaje, int $status = 400): Response
    {
        $response->getBody()->write(json_encode(['error' => $mensaje], JSON_UNESCAPED_UNICODE));
        return $response->withStatus($status)->withHeader('Content-Type','application/json');
    }

    private function definirRutasEstaticas(App $app): void
    {
        // Servir página de login
        $app->get('/Login', function ($req, $res) {
            $file = __DIR__ . '/../../public/Login/login.html';
            if (is_file($file)) {
                $content = file_get_contents($file);
                $res->getBody()->write($content);
                return $res->withHeader('Content-Type', 'text/html; charset=UTF-8');
            }
            return $res->withStatus(404);
        });

        // Servir página de inicio
        $app->get('/Inicio', function ($req, $res) {
            $file = __DIR__ . '/../../public/Inicio/inicio.html';
            if (is_file($file)) {
                $content = file_get_contents($file);
                $res->getBody()->write($content);
                return $res->withHeader('Content-Type', 'text/html; charset=UTF-8');
            }
            return $res->withStatus(404);
        });

        // Servir página de admin
        $app->get('/Admin', function ($req, $res) {
            $file = __DIR__ . '/../../public/Admin/admin.html';
            if (is_file($file)) {
                $content = file_get_contents($file);
                $res->getBody()->write($content);
                return $res->withHeader('Content-Type', 'text/html; charset=UTF-8');
            }
            return $res->withStatus(404);
        });

        // Servir página de carrito
        $app->get('/Carrito', function ($req, $res) {
            $file = __DIR__ . '/../../public/Carrito/carrito.html';
            if (is_file($file)) {
                $content = file_get_contents($file);
                $res->getBody()->write($content);
                return $res->withHeader('Content-Type', 'text/html; charset=UTF-8');
            }
            return $res->withStatus(404);
        });

        // Servir página de pasarela de pago
        $app->get('/Pasarela', function ($req, $res) {
            $file = __DIR__ . '/../../public/Pasarela/pasarela.html';
            if (is_file($file)) {
                $content = file_get_contents($file);
                $res->getBody()->write($content);
                return $res->withHeader('Content-Type', 'text/html; charset=UTF-8');
            }
            return $res->withStatus(404);
        });

              // GET /{cualquier-ruta} - Servir archivos estáticos (debe ir al final para no capturar rutas de API)
        $app->get('/{path}', function ($req, $res, $args) {
            // Obtener la ruta solicitada por el usuario
            $rutaSolicitada = $args['path'];
            
            // CRÍTICO: No servir rutas de API - estas deben manejarse por las rutas específicas
            // Si la ruta empieza con "api", devolver 404 inmediatamente
            // Esto debería evitar que se procese como archivo estático
            if (stripos($rutaSolicitada, 'api') === 0) {
                return $res->withStatus(404);
            }
            
            // Construir la ruta completa del archivo en el servidor
            $rutaCompleta = __DIR__ . '/../../public/' . $rutaSolicitada;
            
            // Verificar que el archivo existe y es un archivo (no una carpeta)
            if (!file_exists($rutaCompleta) || !is_file($rutaCompleta)) {
                return $res->withStatus(404);
            }
            
            // Obtener la extensión del archivo (css, js, png, etc.)
            $extension = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
            
            // Lista de extensiones de archivos permitidos para servir
            $extensionesPermitidas = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'html'];
            
            // Si la extensión no está en la lista permitida, retornar error 404
            if (!in_array($extension, $extensionesPermitidas)) {
                return $res->withStatus(404);
            }
            
            // Mapear cada extensión a su tipo MIME correspondiente (necesario para que el navegador sepa cómo interpretar el archivo)
            $tiposMime = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'html' => 'text/html'
            ];
            
            // Obtener el tipo MIME según la extensión, o usar uno genérico si no existe
            $tipoMime = isset($tiposMime[$extension]) ? $tiposMime[$extension] : 'application/octet-stream';
            
            // Leer el contenido del archivo desde el disco
            $contenido = file_get_contents($rutaCompleta);
            
            // Escribir el contenido en la respuesta HTTP
            $res->getBody()->write($contenido);
            
            // Retornar la respuesta con el tipo de contenido correcto para que el navegador lo interprete bien
            return $res->withHeader('Content-Type', $tipoMime);
        });
    }
}

