# 🎀 Mapa Completo de Vistas: Cliente y Administrador
## Juno's Store — Cobertura 100% de la Experiencia de Usuario y Gestión

Este documento detalla la estructura completa, sin vacíos ni eslabones rotos, de todas las vistas tanto para el **Cliente** (experiencia de compra pastel kawaii) como para el **Administrador** (gestión y control del sistema).

---

## 1. 🛍️ Ecosistema de Vistas del Cliente

| Módulo | Vista Blade | Ruta URL | Propósito y Conexiones |
| :--- | :--- | :--- | :--- |
| **Catálogo & Home** | [`tienda/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/tienda/index.blade.php) | `GET /` o `/catalogo` | Portada kawaii, hero con mascota, buscador en vivo, filtros por píldoras de categorías y grid de productos con fallback de fotos. |
| **Ficha de Producto** | [`tienda/detalle.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/tienda/detalle.blade.php) | `GET /productos/{id}` | Galería de fotos con miniaturas interactivas, badge de stock, selector de cantidad, formulario `POST /carrito/agregar` y carrusel de recomendados. |
| **Carrito de Compras** | [`carrito/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/carrito/index.blade.php) | `GET /carrito` | Tabla de productos agregados, actualización de cantidades en tiempo real con JavaScript, cupones de descuento y botón a Checkout. |
| **Checkout** | [`pedidos/checkout.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/pedidos/checkout.blade.php) | `GET /checkout` | Formulario de dirección de entrega, selección de método de pago, resumen financiero y confirmación de la orden. |
| **Confirmación** | [`pedidos/confirmacion.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/pedidos/confirmacion.blade.php) | `GET /pedidos/confirmacion/{id}` | Pantalla de celebración tras compra exitosa con número de orden, resumen y botón directo a ver el pedido o seguir comprando. |
| **Mis Pedidos** | [`pedidos/historial.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/pedidos/historial.blade.php) | `GET /pedidos/historial` | Lista de compras del cliente con badges de estado de color, previsualización de artículos comprados, fecha, totales y enlaces individuales. |
| **Detalle de Pedido** | [`pedidos/detalle.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/pedidos/detalle.blade.php) | `GET /pedidos/{id}` | **(Nueva)** Seguimiento paso a paso (*Recibido* $\to$ *Confirmado* $\to$ *En Preparación* $\to$ *Enviado* $\to$ *Entregado*), desglose de ítems, dirección y costos. |
| **Autenticación** | [`auth/login.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/auth/login.blade.php) y [`registro.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/auth/registro.blade.php) | `/login`, `/registro` | Formulario split-card con alternador de visibilidad de contraseña (ojito), validaciones y token CSRF. |
| **Páginas de Error** | [`errors/404.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/errors/404.blade.php) y [`403.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/errors/403.blade.php) | Errores HTTP | **(Nuevas)** Páginas ilustradas personalizadas con diseño pastel kawaii para evitar pantallas grises predeterminadas. |

---

## 2. 🛡️ Ecosistema de Vistas del Administrador

| Módulo | Vista Blade | Ruta URL | Propósito y Conexiones |
| :--- | :--- | :--- | :--- |
| **Layout Maestro** | [`layouts/admin.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/layouts/admin.blade.php) | Compartido | Sidebar lateral retráctil en móvil, badges en vivo de pedidos pendientes (`🟡 N`) y stock bajo (`🔴 N`), header con perfil y botón "Ver Tienda". |
| **Dashboard Global** | [`admin/dashboard.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/dashboard.blade.php) | `GET /admin` | 6 KPIs de negocio (Ventas $, Pedidos, Catálogo, Stock crítico, Clientes, Carritos), botiquín de reparación en 1 clic y monitor de salud (PHP, Laravel, MySQL). |
| **Inventario de Productos** | [`admin/productos/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/productos/index.blade.php) | `GET /admin/productos` | Tabla de productos con miniaturas, SKU, precio, stock actual, badges de alerta y botones de editar/eliminar. |
| **Crear Producto** | [`admin/productos/crear.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/productos/crear.blade.php) | `GET /admin/productos/crear` | Formulario por pasos con filtrado reactivo de subcategorías por categoría seleccionada y carga de imágenes. |
| **Editar Producto** | [`admin/productos/editar.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/productos/editar.blade.php) | `GET /admin/productos/{id}/editar` | Formulario de modificación con galería de fotos existentes (eliminar/marcar principal) y actualización de stock. |
| **Gestión de Pedidos** | [`admin/pedidos/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/pedidos/index.blade.php) | `GET /admin/pedidos` | Filtros por píldoras de estado (*Pendientes*, *Confirmados*, *Enviados*, *Entregados*), buscador por cliente/orden y cambio de estado directo. |
| **Detalle de Pedido Admin** | [`admin/pedidos/detalle.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/pedidos/detalle.blade.php) | `GET /admin/pedidos/{id}` | Desglose exhaustivo de ítems, selector de cambio de estado del despacho, datos del cliente y botón de impresión. |
| **Clientes y Usuarios** | [`admin/usuarios/index.blade.php`](file:///c:/laragon/www/junos-store-laravel/resources/views/admin/usuarios/index.blade.php) | `GET /admin/usuarios` | Directorio de clientes con contador de pedidos realizados, datos de contacto y botón de activación/bloqueo de cuentas. |
