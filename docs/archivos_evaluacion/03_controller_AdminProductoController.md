# 🎮 Archivo: `app/Http/Controllers/AdminProductoController.php` — Controlador CRUD de Administración

## 1. Ficha Técnica

* **Ubicación:** `app/Http/Controllers/AdminProductoController.php`
* **Tipo:** Controlador de Recursos RESTful de Laravel.
* **Capa:** Controlador Administrativo (C en MVC).
* **Propósito:** Administrar el ciclo de vida completo del catálogo de productos en el panel de administración: listar inventario, validar y registrar nuevos productos, autogenerar SKU, editar información existente y ejecutar bajas lógicas (`soft delete` manual con `activo = 0`).

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    subgraph "Rutas de Entrada (/admin/productos)"
        RIndex[GET /admin/productos] --> CIndex[AdminProductoController@index]
        RCreate[GET /admin/productos/crear] --> CCreate[AdminProductoController@create]
        RStore[POST /admin/productos] --> CStore[AdminProductoController@store]
        REdit[GET /admin/productos/{id}/editar] --> CEdit[AdminProductoController@edit]
        RUpdate[PUT /admin/productos/{id}] --> CUpdate[AdminProductoController@update]
        RDestroy[DELETE /admin/productos/{id}] --> CDestroy[AdminProductoController@destroy]
    end

    subgraph "Modelos Consultados / Mutados"
        CIndex --> ModelProd[Producto.php]
        CCreate --> ModelCat[Categoria.php]
        CCreate --> ModelSub[Subcategoria.php]
        CCreate --> ModelMarca[Marca.php]
        CStore --> ModelProd
        CEdit --> ModelProd
        CUpdate --> ModelProd
        CDestroy --> ModelProd
    end

    subgraph "Vistas y Redirecciones"
        CIndex --> ViewIndex[admin/productos/index.blade.php]
        CCreate --> ViewCreate[admin/productos/crear.blade.php]
        CStore -->|redirect con session success| ViewIndex
        CEdit --> ViewEdit[admin/productos/editar.blade.php]
        CUpdate -->|redirect con session success| ViewIndex
        CDestroy -->|redirect con session success| ViewIndex
    end
