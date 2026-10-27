# 🌸 Índice General y Mapa de Conexiones — Juno's Store (Laravel)

Este compendio de documentación fue elaborado para la **evaluación técnica y defensa del proyecto Juno's Store**. Cada archivo del sistema cuenta con su propio documento explicativo detallando su código fuente, arquitectura, propósito, conexiones dentro del patrón MVC, análisis de impacto (**"¿Qué sucede si quito esto?"**), contingencias (**"¿Qué pasa si ocurre aquello?"**) y soluciones a errores.

---

## 🗺️ Mapa de Arquitectura y Conexiones Globales

```mermaid
graph TD
    Browser[🌐 Navegador del Cliente] -->|Petición HTTP / URL| WebRoutes[🛣️ routes/web.php]
    
    subgraph "Capa de Enrutamiento y Seguridad"
        WebRoutes -->|Rutas Públicas| PublicFilter[Filtros Públicos]
        WebRoutes -->|Rutas Cliente /carrito, /checkout, /mi-cuenta| AuthMidClient[🛡️ auth Middleware]
        WebRoutes -->|Rutas Admin /admin/*| AuthMidAdmin[🛡️ auth Middleware]
        AuthMidAdmin -->|Si está autenticado| AdminMid[🛡️ AdminMiddleware.php]
        AdminMid -->|Si rol === 1| AdminControllers[Controladores Admin]
        AdminMid -->|Si rol !== 1| Abort403[⛔ 403 Prohibido / Redirect]
    end

    subgraph "Capa de Controladores (C)"
        PublicFilter -->|GET / y /productos/{id}| TiendaCtrl[🎮 TiendaController.php]
        PublicFilter -->|GET/POST /login y /registro| AuthCtrl[🎮 AuthController.php]
        AuthMidClient -->|Carrito y Cupones| CartCtrl[🎮 CarritoController.php]
        AuthMidClient -->|Checkout y Transacciones ACID| OrderCtrl[🎮 PedidoController.php]
        AuthMidClient -->|Portal Mi Espacio Kawaii| ClientCtrl[🎮 ClienteController.php]
        AdminControllers -->|CRUD /admin/productos| AdminProdCtrl[🎮 AdminProductoController.php]
        AdminControllers -->|GET /admin + Diagnóstico| DashCtrl[🎮 AdminDashboardController.php]
        AdminControllers -->|Gestión de Ventas| AdminPedCtrl[🎮 AdminPedidoController.php]
        AdminControllers -->|Gestión de Usuarios| AdminUserCtrl[🎮 AdminUsuarioController.php]
    end

    subgraph "Capa de Modelos y Datos (M)"
        TiendaCtrl & AdminProdCtrl & CartCtrl & OrderCtrl -->|Eloquent ORM| ModelProd[📦 Producto.php]
        ModelProd -->|Relación 1:N| ModelProdImg[🖼️ ProductoImagen.php]
        ModelProd -->|Relación N:1| ModelCat[🏷️ Categoria.php]
        OrderCtrl -->|Transacción ACID| ModelPed[🧾 Pedido.php & PedidoDetalle.php]
        OrderCtrl -->|Registro de Pago| ModelPago[💳 Pago.php & MetodoPago.php]
        CartCtrl -->|Gestión de Ítems| ModelCart[🛒 Carrito.php & CarritoDetalle.php]
        AuthCtrl & ClientCtrl -->|Autenticación y Perfil| ModelUser[👤 Usuario.php]
        ClientCtrl -->|Libreta de Envío| ModelDir[📍 Direccion.php]
        
        ModelProd & ModelPed & ModelCart & ModelUser --> MySQL[(🗄️ MySQL: junos_store)]
    end

    subgraph "Capa de Vistas y Presentación (V)"
        TiendaCtrl --> ViewIndex[🎨 tienda/index.blade.php & detalle.blade.php]
        AuthCtrl --> ViewAuth[🎨 auth/login.blade.php & registro.blade.php]
        CartCtrl & OrderCtrl --> ViewShop[🎨 carrito/index.blade.php & pedidos/checkout.blade.php]
        ClientCtrl --> ViewCli[🎨 cliente/dashboard.blade.php]
        AdminProdCtrl & DashCtrl & AdminPedCtrl --> ViewAdmin[🎨 admin/*]
    end

    subgraph "Layouts Maestros y Estilos"
        ViewIndex & ViewAuth & ViewShop & ViewCli -->|@extends| LayoutApp[📐 layouts/app.blade.php + kawaii.css]
        ViewAdmin -->|@extends| LayoutAdmin[📐 layouts/admin.blade.php + admin-kawaii.css]
    end
```

---

## 📁 Catálogo Completo de Archivos Documentados

