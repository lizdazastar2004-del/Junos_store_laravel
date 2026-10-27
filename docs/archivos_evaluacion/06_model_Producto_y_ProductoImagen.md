# 📦 Modelos Eloquent: `Producto.php` y `ProductoImagen.php` — Catálogo e Imágenes

## 1. Ficha Técnica

* **Ubicaciones:** 
  * `app/Models/Producto.php`
  * `app/Models/ProductoImagen.php`
* **Tipo:** Modelos Eloquent ORM (Mapeo Objeto-Relacional).
* **Capa:** Modelo / Datos (M en MVC).
* **Propósito:** Representar los registros de las tablas MySQL `productos` y `producto_imagenes`. Gestionar sus relaciones (1 a N), scopes de consulta, conversiones de tipo (`$casts`), asignación masiva protegida (`$fillable`) y accesores dinámicos para compatibilidad de rutas de imagen.

---

## 2. Diagrama de Relaciones Eloquent

```mermaid
erDiagram
    CATEGORIAS ||--o{ PRODUCTOS : "id_categoria"
    SUBCATEGORIAS ||--o{ PRODUCTOS : "id_subcategoria"
    MARCAS ||--o{ PRODUCTOS : "id_marca"
    PRODUCTOS ||--|{ PRODUCTO_IMAGENES : "hasMany (id_producto)"
    PRODUCTOS ||--o{ RESENAS : "hasMany (id_producto)"

    PRODUCTOS {
        bigint id_producto PK
        varchar nombre
        text descripcion
        decimal precio
        int stock_actual
        int stock_minimo
        tinyint activo
        varchar sku
    }

    PRODUCTO_IMAGENES {
        bigint id_imagen PK
        bigint id_producto FK
        varchar url_imagen
        tinyint principal
        smallint orden_imagen
    }
```

---

## 3. Código Fuente y Explicación Detallada: `Producto.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    // 1. Configuración de tabla y clave primaria personalizada
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    // 2. Control de marcas de tiempo en español
    public $timestamps = false;
    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    // 3. Lista blanca contra Mass Assignment
    protected $fillable = [
        'nombre', 'descripcion', 'precio',
        'id_categoria', 'id_subcategoria', 'id_marca',
        'sku', 'stock_actual', 'stock_minimo', 'activo',
    ];

    // 4. Casteo automático de tipos primitivos
    protected $casts = [
        'precio' => 'decimal:2',
        'stock_actual' => 'integer',
        'stock_reservado' => 'integer',
        'stock_minimo' => 'integer',
        'activo' => 'boolean',
    ];

    // 5. Relaciones Eloquent
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'id_subcategoria', 'id_subcategoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class, 'id_producto', 'id_producto');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'id_producto', 'id_producto');
    }

    // 6. Scopes Locales de Consulta
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }

    public function scopeConStock($query)
    {
        return $query->where('stock_actual', '>', 0);
    }
}
```

### Puntos Clave de `Producto.php`:
1. **Clave Primaria No Estándar (`id_producto`):** Por defecto, Laravel asume que la clave es `id`. Al declarar `protected $primaryKey = 'id_producto';`, métodos como `find()`, `findOrFail()` y los enlaces de ruta funcionan automáticamente con el esquema existente.
2. **Scopes Locales (`scopeActivos`, `scopeConStock`):** Permiten encadenar consultas limpias en los controladores:
   `Producto::activos()->conStock()->paginate(12);`
   Esto centraliza la lógica de negocio evitando repetir `where('activo', 1)->where('stock_actual', '>', 0)` en múltiples archivos.
3. **Casts (`'activo' => 'boolean'`):** Convierte el `TINYINT(1)` de MySQL en un booleano estricto de PHP (`true`/`false`).

---

## 4. Código Fuente y Explicación Detallada: `ProductoImagen.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';
    protected $primaryKey = 'id_imagen';
    public $timestamps = false;

    protected $fillable = [
        'id_producto', 'ruta_imagen', 'url_imagen',
        'es_principal', 'principal', 'orden',
        'orden_imagen', 'texto_alternativo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'principal' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }

    // ACCESORES DE COMPATIBILIDAD
    public function getRutaImagenAttribute()
    {
        return $this->attributes['ruta_imagen'] ?? $this->attributes['url_imagen'] ?? null;
    }

    public function getEsPrincipalAttribute()
    {
        return (bool) ($this->attributes['principal'] ?? $this->attributes['es_principal'] ?? false);
    }

    public function getUrlLimpiaAttribute()
    {
        $raw = $this->ruta_imagen;
        if (! $raw) {
            return null;
        }

        // Remover prefijo /public/ si viene de la migración PHP anterior
        return preg_replace('#^/?public/#', '/', $raw);
    }
}
```

---

## 5. 💥 ANÁLISIS DE IMPACTO: "¿QUÉ SUCEDE SI QUITO ESTO EN EL PROYECTO?"

| Si quitas... | Error técnico que ocurre | Impacto real en el sistema |
| :--- | :--- | :--- |
| **`protected $primaryKey = 'id_producto';`** | `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'id' in 'where clause'` | Laravel intentará buscar o actualizar registros usando `WHERE id = ?`. Como la columna física en MySQL se llama `id_producto`, cada llamada a `find()`, `update()` o `delete()` arrojará una excepción fatal 500. |
| **`public function imagenes()` en `Producto.php`** | `BadMethodCallException: Call to undefined method App\Models\Producto::imagenes()` | Al cargar la tienda o el detalle de un producto, la relación `with('imagenes')` o la propiedad `$producto->imagenes` fallará de inmediato, impidiendo cargar las fotos de los productos. |
| **`getUrlLimpiaAttribute()` en `ProductoImagen.php`** | Las imágenes no cargan y el navegador muestra el ícono de imagen rota. | Las rutas heredadas de la base de datos que comienzan con `/public/img/...` intentarán cargarse como `http://127.0.0.1:8000/public/img/...`, ruta que no existe porque el DocumentRoot de Laravel ya es la carpeta `public`. |
| **`protected $fillable` en `Producto.php`** | `Illuminate\Database\Eloquent\MassAssignmentException: Add [nombre] to fillable property to allow mass assignment on [App\Models\Producto].` | El método `Producto::create($data)` del panel administrativo se bloquea por completo, impidiendo registrar cualquier producto nuevo. |
| **`public function scopeConStock($query)`** | `BadMethodCallException: Call to undefined method App\Models\Producto::conStock()` | Rompe inmediatamente la consulta del catálogo en `TiendaController@index` al intentar invocar `->conStock()`. |

