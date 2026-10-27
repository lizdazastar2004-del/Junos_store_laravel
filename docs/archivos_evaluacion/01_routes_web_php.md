# 🛣️ Archivo: `routes/web.php` — Enrutador Central de la Aplicación

## 1. Ficha Técnica

* **Ubicación:** `routes/web.php`
* **Tipo:** Archivo de configuración de rutas web de Laravel.
* **Capa:** Entrada / Routing (Enrutamiento HTTP).
* **Propósito:** Definir todos los puntos de entrada HTTP de la aplicación, asociar URLs con métodos de controladores, asignar nombres de ruta (`name()`) para enlaces dinámicos desacoplados y aplicar pipelines de middlewares para seguridad y control de acceso.

---

## 2. Mapa Completo de Conexiones y Rutas

```mermaid
graph TD
    ClientReq[🌐 Petición del Navegador Web] --> WebRoutes[routes/web.php]

    subgraph "1. Rutas Públicas (Sin sesión requerida)"
        WebRoutes -->|GET /| TiendaIndex[TiendaController@index]
        WebRoutes -->|GET /catalogo| TiendaCat[TiendaController@index]
        WebRoutes -->|GET /productos/{id}| TiendaShow[TiendaController@show]
        WebRoutes -->|GET /login| AuthShowLogin[AuthController@showLogin]
        WebRoutes -->|POST /login| AuthLogin[AuthController@login]
        WebRoutes -->|POST /logout| AuthLogout[AuthController@logout]
        WebRoutes -->|GET /registro| AuthShowReg[AuthController@showRegistro]
        WebRoutes -->|POST /registro| AuthReg[AuthController@registro]
    end

    subgraph "2. Rutas del Cliente (middleware: auth)"
        WebRoutes -->|middleware: auth| ClientGroup[Área Cliente]
        ClientGroup -->|GET /carrito| CarritoIndex[CarritoController@index]
        ClientGroup -->|POST /carrito/agregar| CarritoAdd[CarritoController@agregar]
        ClientGroup -->|POST /carrito/actualizar| CarritoUpd[CarritoController@actualizar]
        ClientGroup -->|POST /carrito/eliminar| CarritoDel[CarritoController@eliminar]
        ClientGroup -->|POST /carrito/descuento| CarritoDesc[CarritoController@aplicarDescuento]
        ClientGroup -->|GET /checkout| PedidoCheck[PedidoController@checkout]
        ClientGroup -->|POST /pedidos/confirmar| PedidoConf[PedidoController@confirmar]
        ClientGroup -->|GET /pedidos/confirmacion/{id}| PedidoOk[PedidoController@confirmacion]
        ClientGroup -->|GET /pedidos/historial| PedidoHist[PedidoController@historial]
        ClientGroup -->|GET /pedidos/{id}| PedidoDet[PedidoController@detalle]
        ClientGroup -->|GET /mi-cuenta| CliDash[ClienteController@dashboard]
        ClientGroup -->|POST /cliente/perfil| CliPerfil[ClienteController@actualizarPerfil]
        ClientGroup -->|POST /cliente/password| CliPass[ClienteController@cambiarPassword]
        ClientGroup -->|POST /cliente/direccion| CliDirStore[ClienteController@guardarDireccion]
        ClientGroup -->|DELETE /cliente/direccion/{id}| CliDirDel[ClienteController@eliminarDireccion]
    end

    subgraph "3. Rutas Administrativas (prefix: admin + middleware: [auth, admin])"
        WebRoutes -->|prefix: admin + middleware: [auth, admin]| AdminGroup[Área Administrativa]
        AdminGroup -->|GET /admin| AdminDash[AdminDashboardController@index]
        AdminGroup -->|POST /admin/mantenimiento/*| AdminMaint[Herramientas de Mantenimiento]
        AdminGroup -->|CRUD /admin/productos| AdminProd[AdminProductoController CRUD]
        AdminGroup -->|GET /admin/pedidos| AdminPedIndex[AdminPedidoController@index]
        AdminGroup -->|GET /admin/pedidos/{id}| AdminPedShow[AdminPedidoController@show]
        AdminGroup -->|POST /admin/pedidos/{id}/estado| AdminPedStatus[AdminPedidoController@cambiarEstado]
        AdminGroup -->|GET /admin/usuarios| AdminUserIndex[AdminUsuarioController@index]
        AdminGroup -->|POST /admin/usuarios/{id}/toggle| AdminUserToggle[AdminUsuarioController@toggleActivo]
    end
```

---

## 3. Código Fuente Actual y Explicación Detallada

