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
    subgraph "Entrada HTTP"
        RouteIndex[Route: tienda.index] -->|GET /?q=peluche&categoria=1| TiendaCtrlIndex[TiendaController@index]
        RouteShow[Route: tienda.producto] -->|GET /productos/{id}| TiendaCtrlShow[TiendaController@show]
    end

    subgraph "Modelos y Datos (M)"
        TiendaCtrlIndex -->|Eager loading: with + Scopes: activos, conStock| ModelProd[Producto.php]
        TiendaCtrlIndex -->|withCount productos| ModelCat[Categoria.php]
        TiendaCtrlShow -->|findOrFail $id con relaciones| ModelProd
        TiendaCtrlShow -->|Productos relacionados misma categoria| ModelProd
        ModelProd --> MySQL[(MySQL: junos_store)]
    end

    subgraph "Salida / Vistas Blade (V)"
        TiendaCtrlIndex -->|compact productos, categorias| ViewIndex[resources/views/tienda/index.blade.php]
        TiendaCtrlShow -->|compact producto, relacionados| ViewDetalle[resources/views/tienda/detalle.blade.php]
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

## 4. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **`with(['categoria', 'subcategoria', 'imagenes'])`** | Ningún error visual aparente, pero el log de consultas MySQL registrará decenas de queries adicionales. | **Problema N+1 y colapso de rendimiento:** Al iterar 12 productos en Blade, cada acceso a `$prod->categoria->nombre` o `$prod->imagenes` ejecutará una nueva consulta SQL individual (más de 37 queries por página en vez de 3). |
| **`->conStock()`** | Ningún error de código. | **Experiencia comercial negativa:** Se mostrarán productos con stock 0 en la vitrina pública. El cliente intentará comprarlos y fallará en el checkout, generando desconfianza. |
| **`->activos()`** | Ningún error de código. | **Fuga de productos descontinuados o borrados:** Los productos que el administrador eliminó o desactivó (`activo = 0`) seguirán apareciendo a la venta pública. |
| **La función anónima en `$query->where(function ($q) ...)`** | Rompe la precedencia de operadores booleanos en SQL: `WHERE activo = 1 AND nombre LIKE ? OR descripcion LIKE ?`. | Al usar `OR` sin paréntesis, cualquier producto cuya descripción coincida con la búsqueda será listado **incluso si está inactivo (`activo = 0`) o con stock 0**. |
| **`findOrFail($id)` por `find($id)`** | `Attempt to read property "activo" on null` (Error 500 fatal). | Si el visitante coloca un ID que no existe (ej. `/productos/999`), `find()` retorna `null`. La línea siguiente `$producto->activo` hace explotar PHP con error 500 en lugar de mostrar la página 404. |
| **`compact('productos', 'categorias')`** | `ErrorException: Undefined variable $productos` en `index.blade.php`. | La vista Blade no recibe las variables de la base de datos y la aplicación arroja un error 500 inmediato al cargar la página de inicio. |

---

## 5. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si el cliente busca una palabra que no coincide con ningún producto (`?q=xyz123`)?
* **Comportamiento:** La colección paginada `$productos` llega vacía (`count() === 0`). En `index.blade.php`, la directiva `@forelse` detecta la colección vacía y ejecuta el bloque `@empty`, mostrando una ilustración kawaii con el mensaje *"No encontramos productos que coincidan con tu búsqueda 🌸"*, manteniendo una interfaz limpia sin errores.

### Escenario 2: ¿Qué pasa si un producto no tiene imágenes registradas en la tabla `producto_imagenes`?
* **Comportamiento:** En `Producto.php`, el accesor `imagen_principal` verifica si `$this->imagenes->first()` existe. Si es nulo, retorna por defecto la imagen placeholder del sistema (`/img/placeholder-kawaii.svg`), evitando imágenes rotas con la "X" del navegador.

### Escenario 3: ¿Qué pasa si el administrador desactiva un producto que un usuario tenía guardado en favoritos o en su historial?
* **Comportamiento:** Al entrar a `/productos/{id}`, la línea 92 detecta `if (!$producto->activo)` y ejecuta `abort(404, 'Producto no disponible')`. El usuario ve la pantalla de error 404 personalizada de Junos Store y no puede añadirlo a su carrito.

---

## 6. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: "Cargué productos en MySQL pero no aparecen en la tienda"
* **Causa:** El producto tiene `activo = 0` o `stock_actual = 0`. El controlador filtra con `->activos()->conStock()`.
* **Solución:**
  1. Entrar al panel admin `/admin/dashboard` y usar el botón **"Activar Productos con Stock"** o **"Reponer Stock de Agotados"**.
  2. O directamente en MySQL: `UPDATE productos SET activo = 1, stock_actual = 10 WHERE id_producto = ?;`.

### Problema 2: `Call to a member function paginate() on null`
* **Causa:** Se encadenó un método que retornó `null` antes de llamar a `paginate()`.
* **Solución:** Asegurarse de que la variable `$query` sea una instancia de `Illuminate\Database\Eloquent\Builder`, encadenando los filtros condicionales solo con `$query->where(...)`.

---

## 7. 🎓 Preguntas Frecuentes para Evaluación

1. **¿Qué es el problema N+1 y cómo lo soluciona este controlador?**
   * *Respuesta:* Ocurre cuando al iterar 12 productos en una vista, se hace 1 consulta para los productos y 12 consultas adicionales para sus categorías (1+N). `TiendaController` lo previene usando `with(['categoria', 'subcategoria', 'imagenes'])`, reduciendo las consultas a un número constante mínimo optimizado.
2. **¿Qué diferencia hay entre `$request->has('q')` y `$request->filled('q')`?**
   * *Respuesta:* `$request->has('q')` devuelve `true` si el parámetro existe en la URL aunque esté vacío (`?q=`). `$request->filled('q')` verifica que exista **y** contenga texto real, evitando búsquedas vacías innecesarias en la base de datos.
3. **¿Cómo recibe la vista Blade las variables del controlador?**
   * *Respuesta:* Mediante la función helper de PHP `compact('productos', 'categorias')`, que crea un array asociativo `['productos' => $productos, 'categorias' => $categorias]` accesible en Blade mediante `$productos` y `$categorias`.
