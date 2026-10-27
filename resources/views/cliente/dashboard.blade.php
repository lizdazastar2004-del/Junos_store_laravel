@extends('layouts.app')

@section('title', "Mi Espacio Kawaii - " . $usuario->nombre . " | Juno's Store")

@push('styles')
<style>
/* ══════════════════════════════════════════════════════════════════
   ESTILOS DEDICADOS: PORTAL DEL CLIENTE (KAWAII EXPERIENCE)
   Diseñado específicamente para compradores, con tonos suaves,
   tarjetas acogedoras, micro-animaciones e iconos tiernos.
   ══════════════════════════════════════════════════════════════════ */

.client-portal-container {
    max-width: 1200px;
    margin: 1.5rem auto 4rem auto;
    padding: 0 1rem;
}

/* ── Hero de Bienvenida del Cliente ────────────────────────────── */
.client-hero-card {
    background: linear-gradient(135deg, #f7f2fe 0%, #fff0f5 50%, #f0f7ff 100%);
    border-radius: 28px;
    padding: 2.2rem;
    box-shadow: 0 12px 35px rgba(124, 92, 191, 0.08);
    border: 1px solid rgba(222, 213, 245, 0.7);
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.client-hero-card::after {
    content: "🐾";
    position: absolute;
    right: -15px;
    bottom: -20px;
    font-size: 8rem;
    opacity: 0.05;
    pointer-events: none;
}

.client-avatar-badge {
    width: 78px;
    height: 78px;
    border-radius: 24px;
    background: linear-gradient(135deg, #7c5cbf, #e8648c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 2.2rem;
    box-shadow: 0 8px 20px rgba(124, 92, 191, 0.28);
    flex-shrink: 0;
}

.client-welcome-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #3e265c;
    margin: 0 0 0.35rem 0;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.client-role-tag {
    background: #ffffff;
    color: #7c5cbf;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    border: 1px solid #e5dcf7;
    box-shadow: 0 2px 8px rgba(124, 92, 191, 0.06);
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* ── Tarjetas de Estadísticas del Cliente ──────────────────────── */
.client-stat-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 1.4rem 1.25rem;
    border: 1px solid #ede4f8;
    box-shadow: 0 6px 20px rgba(96, 69, 143, 0.04);
    display: flex;
    align-items: center;
    gap: 1.1rem;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
}

.client-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(124, 92, 191, 0.12);
    border-color: #d8c8f5;
}

.stat-icon-wrapper {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.stat-icon-purple { background: #f3ebff; color: #7c5cbf; }
.stat-icon-pink   { background: #ffe9f1; color: #e84393; }
.stat-icon-green  { background: #e8f8f0; color: #27ae60; }
.stat-icon-orange { background: #fff3e6; color: #f39c12; }

.stat-value {
    font-size: 1.55rem;
    font-weight: 800;
    color: #382451;
    line-height: 1.1;
    margin: 0;
}

.stat-label {
    font-size: 0.82rem;
    color: #796693;
    font-weight: 600;
    margin: 0;
}

/* ── Navegación por Pestañas Kawaii ────────────────────────────── */
.client-nav-tabs {
    display: flex;
    gap: 0.6rem;
    background: #ffffff;
    padding: 0.5rem;
    border-radius: 18px;
    border: 1px solid #ede4f8;
    box-shadow: 0 4px 15px rgba(124, 92, 191, 0.04);
    margin-bottom: 2rem;
    overflow-x: auto;
}

.client-tab-btn {
    border: none;
    background: transparent;
    padding: 0.75rem 1.4rem;
    border-radius: 14px;
    font-size: 0.92rem;
    font-weight: 700;
    color: #695980;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.25s ease;
}

.client-tab-btn:hover {
    color: #7c5cbf;
    background: #f7f2fe;
}

.client-tab-btn.active {
    background: linear-gradient(135deg, #7c5cbf, #8b68d6);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(124, 92, 191, 0.25);
}

/* ── Paneles de Contenido ──────────────────────────────────────── */
.client-section-panel {
    display: none;
    animation: fadeIn 0.3s ease;
}

.client-section-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

.kawaii-box {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.8rem;
    border: 1px solid #ede4f8;
    box-shadow: 0 8px 24px rgba(79, 59, 110, 0.05);
    margin-bottom: 2rem;
}

.kawaii-box-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 1.25rem;
    margin-bottom: 1.4rem;
    border-bottom: 1px solid #f3ecfb;
}

.kawaii-box-title {
    font-size: 1.2rem;
    font-weight: 800;
    color: #382451;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ── Tarjetas de Pedidos ───────────────────────────────────────── */
.order-card-kawaii {
    background: #faf7fd;
    border: 1px solid #ede4f8;
    border-radius: 18px;
    padding: 1.25rem 1.4rem;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}

.order-card-kawaii:hover {
    border-color: #d1bdf2;
    background: #ffffff;
    box-shadow: 0 6px 18px rgba(124, 92, 191, 0.08);
}

.order-badge {
    padding: 5px 12px;
    border-radius: 14px;
    font-size: 0.78rem;
    font-weight: 800;
    text-transform: capitalize;
}

.order-badge-pendiente      { background: #fff5e6; color: #b7791f; }
.order-badge-confirmado     { background: #e8f4fd; color: #1e88e5; }
.order-badge-en_preparacion { background: #f3e8fd; color: #8e44ad; }
.order-badge-enviado        { background: #e3f2fd; color: #0984e3; }
.order-badge-entregado      { background: #e8f8f0; color: #27ae60; }
.order-badge-cancelado      { background: #ffebee; color: #c0392b; }

/* ── Tarjetas de Direcciones ───────────────────────────────────── */
.address-card-kawaii {
    background: #ffffff;
    border: 1.5px solid #ede4f8;
    border-radius: 18px;
    padding: 1.4rem;
    position: relative;
    transition: all 0.2s ease;
    height: 100%;
}

.address-card-kawaii.is-main {
    border-color: #7c5cbf;
    background: #faf6fe;
}

.address-card-kawaii:hover {
    box-shadow: 0 8px 22px rgba(124, 92, 191, 0.1);
}

/* ── Botones Kawaii ────────────────────────────────────────────── */
.btn-kawaii-action {
    background: linear-gradient(135deg, #7c5cbf, #8b68d6);
    color: #ffffff !important;
    border: none;
    padding: 0.65rem 1.4rem;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-kawaii-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(124, 92, 191, 0.28);
}

.btn-kawaii-light {
    background: #f3ebff;
    color: #7c5cbf !important;
    border: 1px solid #ebdffc;
    padding: 0.6rem 1.2rem;
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.88rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-kawaii-light:hover {
    background: #ebdffc;
    color: #5d3f9b !important;
}

.form-kawaii-control {
    border: 1.5px solid #ede4f8;
    border-radius: 14px;
    padding: 0.75rem 1rem;
    font-size: 0.92rem;
    transition: all 0.2s;
    background: #faf7fd;
    width: 100%;
}

.form-kawaii-control:focus {
    outline: none;
    border-color: #7c5cbf;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(124, 92, 191, 0.12);
}
</style>
@endpush

@section('content')
<div class="client-portal-container">

    <!-- Mensajes de Notificación Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" 
             style="background: #eafaf1; color: #1e7051; border: 1px solid #b8ebd0; border-radius: 16px; font-weight: 700;">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert"
             style="background: #fde8ef; color: #9e2a4a; border: 1px solid #f7c3d2; border-radius: 16px; font-weight: 700;">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') ?? 'Por favor corrige los campos señalados en el formulario.' }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════
         1. HERO DE BIENVENIDA DEL CLIENTE
         ══════════════════════════════════════════════════════════════ -->
    <div class="client-hero-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="client-avatar-badge">
                    <i class="fas fa-paw"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        @if($usuario->isAdmin())
                            <span class="client-role-tag" style="background: #ede4f8; color: #7c5cbf; border: 1px solid #d4c2ea;">
                                <i class="fas fa-crown text-warning"></i> Administrador
                            </span>
                        @else
                            <span class="client-role-tag">
                                <i class="fas fa-heart text-danger"></i> Cliente Miembro
                            </span>
                        @endif
                        <span style="font-size: 0.8rem; color: #8e7b9e;">
                            Miembro desde: {{ $usuario->creado_en ? \Carbon\Carbon::parse($usuario->creado_en)->format('M Y') : '2026' }}
                        </span>
                    </div>
                    <h1 class="client-welcome-title">
                        {{ $saludo }}, {{ $usuario->nombre }} 🌸
                    </h1>
                    <p style="color: #695980; margin: 0; font-size: 0.95rem;">
                        Bienvenido a tu rincón personal en Juno's Store. Aquí puedes rastrear tus compras, guardar tus direcciones y cuidar tus colecciones.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                @if($usuario->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn-kawaii-outline" style="background: #ffffff; border-color: #c4b5e0; color: #7c5cbf;">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Panel Admin</span>
                    </a>
                @endif
                <a href="{{ route('tienda.index') }}#productos" class="btn-kawaii-action">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Ir a la Tienda</span>
                </a>
                <a href="{{ route('carrito.index') }}" class="btn-kawaii-light">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Ver Carrito</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         2. TARJETAS DE ESTADÍSTICAS RÁPIDAS
         ══════════════════════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="client-stat-card">
                <div class="stat-icon-wrapper stat-icon-purple">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h3 class="stat-value">{{ $totalPedidos }}</h3>
                    <p class="stat-label">Pedidos Totales</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="client-stat-card">
                <div class="stat-icon-wrapper stat-icon-orange">
                    <i class="fas fa-truck-loading"></i>
                </div>
                <div>
                    <h3 class="stat-value">{{ $pedidosActivos }}</h3>
                    <p class="stat-label">En Camino / Proceso</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="client-stat-card">
                <div class="stat-icon-wrapper stat-icon-green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="stat-value">{{ $pedidosEntregados }}</h3>
                    <p class="stat-label">Entregados con Éxito</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="client-stat-card">
                <div class="stat-icon-wrapper stat-icon-pink">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <h3 class="stat-value">{{ $direcciones->count() }}</h3>
                    <p class="stat-label">Direcciones Guardadas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         3. NAVEGACIÓN POR PESTAÑAS
         ══════════════════════════════════════════════════════════════ -->
    <div class="client-nav-tabs" role="tablist">
        <button type="button" class="client-tab-btn active" onclick="switchClientTab('resumen', this)">
            <i class="fas fa-home"></i> Resumen
        </button>
        <button type="button" class="client-tab-btn" onclick="switchClientTab('pedidos', this)">
            <i class="fas fa-box-open"></i> Mis Pedidos ({{ $totalPedidos }})
        </button>
        <button type="button" class="client-tab-btn" onclick="switchClientTab('direcciones', this)">
            <i class="fas fa-map-marked-alt"></i> Mis Direcciones ({{ $direcciones->count() }})
        </button>
        <button type="button" class="client-tab-btn" onclick="switchClientTab('perfil', this)">
            <i class="fas fa-user-edit"></i> Mi Perfil &amp; Datos
        </button>
        <button type="button" class="client-tab-btn" onclick="switchClientTab('seguridad', this)">
            <i class="fas fa-shield-alt"></i> Seguridad &amp; Clave
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         PESTAÑA 1: RESUMEN / MI ESPACIO
         ══════════════════════════════════════════════════════════════ -->
    <div id="tab-resumen" class="client-section-panel active">
        <div class="row g-4">
            
            <!-- Carrito Activo en Vivo si tiene productos -->
            @if($carrito && $carrito->detalles->count() > 0)
            <div class="col-12">
                <div class="kawaii-box" style="background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%); border-color: #e5dcf7;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width: 36px; height: 36px; border-radius: 10px; background: #ffe9f1; display: flex; align-items: center; justify-content: center; color: #e84393;">
                                <i class="fas fa-shopping-cart"></i>
                            </span>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #382451; margin: 0;">Tienes artículos esperando en tu Carrito</h3>
                                <span style="font-size: 0.82rem; color: #796693;">{{ $carrito->contarItems() }} artículos seleccionados con amor</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <span style="font-size: 1.3rem; font-weight: 800; color: #7c5cbf;">
                                ${{ number_format($carrito->calcularSubtotal(), 2, ',', '.') }} COP
                            </span>
                            <a href="{{ route('checkout') }}" class="btn-kawaii-action">
                                <i class="fas fa-credit-card"></i> Pagar Ahora
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Último Pedido Activo / Historial Reciente -->
            <div class="col-lg-8">
                <div class="kawaii-box">
                    <div class="kawaii-box-header">
                        <h2 class="kawaii-box-title">
                            <i class="fas fa-history" style="color: #7c5cbf;"></i> Última Actividad de Compras
                        </h2>
                        <button type="button" class="btn-kawaii-light" onclick="switchClientTab('pedidos', document.querySelectorAll('.client-tab-btn')[1])">
                            Ver todos &rarr;
                        </button>
                    </div>

                    @forelse($pedidos->take(3) as $pedido)
                    <div class="order-card-kawaii">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div>
                                <span style="font-size: 0.82rem; color: #796693;">Pedido</span>
                                <strong style="font-size: 1.05rem; color: #382451; margin-left: 4px;">#{{ $pedido->id_pedido }}</strong>
                                <span style="font-size: 0.8rem; color: #9b84cc; margin-left: 8px;">
                                    {{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y h:i A') : '' }}
                                </span>
                            </div>

                            <span class="order-badge order-badge-{{ $pedido->estado }}">
                                {{ $pedido->estado_formateado }}
                            </span>
                        </div>

                        <!-- Galería en miniatura de productos en este pedido -->
                        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                            @foreach($pedido->detalles->take(4) as $det)
                                @php
                                    $imgP = $det->producto->imagenes->first() ?? null;
                                @endphp
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: #ffffff; border: 1px solid #ede4f8; overflow: hidden; display: flex; align-items: center; justify-content: center;" title="{{ $det->producto->nombre ?? 'Producto' }}">
                                    @if($imgP)
                                        <img src="{{ asset($imgP->url_limpia ?? $imgP->ruta_imagen) }}" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                    @else
                                        <i class="fas fa-paw" style="color: #c4b5e0;"></i>
                                    @endif
                                </div>
                            @endforeach
                            @if($pedido->detalles->count() > 4)
                                <span style="font-size: 0.8rem; color: #796693; font-weight: 700;">+{{ $pedido->detalles->count() - 4 }} más</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2" style="border-top: 1px dashed #ede4f8;">
                            <div>
                                <span style="font-size: 0.82rem; color: #796693;">Total pagado:</span>
                                <strong style="font-size: 1.15rem; color: #7c5cbf; margin-left: 4px;">
                                    ${{ number_format($pedido->total, 2, ',', '.') }}
                                </strong>
                            </div>

                            <a href="{{ route('pedidos.detalle', $pedido->id_pedido) }}" class="btn-kawaii-light" style="padding: 4px 12px; font-size: 0.82rem;">
                                <i class="fas fa-eye"></i> Ver Detalle &amp; Factura
                            </a>
                        </div>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 3rem 1rem;">
                        <div style="font-size: 3.5rem; color: #d8caec; margin-bottom: 0.8rem;">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h4 style="color: #4b3664; font-weight: 800;">Aún no has realizado pedidos</h4>
                        <p style="color: #796693; font-size: 0.9rem; max-width: 400px; margin: 0 auto 1.5rem auto;">
                            Explora nuestro catálogo kawaii y descubre papelería, accesorios y peluches para llenar de ternura tu día.
                        </p>
                        <a href="{{ route('tienda.index') }}#productos" class="btn-kawaii-action">
                            <i class="fas fa-heart"></i> Explorar Catálogo
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Columna Lateral: Tarjeta de Datos y Dirección Principal -->
            <div class="col-lg-4">
                <!-- Tarjeta Mi Perfil Resumen -->
                <div class="kawaii-box mb-4">
                    <div class="kawaii-box-header">
                        <h3 class="kawaii-box-title" style="font-size: 1.05rem;">
                            <i class="fas fa-user-circle" style="color: #7c5cbf;"></i> Mis Datos
                        </h3>
                        <button type="button" class="btn-kawaii-light" style="padding: 2px 8px; font-size: 0.78rem;" onclick="switchClientTab('perfil', document.querySelectorAll('.client-tab-btn')[3])">
                            Editar
                        </button>
                    </div>

                    <div style="font-size: 0.92rem;">
                        <p style="margin-bottom: 0.6rem;">
                            <strong style="color: #382451;">Nombre:</strong>
                            <span style="color: #695980;">{{ $usuario->nombre_completo }}</span>
                        </p>
                        <p style="margin-bottom: 0.6rem;">
                            <strong style="color: #382451;">Correo:</strong>
                            <span style="color: #695980;">{{ $usuario->email }}</span>
                        </p>
                        <p style="margin-bottom: 0;">
                            <strong style="color: #382451;">Teléfono:</strong>
                            <span style="color: #695980;">{{ $usuario->telefono ?: 'No registrado' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Dirección Principal -->
                <div class="kawaii-box">
                    <div class="kawaii-box-header">
                        <h3 class="kawaii-box-title" style="font-size: 1.05rem;">
                            <i class="fas fa-map-marker-alt" style="color: #e84393;"></i> Envío Predeterminado
                        </h3>
                        <button type="button" class="btn-kawaii-light" style="padding: 2px 8px; font-size: 0.78rem;" onclick="switchClientTab('direcciones', document.querySelectorAll('.client-tab-btn')[2])">
                            Gestionar
                        </button>
                    </div>

                    @php
                        $dirPrincipal = $direcciones->where('principal', 1)->first() ?? $direcciones->first();
                    @endphp

                    @if($dirPrincipal)
                    <div style="font-size: 0.88rem; color: #695980; line-height: 1.6;">
                        <strong style="color: #382451; display: block; font-size: 0.95rem;">{{ $dirPrincipal->calle }}</strong>
                        <span>{{ $dirPrincipal->ciudad }}, {{ $dirPrincipal->departamento }}</span>
                        @if($dirPrincipal->codigo_postal)
                            <span style="display: block;">CP: {{ $dirPrincipal->codigo_postal }}</span>
                        @endif
                        @if($dirPrincipal->referencia)
                            <span style="display: block; font-style: italic; color: #9b84cc; margin-top: 4px;">"{{ $dirPrincipal->referencia }}"</span>
                        @endif
                    </div>
                    @else
                    <div style="text-align: center; padding: 1.5rem 0.5rem; color: #796693;">
                        <i class="fas fa-map-pin" style="font-size: 2rem; color: #d8caec; margin-bottom: 0.5rem;"></i>
                        <p style="font-size: 0.85rem; margin-bottom: 1rem;">No tienes una dirección de entrega guardada.</p>
                        <button type="button" class="btn-kawaii-action" style="padding: 4px 12px; font-size: 0.8rem;" onclick="switchClientTab('direcciones', document.querySelectorAll('.client-tab-btn')[2])">
                            + Agregar Dirección
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         PESTAÑA 2: HISTORIAL COMPLETO DE PEDIDOS
         ══════════════════════════════════════════════════════════════ -->
    <div id="tab-pedidos" class="client-section-panel">
        <div class="kawaii-box">
            <div class="kawaii-box-header">
                <h2 class="kawaii-box-title">
                    <i class="fas fa-box-open" style="color: #7c5cbf;"></i> Historial de Todos mis Pedidos
                </h2>
                <span style="font-size: 0.85rem; color: #796693;">Total: {{ $pedidos->count() }} órdenes</span>
            </div>

            @forelse($pedidos as $pedido)
            <div class="order-card-kawaii">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <strong style="font-size: 1.15rem; color: #382451;">Pedido #{{ $pedido->id_pedido }}</strong>
                        <span style="font-size: 0.82rem; color: #796693; margin-left: 8px;">
                            Fecha: {{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y h:i A') : 'Reciente' }}
                        </span>
                    </div>

                    <span class="order-badge order-badge-{{ $pedido->estado }}">
                        <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i> {{ $pedido->estado_formateado }}
                    </span>
                </div>

                <!-- Lista de Productos en la orden -->
                <div class="table-responsive mb-3">
                    <table class="table table-borderless" style="margin: 0; font-size: 0.88rem;">
                        <tbody>
                            @foreach($pedido->detalles as $det)
                            @php
                                $img = $det->producto->imagenes->first() ?? null;
                            @endphp
                            <tr style="border-bottom: 1px solid #f3ecfb;">
                                <td style="width: 50px; padding: 6px 0;">
                                    <div style="width: 44px; height: 44px; border-radius: 10px; background: #ffffff; border: 1px solid #ede4f8; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                        @if($img)
                                            <img src="{{ asset($img->url_limpia ?? $img->ruta_imagen) }}" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                        @else
                                            <i class="fas fa-paw" style="color: #c4b5e0;"></i>
                                        @endif
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <strong style="color: #382451;">{{ $det->producto->nombre ?? 'Producto no disponible' }}</strong>
                                    <span style="display: block; font-size: 0.78rem; color: #796693;">
                                        Cantidad: {{ $det->cantidad }} × ${{ number_format($det->precio_unitario, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td style="vertical-align: middle; text-align: right; font-weight: 700; color: #7c5cbf;">
                                    ${{ number_format($det->subtotal, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2">
                    <div style="font-size: 0.85rem; color: #796693;">
                        <span>Método: <strong>{{ $pedido->pago->metodoPago->nombre ?? 'Transferencia / PSE' }}</strong></span>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <span style="font-size: 1.15rem; font-weight: 800; color: #7c5cbf;">
                            Total: ${{ number_format($pedido->total, 2, ',', '.') }}
                        </span>

                        <a href="{{ route('pedidos.detalle', $pedido->id_pedido) }}" class="btn-kawaii-action" style="padding: 6px 14px; font-size: 0.85rem;">
                            <i class="fas fa-receipt"></i> Ver Detalle de Orden
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 3rem;">
                <p style="color: #796693;">No hay pedidos registrados.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         PESTAÑA 3: MIS DIRECCIONES DE ENTREGA
         ══════════════════════════════════════════════════════════════ -->
    <div id="tab-direcciones" class="client-section-panel">
        <div class="row g-4">
            <!-- Listado de Direcciones Existentes -->
            <div class="col-lg-7">
                <div class="kawaii-box">
                    <div class="kawaii-box-header">
                        <h2 class="kawaii-box-title">
                            <i class="fas fa-map-marked-alt" style="color: #e84393;"></i> Libreta de Direcciones
                        </h2>
                    </div>

                    <div class="row g-3">
                        @forelse($direcciones as $dir)
                        <div class="col-md-6">
                            <div class="address-card-kawaii {{ $dir->principal ? 'is-main' : '' }}">
                                @if($dir->principal)
                                    <span style="position: absolute; top: 12px; right: 12px; background: #e8f8f0; color: #27ae60; font-size: 0.72rem; font-weight: 800; padding: 2px 8px; border-radius: 10px;">
                                        <i class="fas fa-check"></i> Principal
                                    </span>
                                @endif

                                <div style="margin-bottom: 0.8rem;">
                                    <strong style="font-size: 1rem; color: #382451; display: block;">{{ $dir->calle }}</strong>
                                    <span style="font-size: 0.85rem; color: #796693;">{{ $dir->ciudad }}, {{ $dir->departamento }}</span>
                                    @if($dir->codigo_postal)
                                        <span style="display: block; font-size: 0.8rem; color: #9b84cc;">Código Postal: {{ $dir->codigo_postal }}</span>
                                    @endif
                                    @if($dir->referencia)
                                        <p style="font-size: 0.8rem; color: #8e7b9e; font-style: italic; margin-top: 6px;">
                                            Ref: {{ $dir->referencia }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-2" style="border-top: 1px dashed #ede4f8;">
                                    <form method="POST" action="{{ route('cliente.direccion.eliminar', $dir->id_direccion) }}" onsubmit="return confirm('¿Deseas eliminar esta dirección?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #e8648c; font-size: 0.8rem; font-weight: 700; cursor: pointer; padding: 0;">
                                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12" style="text-align: center; padding: 2rem;">
                            <p style="color: #796693;">Aún no tienes direcciones registradas.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Formulario para Agregar Nueva Dirección -->
            <div class="col-lg-5">
                <div class="kawaii-box">
                    <div class="kawaii-box-header">
                        <h2 class="kawaii-box-title" style="font-size: 1.1rem;">
                            <i class="fas fa-plus-circle" style="color: #7c5cbf;"></i> Agregar Nueva Dirección
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('cliente.direccion.guardar') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.85rem;">
                                Dirección o Calle <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="calle" class="form-kawaii-control" placeholder="Ej: Carrera 15 # 45 - 28 Apto 302" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.85rem;">
                                    Ciudad <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="ciudad" class="form-kawaii-control" placeholder="Ej: Bogotá" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.85rem;">
                                    Departamento <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="departamento" class="form-kawaii-control" placeholder="Ej: Cundinamarca" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.85rem;">
                                Código Postal (Opcional)
                            </label>
                            <input type="text" name="codigo_postal" class="form-kawaii-control" placeholder="Ej: 110111">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.85rem;">
                                Referencia o Indicaciones (Opcional)
                            </label>
                            <textarea name="referencia" class="form-kawaii-control" rows="2" placeholder="Ej: Casa blanca con rejas negras, dejar en portería"></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="principal" value="1" id="checkPrincipal">
                            <label class="form-check-label" for="checkPrincipal" style="font-size: 0.85rem; color: #4b3664; font-weight: 600;">
                                Marcar como dirección de envío principal
                            </label>
                        </div>

                        <button type="submit" class="btn-kawaii-action w-100 justify-content-center">
                            <i class="fas fa-save"></i> Guardar Dirección
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         PESTAÑA 4: MI PERFIL Y DATOS PERSONALES
         ══════════════════════════════════════════════════════════════ -->
    <div id="tab-perfil" class="client-section-panel">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="kawaii-box">
                    <div class="kawaii-box-header">
                        <h2 class="kawaii-box-title">
                            <i class="fas fa-user-edit" style="color: #7c5cbf;"></i> Modificar Mis Datos Personales
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('cliente.perfil.actualizar') }}">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nombre" class="form-kawaii-control" value="{{ old('nombre', $usuario->nombre) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                    Apellido <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="apellido" class="form-kawaii-control" value="{{ old('apellido', $usuario->apellido) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                Correo Electrónico
                            </label>
                            <input type="email" class="form-kawaii-control" value="{{ $usuario->email }}" disabled style="background: #ede6f7; cursor: not-allowed;" title="El correo electrónico no se puede modificar por razones de seguridad">
                            <small style="color: #8e7b9e; font-size: 0.78rem;">
                                <i class="fas fa-lock me-1"></i> Tu correo está protegido y vinculado a tus compras.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                Teléfono de Contacto
                            </label>
                            <input type="text" name="telefono" class="form-kawaii-control" value="{{ old('telefono', $usuario->telefono) }}" placeholder="Ej: 300 123 4567">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-kawaii-action">
                                <i class="fas fa-save"></i> Actualizar Perfil
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         PESTAÑA 5: SEGURIDAD Y CAMBIO DE CLAVE
         ══════════════════════════════════════════════════════════════ -->
    <div id="tab-seguridad" class="client-section-panel">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="kawaii-box">
                    <div class="kawaii-box-header">
                        <h2 class="kawaii-box-title">
                            <i class="fas fa-key" style="color: #7c5cbf;"></i> Cambiar Mi Contraseña
                        </h2>
                    </div>

                    <form method="POST" action="{{ route('cliente.password.actualizar') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                Contraseña Actual <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_actual" class="form-kawaii-control @error('password_actual') is-invalid @enderror" required>
                            @error('password_actual')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                Nueva Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" class="form-kawaii-control @error('password') is-invalid @enderror" placeholder="Mínimo 6 caracteres" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 700; color: #4b3664; font-size: 0.88rem;">
                                Confirmar Nueva Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password_confirmation" class="form-kawaii-control" placeholder="Repite la nueva contraseña" required>
                        </div>

                        <button type="submit" class="btn-kawaii-action w-100 justify-content-center">
                            <i class="fas fa-shield-alt"></i> Guardar Nueva Contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
/**
 * Conmutador de Pestañas Kawaii en el Portal del Cliente
 */
function switchClientTab(tabName, btnElement) {
    // 1. Ocultar todos los paneles
    document.querySelectorAll('.client-section-panel').forEach(function(panel) {
        panel.classList.remove('active');
    });

    // 2. Desactivar todos los botones de pestaña
    document.querySelectorAll('.client-tab-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });

    // 3. Activar el panel y botón seleccionado
    const targetPanel = document.getElementById('tab-' + tabName);
    if (targetPanel) {
        targetPanel.classList.add('active');
    }
    if (btnElement) {
        btnElement.classList.add('active');
    }

    // Actualizar parámetro en la URL sin recargar
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

// Activar pestaña según el parámetro ?tab= en la URL al cargar
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        const btn = document.querySelector(`.client-tab-btn[onclick*="${activeTab}"]`);
        if (btn) {
            switchClientTab(activeTab, btn);
        }
    }
});
</script>
@endpush
