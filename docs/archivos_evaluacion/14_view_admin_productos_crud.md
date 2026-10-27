# 📦 Vistas CRUD de Administración: `index.blade.php`, `crear.blade.php` y `editar.blade.php`

## 1. Ficha Técnica

* **Ubicaciones:**
  * `resources/views/admin/productos/index.blade.php`
  * `resources/views/admin/productos/crear.blade.php`
  * `resources/views/admin/productos/editar.blade.php`
* **Tipo:** Vistas Blade del subsistema de inventario (extienden de `layouts.admin`).
* **Capa:** Vista Administrativa (V en MVC).
* **Propósito:** Ofrecer la suite completa de gestión de productos:
  1. `index.blade.php`: Tabla administrativa de alta legibilidad con miniaturas fotográficas, badges de categoría, semáforos de stock, interruptores de estado y acciones rápidas (Editar / Eliminar con modal de confirmación).
  2. `crear.blade.php`: Formulario estructurado en 4 tarjetas temáticas (Datos Básicos, Clasificación, Precios/Inventario y Visibilidad).
  3. `editar.blade.php`: Mismo formulario con precarga de valores existentes y protección de clave única.

---

## 2. Mapa de Conexiones del CRUD

```mermaid
graph TD
    AdminProdCtrl[AdminProductoController.php]

    subgraph "Listado e Interacciones"
        AdminProdCtrl -->|index() compact('productos')| ViewIndex[admin/productos/index.blade.php]
        ViewIndex -->|Clic en 'Nuevo'| ViewCreate[admin/productos/crear.blade.php]
        ViewIndex -->|Clic en 'Editar'| ViewEdit[admin/productos/editar.blade.php]
        ViewIndex -->|DELETE /admin/productos/{id} + @csrf| AdminProdCtrl
    end

    subgraph "Formulario de Alta"
        AdminProdCtrl -->|create() compact('categorias', 'subcategorias', 'marcas')| ViewCreate
        ViewCreate -->|POST /admin/productos + @csrf| AdminProdCtrl
    end

    subgraph "Formulario de Edición"
        AdminProdCtrl -->|edit($id) compact('producto', ...)| ViewEdit
        ViewEdit -->|POST /admin/productos/{id} + @method('PUT') + @csrf| AdminProdCtrl
    end
```

---

## 3. Código Fuente y Explicación Detallada: `index.blade.php`

```blade
@extends('layouts.admin')

@section('title', "Gestión de Productos - Panel Admin Juno's Store")

@section('content')
<div class="admin-card">
    <div class="admin-card-header">
        <h2><i class="fas fa-list-ul"></i> Listado Completo</h2>
        <span class="badge-kawaii badge-kawaii-purple">{{ $productos->total() }} registrados</span>
    </div>

    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table-kawaii-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <td>#{{ $producto->id_producto }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @php
                                    $thumb = $producto->imagenes->where('es_principal', 1)->first() 
                                          ?? $producto->imagenes->first();
                                @endphp
                                @if($thumb)
                                    <img src="{{ asset($thumb->ruta_imagen) }}" class="table-thumb" alt="">
                                @else
                                    <div class="table-thumb d-flex align-items-center justify-content-center">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                @endif
                                <div>
                                    <strong class="d-block">{{ $producto->nombre }}</strong>
                                    <small class="text-muted">SKU: {{ $producto->sku ?: '—' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kawaii badge-kawaii-purple">{{ $producto->categoria->nombre }}</span>
                        </td>
                        <td><strong>${{ number_format($producto->precio, 2, ',', '.') }}</strong></td>
                        <td>
                            @if($producto->stock_actual <= 0)
                                <span class="badge-kawaii badge-kawaii-pink">Agotado (0)</span>
                            @elseif($producto->stock_actual <= $producto->stock_minimo)
                                <span class="badge-kawaii badge-kawaii-yellow">Bajo ({{ $producto->stock_actual }})</span>
                            @else
                                <span class="badge-kawaii badge-kawaii-purple">{{ $producto->stock_actual }} u.</span>
                            @endif
                        </td>
                        <td>
                            @if($producto->activo)
                                <span class="badge-kawaii badge-kawaii-purple">Activo</span>
                            @else
                                <span class="badge-kawaii badge-kawaii-pink">Inactivo</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.productos.edit', $producto->id_producto) }}" class="btn-action-edit" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.productos.destroy', $producto->id_producto) }}" method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas desactivar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-delete" title="Desactivar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No hay productos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
```