| # | Archivo Documentado | Componente | Enlace al Documento |
|---|---------------------|------------|---------------------|
| **01** | `routes/web.php` | Enrutador Central de la Aplicación | [01_routes_web_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/01_routes_web_php.md) |
| **02** | `app/Http/Controllers/TiendaController.php` | Catálogo Público y Filtros | [02_controller_TiendaController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/02_controller_TiendaController.md) |
| **03** | `app/Http/Controllers/AdminProductoController.php` | CRUD de Productos y Soft Delete | [03_controller_AdminProductoController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/03_controller_AdminProductoController.md) |
| **04** | `app/Http/Controllers/AuthController.php` | Autenticación, Hashing y Sesiones | [04_controller_AuthController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/04_controller_AuthController.md) |
| **05** | `app/Http/Middleware/AdminMiddleware.php` | Control de Acceso por Roles (RBAC) | [05_middleware_AdminMiddleware.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/05_middleware_AdminMiddleware.md) |
| **06** | `app/Models/Producto.php` y `ProductoImagen.php` | Modelos de Catálogo e Imágenes | [06_model_Producto_y_ProductoImagen.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/06_model_Producto_y_ProductoImagen.md) |
| **07** | `app/Models/Categoria.php` y `Usuario.php` | Modelos de Soporte y Autenticación | [07_model_Categoria_y_Usuario.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/07_model_Categoria_y_Usuario.md) |
| **08** | `resources/views/layouts/app.blade.php` | Layout Público Principal y Navbar | [08_layout_app_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/08_layout_app_blade_php.md) |
| **09** | `resources/views/layouts/admin.blade.php` | Layout Administrativo con Sidebar | [09_layout_admin_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/09_layout_admin_blade_php.md) |
| **10** | `resources/views/tienda/index.blade.php` | Vista Catálogo / Home Kawaii | [10_view_tienda_index_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/10_view_tienda_index_blade_php.md) |
| **11** | `resources/views/tienda/detalle.blade.php` | Vista Ficha Técnica de Producto | [11_view_tienda_detalle_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/11_view_tienda_detalle_blade_php.md) |
| **12** | `resources/views/auth/login.blade.php` y `registro.blade.php` | Vistas de Acceso y Registro | [12_view_auth_login_y_registro.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/12_view_auth_login_y_registro.md) |
| **13** | `resources/views/admin/dashboard.blade.php` | Vista Dashboard Admin con Diagnóstico | [13_view_admin_dashboard.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/13_view_admin_dashboard.md) |
| **14** | `resources/views/admin/productos/*` | Vistas CRUD Productos (Crear, Editar, Listar) | [14_view_admin_productos_crud.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/14_view_admin_productos_crud.md) |
| **15** | `public/css/kawaii.css` y `admin-kawaii.css` | Sistema de Diseño Visual y Paleta Pastel | [15_css_kawaii_y_admin.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/15_css_kawaii_y_admin.md) |
| **16** | `AdminDashboardController`, `AdminPedidoController`, `AdminUsuarioController` | Módulo Integral de Mantenimiento y Pedidos | [16_modulo_admin_pedidos_usuarios_y_mantenimiento.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/16_modulo_admin_pedidos_usuarios_y_mantenimiento.md) |
| **17** | Cobertura de Vistas (Cliente y Admin) | Organización y Mapa Completo de Vistas | [17_organizacion_vistas_cliente_y_admin.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/17_organizacion_vistas_cliente_y_admin.md) |
| **18** | `CarritoController.php` y `PedidoController.php` | Transacciones ACID, Checkout y Ventas | [18_controller_Carrito_y_Pedido.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/18_controller_Carrito_y_Pedido.md) |
| **19** | `ClienteController.php` | Portal "Mi Espacio Kawaii" del Cliente | [19_controller_ClienteController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/19_controller_ClienteController.md) |
| **20** | `DatabaseSeeder.php` y `DemoDataSeeder.php` | Semillas de Datos e Idempotencia | [20_seeders_DatabaseSeeder_y_DemoDataSeeder.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/20_seeders_DatabaseSeeder_y_DemoDataSeeder.md) |

---

## 🎯 Guía Rápida para la Defensa Oral

1. **¿Qué arquitectura utiliza el proyecto?**
   * Sigue el patrón **MVC (Modelo - Vista - Controlador)** potenciado por **Laravel 12 / PHP 8.3**.
2. **¿Cómo se asegura la integridad de los datos en ventas?**
   * Mediante **Transacciones ACID** (`DB::beginTransaction`, `commit`, `rollBack`) con decremento atómico de existencias (`decrement('stock_actual', ...)`).
3. **¿Cómo se previene el fraude o acceso indebido?**
   * A través de **Middlewares en pipeline** (`auth` y `admin`), protección contra ataques de fijación de sesión (`session()->regenerate()`), contraseñas cifradas con algoritmo **Bcrypt** (`Hash::make`), protección CSRF en todos los formularios (`@csrf`) y validación estricta de pertenencia contra vulnerabilidades IDOR.
4. **¿Cómo se mantiene la estética visual?**
   * Mediante una arquitectura de **diseño centralizado** (`kawaii.css` y `admin-kawaii.css`), donde las vistas heredan de layouts maestros compartidos (`app.blade.php` y `admin.blade.php`), garantizando tipografía Nunito, paleta pastel (rosa, lila, lavanda, blanco) y adaptabilidad responsiva sin romper lógica PHP.
