@extends('layouts.app')

@section('title', "Juno's Store")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endpush

@section('content')
<!-- ════════════════════════════════════════════
     HERO
════════════════════════════════════════════ -->
<section class="hero" aria-label="Bienvenida">
    <div class="hero-overlay"></div>

    <!-- Círculos decorativos -->
    <div class="hero-circle hero-circle-1"></div>
    <div class="hero-circle hero-circle-2"></div>
    <div class="hero-circle hero-circle-3"></div>

    <!-- Contenido -->
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
            <a href="{{ route('tienda.index') }}" class="btn-hero-primary">
                Ver catálogo
            </a>
            @guest
                <a href="{{ route('registro') }}" class="btn-hero-secondary">
                    Crear cuenta
                </a>
            @else
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-hero-secondary">
                        Mi Panel
                    </a>
                @else
                    <a href="#productos" class="btn-hero-secondary">
                        Explorar
                    </a>
                @endif
            @endguest
        </div>
    </div>

    <!-- Imagen flotante -->
    <div class="hero-image-wrap" aria-hidden="true">
        <img src="{{ asset('img/icon.png') }}" alt="">
    </div>
</section>

<!-- ════════════════════════════════════════════
     STATS
════════════════════════════════════════════ -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-number">{{ $productos->total() > 0 ? $productos->total() . '+' : '—' }}</div>
        <div class="stat-label">Productos destacados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">{{ $categorias->count() > 0 ? $categorias->count() : '—' }}</div>
        <div class="stat-label">Categorías</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">100%</div>
        <div class="stat-label">Envíos seguros</div>
    </div>
</div>

<!-- ════════════════════════════════════════════
     PRODUCTOS DESTACADOS
════════════════════════════════════════════ -->
<section style="margin-bottom:4rem;" aria-labelledby="sec-destacados" id="productos">
    <div class="section-header">
        <div>
            <span class="section-label">Lo más vendido &amp; Colecciones</span>
            <h2 class="section-title" id="sec-destacados">Catálogo de Productos</h2>
        </div>
        @if(request()->filled('q') || request()->filled('categoria'))
            <a href="{{ route('tienda.index') }}#productos" class="section-link">Ver todo el catálogo &rarr;</a>
        @else
            <span class="section-link">{{ $productos->total() }} productos disponibles</span>
        @endif
    </div>

    <!-- Barra de búsqueda y filtros -->
    <div class="catalog-filter-bar">
        <form method="GET" action="{{ route('tienda.index') }}#productos" class="catalog-search-form">
            @if(request('categoria'))
                <input type="hidden" name="categoria" value="{{ request('categoria') }}">
            @endif
            <div class="search-input-box">
                <i class="fas fa-search search-icon"></i>
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Buscar peluches, papelería, accesorios pastel..."
                       class="search-input">
                @if(request('q'))
                    <a href="{{ route('tienda.index', request('categoria') ? ['categoria' => request('categoria')] : []) }}#productos" class="search-clear" title="Borrar búsqueda">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
                <button type="submit" class="btn-search">Buscar</button>
            </div>
        </form>

        @if($categorias->count() > 0)
        <div class="category-pills">
            <a href="{{ route('tienda.index', request('q') ? ['q' => request('q')] : []) }}#productos"
               class="cat-pill {{ !request('categoria') ? 'active' : '' }}">
                <i class="fas fa-th-large me-1"></i> Todas
            </a>
            @foreach($categorias as $cat)
                <a href="{{ route('tienda.index', ['categoria' => $cat->id_categoria] + (request('q') ? ['q' => request('q')] : [])) }}#productos"
                   class="cat-pill {{ request('categoria') == $cat->id_categoria ? 'active' : '' }}">
                    {{ $cat->nombre }}
                    @if(isset($cat->productos_count) && $cat->productos_count > 0)
                        <span class="cat-pill-count">{{ $cat->productos_count }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        @endif

        @if(request()->filled('q') || request()->filled('categoria'))
        <div class="active-filter-notice">
            <div>
                <i class="fas fa-filter me-1"></i>
                <span>Filtro activo:</span>
                @if(request()->filled('q'))
                    <strong>"{{ request('q') }}"</strong>
                @endif
                @if(request()->filled('categoria'))
                    @php $catActual = $categorias->firstWhere('id_categoria', request('categoria')); @endphp
                    @if($catActual)
                        <span>en categoría <strong>{{ $catActual->nombre }}</strong></span>
                    @endif
                @endif
            </div>
            <a href="{{ route('tienda.index') }}#productos">
                <i class="fas fa-times me-1"></i> Quitar filtros
            </a>
        </div>
        @endif
    </div>

    @if($productos->count() > 0)
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
                        <svg viewBox="0 0 24 24" stroke="#9b84cc" fill="none" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="M21 15l-5-5L5 21"/>
                        </svg>
                    </div>
                @else
                    <div class="hpc-img-placeholder">
                        <svg viewBox="0 0 24 24" stroke="#9b84cc" fill="none" stroke-width="1.5"
                             stroke-linecap="round" stroke-linejoin="round">
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

    <!-- Paginación -->
    <div style="display: flex; justify-content: center; margin-top: 2rem;">
        {{ $productos->links() }}
    </div>
    @else
    <div class="empty-state">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <path d="M8 21h8M12 17v4"/>
        </svg>
        <p>No se encontraron productos con los criterios seleccionados.</p>
        <a href="{{ route('tienda.index') }}#productos" class="btn-kawaii-secondary btn-sm mt-3" style="display: inline-flex;">
            Ver todos los productos
        </a>
    </div>
    @endif
</section>

<!-- ════════════════════════════════════════════
     CATEGORÍAS
════════════════════════════════════════════ -->
@if($categorias->count() > 0)
<section class="categories-section" aria-labelledby="sec-categorias" id="categorias">
    <div class="section-header">
        <div>
            <span class="section-label">Navega por sección</span>
            <h2 class="section-title" id="sec-categorias">Explorar Categorías</h2>
        </div>
    </div>

    <div class="cat-grid">
        @foreach($categorias as $cat)
        <a href="{{ route('tienda.index', ['categoria' => $cat->id_categoria]) }}" 
           class="cat-card" 
           title="{{ $cat->nombre }}">
            <div class="cat-icon">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                </svg>
            </div>
            <span class="cat-name">{{ $cat->nombre }}</span>
        </a>
        @endforeach
    </div>
</section>
@endif

<!-- ════════════════════════════════════════════
     BANNER CTA
════════════════════════════════════════════ -->
<div class="cta-banner" role="complementary" aria-label="Llamada a la acción">
    <div class="cta-banner-deco" aria-hidden="true"></div>
    <div class="cta-banner-deco2" aria-hidden="true"></div>

    <div class="cta-text">
        <h3>Crea tu cuenta y comienza a explorar</h3>
        <p>Accede a descuentos exclusivos, historial de pedidos y mucho más con tu cuenta gratuita.</p>
    </div>

    @guest
        <a href="{{ route('registro') }}" class="btn-cta">Registrarse gratis</a>
    @else
        <a href="{{ route('tienda.index') }}" class="btn-cta">Ir al catálogo</a>
    @endguest
</div>

<!-- Intersection Observer para animar cards al hacer scroll -->
<script>
(function () {
    if (!('IntersectionObserver' in window)) return;
    const items = document.querySelectorAll('.hpc, .cat-card, .stat-card');
    const io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    items.forEach(function (el) {
        el.style.animationPlayState = 'paused';
        io.observe(el);
    });
})();
</script>
@endsection
