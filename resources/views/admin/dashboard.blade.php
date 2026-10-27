@extends('layouts.admin')

@section('title', "Dashboard Administrativo - Juno's Store")

@section('content')
<!-- ══════════════════ DASHBOARD HERO ══════════════════ -->
<div class="dash-hero">
    <div class="dash-hero-content">
        <span class="dash-eyebrow">Consola de Control Integral</span>
        <h1>¡Hola, {{ auth()->user()->nombre }}! ✨</h1>
        <p>
            Monitorea ventas, estado de pedidos, inventario en tiempo real y realiza ajustes 
            inmediatos para mantener tu tienda pastel funcionando a la perfección.
        </p>
    </div>
    <img src="{{ asset('img/dashboard_box_cat.jpg') }}" 
         alt="Gatito kawaii" 
         class="dash-hero-img"
         onerror="this.src='{{ asset('img/icon.png') }}'">
</div>

<!-- ══════════════════ ALERTAS INTELIGENTES DE DIAGNÓSTICO ══════════════════ -->
@if($diagnostico['productos_inactivos_con_stock'] > 0)
<div class="alert-kawaii alert-kawaii-warning" style="background: #fff8e7; border: 1.5px solid #fed888; border-radius: 16px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: #ffeaa7; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #d68910;">
            <i class="fas fa-magic"></i>
        </div>
        <div>
            <strong style="color: #8c5700; font-size: 0.95rem; display: block;">¡Detección de productos listos para publicar!</strong>
            <span style="color: #8c6d3f; font-size: 0.85rem;">Tienes <strong>{{ $diagnostico['productos_inactivos_con_stock'] }} productos</strong> con stock físico disponible pero marcados como inactivos.</span>
        </div>
    </div>
    <form action="{{ route('admin.mantenimiento.activar-stock') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" class="btn-kawaii-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 999px;">
            <i class="fas fa-check-double me-1"></i> Publicar y Activar Todos
        </button>
    </form>
</div>
@endif

<!-- ══════════════════ KPI GRID (6 TARJETAS COMPLETAS) ══════════════════ -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));">
    <!-- Ventas Totales -->
    <div class="kpi-card">
        <div class="kpi-icon icon-green" style="background: #e8f8f0; color: #2ecc71;">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Ventas Totales</span>
            <span class="kpi-value" style="font-size: 1.35rem;">${{ number_format($ventasTotales, 0, ',', '.') }}</span>
            <span class="kpi-note" style="color: #27ae60; font-weight: 700;">Recaudación total</span>
        </div>
    </div>

    <!-- Pedidos Totales -->
    <div class="kpi-card">
        <div class="kpi-icon icon-purple">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Pedidos Realizados</span>
            <span class="kpi-value">{{ $totalPedidos }}</span>
            <span class="kpi-note" style="color: #8e44ad; font-weight: 700;">{{ $pedidosPendientes }} pendientes</span>
        </div>
    </div>

    <!-- Productos Activos -->
    <div class="kpi-card">
        <div class="kpi-icon icon-yellow">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Catálogo Activo</span>
            <span class="kpi-value">{{ $productosActivos }} <small style="font-size: 0.75rem; color: #88729f;">/ {{ $totalProductos }}</small></span>
            <span class="kpi-note">En {{ $totalCategorias }} categorías</span>
        </div>
    </div>

    <!-- Stock Crítico -->
    <div class="kpi-card">
        <div class="kpi-icon icon-pink">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Stock Crítico</span>
            <span class="kpi-value" style="color: #e84393;">{{ $productosStockBajo + $productosAgotados }}</span>
            <span class="kpi-note" style="color: #d63031; font-weight: 700;">{{ $productosAgotados }} agotados en 0</span>
        </div>
    </div>

    <!-- Clientes Registrados -->
    <div class="kpi-card">
        <div class="kpi-icon icon-purple" style="background: #f1edfa; color: #9b59b6;">
            <i class="fas fa-users"></i>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Clientes</span>
            <span class="kpi-value">{{ $totalClientes }}</span>
            <span class="kpi-note">{{ $totalAdmins }} administradores</span>
        </div>
    </div>

    <!-- Carritos Activos -->
    <div class="kpi-card">
        <div class="kpi-icon icon-mint" style="background: #eef9f6; color: #1abc9c;">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Carritos en Curso</span>
            <span class="kpi-value">{{ $carritosActivos }}</span>
            <span class="kpi-note">En proceso de compra</span>
        </div>
    </div>
