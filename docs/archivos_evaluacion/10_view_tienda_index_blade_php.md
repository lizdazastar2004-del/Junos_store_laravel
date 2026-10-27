# 🌸 Archivo: `resources/views/tienda/index.blade.php` — Escaparate y Catálogo Principal

## 1. Ficha Técnica

* **Ubicación:** `resources/views/tienda/index.blade.php`
* **Tipo:** Vista Blade (extiende de `layouts.app`).
* **Capa:** Vista (V en MVC).
* **Propósito:** Ser la carta de presentación visual de Juno's Store. Conserva intacta la identidad visual pastel original (hero banner, círculos decorativos, tarjetas flotantes de estadísticas, mascota flotante), incorporando un buscador reactivo por texto, píldoras de filtrado por categoría con insignias de conteo, cuadrícula uniforme de productos con badges de stock y enlaces a la ficha de detalle.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    TiendaCtrl[TiendaController@index] -->|Envía $productos, $categorias| ViewIndex[resources/views/tienda/index.blade.php]
    
    subgraph "Herencia y Estilos"
        ViewIndex -->|@extends| LayoutApp[layouts/app.blade.php]
        ViewIndex -->|@push('styles')| HomeCSS[public/css/home.css]
    end

    subgraph "Componentes Visuales"
        ViewIndex --> Hero[Hero Banner Kawaii + Mascota Flotante]
        ViewIndex --> Stats[Estadísticas: Total Productos, Categorías, Envíos]
        ViewIndex --> FilterBar[Formulario GET con buscador y píldoras de categoría]
        ViewIndex --> ProductGrid[Cuadrícula de Tarjetas .hpc]
        ViewIndex --> Pagination[Paginador Bootstrap 5: $productos->links()]
        ViewIndex --> CTA[Banner de Suscripción / Comunidad Kawaii]
    end

    subgraph "Salida / Navegación"
        ProductGrid -->|href=route('tienda.producto', $id)| DetalleView[tienda/detalle.blade.php]
    end
```

---

## 3. Código Fuente y Explicación Detallada de Secciones

### A. Sección Hero y Personalización por Usuario
```blade
<section class="hero" aria-label="Bienvenida">
    <div class="hero-overlay"></div>
    <div class="hero-circle hero-circle-1"></div>
    <div class="hero-circle hero-circle-2"></div>
    <div class="hero-circle hero-circle-3"></div>

    <div class="hero-content">
        <span class="hero-eyebrow">Colección Pastel & Accesorios</span>
        <h1 class="hero-title">
            @auth
                ¡Hola, {{ auth()->user()->nombre }}!<br>
            @else
                Bienvenida a<br>
            @endauth
            <span>Juno's Store</span>
        </h1>
        <p class="hero-subtitle">
            Papelería, accesorios y peluches con estética pastel.<br>
            Cada producto seleccionado con cuidado para ti.
        </p>
        <div class="hero-actions">
            <a href="#productos" class="btn-hero-primary">Ver catálogo</a>
            @guest
                <a href="{{ route('registro') }}" class="btn-hero-secondary">Crear cuenta</a>
            @else
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-hero-secondary">Mi Panel</a>
                @endif
            @endguest
        </div>
    </div>
    <div class="hero-image-wrap" aria-hidden="true">
        <img src="{{ asset('img/icon.png') }}" alt="">
    </div>
