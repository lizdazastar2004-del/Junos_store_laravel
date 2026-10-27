# 03. Estructura Real del Proyecto — Juno's Store

## 1. Árbol de Directorios del Proyecto
La siguiente jerarquía refleja la estructura real y verificada de los archivos en el entorno Laravel del proyecto:

```text
junos-store-laravel/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminProductoController.php    <-- CRUD de productos del administrador
│   │   │   ├── AuthController.php             <-- Login, registro y cierre de sesión
│   │   │   ├── Controller.php                 <-- Controlador base de Laravel
│   │   │   ├── TiendaController.php           <-- Catálogo público y vista detalle
│   │   │   └── UsuarioController.php          <-- Gestión de usuarios y roles (estilo instructor)
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php            <-- Protección de acceso para rol administrador
│   │       └── CheckRole.php                  <-- Middleware dinámico multi-rol (instructor)
│   └── Models/
│       ├── Categoria.php                      <-- Modelo de categorías del catálogo
│       ├── Direccion.php                      <-- Direcciones de entrega de clientes
│       ├── Marca.php                          <-- Marcas asociadas a productos
│       ├── MetodoPago.php                     <-- Formas de pago (efectivo, transferencia, tarjeta)
│       ├── Pago.php                           <-- Registro de pagos y comprobantes
│       ├── Pedido.php                         <-- Cabecera de pedidos y órdenes de compra
│       ├── PedidoDetalle.php                  <-- Ítems individuales dentro de un pedido
│       ├── Producto.php                       <-- Modelo central del catálogo de productos
│       ├── ProductoImagen.php                 <-- Galería de imágenes de cada producto
│       ├── Resena.php                         <-- Calificaciones y comentarios de clientes
│       ├── Rol.php                            <-- Roles del sistema (Admin, Vendedor, Cliente)
│       ├── Subcategoria.php                   <-- Clasificación secundaria de productos
│       └── Usuario.php                        <-- Modelo de usuarios con autenticación
│
├── bootstrap/
│   └── app.php                                <-- Configuración del framework y registro de middlewares
│
├── config/
│   ├── app.php                                <-- Configuración de zona horaria, idioma y servicios
│   ├── auth.php                               <-- Configuración de guards y proveedores de autenticación
│   └── database.php                           <-- Configuración de conexiones MySQL
│
├── database/
│   ├── migrations/                            <-- Migraciones estructuradas de esquema
│   └── seeders/                               <-- Semillas de datos iniciales
│
├── public/
│   ├── css/
│   │   ├── admin-kawaii.css                   <-- Estilos específicos del panel administrativo
│   │   ├── home.css                           <-- Estilos de la landing page y catálogo kawaii
│   │   ├── kawaii.css                         <-- Paleta global pastel, variables CSS y botones
│   │   ├── login.css                          <-- Estilos de formulario de acceso split-screen
│   │   └── register.css                       <-- Estilos del formulario de creación de cuenta
│   ├── img/
│   │   ├── icon.png                           <-- Logotipo oficial de Juno's Store
│   │   ├── background.jpeg                    <-- Fondo ilustrado suave de la tienda
│   │   ├── dashboard_hero_cats.jpg            <-- Banner decorativo del dashboard
│   │   └── dashboard_box_cat.jpg              <-- Mascota kawaii decorativa
│   └── index.php                              <-- Punto de entrada de todas las peticiones web
│
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── dashboard.blade.php            <-- Dashboard de bienvenida para administradores
│       │   ├── gusuarios/
│       │   │   ├── create.blade.php           <-- Formulario de alta de usuario
│       │   │   └── listausers.blade.php       <-- Tabla DataTables con modales y exportaciones
│       │   └── productos/
│       │       ├── crear.blade.php            <-- Formulario completo para registrar un producto
│       │       ├── editar.blade.php           <-- Formulario para actualizar información del producto
│       │       └── index.blade.php            <-- Listado paginado de productos para el administrador
│       ├── auth/
│       │   ├── login.blade.php                <-- Formulario de inicio de sesión
│       │   └── registro.blade.php             <-- Formulario de registro de clientes
│       ├── layouts/
│       │   ├── admin.blade.php                <-- Layout maestro para vistas administrativas
│       │   ├── app.blade.php                  <-- Layout principal de la tienda (header kawaii + footer)
│       │   └── sidebaradmin.blade.php         <-- Layout lateral administrativo con menú colapsable
│       └── tienda/
│           ├── detalle.blade.php              <-- Ficha detallada de un producto con imágenes
│           └── index.blade.php                <-- Catálogo visual interactivo
│
└── routes/
    ├── web.php                                <-- Declaración de rutas públicas, auth y administración
    └── console.php                            <-- Comandos de consola de Artisan
```

---

## 2. Descripción Detallada de Archivos Clave