</div>

<!-- ══════════════════ BOTIQUÍN DE HERRAMIENTAS Y MANTENIMIENTO ══════════════════ -->
<div class="dash-panel" style="margin-bottom: 2rem; background: linear-gradient(135deg, #ffffff 0%, #fbf9ff 100%);">
    <div class="panel-header" style="flex-wrap: wrap; gap: 0.75rem;">
        <div class="panel-title">
            <span class="panel-eyebrow">Acciones directas para solucionar problemas</span>
            <h2>🛠️ Botiquín de Arreglos & Mantenimiento del Sistema</h2>
        </div>
        <span class="badge-kawaii badge-kawaii-purple" style="font-size: 0.8rem;">Control del Proyecto</span>
    </div>

    <p style="color: #695880; font-size: 0.9rem; margin-bottom: 1.25rem;">
        Si notas que los productos no se ven en la tienda, el stock se agotó o el sistema tiene datos cacheados, utiliza estas herramientas de reparación en 1 clic:
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
        <!-- Botón 1: Activar con Stock -->
        <form action="{{ route('admin.mantenimiento.activar-stock') }}" method="POST">
            @csrf
            <button type="submit" class="btn-kawaii-outline" style="width: 100%; text-align: left; padding: 12px 14px; border-radius: 14px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #ebdffc; display: flex; align-items: center; justify-content: center; color: #7c5cbf; flex-shrink: 0;">
                    <i class="fas fa-eye"></i>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: #433355;">Publicar Productos</strong>
                    <span style="font-size: 0.76rem; color: #7f6e99;">Activar los que tienen stock</span>
                </div>
            </button>
        </form>

        <!-- Botón 2: Reponer Agotados -->
        <form action="{{ route('admin.mantenimiento.reponer-agotados') }}" method="POST">
            @csrf
            <button type="submit" class="btn-kawaii-outline" style="width: 100%; text-align: left; padding: 12px 14px; border-radius: 14px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #ffe3ec; display: flex; align-items: center; justify-content: center; color: #e84393; flex-shrink: 0;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: #433355;">Reponer Agotados</strong>
                    <span style="font-size: 0.76rem; color: #7f6e99;">Dar stock mínimo de emergencia</span>
                </div>
            </button>
        </form>

        <!-- Botón 3: Limpiar Caché -->
        <form action="{{ route('admin.mantenimiento.limpiar-cache') }}" method="POST">
            @csrf
            <button type="submit" class="btn-kawaii-outline" style="width: 100%; text-align: left; padding: 12px 14px; border-radius: 14px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #dff6f0; display: flex; align-items: center; justify-content: center; color: #1abc9c; flex-shrink: 0;">
                    <i class="fas fa-broom"></i>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: #433355;">Limpiar Caché</strong>
                    <span style="font-size: 0.76rem; color: #7f6e99;">Vistas, rutas y configuración</span>
                </div>
            </button>
        </form>

        <!-- Botón 4: Limpiar Carritos -->
        <form action="{{ route('admin.mantenimiento.limpiar-carritos') }}" method="POST">
            @csrf
            <button type="submit" class="btn-kawaii-outline" style="width: 100%; text-align: left; padding: 12px 14px; border-radius: 14px; display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #fff1d8; display: flex; align-items: center; justify-content: center; color: #e67e22; flex-shrink: 0;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: #433355;">Carritos Huérfanos</strong>
                    <span style="font-size: 0.76rem; color: #7f6e99;">Marcar antiguos abandonados</span>
                </div>
            </button>
        </form>

        <!-- Botón 5: Cargar Datos de Prueba (Demo) -->
        <form action="{{ route('admin.mantenimiento.cargar-datos-prueba') }}" method="POST" onsubmit="return confirm('¿Cargar datos de prueba para verificar todo el sistema? Se sincronizarán usuarios demo, catálogo kawaii, cupones y pedidos en todos los estados.');">
            @csrf
            <button type="submit" class="btn-kawaii-outline" style="width: 100%; text-align: left; padding: 12px 14px; border-radius: 14px; display: flex; align-items: center; gap: 10px; border-color: #c7b0e8; background: #faf5ff;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #7c5cbf; display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: #5a3e91;">Cargar Datos de Prueba</strong>
                    <span style="font-size: 0.76rem; color: #7f6e99;">Poblar demo en 1 clic</span>
                </div>
            </button>
        </form>
    </div>