</section>
```
* **Diseño Conservado:** Los círculos decorativos con animación flotante (`.hero-circle-1`, `.hero-circle-2`, `.hero-circle-3`) y el degradado lila/rosa empolvado fueron preservados de la versión de inicio aprobada por el usuario.
* **Experiencia de Usuario Adaptativa:** Si el cliente ya inició sesión, el titular lo saluda personalmente (`¡Hola, Mariana!`) y los botones de acción cambian automáticamente.

---

### B. Barra de Filtros y Búsqueda Amigable
```blade
<div class="catalog-filter-bar">
    <form method="GET" action="{{ route('tienda.index') }}#productos" class="catalog-search-form">
        @if(request('categoria'))
            <input type="hidden" name="categoria" value="{{ request('categoria') }}">
        @endif
        <div class="search-input-box">
            <i class="fas fa-search search-icon"></i>
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Buscar peluches, papelería, accesorios pastel..." class="search-input">
            @if(request('q'))
                <a href="{{ route('tienda.index', request('categoria') ? ['categoria' => request('categoria')] : []) }}#productos" class="search-clear">
                    <i class="fas fa-times"></i>
                </a>
            @endif
            <button type="submit" class="btn-search">Buscar</button>
        </div>
    </form>

    <div class="category-pills">
        <a href="{{ route('tienda.index', request('q') ? ['q' => request('q')] : []) }}#productos"
           class="cat-pill {{ !request('categoria') ? 'active' : '' }}">
            <i class="fas fa-th-large me-1"></i> Todas
        </a>
        @foreach($categorias as $cat)
            <a href="{{ route('tienda.index', ['categoria' => $cat->id_categoria] + (request('q') ? ['q' => request('q')] : [])) }}#productos"
               class="cat-pill {{ request('categoria') == $cat->id_categoria ? 'active' : '' }}">
                {{ $cat->nombre }}
                @if($cat->productos_count > 0)
                    <span class="cat-pill-count">{{ $cat->productos_count }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
```
* **Preservación Cruzada de Filtros:** Si el usuario filtra por una categoría y luego busca `"capi bara"`, el campo `<input type="hidden" name="categoria">` mantiene la categoría activa y busca dentro de ella.
* **Píldoras Pastel Interactivas:** Cada enlace a una categoría concatena la búsqueda actual si existe: `+ (request('q') ? ['q' => request('q')] : [])`.
* **Ancla `#productos`:** Tras realizar una búsqueda o clic en una píldora, la página salta directamente a la sección de productos sin obligar al usuario a hacer scroll manual desde el banner superior.

---

### C. Tarjeta de Producto (`.hpc`) con Fallback de Imagen Seguro
```blade
<div class="home-products-grid">
    @foreach($productos as $producto)
    <article class="hpc">
        <div class="hpc-img">
            @php
                $imagenPrincipal = $producto->imagenes->where('es_principal', 1)->first() 
                                ?? $producto->imagenes->first();
            @endphp
            
            @if($imagenPrincipal)
                <img src="{{ asset($imagenPrincipal->url_limpia) }}"
                     alt="{{ $producto->nombre }}" loading="lazy"
                     onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                <div class="hpc-img-placeholder" style="display: none;">
                    <svg viewBox="0 0 24 24" stroke="#9b84cc" fill="none" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
            @else
                <div class="hpc-img-placeholder">
                    <svg viewBox="0 0 24 24" stroke="#9b84cc" fill="none" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="M21 15l-5-5L5 21"/>
                    </svg>
                </div>
            @endif

            @if($producto->stock_actual <= 0)
                <span class="hpc-badge hpc-badge-agotado">Agotado</span>
            @elseif($producto->stock_actual <= $producto->stock_minimo)
                <span class="hpc-badge">Popular</span>
            @endif
        </div>

        <div class="hpc-body">
            <span class="hpc-cat">{{ $producto->subcategoria->nombre ?? $producto->categoria->nombre }}</span>
            <p class="hpc-name">{{ $producto->nombre }}</p>
            <p class="hpc-price">${{ number_format($producto->precio, 2, ',', '.') }}</p>
            <div class="hpc-footer">
                <a href="{{ route('tienda.producto', $producto->id_producto) }}" class="btn-hpc">
                    Ver producto
                </a>
            </div>
        </div>
    </article>
    @endforeach
</div>
```
* **Fallback a Prueba de Fallos:** Si una imagen registrada en la base de datos no existe en el disco duro local, el evento de Javascript `onerror` oculta la etiqueta rota y enciende de inmediato un SVG vectorial kawaii con fondo pastel, previniendo iconos rotos en la interfaz.
* **Formato de Moneda Seguro:** `number_format($producto->precio, 2, ',', '.')` formatea los precios en notación estándar latinoamericana (`$50.000,00`).

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Cómo se implementa la paginación en esta vista?**
   * *Respuesta:* Mediante `{{ $productos->links() }}`. Gracias a la configuración `Paginator::useBootstrapFive()` en `AppServiceProvider.php` y los estilos en `kawaii.css`, genera botones numerados redondeados con degradados lilas.
2. **¿Cómo se maneja el estado vacío si una búsqueda no arroja resultados?**
   * *Respuesta:* Mediante la directiva `@if($productos->count() > 0) ... @else ... @endif`. Si no hay coincidencias, se renderiza la clase `.empty-state` con una ilustración SVG y un botón para restaurar todos los filtros.
3. **¿Por qué se usa `loading="lazy"` en las imágenes?**
   * *Respuesta:* Es una buena práctica de rendimiento web (Core Web Vitals). Indica al navegador que posponga la carga de imágenes hasta que el usuario haga scroll cerca de ellas, reduciendo el consumo de ancho de banda y acelerando el tiempo de carga inicial.
