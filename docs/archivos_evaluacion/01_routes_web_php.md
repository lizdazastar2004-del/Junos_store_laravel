# 🛣️ Archivo: `routes/web.php` — Enrutador Central de la Aplicación

## 1. Ficha Técnica

* **Ubicación:** `routes/web.php`
* **Tipo:** Archivo de configuración de rutas web de Laravel.
* **Capa:** Entrada / Routing (Enrutamiento HTTP).
* **Propósito:** Definir los puntos de entrada HTTP, mapear URLs a métodos específicos de controladores, asociar nombres de ruta para enlaces dinámicos y aplicar middlewares de protección.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    ClientReq[Petición del Navegador] --> WebRoutes[routes/web.php]

    subgraph "Rutas Públicas"
        WebRoutes -->|GET /| TiendaIndex[TiendaController@index]
        WebRoutes -->|GET /productos/{id}| TiendaShow[TiendaController@show]
        WebRoutes -->|GET /login| AuthShowLogin[AuthController@showLogin]
        WebRoutes -->|POST /login| AuthLogin[AuthController@login]
        WebRoutes -->|POST /logout| AuthLogout[AuthController@logout]
        WebRoutes -->|GET /registro| AuthShowReg[AuthController@showRegistro]
        WebRoutes -->|POST /registro| AuthReg[AuthController@registro]
    end

    subgraph "Grupo Protegido (/admin)"
        WebRoutes -->|Prefijo: admin + Middlewares: auth, admin| AdminGroup[Grupo Admin]
        AdminGroup -->|GET /admin| AdminDash[view: admin.dashboard]
        AdminGroup -->|GET /admin/productos| ProdIndex[AdminProductoController@index]
        AdminGroup -->|GET /admin/productos/crear| ProdCreate[AdminProductoController@create]
        AdminGroup -->|POST /admin/productos| ProdStore[AdminProductoController@store]
        AdminGroup -->|GET /admin/productos/{id}/editar| ProdEdit[AdminProductoController@edit]
        AdminGroup -->|PUT /admin/productos/{id}| ProdUpdate[AdminProductoController@update]
        AdminGroup -->|DELETE /admin/productos/{id}| ProdDestroy[AdminProductoController@destroy]
    end
```

---

## 3. Código Fuente Completo y Explicación Línea por Línea

```php
<?php

use App\Http\Controllers\AdminProductoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TiendaController;
use Illuminate\Support\Facades\Route;
```
* **Líneas 1-6:** Importación de namespaces necesarios. Se importan los tres controladores principales (`AdminProductoController`, `AuthController`, `TiendaController`) y la fachada estática `Route`.

---

### Bloque A: Rutas Públicas de la Tienda
```php
// =====================================================
// RUTAS PÚBLICAS - TIENDA
// =====================================================

Route::get('/', [TiendaController::class, 'index'])->name('tienda.index');
Route::get('/productos/{id}', [TiendaController::class, 'show'])->name('tienda.producto');
```
* **Línea 12 (`GET /`):** Define la raíz del sitio web. Ejecuta el método `index` de `TiendaController`. Su nombre `tienda.index` permite invocarla en Blade con `route('tienda.index')`.
* **Línea 13 (`GET /productos/{id}`):** Ruta de detalle individual. Recibe el parámetro dinámico `{id}` en la URL y lo transfiere al método `show($id)` de `TiendaController`.

---

### Bloque B: Rutas de Autenticación
```php
// =====================================================
// RUTAS DE AUTENTICACIÓN
// =====================================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registro']);
```
* **Líneas 19-20:** El método `GET /login` despliega el formulario visual kawaii de acceso. El método `POST /login` recibe las credenciales (`email` y `password`) para procesar el intento de autenticación.
* **Línea 21 (`POST /logout`):** Cierra la sesión activa del usuario, invalida el token de sesión y regenera el token CSRF para prevenir ataques de fijación de sesión.
* **Líneas 23-24:** El método `GET /registro` muestra la vista con el formulario de nuevo usuario. El método `POST /registro` procesa los datos, valida reglas y persiste el registro en MySQL.

---

### Bloque C: Rutas Administrativas Protegidas
```php
// =====================================================
// RUTAS ADMINISTRADOR (requieren autenticación)
// =====================================================

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Productos
    Route::get('/productos', [AdminProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/crear', [AdminProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [AdminProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{id}/editar', [AdminProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}', [AdminProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}', [AdminProductoController::class, 'destroy'])->name('productos.destroy');
});
```
* **Línea 30:** 
  * `prefix('admin')`: Antepone automáticamente `/admin` a todas las URLs dentro del grupo (ej. `/admin/productos`).
  * `middleware(['auth', 'admin'])`: Pipeline de doble seguridad. Primero verifica que el usuario esté logueado (`auth`); luego, el `AdminMiddleware` comprueba que posea rol de administrador (`id_rol == 1`).
  * `name('admin.')`: Prefijo de nombres de ruta (ej. `admin.dashboard`, `admin.productos.index`).
* **Líneas 33-35:** Renderiza directamente la vista del panel administrativo `admin.dashboard`.
* **Líneas 38-43 (RESTful CRUD de Productos):**
  * `GET /admin/productos` $\rightarrow$ Listado paginado con filtros.
  * `GET /admin/productos/crear` $\rightarrow$ Formulario de alta.
  * `POST /admin/productos` $\rightarrow$ Guardado de nuevo producto en base de datos.
  * `GET /admin/productos/{id}/editar` $\rightarrow$ Formulario con datos precargados para modificación.
  * `PUT /admin/productos/{id}` $\rightarrow$ Actualización en MySQL (usa method spoofing `@method('PUT')` en HTML).
  * `DELETE /admin/productos/{id}` $\rightarrow$ Eliminación física/lógica del registro.

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Por qué se usan nombres de ruta (`name()`) en lugar de URLs fijas?**
   * *Respuesta:* Permite desacoplar las vistas de las URLs físicas. Si en el futuro cambiamos `/productos/{id}` por `/catalogo/{id}`, solo modificamos `routes/web.php` y ningún enlace en las vistas Blade se romperá.
2. **¿Cómo funciona el `middleware(['auth', 'admin'])`?**
   * *Respuesta:* Funciona como una tubería secuencial (pipeline). Si un usuario anónimo intenta acceder a `/admin/productos`, el middleware `auth` lo redirige a `/login`. Si entra un usuario autenticado pero con rol de cliente, el middleware `admin` lo bloquea con un código HTTP 403 (Acceso Prohibido).
3. **¿Por qué las rutas de actualización y borrado usan `PUT` y `DELETE` si HTML solo soporta `GET` y `POST`?**
   * *Respuesta:* Laravel utiliza *Method Spoofing* a través de la directiva `@method('PUT')` o `@method('DELETE')`, la cual inyecta un campo oculto `_method` que el kernel de Laravel interpreta para despachar la ruta correcta.