---

## 6. ❓ ESCENARIOS DE CONTINGENCIA: "¿QUÉ PASA SI SUCEDE ESTO O AQUELLO?"

### Escenario 1: ¿Qué pasa si la base de datos devuelve un precio con muchos decimales (ej. `25000.559999`)?
* **Comportamiento:** La propiedad `protected $casts = ['precio' => 'decimal:2']` formatea automáticamente el número a exactamente 2 posiciones decimales (`25000.56`), garantizando coherencia visual y cálculos contables exactos en pesos/dólares.

### Escenario 2: ¿Qué pasa si un producto tiene 5 fotos y se necesita identificar cuál va en la tarjeta del catálogo?
* **Comportamiento:** En `ProductoImagen.php`, la columna `principal` (o `es_principal`) marca la portada. En la vista se utiliza `$producto->imagenes->where('es_principal', true)->first()`, y si ninguna está marcada, se toma `$producto->imagenes->first()`, evitando dejar la tarjeta sin portada.

---

## 7. 🛠️ POSIBLES PROBLEMAS, ERRORES COMUNES Y SOLUCIONES

### Problema 1: `Unknown column 'updated_at' in 'field list'`
* **Causa:** Laravel intenta actualizar automáticamente las columnas estándar en inglés `created_at` y `updated_at` que no existen en esta base de datos en español.
* **Solución:** Declarar en el modelo:
  ```php
  public $timestamps = false;
  // O configurar sus nombres personalizados:
  const CREATED_AT = 'creado_en';
  const UPDATED_AT = 'actualizado_en';
  ```

### Problema 2: Las fotos de los productos se ven rotas en el navegador
* **Causa:** El archivo físico no fue copiado a la carpeta pública o la vista no usa el accesor `url_limpia`.
* **Solución:** En las plantillas Blade, usar siempre:
  ```html
  <img src="{{ asset($imagen->url_limpia) }}" alt="{{ $producto->nombre }}">
  ```

---

## 8. 🎓 Preguntas Frecuentes para Evaluación

1. **¿Qué es un Accesor en Eloquent y para qué se usó aquí?**
   * *Respuesta:* Un accesor es un método `get{Nombre}Attribute()` que intercepta la lectura de una propiedad en un modelo para formatearla antes de entregarla. Se utilizó `getUrlLimpiaAttribute()` para limpiar rutas heredadas de la base de datos sin necesidad de alterar físicamente los registros en MySQL.
2. **¿Cuál es la diferencia entre `$fillable` y `$guarded`?**
   * *Respuesta:* `$fillable` define una lista blanca explícita de campos que pueden asignarse masivamente (`create()` o `update()`). `$guarded` define una lista negra de campos protegidos. Se usa `$fillable` por mayor seguridad.
3. **¿Cómo se define una relación de uno a muchos (*One to Many*) entre Producto e Imágenes?**
   * *Respuesta:* En `Producto.php` mediante `$this->hasMany(ProductoImagen::class, 'id_producto', 'id_producto')` y en `ProductoImagen.php` con la relación inversa `$this->belongsTo(Producto::class, 'id_producto', 'id_producto')`.
