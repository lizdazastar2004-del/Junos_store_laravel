# 08. Guía de Estudio para Sustentación y Evaluación — Juno's Store

> [!TIP]
> **Propósito de esta guía:** Este documento está redactado de manera clara, pedagógica y directa para que un estudiante de desarrollo de software (SENA / Universidad) pueda comprender a fondo su propio proyecto, responder con seguridad las preguntas del instructor evaluador y realizar una defensa oral exitosa de **Juno's Store**.

---

## 1. Resumen Ejecutivo para la Presentación Oral (Speech de 2 Minutos)

> *"Buenos días, profesor/evaluadores. Mi proyecto se llama **Juno's Store**, una tienda virtual de comercio electrónico orientada a la venta minorista de papelería kawaii, accesorios y coleccionables.*
>
> *El sistema fue concebido originalmente bajo la arquitectura MVC en PHP nativo y ha sido modernizado y estructurado utilizando el framework **Laravel** y una base de datos relacional **MySQL**.*
>
> *La aplicación resuelve la desorganización en el inventario y la falta de un canal de ventas digital propio, proporcionando dos áreas principales:*
> 1. *Un **módulo público para clientes**, con un diseño visual cuidado en paleta pastel y kawaii, catálogo dinámico, fichas de producto con recomendaciones y sistema de autenticación seguro.*
> 2. *Un **panel de administración**, donde el administrador puede gestionar el ciclo de vida completo de los productos (crear, editar, consultar y eliminar existencias con cálculo de stock mínimo y generación automática de SKU) y administrar los usuarios mediante tablas interactivas con filtros y exportación a Excel y PDF.*
>
> *Para la construcción del sistema apliqué las buenas prácticas enseñadas por el instructor: separación estricta de responsabilidades bajo MVC, modelos Eloquent con relaciones bien definidas, validación robusta de formularios en el servidor, protección contra ataques CSRF e inyección SQL, y control de acceso basado en roles mediante middlewares.*
>
> *A continuación, les mostraré la arquitectura del código y el funcionamiento de los flujos principales."*

---

## 2. Conceptos Fundamentales de Laravel Explicados Fácil

| Concepto | ¿Qué es en palabras sencillas? | ¿Cómo se aplica en Juno's Store? |
| :--- | :--- | :--- |
| **MVC** | Patrón que divide el sistema en 3 partes: Datos (Modelo), Pantallas (Vista) y Cerebro lógico (Controlador). | `Producto` (Modelo) consulta la base de datos, `AdminProductoController` (Controlador) valida el formulario y `tienda.index` (Vista) muestra la tarjeta kawaii. |
| **Ruta (`routes/web.php`)** | La dirección o URL que el usuario escribe en el navegador y que le dice a Laravel a qué controlador debe llamar. | `Route::get('/productos/{id}', [TiendaController::class, 'show'])` responde cuando el usuario hace clic en un producto. |
| **Controlador (`Controller`)** | La clase PHP que recibe la petición del usuario, valida que los datos sean correctos, pide los datos al modelo y decide qué vista mostrar. | `AuthController@login` valida el correo y la contraseña y, si son correctos, inicia la sesión con `Auth::login()`. |
| **Modelo Eloquent (`Model`)** | Una clase PHP que representa una tabla de MySQL. Permite interactuar con la base de datos sin escribir SQL a mano. | `Producto::where('activo', 1)->get()` hace un `SELECT * FROM productos WHERE activo = 1` automáticamente. |
| **Vista Blade (`.blade.php`)** | El archivo HTML con superpoderes de Laravel. Permite hacer ciclos `@foreach`, condiciones `@if` y heredar layouts con `@extends`. | `resources/views/tienda/index.blade.php` recorre los productos con `@foreach($productos as $producto)`. |
| **Middleware** | Un "guardián" o filtro que revisa la petición antes de que llegue al controlador. | `AdminMiddleware` y `CheckRole`: revisan si el usuario tiene rol de Administrador antes de dejarlo entrar a `/admin`. Si no, lo bloquea con error 403. |
| **Token CSRF (`@csrf`)** | Un código secreto que Laravel genera en cada formulario para asegurar que la petición viene de nuestro propio sitio y no de un atacante externo. | Se coloca `@csrf` dentro de la etiqueta `<form>` en el login, registro y creación de productos. |
| **Eager Loading (`with`)** | Técnica para cargar relaciones de base de datos en una sola consulta optimizada. | `Producto::with('categoria')->paginate(12)` trae de una sola vez los productos con los nombres de sus categorías. |

---

## 3. Diferencias Clave: PHP Tradicional vs. Laravel

Si el instructor te pregunta: *"¿Por qué migrar a Laravel si ya funcionaba en PHP puro?"*, responde con estos argumentos técnicos:

1. **Seguridad Integrada:**
   * En PHP nativo hay que acordarse de usar `mysqli_real_escape_string()` o PDO preparado en cada consulta. En Laravel, **Eloquent ORM** parametriza todas las consultas automáticamente, eliminando el riesgo de inyección SQL.
   * La protección CSRF en formularios y la encriptación de contraseñas con **Bcrypt** vienen configuradas de fábrica.
2. **Organización del Código:**
   * En PHP puro es común mezclar código SQL, lógica PHP y etiquetas HTML en el mismo archivo (`producto.php`).
   * En Laravel la separación es obligatoria: el modelo solo toca datos, el controlador solo ejecuta lógica y Blade solo muestra HTML.
