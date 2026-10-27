# 05. Rutas, Controladores, Modelos y Vistas — Juno's Store

## 1. Relación Integral entre Componentes en Laravel
En Juno's Store, la interacción entre componentes sigue un ciclo unificado donde cada elemento cumple una única función bien delimitada:

```mermaid
flowchart LR
    A[Ruta en routes/web.php] -->|Despacha a| B[Método en Controlador]
    B -->|Valida datos con| C[Form Request / Validation Rules]
    B -->|Consulta / Guarda con| D[Modelo Eloquent]
    D -->|Lee / Escribe en| E[(Tabla MySQL)]
    B -->|Envía variables compact()| F[Vista Blade resources/views/]
    F -->|Hereda de| G[Layout Maestro Blade]
```

A continuación se explican en detalle los 4 casos de uso más importantes del sistema con código real y verificado.

---

## 2. Caso de Estudio 1: Catálogo Público de la Tienda

### A. La Ruta (`routes/web.php`)
```php
Route::get('/', [TiendaController::class, 'index'])->name('tienda.index');
```
* **Método HTTP:** `GET`
* **Controlador y Acción:** `TiendaController@index`
* **Nombre de Ruta:** `tienda.index` (permite invocarla en vistas mediante `route('tienda.index')`).

### B. El Método del Controlador (`app/Http/Controllers/TiendaController.php`)
```php
public function index(Request $request)
{
    // Consulta categorías activas
    $categorias = Categoria::activas()->get();

    // Consulta productos activos con paginación
    $query = Producto::with(['categoria', 'imagenes'])
        ->where('activo', 1);

    // Filtro opcional por categoría si viene en la query string (?categoria=2)
    if ($request->filled('categoria')) {
        $query->where('id_categoria', $request->categoria);
    }

    $productos = $query->paginate(12);

    // Envío de variables a la vista Blade
    return view('tienda.index', compact('productos', 'categorias'));
}
```
* **Variables enviadas:**
  * `$productos`: Instancia de `LengthAwarePaginator` con los productos y sus imágenes precargadas.
  * `$categorias`: Colección de categorías activas para renderizar los botones de filtro.

### C. La Vista Blade (`resources/views/tienda/index.blade.php`)
```blade
@extends('layouts.app')

@section('title', "Catálogo Kawaii — Juno's Store")

@section('content')
<div class="store-container">
    {{-- Banner de bienvenida --}}
    <section class="hero-banner">
        <h2>✨ Bienvenido al rincón más tierno de la web ✨</h2>
        <p>Encuentra tus artículos de papelería, peluches y accesorios favoritos</p>
    </section>

    {{-- Filtros de categoría --}}
    <div class="category-filters">
        <a href="{{ route('tienda.index') }}" class="btn-filter {{ !request('categoria') ? 'active' : '' }}">Todos</a>
        @foreach($categorias as $cat)
            <a href="{{ route('tienda.index', ['categoria' => $cat->id_categoria]) }}" 
               class="btn-filter {{ request('categoria') == $cat->id_categoria ? 'active' : '' }}">
                {{ $cat->nombre }}
            </a>
        @endforeach
    </div>

    {{-- Cuadrícula de productos --}}
    <div class="products-grid">
        @forelse($productos as $producto)
            @php
                $imgPrincipal = $producto->imagenes->where('es_principal', 1)->first() ?? $producto->imagenes->first();
            @endphp
            <div class="product-card">
                <div class="product-thumb">
                    @if($imgPrincipal)
                        <img src="{{ asset($imgPrincipal->ruta_imagen) }}" alt="{{ $producto->nombre }}">
                    @else
                        <img src="{{ asset('img/icon.png') }}" alt="Juno's Store">
                    @endif
                </div>
                <div class="product-info">
                    <span class="badge-cat">{{ $producto->categoria->nombre ?? 'General' }}</span>
                    <h3 class="product-title">{{ $producto->nombre }}</h3>
                    <p class="product-price">${{ number_format($producto->precio, 0, ',', '.') }}</p>
                    <a href="{{ route('tienda.producto', $producto->id_producto) }}" class="btn-kawaii-sm">Ver Detalle</a>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <p>No encontramos productos en esta categoría por el momento (｡•́︿•̀｡)</p>
            </div>
        @endforelse
    </div>

    {{-- Enlaces de paginación generados por Laravel --}}
    <div class="pagination-wrapper">
        {{ $productos->links() }}
    </div>
</div>
@endsection
```

---

## 3. Caso de Estudio 2: Autenticación de Usuarios (Login)

### A. La Ruta (`routes/web.php`)
```php
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
```

