@extends('layouts.app')

@section('title', 'Catálogo - Junos Store')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<!-- BASE_URL disponible para el JS del catálogo -->
<script>
    const BASE_URL = '{{ url('/') }}';
    const FILTER_URL = '{{ route('tienda.filtrar') }}';
</script>

<div class="catalogo-layout">

    <!-- ══ SIDEBAR FILTROS ══ -->
    <aside class="catalogo-sidebar" aria-label="Filtros del catálogo">
        <h2>🎛️ Filtros</h2>

        <!-- Búsqueda -->
        <div class="search-wrapper">
            <input
                type="search"
                id="busqueda-input"
                placeholder="Buscar productos..."
                autocomplete="off"
                aria-label="Buscar productos"
                value="{{ request('q') }}"
            >
        </div>
        <p class="busqueda-hint" id="busqueda-hint">Ingresa al menos 3 caracteres.</p>

        <!-- Subcategorías agrupadas por categoría -->
        <p class="filtros-titulo">Subcategorías</p>

        @if($categorias->isEmpty())
            <p class="sin-filtros">No hay categorías disponibles.</p>
        @else
            @foreach($categorias as $cat)
                @php
                    // Solo mostrar categorías que tengan subcategorías activas
                    $subcats = $cat->subcategorias;
                @endphp
                
                @if($subcats->count() > 0)
                <div class="filtros-grupo">
                    <strong>{{ $cat->nombre }}</strong>
                    @foreach($subcats as $sub)
                        <div class="filtro-item">
                            <input
                                type="checkbox"
                                class="filtro-sub"
                                id="sub-{{ $sub->id_subcategoria }}"
                                value="{{ $sub->id_subcategoria }}"
                                aria-label="{{ $sub->nombre }}"
                            >
                            <label for="sub-{{ $sub->id_subcategoria }}">
                                {{ $sub->nombre }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @endif
            @endforeach
        @endif

        <!-- Limpiar filtros -->
        <div class="limpiar-wrapper">
            <button id="btn-limpiar" class="btn-secondary btn-sm" type="button">
                🗑️ Limpiar filtros
            </button>
        </div>
    </aside>

    <!-- ══ ÁREA DE PRODUCTOS ══ -->
    <section class="catalogo-area" aria-label="Listado de productos">
        <div class="catalogo-area-header">
            <h1>✨ Catálogo</h1>
            <p style="font-size:.9rem;color:var(--texto-claro);">
                <span id="productos-count">{{ $productos->count() }}</span> producto{{ $productos->count() !== 1 ? 's' : '' }}
            </p>
        </div>

        <!-- Mensajes de estado -->
        <div class="catalogo-mensajes">
            <div id="msg-sin-resultados" style="display:none;" class="alert alert-info">
                No se encontraron productos para los filtros seleccionados.
            </div>
            <div id="msg-busqueda-corta" style="display:none;" class="alert alert-warning">
                Ingresa al menos 3 caracteres para buscar.
            </div>
        </div>

        <!-- Grid de productos (renderizado inicial con Blade) -->
        <div id="productos-grid" class="products-grid">
            @if($productos->isEmpty())
                <div class="alert alert-info" style="grid-column:1/-1;">
                    Aún no hay productos disponibles en el catálogo.
                </div>
            @else
                @foreach($productos as $producto)
                    <div class="product-card">
                        @php
                            $imagenPrincipal = $producto->imagenes->where('es_principal', 1)->first() 
                                            ?? $producto->imagenes->first();
                        @endphp
                        
                        @if($imagenPrincipal)
                            <img
                                src="{{ asset($imagenPrincipal->ruta_imagen) }}"
                                alt="{{ $producto->nombre }}"
                                loading="lazy"
                            >
                        @else
                            <div style="height:200px;display:flex;align-items:center;justify-content:center;background:var(--lila);font-size:3rem;" aria-hidden="true">📷</div>
                        @endif

                        <div class="product-card-body">
                            <p class="product-name">{{ $producto->nombre }}</p>
                            <p class="price">${{ number_format($producto->precio, 2, ',', '.') }}</p>
                            <p style="font-size:.82rem;color:var(--texto-claro)">
                                {{ $producto->subcategoria->nombre ?? $producto->categoria->nombre }}
                            </p>
                            @if($producto->stock_actual <= 0)
                                <span class="badge badge-cancelado">Agotado</span>
                            @endif
                            <div class="product-actions">
                                <a href="{{ route('tienda.producto', $producto->id_producto) }}" class="btn-primary btn-sm">
                                    Ver Producto
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>

</div>

@endsection

@push('scripts')
<script src="{{ asset('js/catalogo.js') }}"></script>
@endpush