### Características de la Tabla:
1. **`.table-responsive`:** Envuelve la tabla para permitir desplazamiento horizontal táctil suave en pantallas móviles sin que se rompa el diseño del contenedor exterior.
2. **Semáforo Visual de Existencias:** Las celdas de stock evalúan automáticamente tres estados: Agotado (Rosa/Rojo), Bajo inventario (Amarillo pastel) y Stock suficiente (Morado pastel).
3. **Confirmación Nativa JavaScript:** El formulario de borrado incluye `onsubmit="return confirm('...');"`, evitando eliminaciones accidentales con un solo clic.

---

## 4. Estructura de Formularios: `crear.blade.php` y `editar.blade.php`

Los formularios de creación y edición fueron organizados en **tarjetas modulares**:

1. **Tarjeta 1: Información General**
   * Nombre del Producto (`<input type="text" name="nombre">`).
   * Descripción comercial (`<textarea name="descripcion">`).
2. **Tarjeta 2: Categorización**
   * Selector dinámico de Categoría (`<select name="id_categoria">`) alimentado por la colección `$categorias`.
   * Selector dinámico de Subcategoría (`<select name="id_subcategoria">`).
   * Selector de Marca (`<select name="id_marca">`).
3. **Tarjeta 3: Precio e Inventario**
   * Precio de venta (`<input type="number" step="0.01" name="precio">`).
   * Código SKU / Código de barras (`<input type="text" name="sku">`).
   * Stock Actual (`stock_actual`) y Stock Mínimo para alertas (`stock_minimo`).
4. **Tarjeta 4: Visibilidad en Tienda**
   * Interruptor estilizado tipo switch (`<input type="checkbox" name="activo" value="1">`) con texto explicativo: *"Visible inmediatamente en el catálogo público"*.

### Method Spoofing en `editar.blade.php`:
```blade
<form method="POST" action="{{ route('admin.productos.update', $producto->id_producto) }}">
    @csrf
    @method('PUT')
    ...
```
* Envía un formulario estándar POST que Laravel interpreta internamente como un verbo HTTP `PUT`, cumpliendo con los estándares de la arquitectura RESTful.

---

## 5. Preguntas Frecuentes para Evaluación

1. **¿Cómo funciona `@method('PUT')` en Laravel?**
   * *Respuesta:* Los navegadores web solo pueden emitir peticiones `GET` y `POST`. La directiva de Blade `@method('PUT')` inyecta un campo oculto `<input type="hidden" name="_method" value="PUT">`. El enrutador de Laravel detecta este campo y procesa la petición como si fuera una solicitud PUT nativa.
2. **¿Por qué se usa `@forelse` en lugar de `@foreach`?**
   * *Respuesta:* `@forelse` combina un bucle `foreach` con una cláusula `@empty`. Si la colección de productos contiene elementos, los itera; si está vacía (`count === 0`), salta automáticamente al bloque `@empty` y muestra un mensaje amigable sin necesidad de anidar un `@if`.
3. **¿Cómo se recuperan los datos en el formulario de edición en caso de error de validación?**
   * *Respuesta:* Usando el segundo parámetro de la función helper `old()`. Por ejemplo: `value="{{ old('precio', $producto->precio) }}"`. Si el usuario envió un dato inválido, muestra lo que el usuario escribió; si es la primera carga de la página, muestra el valor original almacenado en la base de datos.
