# 🎮 Archivo: `app/Http/Controllers/TiendaController.php` — Controlador Público de la Tienda

## 1. Ficha Técnica

* **Ubicación:** `app/Http/Controllers/TiendaController.php`
* **Tipo:** Clase Controladora de Laravel (hereda de `Controller`).
* **Capa:** Controlador (C en MVC).
* **Propósito:** Gestionar la lógica de negocio del escaparate público: consulta de catálogo con búsqueda y filtrado dinámico, paginación, cálculo de productos relacionados y despacho hacia las vistas Blade de la tienda.

---

## 2. Mapa de Conexiones

```mermaid
graph LR
    subgraph "Entrada"
        RouteIndex[Route: tienda.index] -->|GET /?q=peluche&categoria=1| TiendaCtrlIndex[TiendaController@index]
        RouteShow[Route: tienda.producto] -->|GET /productos/{id}| TiendaCtrlShow[TiendaController@show]
    end

    subgraph "Modelos y Datos"
        TiendaCtrlIndex -->|Eager loading: with() + Scopes: activos(), conStock()| ModelProd[Producto.php]
        TiendaCtrlIndex -->|withCount('productos')| ModelCat[Categoria.php]
        TiendaCtrlShow -->|findOrFail($id) con relaciones| ModelProd
        TiendaCtrlShow -->|Productos relacionados de misma categoría| ModelProd
    end

    subgraph "Salida / Vistas"
        TiendaCtrlIndex -->|compact('productos', 'categorias')| ViewIndex[resources/views/tienda/index.blade.php]
        TiendaCtrlShow -->|compact('producto', 'relacionados')| ViewDetalle[resources/views/tienda/detalle.blade.php]
    end
```

---

## 3. Código Fuente y Explicación Detallada

### Método 1: `index(Request $request)` — Catálogo y Filtros

```php
public function index(Request $request)
{
    // 1. Inicialización de la consulta con Eager Loading y Scopes locales
    $query = Producto::with(['categoria', 'subcategoria', 'imagenes'])
        ->activos()
        ->conStock();

    // 2. Filtro de búsqueda textual por nombre o descripción
    if ($request->filled('q')) {
        $termino = $request->q;
        $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }

    // 3. Filtro por categoría seleccionada
    if ($request->filled('categoria')) {
        $query->where('id_categoria', $request->categoria);
    }

    // 4. Ordenamiento y Paginación
    $productos = $query->orderBy('creado_en', 'desc')->paginate(12);

    // 5. Carga de categorías con conteo de productos
    $categorias = Categoria::activas()->withCount('productos')->get();

    // 6. Retorno de la vista con datos compactados
    return view('tienda.index', compact('productos', 'categorias'));
}
```

#### Análisis Técnico:
1. **Eager Loading (`with([...])`):** Previene el problema de rendimiento **N+1**. En vez de ejecutar una consulta SQL por cada producto para saber su categoría e imágenes, Eloquent resuelve todas las relaciones en solo 3 consultas optimizadas con `IN (...)`.
2. **Scopes Locales (`activos()`, `conStock()`):** Encapsulan reglas de negocio en el Modelo `Producto`. Garantizan que nunca se ofrezcan productos descontinuados o sin inventario en el escaparate público.
3. **Agrupación Lógica en Búsqueda (`where(function($q) ...)`):** Envuelve las condiciones `OR` entre paréntesis SQL: `AND (nombre LIKE ? OR descripcion LIKE ?)`. Esto evita que un `OR` rompa el filtro previo de `activo = 1`.
4. **Paginación Eficiente (`paginate(12)`):** Genera automáticamente los metadatos de paginación (`total`, `per_page`, `current_page`, `last_page`), alimentando el componente visual de paginación de Bootstrap 5 en Blade.
5. **`withCount('productos')`:** Agrega una subconsulta `COUNT(*)` a cada categoría, permitiendo renderizar los badges numéricos kawaii en las píldoras de filtro.

---

### Método 2: `show($id)` — Detalle del Producto

```php
public function show($id)
{
    // 1. Búsqueda estricta o excepción 404 automática
    $producto = Producto::with(['categoria', 'subcategoria', 'marca', 'imagenes', 'resenas.usuario'])
        ->findOrFail($id);

    // 2. Verificación de disponibilidad
    if (! $producto->activo) {
        abort(404, 'Producto no disponible');
    }

    // 3. Consulta de hasta 4 productos relacionados
    $relacionados = Producto::with(['imagenes'])
        ->activos()
        ->conStock()
        ->where('id_categoria', $producto->id_categoria)
        ->where('id_producto', '!=', $id)
        ->limit(4)
        ->get();

    // 4. Retorno a la vista de detalle
    return view('tienda.detalle', compact('producto', 'relacionados'));
}
```

#### Análisis Técnico:
1. **`findOrFail($id)`:** Busca la clave primaria `id_producto`. Si el ID no existe en MySQL, lanza de forma nativa `ModelNotFoundException`, la cual Laravel convierte automáticamente en una respuesta HTTP 404 amigable.
2. **Carga Anidada (`resenas.usuario`):** Carga las reseñas del producto y, a su vez, el usuario autor de cada reseña para mostrar su nombre en la ficha técnica.
3. **Productos Relacionados:** Consulta hasta 4 productos de la misma categoría excluyendo el producto en visualización (`where('id_producto', '!=', $id)`).

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Qué es el problema N+1 y cómo lo soluciona este controlador?**
   * *Respuesta:* Ocurre cuando al iterar 12 productos en una vista, se hace 1 consulta para los productos y 12 consultas adicionales para sus categorías (1+N). `TiendaController` lo previene usando `with(['categoria', 'subcategoria', 'imagenes'])`, reduciendo las consultas a un número constante mínimo.
2. **¿Qué diferencia hay entre `$request->has('q')` y `$request->filled('q')`?**
   * *Respuesta:* `$request->has('q')` devuelve `true` si el parámetro existe en la URL aunque esté vacío (`?q=`). `$request->filled('q')` verifica que exista **y** contenga texto real, evitando búsquedas vacías innecesarias en la base de datos.
3. **¿Cómo recibe la vista Blade las variables del controlador?**
   * *Respuesta:* Mediante la función helper de PHP `compact('productos', 'categorias')`, que crea un array asociativo `['productos' => $productos, 'categorias' => $categorias]` accesible en Blade mediante `$productos` y `$categorias`.
