# 🛍️ Archivo: `resources/views/tienda/detalle.blade.php` — Ficha de Producto y Galería

## 1. Ficha Técnica

* **Ubicación:** `resources/views/tienda/detalle.blade.php`
* **Tipo:** Vista Blade (extiende de `layouts.app`).
* **Capa:** Vista / Detalle (V en MVC).
* **Propósito:** Mostrar la información pormenorizada de un producto: migas de pan interactivas (*breadcrumbs*), galería de imágenes dinámica con zoom y conmutación de miniaturas, insignias de categoría, subcategoría y marca, precio destacado con IVA, selector interactivo de cantidad, botón de añadir al carrito, desglose de beneficios (envío rápido, pago seguro, atención kawaii) y carrusel de productos relacionados.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    TiendaCtrlShow[TiendaController@show] -->|Envía $producto, $relacionados| ViewDetalle[tienda/detalle.blade.php]
    
    subgraph "Datos Consumidos en Blade"
        ViewDetalle -->|$producto->categoria| Breadcrumb[Migas de Pan Kawaii]
        ViewDetalle -->|$producto->imagenes| MainGallery[Galería con función changeMainImg()]
        ViewDetalle -->|$producto->stock_actual| StockIndicator[Semáforo de Stock: En Stock / Agotado]
        ViewDetalle -->|$producto->precio| PriceTag[Precio formateado]
        ViewDetalle -->|$relacionados| RelatedGrid[Cuadrícula de 4 Productos Similares]
    end

    subgraph "Navegación Saliente"
        Breadcrumb -->|href=tienda.index?categoria=ID#productos| ViewIndex[Catálogo Filtrado]
        RelatedGrid -->|href=tienda.producto(ID)| ViewDetalleOther[Ficha de otro producto]
        BackBtn[Botón Volver al Catálogo] -->|href=tienda.index#productos| ViewIndex
    end
```

---

## 3. Código Fuente y Explicación Detallada de Módulos

### A. Migas de Pan (*Breadcrumb Kawaii*)
```blade
<nav aria-label="Ruta de navegación">
    <ul class="breadcrumb-kawaii">
        <li>
            <a href="{{ route('tienda.index') }}"><i class="fas fa-home me-1"></i> Inicio</a>
        </li>
        <li class="sep"><i class="fas fa-chevron-right"></i></li>
        <li>
            <a href="{{ route('tienda.index', ['categoria' => $producto->id_categoria]) }}#productos">
                {{ $producto->categoria->nombre }}
            </a>
        </li>
        @if($producto->subcategoria)
            <li class="sep"><i class="fas fa-chevron-right"></i></li>
            <li><span>{{ $producto->subcategoria->nombre }}</span></li>
        @endif
        <li class="sep"><i class="fas fa-chevron-right"></i></li>
        <li class="current">{{ $producto->nombre }}</li>
    </ul>
