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
    Req[🌐 Petición entrante a /admin/*] --> MiddlewareAuth[Middleware auth de Laravel]
    
    MiddlewareAuth -->|¿Tiene sesión activa?| CheckAuth{auth()->check()}
    CheckAuth -->|No| RedirectLogin[Redirige a /login con error]
    
    CheckAuth -->|Sí| AdminMiddleware[🛡️ AdminMiddleware]
    AdminMiddleware -->|¿Es administrador?| CheckAdmin{auth()->user()->isAdmin()}
    
    CheckAdmin -->|No (Rol Cliente)| RedirectTienda[Redirige a / o abort 403]
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

## 4. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **La línea `if (! auth()->check())`** | `Error: Call to a member function isAdmin() on null` (Error 500 fatal). | Si un usuario anónimo (sin login) entra a `/admin`, `auth()->user()` es `null`. Llamar a `null->isAdmin()` tumba la aplicación con un error fatal en lugar de enviarlo a login. |
| **La línea `if (! auth()->user()->isAdmin())`** | Ningún error de código (código 200 OK). | **Brecha de seguridad total:** Cualquier usuario registrado como cliente normal (`id_rol = 2`) podría acceder a todo el panel de control administrativo, alterar pedidos, modificar precios y borrar usuarios. |
| **La línea `return $next($request);`** | `TypeError: AdminMiddleware::handle(): Return value must be of type Response, null returned.` | La petición queda en el limbo y nunca llega al controlador. Todas las pantallas de administración se quedan en blanco con error 500. |
| **El alias `'admin' => AdminMiddleware::class` en `bootstrap/app.php`** | `InvalidArgumentException: Target class [admin] does not exist.` | Laravel no reconoce la palabra clave `admin` en `routes/web.php`, rompiendo todo el enrutamiento de la aplicación al intentar cargar cualquier página. |

---

## 5. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si un usuario cliente intenta manipular la URL y navegar a `/admin/productos/crear`?
* **Comportamiento:** Pasa el middleware `auth` porque tiene cuenta, pero al llegar a `AdminMiddleware`, `auth()->user()->isAdmin()` devuelve `false`. Es redirigido de inmediato a la tienda pública con un mensaje flash de alerta: *"No tienes permisos de administrador"*.

### Escenario 2: ¿Qué pasa si un administrador pierde su conexión o se le borra la sesión mientras está en el panel?
* **Comportamiento:** Al dar clic en cualquier botón o menú, `auth()->check()` da `false`. Es redirigido de inmediato a `/login` con el mensaje: *"Debes iniciar sesión"*, protegiendo la terminal si se deja desatendida.

---

## 6. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: `Target class [admin] does not exist`
* **Causa:** El middleware fue creado pero no fue registrado en el kernel/bootstrap de Laravel.
* **Solución:** Abrir `bootstrap/app.php` y agregar en `withMiddleware`:
  ```php
  $middleware->alias([
      'admin' => \App\Http\Middleware\AdminMiddleware::class,
  ]);
  ```

### Problema 2: El usuario es administrador pero el middleware no lo deja pasar
* **Causa:** En la base de datos MySQL, el usuario tiene un valor de `id_rol` diferente de 1 (por ejemplo `null` o `2`), o el tipo de dato se compara estrictamente sin casting (`"1" !== 1`).
* **Solución:** En `Usuario.php`, usamos `(int) $this->id_rol === 1` para asegurar la conversión numérica sin importar si MySQL retorna string o int.

---

## 7. 🎓 Preguntas Frecuentes para Evaluación

1. **¿Qué ocurre si un usuario normal (cliente) escribe directamente en el navegador `http://127.0.0.1:8000/admin/productos`?**
   * *Respuesta:* El middleware `admin` intercepta la petición antes de que llegue a `AdminProductoController`. Al evaluar `auth()->user()->isAdmin()` como `false`, lo redirige a la página principal con el mensaje de sesión: *"No tienes permisos de administrador"*.
2. **¿Por qué se evalúa `auth()->check()` antes de `auth()->user()->isAdmin()`?**
   * *Respuesta:* Para evitar un error fatal de PHP (*Call to a member function isAdmin() on null*). Si el usuario no está logueado, `auth()->user()` retorna `null`. Evaluar primero `auth()->check()` garantiza que `auth()->user()` sea una instancia válida de `Usuario`.
3. **¿Cómo se vincula este middleware con las rutas?**
   * *Respuesta:* Se le asigna el alias `'admin'` en `bootstrap/app.php` y se aplica al grupo de rutas en `routes/web.php` mediante `Route::prefix('admin')->middleware(['auth', 'admin'])->group(...)`.
