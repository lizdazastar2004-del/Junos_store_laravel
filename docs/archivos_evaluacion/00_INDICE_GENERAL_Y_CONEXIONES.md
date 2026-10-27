# 🌸 Índice General y Mapa de Conexiones — Juno's Store (Laravel)

Este compendio de documentación fue elaborado para la **evaluación técnica y defensa del proyecto Juno's Store**. Cada archivo del sistema cuenta con su propio documento explicativo detallando su código fuente, arquitectura, propósito y conexiones dentro del patrón MVC.

---

## 🗺️ Mapa de Arquitectura y Conexiones Globales

```mermaid
graph TD
    Browser[🌐 Navegador del Cliente] -->|Petición HTTP / URL| WebRoutes[🛣️ routes/web.php]
    
    subgraph "Capa de Enrutamiento y Seguridad"
        WebRoutes -->|Rutas Públicas| PublicFilter[Filtros Públicos]
        WebRoutes -->|Rutas Admin /admin/*| AuthMid[🛡️ auth Middleware]
        AuthMid -->|Si está autenticado| AdminMid[🛡️ AdminMiddleware.php]
        AdminMid -->|Si rol === 1| AdminControllers[Controladores Admin]
        AdminMid -->|Si rol !== 1| Abort403[⛔ 403 Prohibido]
    end

    subgraph "Capa de Controladores (C)"
        PublicFilter -->|GET / y /productos/{id}| TiendaCtrl[🎮 TiendaController.php]
        PublicFilter -->|GET/POST /login y /registro| AuthCtrl[🎮 AuthController.php]
        AdminControllers -->|CRUD /admin/productos| AdminProdCtrl[🎮 AdminProductoController.php]
        AdminControllers -->|GET /admin| DashCtrl[🎮 Dashboard Closure / View]
    end

    subgraph "Capa de Modelos y Datos (M)"
        TiendaCtrl -->|Consultas Eloquent| ModelProd[📦 Producto.php]
        TiendaCtrl -->|Consultas Eloquent| ModelCat[🏷️ Categoria.php]
        AdminProdCtrl -->|CRUD Eloquent| ModelProd
        AdminProdCtrl -->|Carga de fotos| ModelProdImg[🖼️ ProductoImagen.php]
        AuthCtrl -->|Autenticación y Registro| ModelUser[👤 Usuario.php]
        
        ModelProd -->|Relación 1:N| ModelProdImg
        ModelProd -->|Relación N:1| ModelCat
        ModelProd -->|Lee / Escribe| MySQL[(🗄️ MySQL: junos_store)]
    end

    subgraph "Capa de Vistas y Presentación (V)"
        TiendaCtrl -->|compact('productos', 'categorias')| ViewIndex[🎨 tienda/index.blade.php]
        TiendaCtrl -->|compact('producto', 'relacionados')| ViewDetalle[🎨 tienda/detalle.blade.php]
        AuthCtrl -->|view()| ViewLogin[🎨 auth/login.blade.php]
        AuthCtrl -->|view()| ViewRegistro[🎨 auth/registro.blade.php]
        AdminProdCtrl -->|compact('productos', 'categorias')| ViewAdminIndex[🎨 admin/productos/index.blade.php]
        AdminProdCtrl -->|compact('producto', ...)| ViewAdminEdit[🎨 admin/productos/editar.blade.php]
        AdminProdCtrl -->|compact('categorias', ...)| ViewAdminCreate[🎨 admin/productos/crear.blade.php]
        DashCtrl -->|view()| ViewAdminDash[🎨 admin/dashboard.blade.php]
    end

    subgraph "Layouts Maestros y Estilos"
        ViewIndex -->|@extends| LayoutApp[📐 layouts/app.blade.php]
        ViewDetalle -->|@extends| LayoutApp
        ViewLogin -->|@extends| LayoutApp
        ViewRegistro -->|@extends| LayoutApp
        
        ViewAdminDash -->|@extends| LayoutAdmin[📐 layouts/admin.blade.php]
        ViewAdminIndex -->|@extends| LayoutAdmin
        ViewAdminCreate -->|@extends| LayoutAdmin
        ViewAdminEdit -->|@extends| LayoutAdmin

        LayoutApp -->|Importa| CSSKawaii[🎀 kawaii.css]
        LayoutApp -->|Importa| CSSHome[🎀 home.css]
        LayoutAdmin -->|Importa| CSSAdmin[🎀 admin-kawaii.css]
    end
```