</nav>
```
* Ofrece orientación espacial clara al usuario dentro de la jerarquía de la tienda.
* El enlace de la categoría redirige de vuelta al catálogo con la categoría prefiltrada.

---

### B. Galería Interactiva con Miniaturas y JavaScript Vanilla
```blade
<div class="product-gallery-wrap">
    @php
        $imagenPrincipal = $producto->imagenes->where('es_principal', 1)->first() 
                        ?? $producto->imagenes->first();
    @endphp

    <div class="main-image-container">
        @if($imagenPrincipal)
            <img id="mainProductImg" src="{{ asset($imagenPrincipal->url_limpia ?? $imagenPrincipal->ruta_imagen) }}"
                 alt="{{ $producto->nombre }}" loading="eager"
                 onerror="this.style.display='none'; document.getElementById('mainImgPlaceholder').style.display='flex';">
            <div id="mainImgPlaceholder" class="main-image-placeholder" style="display: none;">
                <i class="fas fa-paw"></i>
                <span style="font-weight:700;">Imagen en preparación</span>
            </div>
        @else
            <div class="main-image-placeholder">
                <i class="fas fa-paw"></i>
                <span style="font-weight:700;">Imagen en preparación</span>
            </div>
        @endif
    </div>

    @if($producto->imagenes->count() > 1)
    <div class="thumbnails-row" role="tablist">
        @foreach($producto->imagenes as $idx => $imagen)
        <div class="thumb-item {{ $imagenPrincipal && $imagen->id_imagen == $imagenPrincipal->id_imagen ? 'active' : '' }}"
             onclick="changeMainImg('{{ asset($imagen->url_limpia ?? $imagen->ruta_imagen) }}', this)"
             tabindex="0" role="button">
            <img src="{{ asset($imagen->url_limpia ?? $imagen->ruta_imagen) }}" alt="Vista {{ $idx + 1 }}">
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
function changeMainImg(src, el) {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg) {
        mainImg.style.opacity = '0.4';
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = '1';
        }, 120);
    }
    document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
}
</script>
@endpush
```
* **Transición de Opacidad:** La función JavaScript `changeMainImg` reduce temporalmente la opacidad a `0.4` durante 120 milisegundos para simular un desvanecimiento suave (*fade-in*) al cambiar de foto.
* **Marcado Activo:** Marca con un borde lila brillante la miniatura que se encuentra actualmente seleccionada.

---

### C. Selector de Cantidad y Semáforo de Disponibilidad
```blade
@if($producto->stock_actual > 0)
    <div class="stock-indicator-box stock-box-in">
        <i class="fas fa-check-circle"></i>
        <span>{{ $producto->stock_actual }} unidades disponibles en tienda</span>
    </div>

    <!-- Controles de Cantidad -->
    <div class="quantity-selector-box">
        <label for="cantInput" class="qty-label">Cantidad:</label>
        <div class="qty-control">
            <button type="button" class="btn-qty" onclick="adjustQty(-1)">-</button>
            <input type="number" id="cantInput" value="1" min="1" max="{{ $producto->stock_actual }}">
            <button type="button" class="btn-qty" onclick="adjustQty(1)">+</button>
        </div>
    </div>

    <button type="button" class="btn-detail-cart">
        <i class="fas fa-shopping-bag"></i> Añadir al Carrito
    </button>
@else
    <div class="stock-indicator-box stock-box-out">
        <i class="fas fa-times-circle"></i>
        <span>Agotado temporalmente</span>
    </div>
    <button type="button" class="btn-detail-cart" disabled style="opacity: 0.6; cursor: not-allowed;">
        Producto Agotado
    </button>
@endif
```
* **Lógica Defensiva de Stock:** El selector de cantidad tiene como límite máximo `max="{{ $producto->stock_actual }}"`. Si el stock llega a 0, el botón de compra se desactiva visual y funcionalmente.

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Cómo se previene que el usuario ingrese una cantidad mayor al stock disponible?**
   * *Respuesta:* Mediante una doble capa: en el frontend, el atributo `max="{{ $producto->stock_actual }}"` y la función JavaScript `adjustQty()` impiden sobrepasar el inventario existente; en el backend, la validación del controlador verifica la regla `'cantidad' => 'required|integer|min:1|max:' . $producto->stock_actual`.
2. **¿Por qué se usan badges de colores diferenciados para la categoría y la marca?**
   * *Respuesta:* Para establecer una **jerarquía visual**. La categoría utiliza `.badge-kawaii-purple` (morado pastel), la subcategoría `.badge-kawaii-pink` (rosa suave) y la marca `.badge-kawaii-yellow` (dorado pastel), guiando al ojo de forma intuitiva.
3. **¿Cómo se cargan los productos relacionados?**
   * *Respuesta:* El controlador ejecuta `Producto::where('id_categoria', $producto->id_categoria)->where('id_producto', '!=', $id)->limit(4)->get()`. Si existen productos en la misma categoría, Blade los renderiza debajo de la ficha principal en una sección titulada *"Te podría encantar"*.
