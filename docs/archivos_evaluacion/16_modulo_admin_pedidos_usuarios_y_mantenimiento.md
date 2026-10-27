# 🛡️ Ficha Técnica — Módulo Integral del Dashboard y Administración
## Juno's Store (Laravel 12 / PHP 8.3)

---

### 1. Información General del Módulo
* **Controladores Involucrados:** 
  - [`AdminDashboardController.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Controllers/AdminDashboardController.php)
  - [`AdminPedidoController.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Controllers/AdminPedidoController.php)
  - [`AdminUsuarioController.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Controllers/AdminUsuarioController.php)
* **Vistas Blade:**
  - [`resources/views/admin/dashboard.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/dashboard.blade.php)
  - [`resources/views/admin/pedidos/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/pedidos/index.blade.php)
  - [`resources/views/admin/pedidos/detalle.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/pedidos/detalle.blade.php)
  - [`resources/views/admin/usuarios/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/usuarios/index.blade.php)
* **Rutas:** `/admin/`, `/admin/pedidos`, `/admin/pedidos/{id}`, `/admin/usuarios`, y acciones `/admin/mantenimiento/*`.
* **Middlewares de Protección:** `['auth', 'admin']` (Requiere inicio de sesión y `id_rol === 1`).

---

### 2. Propósito y Nuevas Funcionalidades para el Administrador

Antes de esta mejora, el administrador solo podía gestionar productos básicos. Con esta expansión, el administrador puede **visualizar todo el ecosistema de la tienda en tiempo real y reparar inconsistencias de forma inmediata**:

1. **Dashboard con Métricas Globales (6 KPIs):**
   - 💰 **Ventas Totales:** Suma acumulada de ingresos por pedidos concretados (`sum('total')`).
   - 🛍️ **Pedidos:** Total histórico y conteo en tiempo real de pedidos pendientes.
   - 📦 **Catálogo Activo:** Productos visibles vs total registrado y categorías.
   - ⚠️ **Stock Crítico y Agotado:** Alerta directa de artículos que requieren reposición.
   - 👥 **Clientes y Administradores:** Conteo de usuarios registrados en el sistema.
   - 🛒 **Carritos Activos:** Carritos de compra en curso por visitantes y clientes.

2. **Botiquín de Reparación y Mantenimiento del Sistema (1-Click Fixes):**
   - 🌸 **Publicar / Activar Catálogo con Stock:** Revisa los productos que tienen unidades físicas (`stock_actual > 0`) pero que quedaron con `activo = 0` (tras migraciones o cargas masivas) y los activa al público en un solo clic.
   - 📦 **Reponer Stock de Emergencia:** Abastece automáticamente los productos que quedaron en 0 unidades asignándoles su stock mínimo o 5 unidades de contingencia.
   - 🧹 **Limpieza de Caché del Sistema:** Ejecuta `Artisan::call('optimize:clear')` para refrescar rutas, plantillas Blade compiladas y configuraciones cuando se suben cambios.
   - 🛒 **Depuración de Carritos Huérfanos:** Marca como `ABANDONADO` los carritos antiguos para mantener la base de datos limpia.
   - ⚡ **Ajuste Rápido de Stock en Línea:** En el panel de alertas de stock, el admin cuenta con botones directos `+5` y `+10` para reponer existencias inmediatamente sin abrir formularios complejos.

3. **Módulo de Gestión de Pedidos (`/admin/pedidos`):**
   - Listado con filtros en píldoras: *Todos*, *Pendientes*, *Confirmados*, *En Preparación*, *Enviados*, *Entregados*, *Cancelados*.
   - Buscador por número de orden o nombre/email del cliente.
   - Cambio de estado en vivo desde la tabla o desde la vista de detalle.
   - Vista de detalle con desglose de artículos, costos de envío, descuentos y datos de contacto del cliente.

4. **Módulo de Gestión de Usuarios y Clientes (`/admin/usuarios`):**
   - Listado de usuarios con conteo de compras realizadas.
   - Filtro por roles (*Clientes* vs *Administradores*).
   - Botón de activación/desactivación de cuentas (con protección para no desactivar la cuenta propia del admin).

---

### 3. Preguntas Típicas de Evaluación Oral

> **P: ¿Cómo garantizas que solo el administrador acceda a estas herramientas de reparación y pedidos?**  
> **R:** Todas las rutas dentro del grupo `/admin/*` en [`routes/web.php`](file:///c:/laragon/www/junos-store-laravel/routes/web.php) están protegidas por dos middlewares consecutivos: `auth` (verifica que haya una sesión iniciada) y `admin` ([`AdminMiddleware.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Middleware/AdminMiddleware.php)), el cual evalúa `$user->isAdmin()` (`id_rol === 1`). Si un cliente o invitado intenta ingresar, recibe un HTTP `403 Forbidden`.

> **P: ¿Cómo funciona la herramienta de reparación de stock y activación de catálogo?**  
> **R:** El método `activarProductosConStock()` en [`AdminDashboardController.php`](file:///c:/laragon/www/junos-store-laravel/app/Http/Controllers/AdminDashboardController.php) ejecuta una consulta Eloquent:
> ```php
> Producto::where('stock_actual', '>', 0)->where('activo', 0)->update(['activo' => 1]);
> ```
> Esto corrige masivamente cualquier discrepancia de visibilidad generada al migrar los datos desde la base de datos original.