```php
<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPedidoController;
use App\Http\Controllers\AdminProductoController;
use App\Http\Controllers\AdminUsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\TiendaController;
use Illuminate\Support\Facades\Route;
```
* **Líneas 1-12:** Importación explícita de controladores con su namespace (`App\Http\Controllers\...`) y de la fachada estática `Route`. En Laravel 11/12 se utiliza la sintaxis de array invocable `[Controlador::class, 'metodo']`, que proporciona detección de errores en tiempo de compilación y soporte en IDEs.

---

### Bloque A: Rutas Públicas (Tienda y Catálogo)
```php
Route::get('/', [TiendaController::class, 'index'])->name('tienda.index');
Route::get('/catalogo', [TiendaController::class, 'index'])->name('tienda.catalogo');
Route::get('/productos/{id}', [TiendaController::class, 'show'])->name('tienda.producto');
```
* `tienda.index` y `tienda.catalogo`: Apuntan al mismo método `index()` para permitir navegación fluida desde la barra de menú o desde el banner principal.
* `tienda.producto`: Recibe el parámetro dinámico `{id}` en la URL y lo inyecta como primer argumento al método `show($id)`.

---

### Bloque B: Rutas del Carrito, Checkout y Portal Cliente (`auth`)
```php
Route::middleware('auth')->group(function () {
    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::post('/carrito/actualizar', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::post('/carrito/eliminar', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    Route::post('/carrito/descuento', [CarritoController::class, 'aplicarDescuento'])->name('carrito.descuento');

    // Checkout y Pedidos
    Route::get('/checkout', [PedidoController::class, 'checkout'])->name('checkout');
    Route::post('/pedidos/confirmar', [PedidoController::class, 'confirmar'])->name('pedidos.confirmar');
    Route::get('/pedidos/confirmacion/{id}', [PedidoController::class, 'confirmacion'])->name('pedidos.confirmacion');
    Route::get('/pedidos/historial', [PedidoController::class, 'historial'])->name('pedidos.historial');
    Route::get('/pedidos/{id}', [PedidoController::class, 'detalle'])->name('pedidos.detalle');

    // Portal Personal del Cliente ("Mi Espacio Kawaii")
    Route::get('/mi-cuenta', [ClienteController::class, 'dashboard'])->name('cliente.dashboard');
    Route::post('/cliente/perfil', [ClienteController::class, 'actualizarPerfil'])->name('cliente.perfil.actualizar');
    Route::post('/cliente/password', [ClienteController::class, 'cambiarPassword'])->name('cliente.password.actualizar');
    Route::post('/cliente/direccion', [ClienteController::class, 'guardarDireccion'])->name('cliente.direccion.guardar');
    Route::delete('/cliente/direccion/{id}', [ClienteController::class, 'eliminarDireccion'])->name('cliente.direccion.eliminar');
});
```
* **`middleware('auth')`:** Aplica el guardián de autenticación. Si el visitante no ha iniciado sesión, Laravel lo intercepta y lo envía de inmediato a la ruta con nombre `login`.
* Permite agrupar las acciones que requieren conocer la identidad del comprador (`Auth::id()`) para persistir su carrito y pedidos en MySQL.

---

