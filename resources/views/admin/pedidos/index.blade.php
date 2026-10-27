@extends('layouts.admin')

@section('title', "Gestión de Pedidos - Admin Juno's Store")

@section('content')
<!-- Header de la sección -->
<div class="dash-panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header" style="flex-wrap: wrap; gap: 1rem;">
        <div class="panel-title">
            <span class="panel-eyebrow">Módulo de Ventas & Despachos</span>
            <h1>📦 Gestión Integral de Pedidos</h1>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn-kawaii-outline">
            <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
        </a>
    </div>
    <p style="color: #695880; font-size: 0.92rem; margin: 0;">
        Supervisa los pedidos de tus clientes, actualiza el estado de despacho y revisa el detalle de cada compra.
    </p>
</div>

<!-- Filtros por Estado en Píldoras Kawaii -->
<div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 1.25rem;">
    <a href="{{ route('admin.pedidos.index') }}" 
       class="btn-pill {{ empty($estado) || $estado == 'todos' ? 'active' : '' }}" 
       style="{{ empty($estado) || $estado == 'todos' ? 'background: #7c5cbf; color: white;' : 'background: white; color: #503d68;' }} border: 1px solid #d5c8ea; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Todos ({{ $conteos['todos'] }})
    </a>
    <a href="{{ route('admin.pedidos.index', ['estado' => 'pendiente']) }}" 
       class="btn-pill" 
       style="{{ $estado == 'pendiente' ? 'background: #f39c12; color: white;' : 'background: white; color: #f39c12;' }} border: 1px solid #f39c1266; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Pendientes ({{ $conteos['pendiente'] }})
    </a>
    <a href="{{ route('admin.pedidos.index', ['estado' => 'confirmado']) }}" 
       class="btn-pill" 
       style="{{ $estado == 'confirmado' ? 'background: #3498db; color: white;' : 'background: white; color: #3498db;' }} border: 1px solid #3498db66; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Confirmados ({{ $conteos['confirmado'] }})
    </a>
    <a href="{{ route('admin.pedidos.index', ['estado' => 'en_preparacion']) }}" 
       class="btn-pill" 
       style="{{ $estado == 'en_preparacion' ? 'background: #9b59b6; color: white;' : 'background: white; color: #9b59b6;' }} border: 1px solid #9b59b666; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        En Preparación ({{ $conteos['en_preparacion'] }})
    </a>
    <a href="{{ route('admin.pedidos.index', ['estado' => 'enviado']) }}" 
       class="btn-pill" 
       style="{{ $estado == 'enviado' ? 'background: #1abc9c; color: white;' : 'background: white; color: #1abc9c;' }} border: 1px solid #1abc9c66; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Enviados ({{ $conteos['enviado'] }})
    </a>
    <a href="{{ route('admin.pedidos.index', ['estado' => 'entregado']) }}" 
       class="btn-pill" 
       style="{{ $estado == 'entregado' ? 'background: #27ae60; color: white;' : 'background: white; color: #27ae60;' }} border: 1px solid #27ae6066; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Entregados ({{ $conteos['entregado'] }})
    </a>
    <a href="{{ route('admin.pedidos.index', ['estado' => 'cancelado']) }}" 
       class="btn-pill" 
       style="{{ $estado == 'cancelado' ? 'background: #e74c3c; color: white;' : 'background: white; color: #e74c3c;' }} border: 1px solid #e74c3c66; padding: 6px 14px; text-decoration: none; border-radius: 999px; font-size: 0.82rem; font-weight: 700;">
        Cancelados ({{ $conteos['cancelado'] }})
    </a>
</div>

<!-- Barra de Búsqueda -->
<div class="dash-panel" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
    <form action="{{ route('admin.pedidos.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        @if(!empty($estado))
            <input type="hidden" name="estado" value="{{ $estado }}">
        @endif
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9b84cc;"></i>
            <input type="text" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Buscar por # de pedido, nombre o correo del cliente..." 
                   class="form-control" style="padding-left: 38px; border-radius: 999px; border: 1.5px solid #d4c2ea; font-size: 0.88rem;">
        </div>
        <button type="submit" class="btn-kawaii-primary" style="padding: 0.5rem 1.25rem; border-radius: 999px; font-size: 0.88rem;">
            Buscar
        </button>
        @if(!empty($buscar))
            <a href="{{ route('admin.pedidos.index', ['estado' => $estado]) }}" class="btn-kawaii-outline" style="padding: 0.5rem 1rem; border-radius: 999px; font-size: 0.88rem;">
                Limpiar filtro
            </a>
        @endif
    </form>