---

## 📁 Catálogo de Archivos Documentados

| # | Archivo Documentado | Componente | Enlace al Documento |
|---|---------------------|------------|---------------------|
| **01** | `routes/web.php` | Enrutador Central | [01_routes_web_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/01_routes_web_php.md) |
| **02** | `app/Http/Controllers/TiendaController.php` | Controlador Público | [02_controller_TiendaController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/02_controller_TiendaController.md) |
| **03** | `app/Http/Controllers/AdminProductoController.php` | Controlador Administrativo | [03_controller_AdminProductoController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/03_controller_AdminProductoController.md) |
| **04** | `app/Http/Controllers/AuthController.php` | Controlador de Autenticación | [04_controller_AuthController.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/04_controller_AuthController.md) |
| **05** | `app/Http/Middleware/AdminMiddleware.php` | Middleware de Seguridad | [05_middleware_AdminMiddleware.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/05_middleware_AdminMiddleware.md) |
| **06** | `app/Models/Producto.php` y `ProductoImagen.php` | Modelos de Catálogo | [06_model_Producto_y_ProductoImagen.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/06_model_Producto_y_ProductoImagen.md) |
| **07** | `app/Models/Categoria.php` y `Usuario.php` | Modelos de Soporte | [07_model_Categoria_y_Usuario.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/07_model_Categoria_y_Usuario.md) |
| **08** | `resources/views/layouts/app.blade.php` | Layout Público Principal | [08_layout_app_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/08_layout_app_blade_php.md) |
| **09** | `resources/views/layouts/admin.blade.php` | Layout Administrativo | [09_layout_admin_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/09_layout_admin_blade_php.md) |
| **10** | `resources/views/tienda/index.blade.php` | Vista Catálogo / Home | [10_view_tienda_index_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/10_view_tienda_index_blade_php.md) |
| **11** | `resources/views/tienda/detalle.blade.php` | Vista Ficha de Producto | [11_view_tienda_detalle_blade_php.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/11_view_tienda_detalle_blade_php.md) |
| **12** | `resources/views/auth/login.blade.php` y `registro.blade.php` | Vistas Acceso / Registro | [12_view_auth_login_y_registro.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/12_view_auth_login_y_registro.md) |
| **13** | `resources/views/admin/dashboard.blade.php` | Vista Dashboard Admin | [13_view_admin_dashboard.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/13_view_admin_dashboard.md) |
| **14** | `resources/views/admin/productos/*` | Vistas CRUD Productos | [14_view_admin_productos_crud.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/14_view_admin_productos_crud.md) |
| **15** | `public/css/kawaii.css` y `admin-kawaii.css` | Sistema de Diseño Visual | [15_css_kawaii_y_admin.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/15_css_kawaii_y_admin.md) |
| **16** | `AdminDashboardController`, `AdminPedidoController`, `AdminUsuarioController` | Módulo Integral de Control y Mantenimiento | [16_modulo_admin_pedidos_usuarios_y_mantenimiento.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/16_modulo_admin_pedidos_usuarios_y_mantenimiento.md) |
| **17** | Cobertura de Vistas (Cliente y Admin) | Organización y Mapa Completo de Vistas | [17_organizacion_vistas_cliente_y_admin.md](file:///c:/laragon/www/junos-store-laravel/docs/archivos_evaluacion/17_organizacion_vistas_cliente_y_admin.md) |

---

## 🎯 Guía Rápida para la Defensa Oral

1. **¿Qué arquitectura utiliza el proyecto?**
   * Sigue el patrón **MVC (Modelo - Vista - Controlador)** potenciado por **Laravel 12 / PHP 8.3**.
2. **¿Cómo se asegura la integridad de los datos?**
   * Se utiliza **Eloquent ORM** con relaciones definidas (`belongsTo`, `hasMany`), métodos de consulta seguros contra inyección SQL, validación de formularios en controlador (`$request->validate()`) y protección CSRF (`@csrf`).
3. **¿Cómo se mantiene la estética visual?**
   * Mediante una arquitectura de **diseño centralizado** (`kawaii.css` y `admin-kawaii.css`), donde las vistas heredan de layouts maestros compartidos (`app.blade.php` y `admin.blade.php`), garantizando tipografía Nunito, paleta pastel (rosa, lila, lavanda, blanco) y adaptabilidad responsiva sin romper lógica PHP.