</div>

<!-- ══════════════════ TABLAS PRINCIPALES DUALES ══════════════════ -->
<div class="row g-4" style="margin-bottom: 2rem;">
    <!-- COLUMNA 1: ÚLTIMOS PEDIDOS CON CAMBIO DE ESTADO -->
    <div class="col-lg-7">
        <div class="dash-panel" style="height: 100%;">
            <div class="panel-header">
                <div class="panel-title">
                    <span class="panel-eyebrow">Ventas y despachos</span>
                    <h2>🛍️ Pedidos Recientes</h2>
                </div>
                <a href="{{ route('admin.pedidos.index') }}" class="btn-pill">Ver todos &rarr;</a>
            </div>

            @if($ultimosPedidos->count() > 0)
            <div class="table-responsive" style="margin-top: 0.5rem;">
                <table class="table-kawaii" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th># Orden</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Estado Actual</th>
                            <th>Acción Rápida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosPedidos as $ped)
                        <tr>
                            <td>
                                <a href="{{ route('admin.pedidos.show', $ped->id_pedido) }}" style="font-weight: 800; color: #7c5cbf; text-decoration: none;">
                                    #{{ $ped->id_pedido }}
                                </a>
                            </td>
                            <td>
                                <strong style="color: #352b43; display: block;">{{ $ped->usuario?->nombre_completo ?? 'Invitado' }}</strong>
                                <span style="font-size: 0.75rem; color: #9b84cc;">{{ $ped->creado_en ? $ped->creado_en->format('d/m H:i') : 'Reciente' }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 800; color: #27ae60;">${{ number_format($ped->total, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="badge-kawaii" style="background: {{ $ped->estadoColor }}22; color: {{ $ped->estadoColor }}; border: 1px solid {{ $ped->estadoColor }}55; font-size: 0.72rem;">
                                    {{ $ped->estadoFormateado }}
                                </span>
                            </td>
                            <td>
                                <!-- Formulario mini para cambiar estado -->
                                <form action="{{ route('admin.pedidos.estado', $ped->id_pedido) }}" method="POST" style="margin: 0; display: inline-flex; gap: 4px;">
                                    @csrf
                                    <select name="estado" onchange="this.form.submit()" style="font-size: 0.75rem; padding: 3px 6px; border-radius: 8px; border: 1px solid #d4c2ea; background: #faf7fd; color: #503d68;">
                                        <option value="pendiente" {{ $ped->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="confirmado" {{ $ped->estado == 'confirmado' ? 'selected' : '' }}>Confirmado</option>
                                        <option value="en_preparacion" {{ $ped->estado == 'en_preparacion' ? 'selected' : '' }}>En prep.</option>
                                        <option value="enviado" {{ $ped->estado == 'enviado' ? 'selected' : '' }}>Enviado</option>
                                        <option value="entregado" {{ $ped->estado == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                        <option value="cancelado" {{ $ped->estado == 'cancelado' ? 'selected' : '' }}>Cancelar</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="panel-empty" style="padding: 2.5rem 1rem;">
                <i class="fas fa-receipt" style="font-size: 2.5rem; color: #d8cce8; margin-bottom: 0.5rem;"></i>
                <p>Aún no se han registrado pedidos en la tienda virtual.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- COLUMNA 2: ALERTA DE STOCK CRÍTICO (ARREGLO EN LÍNEA) -->
    <div class="col-lg-5">
        <div class="dash-panel" style="height: 100%;">
            <div class="panel-header">
                <div class="panel-title">
                    <span class="panel-eyebrow">Inventario urgente</span>
                    <h2>⚠️ Alertas de Stock</h2>
                </div>
                <a href="{{ route('admin.productos.index') }}" class="btn-pill">Gestionar &rarr;</a>
            </div>

            @if($productosAlertaStock->count() > 0)
            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.5rem;">
                @foreach($productosAlertaStock as $item)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #fff5f8; border-radius: 12px; border: 1px solid #fed1df; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                        @php
                            $thumb = $item->imagenes->where('es_principal', 1)->first() ?? $item->imagenes->first();
                        @endphp
                        @if($thumb)
                            <img src="{{ asset($thumb->ruta_imagen) }}" alt="" style="width: 36px; height: 36px; border-radius: 8px; object-fit: cover; flex-shrink: 0;" onerror="this.src='{{ asset('img/icon.png') }}'">
                        @else
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #ffe4ee; display: flex; align-items: center; justify-content: center; color: #e84393; flex-shrink: 0;">
                                <i class="fas fa-box"></i>
                            </div>
                        @endif
                        <div style="min-width: 0;">
                            <strong style="color: #352b43; font-size: 0.85rem; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item->nombre }}</strong>
                            <span style="color: #e84393; font-size: 0.74rem; font-weight: 700;">
                                Quedan: {{ $item->stock_actual }} (Mín: {{ $item->stock_minimo }})
                            </span>
                        </div>
                    </div>

                    <!-- Mini formulario para agregar stock directo -->
                    <div style="display: flex; align-items: center; gap: 4px; flex-shrink: 0;">
                        <form action="{{ route('admin.productos.ajustar-stock', $item->id_producto) }}" method="POST" style="margin: 0; display: inline;">
                            @csrf
                            <input type="hidden" name="cantidad" value="5">
                            <button type="submit" class="btn-kawaii-primary" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 6px;" title="Sumar 5 unidades">+5</button>
                        </form>
                        <form action="{{ route('admin.productos.ajustar-stock', $item->id_producto) }}" method="POST" style="margin: 0; display: inline;">
                            @csrf
                            <input type="hidden" name="cantidad" value="10">
                            <button type="submit" class="btn-kawaii-secondary" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 6px;" title="Sumar 10 unidades">+10</button>
                        </form>
                        <a href="{{ route('admin.productos.edit', $item->id_producto) }}" class="btn-kawaii-outline" style="padding: 2px 7px; font-size: 0.72rem; border-radius: 6px;" title="Editar producto completo">
                            <i class="fas fa-pen"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="panel-empty" style="padding: 2.5rem 1rem;">
                <i class="fas fa-smile-beam" style="font-size: 2.5rem; color: #70a1ff; margin-bottom: 0.5rem;"></i>
                <p>¡Excelente! Todos los productos cuentan con suficiente inventario.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- ══════════════════ DIAGNÓSTICO DEL SISTEMA Y AMBIENTE ══════════════════ -->
<div class="dash-panel" style="background: #faf8fd; border: 1.5px dashed #d5c8ea;">
    <div class="panel-header" style="margin-bottom: 1rem;">
        <div class="panel-title">
            <span class="panel-eyebrow">Información técnica del servidor y software</span>
            <h2>🩺 Estado de Salud de la Plataforma Juno's Store</h2>
        </div>
        <span class="badge-kawaii badge-kawaii-mint"><i class="fas fa-heartbeat me-1"></i> Sistema OK</span>
    </div>

    <div class="row g-3 text-center" style="font-size: 0.85rem;">
        <div class="col-6 col-md-3">
            <div style="background: white; padding: 12px; border-radius: 12px; border: 1px solid #ebdffc;">
                <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Versión de PHP</span>
                <strong style="color: #4a3860; font-size: 0.95rem;">PHP {{ $diagnostico['php_version'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div style="background: white; padding: 12px; border-radius: 12px; border: 1px solid #ebdffc;">
                <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Framework Laravel</span>
                <strong style="color: #4a3860; font-size: 0.95rem;">v{{ $diagnostico['laravel_version'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div style="background: white; padding: 12px; border-radius: 12px; border: 1px solid #ebdffc;">
                <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Base de Datos</span>
                <strong style="color: #27ae60; font-size: 0.95rem;">{{ $diagnostico['db_connection'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div style="background: white; padding: 12px; border-radius: 12px; border: 1px solid #ebdffc;">
                <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Modo de Ejecución</span>
                <strong style="color: #7c5cbf; font-size: 0.95rem;">{{ $diagnostico['debug_mode'] }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════ FOOTER BANNER ══════════════════ -->
<div class="dash-footer-banner" style="margin-top: 2rem;">
    <div class="dfb-content">
        <div class="dfb-icon">🌸</div>
        <div class="dfb-text">
            <h3>Juno's Store está lista para recibir clientes</h3>
            <p>Los productos activos se visualizan en el catálogo con diseño kawaii adaptado a todos los dispositivos.</p>
        </div>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('admin.productos.create') }}" class="btn-new-item">
            <i class="fas fa-plus-circle me-1"></i> Nuevo Producto
        </a>
        <a href="{{ route('tienda.index') }}" target="_blank" class="btn-catalog">
            <i class="fas fa-store me-1"></i> Ver Tienda Pública
        </a>
    </div>
</div>
@endsection