### B. El Método del Controlador (`app/Http/Controllers/AuthController.php`)
```php
public function login(Request $request)
{
    // 1. Reglas estrictas de validación
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ], [
        'email.required' => 'Debes ingresar tu correo electrónico.',
        'email.email' => 'El formato del correo electrónico no es válido.',
        'password.required' => 'La contraseña es obligatoria.',
    ]);

    // 2. Búsqueda del usuario en base de datos
    $usuario = Usuario::where('email', strtolower($request->email))->first();

    // 3. Verificación criptográfica con Bcrypt
    if (!$usuario || !Hash::check($request->password, $usuario->password_hash)) {
        return back()
            ->withErrors(['email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.'])
            ->withInput($request->only('email'));
    }

    // 4. Verificación de cuenta activa
    if (!$usuario->activo) {
        return back()
            ->withErrors(['email' => 'Tu cuenta ha sido desactivada temporalmente.'])
            ->withInput($request->only('email'));
    }

    // 5. Iniciar sesión y regenerar sesión para evitar fijación
    Auth::login($usuario, $request->boolean('remember'));
    $request->session()->regenerate();

    // 6. Redirección condicional según el rol
    return redirect()->intended(
        $usuario->isAdmin() ? route('admin.dashboard') : route('tienda.index')
    )->with('success', '¡Hola de nuevo, ' . $usuario->nombre . '! ✨');
}
```

### C. El Formulario Blade (`resources/views/auth/login.blade.php`)
```blade
@extends('layouts.app')

@section('title', "Iniciar Sesión — Juno's Store")

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <img src="{{ asset('img/icon.png') }}" alt="Logo" class="auth-logo">
            <h2>Bienvenida a Juno's Store</h2>
            <p>Ingresa tus datos para continuar</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            {{-- Protección obligatoria contra ataques Cross-Site Request Forgery --}}
            @csrf

            <div class="form-group mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" id="email" name="email" 
                       value="{{ old('email') }}" 
                       class="form-control @error('email') is-invalid @enderror" 
                       required autofocus>
                @error('email')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       required>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Recordar mi sesión</label>
            </div>

            <button type="submit" class="btn-auth-primary">Ingresar a mi Cuenta</button>
        </form>

        <div class="auth-footer">
            <p>¿Aún no tienes cuenta? <a href="{{ route('registro') }}">Regístrate aquí</a></p>
        </div>
    </div>
</div>
@endsection
```

---

## 4. Caso de Estudio 3: Creación y Validación de Productos en el Panel Admin

### A. La Ruta (`routes/web.php`)
```php
Route::post('/admin/productos', [AdminProductoController::class, 'store'])->name('admin.productos.store');
```

### B. El Método del Controlador (`app/Http/Controllers/AdminProductoController.php`)
```php
public function store(Request $request)
{
    // Validación exhaustiva
    $request->validate([
        'nombre' => 'required|string|max:180',
        'descripcion' => 'nullable|string',
        'precio' => 'required|numeric|min:0',
        'id_categoria' => 'required|exists:categorias,id_categoria',
        'id_subcategoria' => 'required|exists:subcategorias,id_subcategoria',
        'id_marca' => 'nullable|exists:marcas,id_marca',
        'sku' => 'nullable|string|max:30|unique:productos,sku',
        'stock_actual' => 'required|integer|min:0',
        'stock_minimo' => 'required|integer|min:0',
    ]);

    $data = $request->all();

    // Generar SKU automático si viene vacío (Práctica recomendada en e-commerce)
    if (empty($data['sku'])) {
        $data['sku'] = 'JUNO-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    $data['activo'] = $request->has('activo') ? 1 : 0;

    // Creación mediante asignación masiva protegida por $fillable
    Producto::create($data);

    return redirect()
        ->route('admin.productos.index')
        ->with('success', '¡El producto se ha registrado exitosamente en el catálogo! 🎉');
}
```

---

## 5. Caso de Estudio 4: Gestión de Usuarios con DataTables (Metodología del Instructor)

### A. Rutas y Middleware (`routes/web.php`)
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/listausuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::put('/admin/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/admin/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
});
```

### B. El Controlador (`app/Http/Controllers/UsuarioController.php`)
```php
public function index()
{
    // Eager loading para obtener el nombre del rol sin consultas adicionales
    $usuarios = User::with('role')->get();
    $roles = Role::all();

    return view('admin.gusuarios.listausers', compact('usuarios', 'roles'));
}

public function update(Request $request, string $id)
{
    $usuario = User::findOrFail($id);

    $usuario->name = $request->input('name');
    $usuario->email = $request->input('email');
    $usuario->role_id = $request->input('role_id');
    $usuario->estado = $request->input('estado');
    $usuario->save();

    return back()->with('success', 'Usuario actualizado exitosamente');
}
```

### C. La Vista con Modales y DataTables (`resources/views/admin/gusuarios/listausers.blade.php`)
* **Integración del Modal:** Cada fila de la tabla renderiza un botón con atributos de Bootstrap 5 `data-bs-toggle="modal" data-bs-target="#editModal{{ $usuario->id }}"`. Al pulsar el botón, se abre el modal correspondiente sin recargar la página.
* **Formulario Spoofing:** El modal utiliza un formulario con `@csrf` y la directiva `@method('PUT')` apuntando a `route('usuarios.update', $usuario->id)`.
* **Exportación y Filtros:** DataTables intercepta la tabla `#usuariosTable` aplicando paginación local, búsqueda instantánea por cualquier columna y botones de descarga instantánea en formato Excel y PDF.