```

---

## 3. Código Fuente y Explicación Detallada de Métodos

### Método 1: `index()` — Listado de Inventario
```php
public function index()
{
    $productos = Producto::with(['categoria', 'subcategoria'])
        ->orderBy('creado_en', 'desc')
        ->paginate(15);

    return view('admin.productos.index', compact('productos'));
}
```
* Carga la lista completa de productos (tanto activos como inactivos) ordenados por fecha de creación descendente.
* Precarga las relaciones de categoría y subcategoría para desplegar sus nombres y badges en la tabla administrativa sin generar consultas N+1.

---

### Método 2: `create()` — Formulario de Alta
```php
public function create()
{
    $categorias = Categoria::activas()->get();
    $subcategorias = Subcategoria::where('activo', 1)->get();
    $marcas = Marca::all();

    return view('admin.productos.crear', compact('categorias', 'subcategorias', 'marcas'));
}
```
* Consulta los diccionarios de datos necesarios para alimentar los selectores `<select>` del formulario de creación.

---

### Método 3: `store(Request $request)` — Validación y Creación
```php
public function store(Request $request)
{
    // 1. Reglas estrictas de validación
    $request->validate([
        'nombre' => 'required|max:180',
        'descripcion' => 'nullable',
        'precio' => 'required|numeric|min:0',
        'id_categoria' => 'required|exists:categorias,id_categoria',
        'id_subcategoria' => 'required|exists:subcategorias,id_subcategoria',
        'id_marca' => 'nullable|exists:marcas,id_marca',
        'sku' => 'nullable|unique:productos,sku',
        'stock_actual' => 'required|integer|min:0',
        'stock_minimo' => 'required|integer|min:0',
    ]);

    $data = $request->all();

    // 2. Generación automática de SKU comercial si el usuario no ingresó uno
    if (empty($data['sku'])) {
        $data['sku'] = 'JUNO-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    }

    // 3. Normalización del interruptor checkbox HTML
    $data['activo'] = $request->has('activo') ? 1 : 0;

    // 4. Inserción masiva protegida mediante $fillable
    $producto = Producto::create($data);

    // 5. Redirección con mensaje flash en sesión
    return redirect()
        ->route('admin.productos.index')
        ->with('success', 'Producto creado exitosamente');
}
```
* **Regla `exists`:** Previene errores de integridad referencial asegurando que el `id_categoria` exista previamente en la tabla `categorias`.
* **Regla `unique:productos,sku`:** Impide que dos productos compartan el mismo código de barras / SKU.
* **Manejo de checkbox en HTML:** En los formularios web, si un checkbox no está marcado, el navegador no envía su clave en el payload HTTP. `$request->has('activo') ? 1 : 0` garantiza almacenar un valor booleano correcto en la base de datos.

---

### Método 4: `edit($id)` — Edición con Precarga
```php
public function edit($id)
{
    $producto = Producto::findOrFail($id);
    $categorias = Categoria::activas()->get();
    $subcategorias = Subcategoria::where('activo', 1)->get();
    $marcas = Marca::all();

    return view('admin.productos.editar', compact('producto', 'categorias', 'subcategorias', 'marcas'));
}
```
* Obtiene la instancia del producto a editar mediante su clave primaria. Si no existe, genera error 404.

---

### Método 5: `update(Request $request, $id)` — Actualización
```php
public function update(Request $request, $id)
{
    $producto = Producto::findOrFail($id);

    $request->validate([
        'nombre' => 'required|max:180',
        'descripcion' => 'nullable',
        'precio' => 'required|numeric|min:0',
        'id_categoria' => 'required|exists:categorias,id_categoria',
        'id_subcategoria' => 'required|exists:subcategorias,id_subcategoria',
        'id_marca' => 'nullable|exists:marcas,id_marca',
        // Excepción de unicidad para el propio registro que se está editando
        'sku' => 'nullable|unique:productos,sku,' . $id . ',id_producto',
        'stock_actual' => 'required|integer|min:0',
        'stock_minimo' => 'required|integer|min:0',
    ]);

    $data = $request->all();
    $data['activo'] = $request->has('activo') ? 1 : 0;

    $producto->update($data);

    return redirect()
        ->route('admin.productos.index')
        ->with('success', 'Producto actualizado exitosamente');
}
```
* **Excepción de clave única en validación:** `'sku' => 'nullable|unique:productos,sku,'.$id.',id_producto'`. Permite guardar cambios en el producto sin que el validador rechace su propio SKU existente como duplicado.

---

### Método 6: `destroy($id)` — Eliminación Lógica
```php
public function destroy($id)
{
    $producto = Producto::findOrFail($id);
    $producto->activo = 0;
    $producto->save();

    return redirect()
        ->route('admin.productos.index')
        ->with('success', 'Producto eliminado exitosamente');
}
```
* **Soft Delete Manual:** En lugar de ejecutar un `DELETE FROM productos WHERE id_producto = ?` que destruiría el historial de compras y pedidos antiguos asociados en MySQL, desactiva el registro (`activo = 0`). De este modo, desaparece inmediatamente del escaparate público sin corromper la integridad referencial.

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Por qué se prefiere un borrado lógico (`activo = 0`) sobre un borrado físico (`$producto->delete()`)?**
   * *Respuesta:* Por integridad de datos en comercio electrónico. Si un cliente compró este producto el mes pasado y se borra físicamente, las tablas `pedidos_detalle` quedarían huérfanas o provocarían un error de clave foránea en la base de datos MySQL.
2. **¿Cómo se protegen los formularios contra inyección masiva (*Mass Assignment*)?**
   * *Respuesta:* Mediante la propiedad `$fillable` en el modelo `Producto`. Aunque un atacante envíe campos maliciosos en `$request->all()`, Eloquent únicamente persiste los atributos autorizados en la lista blanca.
3. **¿Cómo se visualiza el mensaje `with('success', '...')` en Blade?**
   * *Respuesta:* Laravel guarda ese string en la sesión flash durante un único ciclo de petición. En `layouts/admin.blade.php`, se evalúa `@if(session('success'))` y se renderiza una tarjeta de alerta pastel kawaii.
