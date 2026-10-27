<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ==============================================================================
 * MIDDLEWARE: AdminMiddleware
 * ==============================================================================
 * Filtro de seguridad perimetral para el Panel de Administración de Juno's Store.
 *
 * Responsabilidad:
 * 1. Comprueba si existe una sesión activa mediante auth()->check().
 *    Si no hay sesión, redirige al formulario de login con mensaje de alerta.
 * 2. Comprueba si el usuario autenticado tiene rol de Administrador mediante isAdmin().
 *    Si el usuario tiene rol de Cliente (id_rol != 1), detiene la ejecución
 *    arrojando un código de estado HTTP 403 (Forbidden) y mostrando la vista
 *    estilizada 'errors/403.blade.php'.
 * 3. Si ambas condiciones se cumplen, da paso a la siguiente capa ($next($request)).
 * ==============================================================================
 */
class AdminMiddleware
{
    /**
     * Intercepta y valida la petición entrante antes de que llegue a los controladores /admin.
     *
     * @param  Request  $request  Petición HTTP entrante
     * @param  Closure  $next  Siguiente middleware o controlador en la tubería
     * @return Response Respuesta HTTP
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificación de Autenticación: ¿El usuario ha iniciado sesión?
        if (! auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para acceder al panel administrativo.');
        }

        // 2. Verificación de Autorización (RBAC): ¿El usuario tiene rol de Administrador?
        if (! auth()->user()->isAdmin()) {
            // Emite respuesta HTTP 403 Forbidden que renderiza 'resources/views/errors/403.blade.php'
            abort(403, 'Acceso restringido: Esta sección requiere privilegios de Administrador.');
        }

        // 3. Autorización exitosa: Continuar hacia el controlador administrativo
        return $next($request);
    }
}
