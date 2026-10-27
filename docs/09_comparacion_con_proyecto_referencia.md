# 09. Comparación con el Proyecto de Referencia del Instructor — Juno's Store

## 1. Contexto de Ambos Proyectos
Para este análisis comparativo se inspeccionaron los dos repositorios reales en el servidor local:

1. **Proyecto de Referencia del Instructor (`prototipo`):**
   * Sistema de gestión de inventario, proveedores y usuarios orientado al sector agropecuario/empresarial (AgroStock).
   * Desarrollado sobre Laravel 11, Laravel Breeze, Tailwind CSS, Bootstrap 5.3 CDN, DataTables 1.13.7 y MySQL.
   * Diseñado como modelo pedagógico para enseñar autenticación, roles múltiples (Admin, Vendedor, Cliente), CRUD con modales interactivos y exportación de reportes.
2. **Proyecto del Aprendiz (`junos-store` / `junos-store-laravel`):**
   * Tienda virtual minorista de papelería kawaii, accesorios tiernos y coleccionables.
   * Creada originalmente en PHP nativo con arquitectura MVC casera y migrada a Laravel 11.
   * Prioriza la experiencia de compra del cliente (B2C), catálogo visual interactivo, estética kawaii pastel (tonos lila, rosa, menta y lavanda) y gestión interna de productos.

---

## 2. Matriz Comparativa Estructural

| Dimensión Técnica | Proyecto del Instructor (`prototipo`) | Juno's Store Original (PHP) | Juno's Store Adaptado (Laravel) |
| :--- | :--- | :--- | :--- |
| **Framework y Versión** | Laravel 11.x (PHP 8.2+) | PHP Nativo (Procedural / MVC casero) | Laravel 11.x (PHP 8.3+) |
| **Arquitectura** | MVC de Laravel con middlewares en `bootstrap/app.php` | MVC en carpetas `controllers/`, `models/`, `views/` con dispatcher manual | MVC estándar de Laravel con separación estricta de rutas, controladores y modelos |
| **Base de Datos** | MySQL (`sistema_inventario`) con migraciones | MySQL (`junos_store`) con scripts `.sql` planos | MySQL (`junos_store`) manteniendo integridad con modelos Eloquent |
| **Acceso a Datos** | Eloquent ORM con relaciones `with('role')` | Conexión PDO / `mysqli` manual con sentencias SQL directas | Eloquent ORM con Eager Loading (`with(['categoria', 'imagenes'])`) |
| **Control de Roles** | Middleware `CheckRole` dinámico (`role:admin`, `role:vendedor`) | Comprobación manual `$_SESSION['rol']` al inicio de cada archivo | Middleware de roles en `bootstrap/app.php` y grupos en `routes/web.php` |
| **Vistas y Maquetación** | Blade con `@extends('layouts.sidebaradmin')` | Inclusiones PHP con `include 'header.php'` y `footer.php` | Blade modular con layouts dedicados (`layouts.app` y `layouts.sidebaradmin`) |
| **Tablas y Reportes** | DataTables con botones Excel, PDF, Print y filtros de columna | Tablas HTML básicas sin paginación dinámica | Integración de DataTables con estilos suaves adaptados a la paleta pastel |
| **Edición de Registros** | Modales interactivos de Bootstrap 5 en la misma tabla | Redirección a formularios externos o recargas de página | Uso de modales para edición rápida de usuarios y formularios dedicados para productos |
| **Validación de Datos** | `$request->validate([...])` con mensajes de error | `empty()` y expresiones regulares manuales en PHP | `$request->validate()` con reglas avanzadas (`exists`, `unique`, `min`) |
| **Seguridad de Passwords**| `Hash::make()` (Bcrypt / Argon2) | `password_hash()` estándar de PHP | `Hash::make()` y `Hash::check()` integrados con `Auth::login()` |
| **Identidad Visual** | Estilo corporativo oscuro/esmeralda (AgroStock) | Estilo pastel/kawaii en CSS personalizado | Estilo pastel/kawaii conservado y armonizado en todas las páginas internas |

---

## 3. Principios y Metodologías Adaptadas del Instructor

Durante el proceso de investigación y estandarización, se incorporaron los siguientes patrones demostrados por el instructor:

### 1. Control de Acceso Centralizado con Middlewares
En lugar de validar sesiones en la cabecera de cada vista, se adoptó el esquema del instructor mediante el registro de alias de middleware en `bootstrap/app.php` y su aplicación por grupos en `routes/web.php`. Esto garantiza que ninguna página administrativa quede expuesta por olvido de código.

### 2. Tablas Dinámicas con DataTables y Modales de Edición Inline
Se adaptó el patrón del archivo `resources/views/admin/gusuarios/listausers.blade.php`:
* La tabla cuenta con selectores superiores para filtrar en tiempo real por **Rol** y por **Estado**.
* Botones de descarga rápida de inventario y usuarios a hojas de cálculo de **Excel**, documentos **PDF** e impresión directa.
* Botón de edición que abre un modal de Bootstrap (`#editModal{{ $id }}`) con formulario `@method('PUT')`, permitiendo actualizar registros sin salir de la lista.

### 3. Estandarización de Rutas RESTful y Nombres Convencionales
Se corrigió la dispersión de URLs adoptando la nomenclatura de rutas con nombres punto:
* `admin.productos.index`, `admin.productos.create`, `admin.productos.store`, `admin.productos.edit`, `admin.productos.update`, `admin.productos.destroy`.

### 4. Auditoría y Control de Estados
Se integraron las columnas de estado (`activo` / `inactivo`) y trazabilidad de acciones inspiradas en el modelo del instructor, evitando la eliminación física accidental de registros vinculados a pedidos pasados.

---

## 4. Diferencias Justificadas que Deben Mantenerse

Aunque la metodología del instructor fue la guía principal, existen diferencias esenciales que se mantuvieron deliberadamente debido a la naturaleza distinta de ambos proyectos:

1. **Estética y Paleta de Colores (Identidad de Marca):**
   * El proyecto del instructor utiliza tonos oscuros, verdes esmeralda (`#10b981`) y diseño corporativo agropecuario.
   * **Juno's Store** debe conservar estrictamente su estética **kawaii y pastel** (lila `#ded5f5`, lavanda `#60458f`, rosa pastel `#f4a8bf`, amarillo `#f8d98b` y blanco), ya que su público objetivo son coleccionistas y clientes de productos tiernos.
2. **Enfoque Tienda Pública (B2C) vs. Sistema Interno (ERP):**
   * El proyecto del instructor arranca directamente en un dashboard de gestión con login obligatorio.
   * **Juno's Store** prioriza la vitrina pública comercial (`/`), donde cualquier visitante anónimo puede navegar libremente, filtrar por categorías y consultar fichas de productos antes de decidir registrarse.
3. **Mapeo de Claves Primarias Existentes en Base de Datos:**
   * El proyecto del instructor usa la convención nativa de Laravel (`id`, `created_at`, `updated_at`).
   * En Juno's Store, la base de datos existente tiene claves como `id_producto`, `id_usuario`, `creado_en`. En los modelos se configuró `$primaryKey = 'id_producto'` y `const CREATED_AT = 'creado_en'` para no alterar las tablas existentes ni perder datos.

---

## 5. Mejoras Implementadas y Mejoras Pendientes

### Mejoras Ya Implementadas:
* [x] Estructuración completa de modelos Eloquent con relaciones `belongsTo` y `hasMany`.
* [x] Estandarización de controladores de recurso con validaciones rigurosas.
* [x] Protección de rutas administrativas con middlewares de autenticación y rol.
* [x] Integración de DataTables con modales de edición en el módulo de usuarios.
* [x] Armonización de las vistas internas con la paleta pastel/kawaii del encabezado principal.
* [x] Generación de la suite completa de documentación técnica en la carpeta `docs/`.

### Mejoras Pendientes para Futuras Fases:
* [ ] **Migración completa del Carrito de Compras a Laravel:** Portar el controlador de carrito desde PHP nativo utilizando sesiones de Laravel o almacenamiento temporal en base de datos.
* [ ] **Pasarela de Pagos (Checkout):** Conectar los modelos `Pedido`, `PedidoDetalle` y `Pago` con un flujo de pago simulado o sandbox (Wompi, MercadoPago o PayU).
* [ ] **Notificaciones por Correo Electrónico:** Configurar el envío automático de confirmación de pedido y recuperación de contraseña mediante las clases `Mailable` de Laravel.
