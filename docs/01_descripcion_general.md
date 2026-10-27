# 01. Descripción General del Proyecto — Juno's Store

## 1. Identificación del Proyecto
* **Nombre del Sistema:** Juno's Store
* **Tipo de Aplicación:** Tienda Virtual de Comercio Electrónico (E-Commerce) con Panel de Gestión Administrativa
* **Nivel Académico / Formativo:** Proyecto Formativo SENA (Tecnología en Análisis y Desarrollo de Software - ADSO)
* **Paradigma:** Arquitectura Modelo-Vista-Controlador (MVC) en PHP moderno migrado a Laravel Framework
* **Motor de Base de Datos:** MySQL (con motor de almacenamiento InnoDB y juego de caracteres `utf8mb4_unicode_ci`)
* **Identidad Visual:** Diseño Kawaii Pastel / Soft Aesthetic (paleta en tonos lila, lavanda, rosa pastel, menta y blanco)

---

## 2. Descripción de Juno's Store
**Juno's Store** es una plataforma web de comercio electrónico especializada en la comercialización minorista de productos de papelería kawaii, accesorios de colección, artículos de diseño tierno y productos de moda asiática/anime. 

El proyecto nació originalmente como una aplicación web desarrollada en PHP nativo siguiendo el patrón arquitectónico MVC tradicional, y se encuentra en un proceso de modernización y estandarización hacia **Laravel**, el framework líder del ecosistema PHP. La plataforma combina una experiencia de usuario orientada al cliente —con navegación fluida, diseño delicado, catálogo dinámico y visualización de productos— con un robusto panel de control administrativo para la gestión de productos, categorías, inventario, usuarios y trazabilidad de transacciones.

---

## 3. Problema o Necesidad que Resuelve
En el comercio minorista tradicional y en pequeños emprendimientos dedicados a nichos específicos (como los productos de estética kawaii y coleccionables):
1. **Desorganización en el control de stock:** Pérdida de visibilidad de existencias mínimas y sobreventas por falta de sincronización en tiempo real.
2. **Experiencia de compra fragmentada:** Las ventas realizadas a través de redes sociales (Instagram, WhatsApp) conllevan tiempos de respuesta lentos, desorden en pedidos y dificultades para que los clientes consulten el catálogo con filtros por categorías y subcategorías.
3. **Ausencia de trazabilidad y reportes:** Inexistencia de un histórico digitalizado de pedidos, clientes y transacciones para la toma de decisiones comerciales.
4. **Falta de identidad de marca en plataformas estándar:** Las plantillas comerciales genéricas carecen de la personalidad visual delicada y coherente que exige el público objetivo juvenil interesado en la estética kawaii.

**Juno's Store** centraliza las operaciones del negocio en un único entorno tecnológico seguro, automatizando el catálogo público, el control de acceso de clientes y administradores, y la administración del inventario.

---

## 4. Objetivo General
Desarrollar e implementar la plataforma web de comercio electrónico **Juno's Store**, migrada a la arquitectura Laravel MVC con base de datos MySQL, que garantice una experiencia de compra atractiva e intuitiva bajo una estética visual pastel/kawaii y proporcione a los administradores herramientas eficientes para la gestión del catálogo, inventario y usuarios.

---

## 5. Objetivos Específicos
1. **Analizar la metodología del proyecto de referencia del instructor** (arquitectura Laravel, control de roles mediante middlewares, CRUDs interactivos con validación, auditoría y vistas protegidas) para estandarizar las buenas prácticas en Juno's Store.
2. **Diseñar y optimizar el esquema relacional de la base de datos MySQL** (`junos_store`), asegurando la integridad referencial, normalización de tablas y optimización de consultas para catálogo, usuarios, roles, pedidos e imágenes.
3. **Migrar la lógica de negocio desde PHP nativo hacia Laravel**, estructurando modelos Eloquent con relaciones bien definidas (`belongsTo`, `hasMany`), controladores de recurso con validaciones estrictas y rutas centralizadas.
4. **Implementar el sistema de autenticación y control de acceso por roles**, distinguiendo de manera estricta entre Administradores y Clientes, protegiendo las rutas críticas mediante middlewares.
5. **Preservar y perfeccionar la identidad visual pastel/kawaii** en todas las vistas de la aplicación (catálogo, detalle de productos, formularios de autenticación y dashboard administrativo), eliminando inconsistencias estéticas.
6. **Construir interfaces de gestión administrativa ágiles**, integrando tablas dinámicas, modales interactivos y reportes exportables a Excel, PDF e impresión.

---

## 6. Alcance del Sistema
El alcance del sistema abarca los siguientes módulos y funcionalidades operativas:

