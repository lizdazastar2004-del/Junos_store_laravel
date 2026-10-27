@extends('layouts.app')

@section('title', $producto->nombre . " - Juno's Store")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/home.css') }}">
<style>
/* ── Estilos específicos para Detalle de Producto ────────────── */
.detail-container {
    width: min(1180px, calc(100% - 2rem));
    margin: 0 auto;
}

.product-gallery-wrap {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.5rem;
    box-shadow: 0 12px 36px rgba(79, 59, 110, 0.08);
    border: 1px solid rgba(222, 213, 245, 0.5);
}

.main-image-container {
    position: relative;
    width: 100%;
    height: 440px;
    border-radius: 18px;
    overflow: hidden;
    background: linear-gradient(135deg, #f7f2fe, #fcf5f8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-image-container img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.35s ease;
}

.main-image-container:hover img {
    transform: scale(1.04);
}

.main-image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    color: #9b84cc;
}

.main-image-placeholder i {
    font-size: 4rem;
    opacity: 0.4;
}

.thumbnails-row {
    display: flex;
    gap: 0.75rem;
    margin-top: 1.25rem;
    overflow-x: auto;
    padding-bottom: 0.35rem;
}

.thumb-item {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
    border-radius: 14px;
    overflow: hidden;
    border: 2px solid #ede5f8;
    cursor: pointer;
    background: #faf8ff;
    padding: 3px;
    transition: all 0.2s ease;
}

.thumb-item:hover, .thumb-item.active {
    border-color: #8652ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(134, 82, 202, 0.25);
}

.thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}

.product-info-panel {
    background: #ffffff;
    border-radius: 24px;
    padding: 2.5rem;
    box-shadow: 0 12px 36px rgba(79, 59, 110, 0.08);
    border: 1px solid rgba(222, 213, 245, 0.5);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.detail-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

.detail-title {
    font-size: 2.1rem;
    font-weight: 800;
    color: #35234d;
    line-height: 1.2;
    margin-bottom: 1.25rem;
}

.detail-price-wrap {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #f1e9fa;
}

.detail-price {
    font-size: 2.3rem;
    font-weight: 800;
    color: #7c5cbf;
    letter-spacing: -0.02em;
}

.detail-desc {
    color: #695980;
    font-size: 1rem;
    line-height: 1.75;
    margin-bottom: 1.75rem;
    flex-grow: 1;
}

.stock-indicator-box {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    margin-bottom: 1.5rem;
}

.stock-box-in {
    background: #eafaf1;
    border: 1px solid rgba(75, 175, 144, 0.3);
}

.stock-box-out {
    background: #fde8ef;
    border: 1px solid rgba(232, 100, 140, 0.3);
}

.stock-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.stock-box-in .stock-icon-wrap {
    background: #d4f4e4;
    color: #1e7051;
}

.stock-box-out .stock-icon-wrap {
    background: #fcd5e2;
    color: #9e2a4a;
}

.sku-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.85rem;
    background: #faf7fd;
    border-radius: 10px;
    border: 1px solid #ede4f8;
    font-size: 0.82rem;
    color: #6d5b84;
    margin-bottom: 1.75rem;
}

.detail-actions {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    margin-top: auto;
}

