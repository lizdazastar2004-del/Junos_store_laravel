# 🛡️ Archivo: `app/Http/Middleware/AdminMiddleware.php` — Middleware de Control de Acceso (RBAC)

## 1. Ficha Técnica

* **Ubicación:** `app/Http/Middleware/AdminMiddleware.php`
* **Tipo:** Middleware HTTP de Laravel.
* **Capa:** Seguridad y Filtros HTTP (Pipeline).
* **Propósito:** Actuar como barrera perimetral en todas las rutas bajo el prefijo `/admin/*`. Verifica que la petición provenga de un usuario con sesión activa y cuyos privilegios correspondan al rol de administrador (`id_rol === 1`).

---

## 2. Mapa de Conexiones

```mermaid
flowchart TD
    Req[Petición entrante a /admin/*] --> MiddlewareAuth[Middleware auth de Laravel]
    
    MiddlewareAuth -->|¿Tiene sesión activa?| CheckAuth{auth()->check()}
    CheckAuth -->|No| RedirectLogin[Redirige a /login con error]
    
    CheckAuth -->|Sí| AdminMiddleware[🛡️ AdminMiddleware]
    AdminMiddleware -->|¿Es administrador?| CheckAdmin{auth()->user()->isAdmin()}
    
    CheckAdmin -->|No (Rol Cliente)| RedirectTienda[Redirige a / con error de permisos]
    CheckAdmin -->|Sí (id_rol === 1)| NextClosure[$next($request) $\rightarrow$ Controlador Admin]
```

---

## 3. Código Fuente y Explicación Detallada

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificación de Autenticación
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión');
        }

        // 2. Verificación de Rol Administrativo
        if (! auth()->user()->isAdmin()) {
            return redirect()->route('tienda.index')->with('error', 'No tienes permisos de administrador');
        }

        // 3. Continuación de la petición hacia el siguiente middleware o controlador
        return $next($request);
    }
}
```

### Análisis Técnico:
1. **Patrón de Diseño *Pipeline / Chain of Responsibility*:** Los middlewares en Laravel reciben la petición `$request` y una clausura `$next`. El middleware puede interceptar, modificar, rechazar o permitir el paso de la solicitud.
2. **`auth()->check()`:** Helper global de autenticación que consulta el guard por defecto. Si no hay una sesión activa válida, aborta el flujo y redirige al formulario de login.
3. **`auth()->user()->isAdmin()`:** Invoca el método auxiliar definido en el modelo `Usuario.php`:
   ```php
   public function isAdmin(): bool
   {
       return (int) $this->id_rol === 1;
   }
   ```
4. **Registro en `bootstrap/app.php`:**
   En Laravel 11 y 12, los middlewares se registran en `bootstrap/app.php` mediante el método `withMiddleware`:
   ```php
   ->withMiddleware(function (Middleware $middleware): void {
       $middleware->alias([
           'admin' => AdminMiddleware::class,
       ]);
   })
   ```
   Esto permite que en `routes/web.php` se declare de forma limpia como: `middleware(['auth', 'admin'])`.

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Qué ocurre si un usuario normal (cliente) escribe directamente en el navegador `http://127.0.0.1:8000/admin/productos`?**
   * *Respuesta:* El middleware `admin` intercepta la petición antes de que llegue a `AdminProductoController`. Al evaluar `auth()->user()->isAdmin()` como `false`, lo redirige a la página principal con el mensaje de sesión: *"No tienes permisos de administrador"*.
2. **¿Por qué se evalúa `auth()->check()` antes de `auth()->user()->isAdmin()`?**
   * *Respuesta:* Para evitar un error fatal de PHP (*Call to a member function isAdmin() on null*). Si el usuario no está logueado, `auth()->user()` retorna `null`. Evaluar primero `auth()->check()` garantiza que `auth()->user()` sea una instancia válida de `Usuario`.
3. **¿Cómo se vincula este middleware con las rutas?**
   * *Respuesta:* Se le asigna el alias `'admin'` en `bootstrap/app.php` y se aplica al grupo de rutas en `routes/web.php` mediante `Route::prefix('admin')->middleware(['auth', 'admin'])->group(...)`.