3. **Mantenibilidad y Reutilización:**
   * Con Blade usamos `@extends('layouts.app')`, lo que significa que el encabezado y el pie de página están en un solo archivo. Si queremos cambiar el logo o el menú, lo editamos una sola vez y se actualiza en todas las páginas.

---

## 4. Preguntas Típicas del Instructor y Cómo Responderlas

### Pregunta 1: *"¿Dónde se define la conexión a la base de datos y cómo se protege la contraseña?"*
* **Respuesta:**
  *"Profesor, la conexión se configura en el archivo `config/database.php`, pero los valores sensibles nunca se escriben en código duro. Se leen desde las variables de entorno del archivo `.env` (`DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_DATABASE=junos_store`, `DB_USERNAME=root`, `DB_PASSWORD=`). El archivo `.env` está excluido en el `.gitignore` para que las credenciales nunca se suban a un repositorio público como GitHub."*

---

### Pregunta 2: *"¿Cómo funciona el flujo completo desde que hago clic en un producto hasta que se muestra su detalle?"*
* **Respuesta:**
  *"1. El usuario hace clic en el enlace `<a href="{{ route('tienda.producto', $producto->id_producto) }}">`.
  2. La petición `GET /productos/5` llega a `routes/web.php`.
  3. La ruta delega la ejecución al método `show($id)` de `TiendaController`.
  4. El controlador ejecuta `Producto::with(['categoria', 'subcategoria', 'marca', 'imagenes'])->findOrFail($id)`. Si el producto no existe, arroja un 404. Si existe, busca también 4 productos relacionados de la misma categoría.
  5. El controlador inyecta los datos con `view('tienda.detalle', compact('producto', 'relacionados'))`.
  6. Blade compila la plantilla heredando `layouts.app` y muestra la galería, el precio y la información."*

---

### Pregunta 3: *"¿Cómo evitan que un usuario común entre a las páginas del administrador?"*
* **Respuesta:**
  *"Utilizamos un **Middleware** de autenticación y autorización. En `routes/web.php` todas las rutas administrativas están agrupadas bajo el prefijo `admin` y protegidas por los middlewares `auth` y `admin` o `role:admin`:
  ```php
  Route::prefix('admin')->middleware(['auth', 'admin'])->group(...);
  ```
  Cuando alguien intenta ingresar a `/admin/productos`, el middleware intercepta la petición. Primero comprueba si hay sesión iniciada; si no la hay, redirige al `/login`. Si hay sesión iniciada, verifica si el usuario tiene rol de administrador (`$user->isAdmin()` o `$user->role_id == 1`). Si no es administrador, aborta la petición con código HTTP 403 (Acceso No Autorizado)."*

---

### Pregunta 4: *"¿Dónde y cómo validan que los datos de un nuevo producto sean correctos?"*
* **Respuesta:**
  *"La validación se realiza en el método `store(Request $request)` de `AdminProductoController` utilizando `$request->validate([...])`. Verificamos que el nombre sea obligatorio y no supere 180 caracteres, que el precio sea numérico y mayor o igual a cero (`numeric|min:0`), que la categoría exista en la tabla `categorias` (`exists:categorias,id_categoria`), y que los stocks sean enteros no negativos. Si alguna regla se incumple, Laravel detiene el proceso y devuelve los errores a la vista para mostrarlos con la directiva `@error`."*

---

### Pregunta 5: *"¿Qué es el método `compact()` que usan al retornar las vistas?"*
* **Respuesta:**
  *"Es una función estándar de PHP que toma nombres de variables como cadenas de texto y crea un arreglo asociativo clave-valor. Al escribir `return view('tienda.index', compact('productos', 'categorias'));` equivale a pasar `['productos' => $productos, 'categorias' => $categorias]`. De esta forma, Blade puede acceder directamente a las variables `$productos` y `$categorias`."*

---

### Pregunta 6: *"¿Qué son los modales y por qué los usaron en la lista de usuarios?"*
* **Respuesta:**
  *"Es una metodología tomada del proyecto del instructor. En lugar de redirigir al administrador a otra página para editar un usuario, cada fila de la tabla tiene un botón con atributos de Bootstrap `data-bs-toggle="modal"` y `data-bs-target="#modal{{ $usuario->id }}"`. Al pulsarlo, se abre una ventana emergente en la misma pantalla con el formulario de edición. Esto hace que la administración sea mucho más rápida y cómoda sin perder el contexto de la tabla."*

---

## 5. Archivos Críticos que Debes Saber Abrir y Explicar

Si el instructor te pide *"muéstrame el código donde hiciste X cosa"*, ten a mano estas ubicaciones:

1. **Rutas:** `routes/web.php` (mostrar cómo se dividen rutas públicas, auth y grupo admin).
2. **Controlador de Productos:** `app/Http/Controllers/AdminProductoController.php` (mostrar `store` y `update`).
3. **Controlador de Autenticación:** `app/Http/Controllers/AuthController.php` (mostrar `login` con `Hash::check()`).
4. **Modelo con Relaciones:** `app/Models/Producto.php` (mostrar `$fillable`, `categoria()`, `imagenes()`).
5. **Layout Principal:** `resources/views/layouts/app.blade.php` (mostrar el `@yield('content')` y cómo se incluye el CSS kawaii).
6. **Vista de Gestión con DataTables:** `resources/views/admin/gusuarios/listausers.blade.php` (mostrar la tabla, los filtros y los modales).