### Bloque C: Rutas Administrativas Protegidas (`auth` + `admin`)
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Mantenimiento
    Route::post('/mantenimiento/activar-stock', [AdminDashboardController::class, 'activarProductosConStock'])->name('mantenimiento.activar-stock');
    Route::post('/mantenimiento/reponer-agotados', [AdminDashboardController::class, 'reponerStockAgotados'])->name('mantenimiento.reponer-agotados');
    Route::post('/mantenimiento/limpiar-cache', [AdminDashboardController::class, 'limpiarCache'])->name('mantenimiento.limpiar-cache');
    Route::post('/mantenimiento/limpiar-carritos', [AdminDashboardController::class, 'limpiarCarritos'])->name('mantenimiento.limpiar-carritos');
    Route::post('/mantenimiento/cargar-datos-prueba', [AdminDashboardController::class, 'cargarDatosPrueba'])->name('mantenimiento.cargar-datos-prueba');
    Route::post('/productos/{id}/ajustar-stock', [AdminDashboardController::class, 'ajustarStock'])->name('productos.ajustar-stock');

    // CRUD Productos
    Route::get('/productos', [AdminProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/crear', [AdminProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [AdminProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{id}/editar', [AdminProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}', [AdminProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}', [AdminProductoController::class, 'destroy'])->name('productos.destroy');

    // Pedidos y Usuarios
    Route::get('/pedidos', [AdminPedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{id}', [AdminPedidoController::class, 'show'])->name('pedidos.show');
    Route::post('/pedidos/{id}/estado', [AdminPedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
    Route::get('/usuarios', [AdminUsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('/usuarios/{id}/toggle', [AdminUsuarioController::class, 'toggleActivo'])->name('usuarios.toggle');
});
```

---

## 4. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

Esta sección es fundamental para tu evaluación: si el evaluador te pregunta *"¿Qué pasa si borras esta línea?"*, aquí tienes la respuesta técnica exacta:

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **`middleware(['auth', 'admin'])`** | Ningún error de código (código 200 OK). | **Brecha crítica de seguridad:** Cualquier visitante anónimo o cliente normal podría entrar a `/admin`, ver los ingresos monetarios, crear o borrar productos y alterar el stock sin tener cuenta de administrador. |
| **`middleware('auth')` en el grupo de Carrito** | `Error: Call to a member function id() on null` en los controladores. | Al no existir usuario autenticado, `$request->user()->id_usuario` o `Auth::id()` devuelve `null`. El sistema colapsa al intentar buscar el carrito del cliente en base de datos. |
| **`->name('...')` en una ruta** | `Symfony\Component\Routing\Exception\RouteNotFoundException: Route [nombre.ruta] not defined.` | Todas las vistas Blade que utilicen `route('nombre.ruta')` o los redireccionamientos en los controladores (`redirect()->route(...)`) romperán la aplicación con pantalla de error 500. |
| **`{id}` en `Route::get('/productos/{id}')`** | `ArgumentCountError: Too few arguments to function TiendaController::show(), 0 passed.` | El controlador espera obligatoriamente el parámetro `$id`. Si la ruta no define `{id}`, Laravel no sabrá qué producto consultar y la aplicación generará una excepción 500. |
| **`prefix('admin')`** | `404 Not Found` al visitar URLs como `/admin/productos`. | Todas las rutas administrativas pasan a vivir en la raíz (ej. `/productos`), colisionando o sobrescribiendo las rutas públicas de la tienda. |
| **`name('admin.')`** | `RouteNotFoundException: Route [admin.dashboard] not defined.` | El prefijo de nombre evita escribir `admin.` manualmente en cada una de las 15 rutas hijas. Si se quita, fallan todas las redirecciones del panel. |

---

## 5. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si un usuario no logueado intenta entrar a `/carrito` o `/checkout`?
* **Comportamiento:** El middleware `auth` captura la petición antes de que toque el controlador, guarda la URL que deseaba en la sesión (`url.intended`) y lo redirige automáticamente a `/login`. Tras loguearse con éxito, `intended()` lo devuelve automáticamente a su carrito.

### Escenario 2: ¿Qué pasa si un usuario con rol de cliente (`id_rol = 2`) ingresa manualmente `/admin` en su navegador?
* **Comportamiento:** Pasa el middleware `auth` (porque está logueado), pero es bloqueado en seco por `AdminMiddleware`, el cual ejecuta `abort(403, 'Acceso no autorizado al panel administrativo')`. El usuario ve una pantalla de error 403 y nunca tiene acceso a datos de gestión.

### Escenario 3: ¿Qué pasa si un cliente intenta ver el detalle de un pedido que pertenece a otro usuario (`/pedidos/{id}`)?
* **Comportamiento:** En `PedidoController@detalle`, se ejecuta la validación:
  ```php
  if ($pedido->id_usuario !== Auth::id()) {
      abort(403, 'No tienes permiso para ver este pedido');
  }
  ```
  Esto garantiza que ningún cliente pueda espiar las compras de otra persona mediante *Insecure Direct Object Reference* (IDOR).

### Escenario 4: ¿Qué pasa si se envía un formulario POST sin `@csrf`?
* **Comportamiento:** Laravel rechaza la petición con una pantalla de error **`419 | Page Expired`**. Esto protege al sistema contra ataques de falsificación de peticiones en sitios cruzados (*Cross-Site Request Forgery*).

---

## 6. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: `MethodNotAllowedHttpException: The POST method is not supported for route admin/productos/5. Supported methods: PUT.`
* **Causa:** El formulario HTML de edición en Blade tiene `<form method="POST">` pero olvidó incluir la directiva `@method('PUT')`.
* **Solución:** Agregar `@method('PUT')` inmediatamente debajo de `@csrf` dentro del `<form>`.

### Problema 2: `RouteNotFoundException: Route [admin.dashboard] not defined`
* **Causa:** La caché de rutas de Laravel quedó obsoleta tras editar `routes/web.php`.
* **Solución:** Ejecutar en la terminal de Laragon:
  ```bash
  php artisan route:clear
  ```

### Problema 3: Las rutas nuevas no se ven reflejadas o arrojan 404
* **Causa:** Conflicto de orden en las rutas. Si defines `Route::get('/productos/{id}')` ANTES de `Route::get('/productos/crear')`, Laravel pensará que la palabra "crear" es un `{id}` numérico.
* **Solución:** En Laravel las rutas estáticas específicas siempre deben colocarse **antes** que las rutas con comodines dinámicos `{id}`.