### A. Archivo de Enrutamiento: `routes/web.php`
Define los endpoints a los que responden las URLs de la aplicación. Agrupa las rutas según su propósito y nivel de protección:

```php
// Ruta del catálogo principal
Route::get('/', [TiendaController::class, 'index'])->name('tienda.index');

// Ficha de producto individual
Route::get('/productos/{id}', [TiendaController::class, 'show'])->name('tienda.producto');

// Autenticación pública
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro');
Route::post('/registro', [AuthController::class, 'registro']);

// Grupo administrativo protegido por sesión y rol
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', function() { return view('admin.dashboard'); })->name('dashboard');
    Route::get('/productos', [AdminProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/crear', [AdminProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [AdminProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{id}/editar', [AdminProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{id}', [AdminProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{id}', [AdminProductoController::class, 'destroy'])->name('productos.destroy');
});
```

---

### B. Controladores Principales (`app/Http/Controllers/`)

#### 1. `TiendaController.php`
Gestiona la experiencia del cliente en la tienda:
* `index()`: Consulta las categorías activas y los productos habilitados aplicando paginación para optimizar la memoria y renderiza `tienda.index`.
* `show($id)`: Carga un producto específico junto a sus relaciones (`imagenes`, `categoria`, `marca`) mediante eager loading para evitar el problema de consultas N+1, y selecciona artículos relacionados de la misma categoría.

#### 2. `AdminProductoController.php`
Implementa el ciclo CRUD del catálogo administrativo:
* `index()`: Lista los productos ordenados de forma descendente por fecha de creación con paginación.
* `create()`: Obtiene categorías activas, subcategorías y marcas para poblar los selectores del formulario de creación `admin.productos.crear`.
* `store(Request $request)`: Valida tipos de datos, precios positivos, existencia de claves foráneas y genera un SKU único con formato `JUNO-XXXXXX` si el usuario no especificó uno.
* `edit($id)` / `update(Request $request, $id)`: Permite actualizar datos verificando que el SKU sea único ignorando el ID del producto que se está editando.
* `destroy($id)`: Elimina el registro o conmuta su estado a inactivo para mantener la integridad histórica.

#### 3. `AuthController.php`
Administra el ciclo de vida de la sesión:
* `login(Request $request)`: Valida correo y contraseña, busca el usuario en la tabla `usuarios`, verifica el hash Bcrypt mediante `Hash::check()`, comprueba que el estado de la cuenta sea activo y autentica la sesión regenerando el token CSRF para prevenir secuestros de sesión.
* `logout(Request $request)`: Desconecta al usuario, invalida la sesión y borra las cookies de autenticación.

---

### C. Modelos Eloquent Principales (`app/Models/`)

#### 1. `Producto.php`
Representa la entidad central del catálogo:
```php
class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'nombre', 'descripcion', 'precio', 'id_categoria',
        'id_subcategoria', 'id_marca', 'sku', 'stock_actual',
        'stock_minimo', 'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'stock_actual' => 'integer',
        'activo' => 'boolean',
    ];

    public function categoria() {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }
    public function imagenes() {
        return $this->hasMany(ProductoImagen::class, 'id_producto', 'id_producto');
    }
}
```

#### 2. `Usuario.php`
Implementa la interfaz `Authenticatable` para la seguridad de Laravel:
```php
class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $timestamps = false;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'id_rol', 'nombre', 'apellido', 'email',
        'password_hash', 'telefono', 'activo', 'email_verificado'
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function rol() {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function isAdmin(): bool {
        return $this->id_rol === 1 || ($this->rol && strtolower($this->rol->nombre) === 'admin');
    }
}
```

---

### D. Layouts y Vistas Blade (`resources/views/`)

* **`layouts/app.blade.php`:** Plantilla base de la tienda pública. Contiene el encabezado kawaii con el logo oficial (`img/icon.png`), la barra de navegación responsive, el área `@yield('content')` para inyectar contenido dinámico y el pie de página con enlaces y copyright.
* **`layouts/sidebaradmin.blade.php`:** Plantilla maestra para el panel administrativo. Incluye barra lateral colapsable, menú de opciones (Productos, Categorías, Usuarios, Reportes) y scripts base de Bootstrap y DataTables.
* **`tienda/index.blade.php`:** Vista principal con banner de bienvenida suave, tarjetas de productos con efecto hover flotante, indicador de stock y precios formateados con separador de miles.
* **`tienda/detalle.blade.php`:** Vista de detalle con galería de imágenes en miniatura, descripción extendida, selector de cantidad y cuadrícula de artículos recomendados.
* **`admin/gusuarios/listausers.blade.php`:** Vista basada en la metodología del instructor con tabla interactiva DataTables, botones para descargar reportes en Excel/PDF e imprimir, filtros selectores por Rol y Estado, y modales de Bootstrap para editar cada usuario sin abandonar la página.