### Módulo Público / Cliente
* **Página de Bienvenida y Catálogo General:** Presentación de la marca, banners kawaii, listado de productos con paginación, filtros por categoría y ordenamiento.
* **Detalle de Producto:** Ficha completa con carrusel de imágenes, precio formateado, disponibilidad de stock, selector de cantidades y productos relacionados.
* **Gestión de Cuentas de Usuario:** Formulario de registro con validaciones en tiempo real, inicio de sesión seguro, control de sesiones y recordación de credenciales.
* **Carrito y Checkout (En proceso de migración):** Adición de ítems a la cesta de compras, cálculo de totales e integración con métodos de pago.

### Módulo Administrativo (Backoffice)
* **Dashboard Administrativo:** Vista general de indicadores del negocio.
* **Gestión de Productos (CRUD):** Listado paginado, alta de nuevos productos con SKU autogenerado, edición de precios y stock, eliminación y asignación a categorías y marcas.
* **Gestión de Categorías y Subcategorías:** Clasificación jerárquica del catálogo de artículos.
* **Control de Usuarios y Roles:** Administración de perfiles, estados (activo/inactivo) y permisos.

---

## 7. Usuarios y Roles del Sistema
El sistema implementa un modelo de control de acceso basado en roles (RBAC - Role-Based Access Control):

| Rol | Identificador en BD | Descripción y Responsabilidades | Acceso a Rutas |
| :--- | :---: | :--- | :--- |
| **Administrador** | `id_rol = 1` o `rol = 'admin'` | Dueño o gestor de la tienda. Control total sobre el inventario, productos, categorías, reportes y estado de los usuarios. | Prefijo `/admin/*` y vistas públicas. |
| **Vendedor / Operador** | `id_rol = 2` o `rol = 'vendedor'` | Encargado de la atención de pedidos, despacho de productos y actualización de existencias de stock. | Dashboard operativo y consultas. |
| **Cliente** | `id_rol = 3` o `rol = 'cliente'` | Usuario final registrado que navega el catálogo, consulta detalles, añade ítems a su carrito y realiza pedidos. | Catálogo `/`, `/productos/{id}`, perfil propio. |
| **Visitante / Invitado** | No autenticado | Usuario anónimo que explora la tienda virtual antes de registrarse o iniciar sesión. | Catálogo público, login y registro. |

---

## 8. Matriz de Funcionalidades Verificadas en el Código

| Funcionalidad | Estado Real en Código | Archivos / Componentes Involucrados | Observaciones Técnicas |
| :--- | :---: | :--- | :--- |
| **Landing Page / Catálogo Principal** | **Implementada (100%)** | `TiendaController@index`, `tienda/index.blade.php`, `home.css` | Carga de categorías activas, productos destacados y paginados. |
| **Ficha Detallada de Producto** | **Implementada (100%)** | `TiendaController@show`, `tienda/detalle.blade.php` | Carga producto con relación de imágenes y productos de la misma categoría. |
| **Autenticación (Login)** | **Implementada (100%)** | `AuthController@showLogin`, `@login`, `auth/login.blade.php` | Validación de email/password, `Hash::check()`, regeneración de sesión. |
| **Registro de Usuarios** | **Implementada (100%)** | `AuthController@showRegistro`, `@registro`, `auth/registro.blade.php` | Alta de clientes con validación y contraseña encriptada con Bcrypt. |
| **Cierre de Sesión (Logout)** | **Implementada (100%)** | `AuthController@logout`, `routes/web.php` | Invalida sesión y regenera token CSRF. |
| **Protección por Middleware Admin** | **Implementada (100%)** | `AdminMiddleware.php`, `bootstrap/app.php` | Restringe rutas bajo prefijo `/admin` a usuarios con rol administrativo. |
| **Listado Administrativo de Productos**| **Implementada (100%)** | `AdminProductoController@index`, `admin/productos/index.blade.php` | Paginación, eager loading de categorías y subcategorías. |
| **Creación de Productos** | **Implementada (100%)** | `AdminProductoController@create`, `@store`, `admin/productos/crear.blade.php` | Validación de stock, precio numérico, selección de categoría y generación de SKU. |
| **Edición de Productos** | **Implementada (100%)** | `AdminProductoController@edit`, `@update`, `admin/productos/editar.blade.php` | Búsqueda por ID, actualización controlada y validación de SKU único excluyendo al actual. |
| **Eliminación de Productos** | **Implementada (100%)** | `AdminProductoController@destroy` | Borrado o desactivación lógica de artículos. |
| **Gestión de Usuarios con DataTables** | **Migrada según Instructor** | `UsuarioController.php`, `admin/gusuarios/listausers.blade.php` | Filtros por rol y estado, exportación a Excel/PDF/Print y modales de edición. |
| **Carrito y Pasarela de Pagos** | **Propuesta / En Migración** | Modelos `Pedido`, `PedidoDetalle`, `Pago`, `MetodoPago` creados | Lógica original en PHP nativo pendiente de controladores Laravel dedicados. |
