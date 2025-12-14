<?php

namespace App\Controllers;

use App\Models\Usuarios;
use App\Database\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {
    
    private $usuarios;
    private $localidades;
    private $emailService;
    private $jwtSecret;
    private $pdo;
    private $otpTtlMin;
    private $maxIntentos;
    private $rateSeconds;
    private $maxPer24h;
    private $emailVerificacionTtlHoras;

    public function __construct() {
        $this->pdo = Database::obtenerInstancia()->obtenerPdo();
        $this->usuarios = new Usuarios($this->pdo);
        $this->localidades = new \App\Models\Localidades($this->pdo);
        require_once __DIR__ . '/../Services/EmailService.php';
        $this->emailService = new \App\Services\EmailService();
        
        // Obtener JWT_SECRET del .env
        // trim() elimina comillas 
        $jwtSecretRaw = $_ENV['JWT_SECRET'];
        $this->jwtSecret = trim($jwtSecretRaw, '"\'');
        
        // Configuración OTP desde .env (obligatorias)
        $this->otpTtlMin = (int)$_ENV['OTP_TTL_MIN']; 
        $this->maxIntentos = (int)$_ENV['OTP_MAX_INTENTOS'];
        $this->rateSeconds = (int)$_ENV['OTP_RATE_SECONDS'];
        $this->maxPer24h = (int)$_ENV['OTP_MAX_PER_24H'];
        
        // Tiempo de expiración del token de verificación de email (en horas, por defecto 24)
        $this->emailVerificacionTtlHoras = (int)($_ENV['EMAIL_VERIFICACION_TTL_HORAS']); 
        

    }

    public function iniciarSesion(Request $request, Response $response): Response {
        // Obtener datos del body del request, o usar array vacío si no hay datos
        $body = $request->getParsedBody();
        $data = is_array($body) ? (array)$body : [];
        
        // Extraer email y contraseña del array, o usar cadena vacía si no existen
        $email = isset($data['email']) ? trim(strtolower($data['email'])) : '';
        $pass = isset($data['password']) ? $data['password'] : '';

        // Validar formato
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, ['error' => 'Email inválido'], 400);
        }
        if (empty($pass)) {
            return $this->json($response, ['error' => 'Contraseña requerida'], 400);
        }

        // Buscar usuario
        $usuario = $this->usuarios->buscarPorEmail($email);
        if (!$usuario) {
            return $this->json($response, ['error' => 'Credenciales incorrectas'], 401);
        }

        // Verificar contraseña
        if (!password_verify($pass, $usuario['contrasena_hash'])) {
            return $this->json($response, ['error' => 'Credenciales incorrectas'], 401);
        }

        // Verificar cuenta activa
        if (!$usuario['cuenta_verificada']) {
            return $this->json($response, ['error' => 'Cuenta no verificada'], 403);
        }

        // Generar JWT
        $now = time();
        $exp = $now + (86400 * 7); // 7 días de expiración
        $payload = [
            'iat' => $now,
            'exp' => $exp,
            'data' => [
                'id_usuario' => $usuario['id_usuario'],
                'email' => $usuario['email'],
                'nombre' => $usuario['nombre_usuario'],
                'apellido' => $usuario['apellido'] ?? '',
                'id_rol' => $usuario['id_rol']
            ]
        ];
        
        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        // Determinar redirección según el rol
        // id_rol == 1 (admin) → /Admin
        // cualquier otro rol → /Inicio
        $idRol = (int)$usuario['id_rol'];
        $redirect = ($idRol === 1) ? '/calcomania-proyecto-final/Admin' : '/calcomania-proyecto-final/Inicio';
        
        // Responder con JWT
        return $this->json($response
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('X-Token-Exp', (string)$exp), [
                'ok' => true,
                'token' => $token,
                'exp' => $exp,
                'usuario' => [
                    'id' => $usuario['id_usuario'],
                    'email' => $usuario['email'],
                    'nombre' => $usuario['nombre_usuario'],
                    'apellido' => $usuario['apellido'] ?? '',
                    'rol' => $idRol,
                    'id_rol' => $idRol
                ],
                'redirect' => $redirect
            ]);
    }

    public function registrar(Request $request, Response $response): Response {
        try {
            // Obtener datos del body del request, o usar array vacío si no hay datos
            $body = $request->getParsedBody();
            $data = is_array($body) ? (array)$body : [];
            
            // Validar campos requeridos (localidad no se guarda en la BD, solo se valida si se envía)
            $camposRequeridos = ['nombre_usuario', 'apellido', 'email', 'password', 'telefono', 'direccion', 'codigo_postal'];
            foreach ($camposRequeridos as $campo) {
                if (empty($data[$campo])) {
                    return $this->json($response, ['error' => "El campo {$campo} es requerido"], 400);
                }
            }

            // Validar formato email (FILTER_VALIDATE_EMAIL constante de PHP)
            $email = strtolower(trim($data['email']));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json($response, ['error' => 'Formato de email inválido'], 400);
            }

            // Validar contraseña
            if (strlen($data['password']) < 6) {
                return $this->json($response, ['error' => 'La contraseña debe tener al menos 6 caracteres'], 400);
            }

            // Validar que las contraseñas coincidan (acepta ambos nombres de campo del frontend)
            $passwordConfirm = $data['password_confirm'] ?? $data['confirmar_password'] ?? null;
            if ($passwordConfirm && $data['password'] !== $passwordConfirm) {
                return $this->json($response, ['error' => 'Las contraseñas no coinciden'], 400);
            }

            // Verificar si el email ya existe
            if ($this->usuarios->buscarPorEmail($email)) {
                return $this->json($response, ['error' => 'El email ya está registrado. Usa otro email o inicia sesión.'], 400);
            }

            // Validar código postal
            if (!preg_match('/^\d+$/', $data['codigo_postal'])) {
                return $this->json($response, ['error' => 'El código postal debe contener solo números'], 400);
            }

            $codigoPostal = trim($data['codigo_postal']);

            // Verificar si el código postal existe en localidades
            if (!$this->localidades->existe($codigoPostal)) {
                $nombreLocalidad = isset($data['localidad']) ? trim($data['localidad']) : '';
                
                if (empty($nombreLocalidad)) {
                    return $this->json($response, ['error' => 'Debe ingresar el nombre de la localidad'], 400);
                }
                
                try {
                    $this->localidades->crear($codigoPostal, $nombreLocalidad);
                } catch (\PDOException $e) {
                    // Si falla (por ejemplo, código postal duplicado por race condition), verificar si se creó
                    if (!$this->localidades->existe($codigoPostal)) {
                        return $this->json($response, ['error' => 'Error al crear la localidad: ' . $e->getMessage()], 500);
                    }
                } catch (\Exception $e) {
                    return $this->json($response, ['error' => $e->getMessage()], 500);
                }
            }

            // Validar longitud de campos
            if (strlen($data['nombre_usuario']) < 2) {
                return $this->json($response, ['error' => 'El nombre debe tener al menos 2 caracteres'], 400);
            }
            if (strlen($data['apellido']) < 2) {
                return $this->json($response, ['error' => 'El apellido debe tener al menos 2 caracteres'], 400);
            }
            if (strlen($data['telefono']) < 8) {
                return $this->json($response, ['error' => 'El teléfono debe tener al menos 8 caracteres'], 400);
            }

            // Crear usuario NO verificado
            $datosUsuario = [
                ':nombre' => trim($data['nombre_usuario']),
                ':apellido' => trim($data['apellido']),
                ':email' => $email,
                ':pass' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':direccion' => trim($data['direccion']),
                ':telefono' => trim($data['telefono']),
                ':cp' => $codigoPostal,
                ':verificada' => 0,
                ':fecha' => date('Y-m-d H:i:s'),
                ':rol' => 2
            ];

            $this->usuarios->crear($datosUsuario);
            
            // Generar token JWT para verificación de email
            $now = time();
            $exp = $now + (86400 * $this->emailVerificacionTtlHoras); // Expiración configurable desde .env
            $payload = [
                'iat' => $now,
                'exp' => $exp,
                'type' => 'email_verification',
                'email' => $email
            ];
            $tokenVerificacion = JWT::encode($payload, $this->jwtSecret, 'HS256');
            
            // Enviar email de verificación
            $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            // Obtener base path (subdirectorio del proyecto) - mismo método que en index.php
            $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if ($basePath !== '/' && $basePath !== '\\' && $basePath !== '.' && $basePath !== '') {
                $basePath = rtrim($basePath, '/\\');
            } else {
                $basePath = '';
            }
            $urlVerificacion = $baseUrl . $basePath . '/verificar?token=' . urlencode($tokenVerificacion);
            
            $this->emailService->enviarVerificacion($email, trim($data['nombre_usuario']), $urlVerificacion);
            
            return $this->json($response, [
                'ok' => true, 
                'mensaje' => '✓ Usuario registrado correctamente. Revisa tu email para verificar tu cuenta.'
            ], 201);
        } catch (\PDOException $e) {
            error_log('Error PDO en registrar: ' . $e->getMessage() . ' - Código: ' . $e->getCode());
            return $this->json($response, ['error' => 'Error de base de datos: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            error_log('Error en registrar: ' . $e->getMessage() . ' - Archivo: ' . $e->getFile() . ' - Línea: ' . $e->getLine());
            return $this->json($response, ['error' => 'Error al procesar el registro: ' . $e->getMessage()], 500);
        }
    }

    public function verificarEmail(Request $request, Response $response): Response {
        // Obtener token de los parámetros de la URL
        $queryParams = $request->getQueryParams();
        $token = isset($queryParams['token']) ? $queryParams['token'] : '';
        
        if (empty($token)) {
            return $this->json($response, ['error' => 'Token de verificación no proporcionado'], 400);
        }
        
        try {
            // Decodificar y validar el token JWT
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            
            // Verificar que el token es de tipo verificación de email
            if (!isset($decoded->type) || $decoded->type !== 'email_verification') {
                return $this->json($response, ['error' => 'Token inválido'], 400);
            }
            
            // Obtener el email del token
            $email = $decoded->email;
            
            // Verificar que el usuario existe
            $usuario = $this->usuarios->buscarPorEmail($email);
            
            if (!$usuario) {
                return $this->json($response, ['error' => 'Usuario no encontrado'], 404);
            }
            
            if ($usuario['cuenta_verificada']) {
                return $this->json($response, [
                    'ok' => true,
                    'mensaje' => 'Tu cuenta ya está verificada',
                    'ya_verificada' => true
                ]);
            }
            
            // Verificar la cuenta
            $resultado = $this->usuarios->verificar($email);
            if ($resultado) {
                return $this->json($response, [
                    'ok' => true,
                    'mensaje' => '✓ ¡Email verificado correctamente! Ya puedes iniciar sesión'
                ]);
            } else {
                return $this->json($response, ['error' => 'Error al verificar el email'], 500);
            }
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->json($response, ['error' => 'El enlace de verificación ha expirado. Por favor, solicita un nuevo enlace.'], 400);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $this->json($response, ['error' => 'Token de verificación inválido'], 400);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => 'Error al verificar el token: ' . $e->getMessage()], 500);
        }
    }
    
    // Método para mostrar la página de verificación (HTML)
    public function mostrarVerificacion(Request $request, Response $response): Response {
        // Obtener token de los parámetros de la URL
        $queryParams = $request->getQueryParams();
        $token = isset($queryParams['token']) ? $queryParams['token'] : '';
        $mensaje = '';
        $exito = false;
        
        if (empty($token)) {
            $mensaje = 'Error: Token de verificación no proporcionado';
        } else {
            try {
                // Decodificar y validar el token JWT
                $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
                
                // Verificar que el token es de tipo verificación de email
                if (!isset($decoded->type) || $decoded->type !== 'email_verification') {
                    $mensaje = 'Error: Token inválido';
                } else {
                    $email = $decoded->email;
                    $usuario = $this->usuarios->buscarPorEmail($email);
                    
                    if (!$usuario) {
                        $mensaje = 'Error: Usuario no encontrado';
                    } elseif ($usuario['cuenta_verificada']) {
                        $mensaje = 'Tu cuenta ya está verificada';
                        $exito = true;
                    } else {
                        $resultado = $this->usuarios->verificar($email);
                        if ($resultado) {
                            $mensaje = '✓ ¡Email verificado correctamente! Ya puedes iniciar sesión';
                            $exito = true;
                        } else {
                            $mensaje = 'Error al verificar el email';
                        }
                    }
                }
            } catch (\Firebase\JWT\ExpiredException $e) {
                $mensaje = 'El enlace de verificación ha expirado. Por favor, solicita un nuevo enlace.';
            } catch (\Firebase\JWT\SignatureInvalidException $e) {
                $mensaje = 'Token de verificación inválido';
            } catch (\Exception $e) {
                $mensaje = 'Error: ' . $e->getMessage();
            }
        }
        
        // Obtener base path para la redirección al login - mismo método que en index.php
        $basePath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($basePath !== '/' && $basePath !== '\\' && $basePath !== '.' && $basePath !== '') {
            $basePath = rtrim($basePath, '/\\');
        } else {
            $basePath = '';
        }
        $urlLogin = $basePath . '/Login';
        $html = $this->generarHtmlVerificacion($mensaje, $urlLogin);
        $response->getBody()->write($html);
        return $response->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
    
    private function generarHtmlVerificacion($mensaje, $urlLogin): string {
        return '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Email</title>
</head>
<body>
    <script>
        alert(' . json_encode($mensaje) . ');
        window.location.href = ' . json_encode($urlLogin) . ';
    </script>
</body>
</html>';
    }

    public function cerrarSesion(Request $request, Response $response): Response {
        // Con JWT, el logout es principalmente del lado del cliente
        return $this->json($response, ['ok' => true, 'mensaje' => 'Sesión cerrada exitosamente']);
    }

    public function obtenerUsuario(Request $request, Response $response): Response {
        $usuario = $this->extraerUsuarioDelToken($request);
        if (!$usuario) {
            return $this->json($response, ['error' => 'No autenticado'], 401);
        }
        
        return $this->json($response, ['ok' => true, 'usuario' => $usuario]);
    }

    public function recuperarPassword(Request $request, Response $response): Response {
        // Obtener datos del body del request, o usar array vacío si no hay datos
        $body = $request->getParsedBody();
        $data = is_array($body) ? (array)$body : [];
        // Extraer email del array, o usar cadena vacía si no existe
        $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, ['error' => 'Email inválido'], 400);
        }

        try {
            // Verificar si el usuario existe
            $usuario = $this->usuarios->buscarPorEmail($email);
            $idUsuario = $usuario ? (int)$usuario['id_usuario'] : 0;
            
            // No revelar si el usuario existe (seguridad)
            if ($idUsuario <= 0) {
                return $this->json($response, ['ok' => true, 'mensaje' => 'Si el email existe, recibirás un código']);
            }

            // Rate limit: 1 código cada RATE_SECONDS
            $st = $this->pdo->prepare("
                SELECT COUNT(*) FROM otp
                WHERE id_usuario = ? AND fecha_creacion >= (NOW() - INTERVAL ? SECOND)
            ");
            $st->execute([$idUsuario, $this->rateSeconds]);
            if ((int)$st->fetchColumn() > 0) {
                $mensaje = $this->rateSeconds >= 60
                    ? 'Demasiadas solicitudes. Esperá 1 minuto antes de pedir otro código.'
                    : "Demasiadas solicitudes. Esperá {$this->rateSeconds} segundos antes de pedir otro código.";
                return $this->json($response, ['error' => $mensaje], 429);
            }

            // Tope de códigos en 24 horas
            $st = $this->pdo->prepare("
                SELECT COUNT(*) FROM otp
                WHERE id_usuario = ? AND fecha_creacion >= (NOW() - INTERVAL 1 DAY)
            ");
            $st->execute([$idUsuario]);
            if ((int)$st->fetchColumn() >= $this->maxPer24h) {
                return $this->json($response, [
                    'error' => 'Ya pediste el máximo de ' . $this->maxPer24h . ' códigos en las últimas 24 horas. Intentá más tarde.'
                ], 429);
            }

            // Generar código OTP
            $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $ahora = new \DateTimeImmutable('now');
            $vence = $ahora->modify('+' . $this->otpTtlMin . ' minutes');

            // Invalidar códigos anteriores no usados
            $this->pdo->prepare("UPDATE otp SET usado = 1 WHERE id_usuario = ? AND usado = 0")
                ->execute([$idUsuario]);

            // Insertar nuevo código OTP
            $st = $this->pdo->prepare("
                INSERT INTO otp (id_usuario, codigo, fecha_creacion, fecha_expiracion, intentos, usado)
                VALUES (?, ?, ?, ?, 0, 0)
            ");
            $st->execute([
                $idUsuario,
                $otp,
                $ahora->format('Y-m-d H:i:s'),
                $vence->format('Y-m-d H:i:s')
            ]);

            // Enviar email con OTP
            $this->emailService->enviarOTP($email, $otp);

            return $this->json($response, ['ok' => true, 'mensaje' => 'Código enviado a tu email']);

        } catch (\Exception $e) {
            error_log('Error en recuperarPassword: ' . $e->getMessage());
            return $this->json($response, ['error' => 'Error al procesar la solicitud'], 500);
        }
    }

    public function verificarCodigo(Request $request, Response $response): Response {
        // Obtener datos del body del request, o usar array vacío si no hay datos
        $body = $request->getParsedBody();
        $data = is_array($body) ? (array)$body : [];
        // Extraer email del array, o usar cadena vacía si no existe
        $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
        // Extraer código del array, o usar cadena vacía si no existe
        $codigo = isset($data['codigo']) ? trim($data['codigo']) : '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json($response, ['error' => 'Email inválido'], 400);
        }

        if (empty($codigo) || !preg_match('/^\d{6}$/', $codigo)) {
            return $this->json($response, ['error' => 'Código inválido. Debe tener 6 dígitos.'], 400);
        }

        try {
            // Buscar usuario
            $usuario = $this->usuarios->buscarPorEmail($email);
            $idUsuario = $usuario ? (int)$usuario['id_usuario'] : 0;
            
            if ($idUsuario <= 0) {
                return $this->json($response, ['error' => 'Código incorrecto'], 400);
            }

            // Buscar último código OTP válido
            $st = $this->pdo->prepare("
                SELECT id_otp, codigo, intentos, fecha_expiracion, usado
                FROM otp
                WHERE id_usuario = ?
                ORDER BY id_otp DESC
                LIMIT 1
            ");
            $st->execute([$idUsuario]);
            $row = $st->fetch(\PDO::FETCH_ASSOC);

            // Verificar si existe y está válido
            if (!$row || (int)$row['usado'] === 1) {
                return $this->json($response, ['error' => 'Código no encontrado o ya usado. Pedí un nuevo código.'], 400);
            }

            // Verificar expiración
            $now = new \DateTimeImmutable('now');
            $expira = new \DateTimeImmutable($row['fecha_expiracion']);
            if ($now > $expira) {
                return $this->json($response, ['error' => 'Código expirado. Pedí un nuevo código.'], 400);
            }

            // Verificar intentos máximos
            if ((int)$row['intentos'] >= $this->maxIntentos) {
                // Invalidar OTP bloqueado
                $this->pdo->prepare("UPDATE otp SET usado = 1 WHERE id_otp = ?")
                    ->execute([$row['id_otp']]);
                return $this->json($response, [
                    'error' => 'Superaste el máximo de intentos. Pedí un nuevo código.'
                ], 400);
            }

            // Verificar código
            if (!hash_equals($row['codigo'], $codigo)) {
                // Incrementar intentos
                $this->pdo->prepare("UPDATE otp SET intentos = intentos + 1 WHERE id_otp = ?")
                    ->execute([$row['id_otp']]);
                
                // Verificar si llegó al máximo después de incrementar
                $st = $this->pdo->prepare("SELECT intentos FROM otp WHERE id_otp = ?");
                $st->execute([$row['id_otp']]);
                $intentos = (int)$st->fetchColumn();
                
                if ($intentos >= $this->maxIntentos) {
                    $this->pdo->prepare("UPDATE otp SET usado = 1 WHERE id_otp = ?")
                        ->execute([$row['id_otp']]);
                    return $this->json($response, [
                        'error' => 'Superaste el máximo de intentos. Pedí un nuevo código.'
                    ], 400);
                }

                return $this->json($response, ['error' => 'Código incorrecto'], 400);
            }

            // Código correcto - marcar como usado
            $this->pdo->prepare("UPDATE otp SET usado = 1 WHERE id_otp = ?")
                ->execute([$row['id_otp']]);

            return $this->json($response, ['ok' => true, 'mensaje' => 'Código verificado correctamente']);

        } catch (\Exception $e) {
            error_log('Error en verificarCodigo: ' . $e->getMessage());
            return $this->json($response, ['error' => 'Error al verificar el código'], 500);
        }
    }

    public function cambiarPassword(Request $request, Response $response): Response {
        // Obtener datos del body del request, o usar array vacío si no hay datos
        $body = $request->getParsedBody();
        $data = is_array($body) ? (array)$body : [];
        
        // Extraer email del array, o usar cadena vacía si no existe
        $email = isset($data['email']) ? strtolower(trim($data['email'])) : '';
        
        // Extraer contraseña del array, o usar cadena vacía si no existe
        $password = isset($data['password']) ? $data['password'] : '';

        if (empty($email) || empty($password)) {
            return $this->json($response, ['error' => 'Email y contraseña requeridos'], 400);
        }

        // Validar contraseña
        if (strlen($password) < 6) {
            return $this->json($response, ['error' => 'La contraseña debe tener al menos 6 caracteres'], 400);
        }

        try {
            // Buscar usuario
            $usuario = $this->usuarios->buscarPorEmail($email);
            if (!$usuario) {
                return $this->json($response, ['error' => 'Usuario no encontrado'], 404);
            }

            // Verificar que existe un código OTP verificado recientemente
            $st = $this->pdo->prepare("
                SELECT id_otp FROM otp
                WHERE id_usuario = ? AND usado = 1 
                AND fecha_expiracion >= (NOW() - INTERVAL ? MINUTE)
                ORDER BY id_otp DESC
                LIMIT 1
            ");
            $st->execute([$usuario['id_usuario'], $this->otpTtlMin]);
            
            if (!$st->fetch()) {
                return $this->json($response, [
                    'error' => 'Debes verificar el código OTP primero o el código expiró.'
                ], 403);
            }

            // Actualizar contraseña
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $this->usuarios->actualizarPassword($usuario['id_usuario'], $hash);

            return $this->json($response, ['ok' => true, 'mensaje' => 'Contraseña actualizada correctamente']);

        } catch (\Exception $e) {
            error_log('Error en cambiarPassword: ' . $e->getMessage());
            return $this->json($response, ['error' => 'Error al actualizar la contraseña'], 500);
        }
    }

    public function verificarSesion(Request $request, Response $response): Response {
        try {
            $usuario = $this->extraerUsuarioDelToken($request);
            if (!$usuario) {
                return $this->json($response, ['error' => 'No autenticado'], 401);
            }
            
            return $this->json($response, [
                'ok' => true,
                'autenticado' => true,
                'usuario' => $usuario
            ]);
        } catch (\Exception $e) {
            error_log('Error en verificarSesion: ' . $e->getMessage());
            return $this->json($response, [
                'ok' => false,
                'error' => 'Error al verificar sesión: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método privado para extraer datos del usuario del token (elimina duplicación)
    private function extraerUsuarioDelToken(Request $request): ?array {
        $token = $request->getAttribute("token");
        if (!$token) {
            return null;
        }
        
        // Manejar tanto objeto como array
        $data = null;
        if (is_object($token)) {
            if (isset($token->data)) {
                $data = $token->data;
            }
        } else {
            if (is_array($token) && isset($token['data'])) {
                $data = $token['data'];
            }
        }
        
        if (!$data) {
            return null;
        }
        
        // Extraer datos normalizados
        return [
            'id' => is_object($data) ? (isset($data->id_usuario) ? $data->id_usuario : null) : (isset($data['id_usuario']) ? $data['id_usuario'] : null),
            'email' => is_object($data) ? (isset($data->email) ? $data->email : null) : (isset($data['email']) ? $data['email'] : null),
            'nombre' => is_object($data) ? (isset($data->nombre) ? $data->nombre : null) : (isset($data['nombre']) ? $data['nombre'] : null),
            'apellido' => is_object($data) ? (isset($data->apellido) ? $data->apellido : null) : (isset($data['apellido']) ? $data['apellido'] : null),
            'rol' => is_object($data) ? (isset($data->id_rol) ? $data->id_rol : null) : (isset($data['id_rol']) ? $data['id_rol'] : null)
        ];
    }

    private function json(Response $res, array $data, int $status = 200): Response
    {
        $res->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $res->withStatus($status)->withHeader('Content-Type','application/json');
    }
}