</div>

<!-- Tabla de Pedidos -->
<div class="dash-panel">
    @if($pedidos->count() > 0)
    <div class="table-responsive">
        <table class="table-kawaii" style="vertical-align: middle;">
            <thead>
                <tr>
                    <th># Orden</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Cambiar Estado Rápido</th>
                    <th style="text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $p)
                <tr>
                    <td>
                        <a href="{{ route('admin.pedidos.show', $p->id_pedido) }}" style="font-weight: 800; color: #7c5cbf; text-decoration: none;">
                            #{{ $p->id_pedido }}
                        </a>
                    </td>
                    <td>
                        <span style="font-size: 0.82rem; color: #503d68; font-weight: 600;">
                            {{ $p->creado_en ? $p->creado_en->format('d/m/Y') : '-' }}
                        </span>
                        <small style="display: block; font-size: 0.72rem; color: #9b84cc;">
                            {{ $p->creado_en ? $p->creado_en->format('H:i a') : '' }}
                        </small>
                    </td>
                    <td>
                        <strong style="color: #352b43; font-size: 0.88rem; display: block;">{{ $p->usuario?->nombre_completo ?? 'Cliente Desconocido' }}</strong>
                        <span style="font-size: 0.76rem; color: #9b84cc;">{{ $p->usuario?->email ?? '-' }}</span>
                    </td>
                    <td>
                        <span class="badge-kawaii badge-kawaii-purple" style="font-size: 0.75rem;">
                            {{ $p->detalles->sum('cantidad') }} arts.
                        </span>
                    </td>
                    <td>
                        <strong style="font-size: 0.95rem; color: #27ae60;">${{ number_format($p->total, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        <span class="badge-kawaii" style="background: {{ $p->estadoColor }}22; color: {{ $p->estadoColor }}; border: 1px solid {{ $p->estadoColor }}55;">
                            {{ $p->estadoFormateado }}
                        </span>
                    </td>
                    <td>
                        <!-- Formulario para cambiar estado -->
                        <form action="{{ route('admin.pedidos.estado', $p->id_pedido) }}" method="POST" style="margin: 0;">
                            @csrf
                            <select name="estado" onchange="this.form.submit()" class="form-select form-select-sm" style="font-size: 0.78rem; border-radius: 8px; border-color: #d4c2ea; max-width: 140px;">
                                <option value="pendiente" {{ in_array(strtolower($p->estado), ['pendiente']) ? 'selected' : '' }}>🟡 Pendiente</option>
                                <option value="confirmado" {{ in_array(strtolower($p->estado), ['confirmado', 'pagado']) ? 'selected' : '' }}>🔵 Confirmado</option>
                                <option value="en_preparacion" {{ in_array(strtolower($p->estado), ['en_preparacion', 'en_proceso']) ? 'selected' : '' }}>🟣 En Preparación</option>
                                <option value="enviado" {{ in_array(strtolower($p->estado), ['enviado']) ? 'selected' : '' }}>🚚 Enviado</option>
                                <option value="entregado" {{ in_array(strtolower($p->estado), ['entregado']) ? 'selected' : '' }}>🟢 Entregado</option>
                                <option value="cancelado" {{ in_array(strtolower($p->estado), ['cancelado']) ? 'selected' : '' }}>🔴 Cancelado</option>
                            </select>
                        </form>
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('admin.pedidos.show', $p->id_pedido) }}" class="btn-kawaii-outline" style="padding: 4px 10px; font-size: 0.78rem; border-radius: 8px;">
                            <i class="fas fa-eye me-1"></i> Ver Detalle
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
        {{ $pedidos->links() }}
    </div>
    @else
    <div class="panel-empty" style="padding: 3rem 1rem;">
        <i class="fas fa-receipt" style="font-size: 3rem; color: #d8cce8; margin-bottom: 0.75rem;"></i>
        <h4>No se encontraron pedidos</h4>
        <p style="color: #88729f;">No hay registros que coincidan con los filtros seleccionados.</p>
        <a href="{{ route('admin.pedidos.index') }}" class="btn-kawaii-primary" style="margin-top: 0.5rem;">
            Ver todos los pedidos
        </a>
    </div>
    @endif
</div>
@endsection