.btn-detail-cart {
    width: 100%;
    background: linear-gradient(135deg, #b66be8 0%, #8652ca 100%);
    color: #fff !important;
    border: none;
    border-radius: 999px;
    padding: 1rem 1.5rem;
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    font-size: 1.05rem;
    cursor: pointer;
    box-shadow: 0 8px 22px rgba(134, 82, 202, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    text-decoration: none;
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
}

.btn-detail-cart:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(134, 82, 202, 0.45);
    background: linear-gradient(135deg, #a75adb 0%, #7542b8 100%);
}

.btn-detail-back {
    width: 100%;
    background: #f7f1fd;
    color: #6a4f91 !important;
    border: 1px solid #dccff0;
    border-radius: 999px;
    padding: 0.85rem 1.5rem;
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-detail-back:hover {
    background: #ebdffc;
    color: #4b2e75 !important;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .main-image-container { height: 320px; }
    .detail-title { font-size: 1.6rem; }
    .product-info-panel { padding: 1.75rem; margin-top: 1.5rem; }
}
</style>
@endpush

@section('content')
<div class="detail-container">

    <!-- Breadcrumb Kawaii -->
    <nav aria-label="Navegación secundaria">
        <ul class="breadcrumb-kawaii">
            <li>
                <a href="{{ route('tienda.index') }}">
                    <i class="fas fa-home me-1"></i> Inicio
                </a>
            </li>
            <li class="sep"><i class="fas fa-chevron-right"></i></li>
            <li>
                <a href="{{ route('tienda.index', ['categoria' => $producto->id_categoria]) }}#productos">
                    {{ $producto->categoria->nombre }}
                </a>
            </li>
            @if($producto->subcategoria)
                <li class="sep"><i class="fas fa-chevron-right"></i></li>
                <li>
                    <span>{{ $producto->subcategoria->nombre }}</span>
                </li>
            @endif
            <li class="sep"><i class="fas fa-chevron-right"></i></li>
            <li class="current">{{ $producto->nombre }}</li>
        </ul>
    </nav>

    <!-- Ficha del Producto -->
    <div class="row g-4 align-items-stretch">
        <!-- Galería de Imágenes -->
        <div class="col-lg-6">
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
                <div class="thumbnails-row" role="tablist" aria-label="Imágenes del producto">
                    @foreach($producto->imagenes as $idx => $imagen)
                    <div class="thumb-item {{ $imagenPrincipal && $imagen->id_imagen == $imagenPrincipal->id_imagen ? 'active' : '' }}"
                         onclick="changeMainImg('{{ asset($imagen->url_limpia ?? $imagen->ruta_imagen) }}', this)"
                         tabindex="0"
                         role="button"
                         title="Ver imagen {{ $idx + 1 }}">
                        <img src="{{ asset($imagen->url_limpia ?? $imagen->ruta_imagen) }}" alt="{{ $producto->nombre }} vista {{ $idx + 1 }}"
                             onerror="this.src='/img/dashboard_box_cat.jpg';">
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Información del Producto -->
        <div class="col-lg-6">
            <div class="product-info-panel">
                <!-- Badges -->
                <div class="detail-badges">
                    <span class="badge-kawaii badge-kawaii-purple">
                        <i class="fas fa-tag"></i> {{ $producto->categoria->nombre }}
                    </span>
                    @if($producto->subcategoria)
                    <span class="badge-kawaii badge-kawaii-pink">
                        <i class="fas fa-bookmark"></i> {{ $producto->subcategoria->nombre }}
                    </span>
                    @endif
                    @if($producto->marca)
                    <span class="badge-kawaii badge-kawaii-yellow">
                        <i class="fas fa-star"></i> {{ $producto->marca->nombre }}
                    </span>
                    @endif
                </div>

                <!-- Título -->
                <h1 class="detail-title">{{ $producto->nombre }}</h1>

                <!-- Precio -->
                <div class="detail-price-wrap">
                    <span class="detail-price">${{ number_format($producto->precio, 2, ',', '.') }}</span>
                    <span style="color: #9b84cc; font-size: 0.88rem; font-weight: 700;">IVA incluido</span>
                </div>

                <!-- Descripción -->
                <p class="detail-desc">
                    {{ $producto->descripcion ?: 'Producto seleccionado con el mayor cariño para tu colección kawaii.' }}
                </p>

                <!-- Estado del Stock -->
                @if($producto->stock_actual > 0)
                <div class="stock-indicator-box stock-box-in">
                    <div class="stock-icon-wrap">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <strong style="color: #1e7051; display: block; font-size: 0.95rem;">Disponible para envío</strong>
                        <span style="color: #4b8b70; font-size: 0.82rem; font-weight: 600;">
                            {{ $producto->stock_actual }} unidades disponibles en tienda
                        </span>
                    </div>
                </div>
                @else
                <div class="stock-indicator-box stock-box-out">
                    <div class="stock-icon-wrap">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div>
                        <strong style="color: #9e2a4a; display: block; font-size: 0.95rem;">Agotado temporalmente</strong>
                        <span style="color: #b54a67; font-size: 0.82rem; font-weight: 600;">
                            Pronto tendremos más unidades disponibles.
                        </span>
                    </div>
                </div>
                @endif

                <!-- SKU -->
                @if($producto->sku)
                <div class="sku-tag">
                    <i class="fas fa-barcode"></i>
                    <span>SKU: <strong style="font-family: monospace;">{{ $producto->sku }}</strong></span>
                </div>
                @endif

                <!-- Botones de Acción -->
                <div class="detail-actions">
                    @if($producto->stock_actual > 0)
                    <form method="POST" action="{{ route('carrito.agregar') }}" style="width: 100%;">
                        @csrf
                        <input type="hidden" name="producto_id" value="{{ $producto->id_producto }}">
                        <input type="hidden" name="cantidad" value="1">
                        <button type="submit" class="btn-detail-cart">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Agregar al Carrito</span>
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('tienda.index') }}#productos" class="btn-detail-back">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver al Catálogo</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         PRODUCTOS RELACIONADOS
    ════════════════════════════════════════════ -->
    @if($relacionados->count() > 0)
    <section style="margin-top: 5rem; margin-bottom: 2rem;" aria-labelledby="sec-relacionados">
        <div class="section-header">
            <div>
                <span class="section-label">Te podría encantar</span>
                <h2 class="section-title" id="sec-relacionados">Productos Relacionados</h2>
            </div>
            <a href="{{ route('tienda.index', ['categoria' => $producto->id_categoria]) }}#productos" class="section-link">
                Ver más en {{ $producto->categoria->nombre }} &rarr;
            </a>
        </div>

        <div class="home-products-grid">
            @foreach($relacionados as $rel)
            <article class="hpc">
                <div class="hpc-img">
                    @php
                        $relImg = $rel->imagenes->where('es_principal', 1)->first() ?? $rel->imagenes->first();
                    @endphp

                    @if($relImg)
                        <img src="{{ asset($relImg->ruta_imagen) }}" alt="{{ $rel->nombre }}" loading="lazy">
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

                    @if($rel->stock_actual <= 0)
                        <span class="hpc-badge hpc-badge-agotado">Agotado</span>
                    @endif
                </div>

                <div class="hpc-body">
                    <span class="hpc-cat">{{ $rel->categoria->nombre ?? 'Colección' }}</span>
                    <p class="hpc-name">{{ $rel->nombre }}</p>
                    <p class="hpc-price">${{ number_format($rel->precio, 2, ',', '.') }}</p>
                    <div class="hpc-footer">
                        <a href="{{ route('tienda.producto', $rel->id_producto) }}" class="btn-hpc">
                            Ver producto
                        </a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </section>
    @endif

</div>
@endsection

@push('scripts')
<script>
function changeMainImg(src, el) {
    const mainImg = document.getElementById('mainProductImg');
    if (mainImg) {
        mainImg.style.opacity = '0.3';
        mainImg.src = src;
        setTimeout(() => {
            mainImg.style.opacity = '1';
        }, 150);
    }
    document.querySelectorAll('.thumb-item').forEach(item => item.classList.remove('active'));
    if (el) el.classList.add('active');
}
</script>
@endpush

