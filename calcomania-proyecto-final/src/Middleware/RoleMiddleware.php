<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class RoleMiddleware implements MiddlewareInterface
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles)
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getAttribute("token");
        
        // Extraer datos del token (soporta objeto y array)
        $data = is_object($token) ? ($token->data ?? null) : ($token['data'] ?? null);
        
        // Extraer rol del usuario
        $userRole = is_object($data) ? ($data->id_rol ?? null) : ($data['id_rol'] ?? null);
        $userRole = $userRole ? (int)$userRole : null;
        
        if ($userRole === null) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        if (!in_array($userRole, $this->allowedRoles, true)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Acceso denegado'], JSON_UNESCAPED_UNICODE));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}

