@extends('layouts.admin')

@section('title', "Detalle Pedido #{$pedido->id_pedido} - Admin Juno's Store")

@section('content')
<!-- Barra superior de navegación -->
<div class="dash-panel" style="margin-bottom: 1.5rem;">
    <div class="panel-header" style="flex-wrap: wrap; gap: 1rem;">
        <div class="panel-title">
            <span class="panel-eyebrow">Detalle de Venta</span>
            <h1>Pedido #{{ $pedido->id_pedido }}</h1>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('admin.pedidos.index') }}" class="btn-kawaii-outline">
                <i class="fas fa-arrow-left me-1"></i> Volver a la Lista
            </a>
            <button onclick="window.print()" class="btn-kawaii-secondary">
                <i class="fas fa-print me-1"></i> Imprimir Comprobante
            </button>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- COLUMNA IZQUIERDA: ITEMS DEL PEDIDO -->
    <div class="col-lg-8">
        <div class="dash-panel" style="margin-bottom: 1.5rem;">
            <div class="panel-header">
                <h2>Artículos del Pedido ({{ $pedido->detalles->sum('cantidad') }})</h2>
                <span class="badge-kawaii" style="background: {{ $pedido->estadoColor }}22; color: {{ $pedido->estadoColor }}; border: 1px solid {{ $pedido->estadoColor }}55;">
                    {{ $pedido->estadoFormateado }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table-kawaii">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="text-align: center;">Cantidad</th>
                            <th style="text-align: right;">Precio Unit.</th>
                            <th style="text-align: right;">Total Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedido->detalles as $item)
                        @php
                            $thumb = $item->producto?->imagenes?->where('es_principal', 1)->first() ?? $item->producto?->imagenes?->first();
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    @if($thumb)
                                        <img src="{{ asset($thumb->ruta_imagen) }}" alt="" style="width: 46px; height: 46px; border-radius: 10px; object-fit: cover;" onerror="this.src='{{ asset('img/icon.png') }}'">
                                    @else
                                        <div style="width: 46px; height: 46px; border-radius: 10px; background: #ebdffc; display: flex; align-items: center; justify-content: center; color: #7c5cbf;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong style="color: #352b43; font-size: 0.9rem; display: block;">{{ $item->producto?->nombre ?? 'Producto no disponible' }}</strong>
                                        <small style="color: #9b84cc;">SKU: {{ $item->producto?->sku ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-kawaii badge-kawaii-purple">{{ $item->cantidad }}</span>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #695880;">
                                ${{ number_format($item->precio_unitario, 0, ',', '.') }}
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #7c5cbf;">
                                ${{ number_format($item->subtotal ?? ($item->precio_unitario * $item->cantidad), 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #9b84cc; padding: 2rem;">
                                No hay artículos registrados en este pedido.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Resumen Financiero -->
            <div style="max-width: 320px; margin-left: auto; margin-top: 1.5rem; background: #faf7fd; padding: 1.25rem; border-radius: 16px; border: 1px solid #ebdffc;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #695880;">
                    <span>Subtotal:</span>
                    <strong>${{ number_format($pedido->subtotal, 0, ',', '.') }}</strong>
                </div>
                @if($pedido->descuento > 0)
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem; color: #e84393;">
                    <span>Descuento:</span>
                    <strong>-${{ number_format($pedido->descuento, 0, ',', '.') }}</strong>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #695880;">
                    <span>Envío:</span>
                    <strong>${{ number_format($pedido->costo_envio, 0, ',', '.') }}</strong>
                </div>
                <hr style="border-color: #d4c2ea; margin: 8px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 1.15rem; color: #27ae60; font-weight: 900;">
                    <span>Total:</span>
                    <span>${{ number_format($pedido->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: ESTADO Y CLIENTE -->
    <div class="col-lg-4">
        <!-- Cambio de Estado -->
        <div class="dash-panel" style="margin-bottom: 1.5rem; border: 1.5px solid #d4c2ea;">
            <div class="panel-header" style="margin-bottom: 1rem;">
                <h2>⚙️ Modificar Estado</h2>
            </div>
            <form action="{{ route('admin.pedidos.estado', $pedido->id_pedido) }}" method="POST">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label class="form-label" style="font-weight: 700; color: #503d68; font-size: 0.85rem;">Estado de la Orden</label>
                    <select name="estado" class="form-select" style="border-radius: 12px; border-color: #d4c2ea; padding: 8px 12px;">
                        <option value="pendiente" {{ in_array(strtolower($pedido->estado), ['pendiente']) ? 'selected' : '' }}>🟡 Pendiente de Pago/Confirmación</option>
                        <option value="confirmado" {{ in_array(strtolower($pedido->estado), ['confirmado', 'pagado']) ? 'selected' : '' }}>🔵 Confirmado</option>
                        <option value="en_preparacion" {{ in_array(strtolower($pedido->estado), ['en_preparacion', 'en_proceso']) ? 'selected' : '' }}>🟣 En Preparación (Empacando)</option>
                        <option value="enviado" {{ in_array(strtolower($pedido->estado), ['enviado']) ? 'selected' : '' }}>🚚 Enviado (En Tránsito)</option>
                        <option value="entregado" {{ in_array(strtolower($pedido->estado), ['entregado']) ? 'selected' : '' }}>🟢 Entregado al Cliente</option>
                        <option value="cancelado" {{ in_array(strtolower($pedido->estado), ['cancelado']) ? 'selected' : '' }}>🔴 Cancelado</option>
                    </select>
                </div>
                <button type="submit" class="btn-kawaii-primary" style="width: 100%; border-radius: 12px; justify-content: center;">
                    <i class="fas fa-sync-alt me-1"></i> Actualizar Estado
                </button>
            </form>
        </div>

        <!-- Información del Cliente -->
        <div class="dash-panel">
            <div class="panel-header" style="margin-bottom: 1rem;">
                <h2>👤 Datos del Cliente</h2>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.88rem;">
                <div>
                    <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Nombre:</span>
                    <strong style="color: #352b43;">{{ $pedido->usuario?->nombre_completo ?? 'Cliente Invitado' }}</strong>
                </div>
                <div>
                    <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Correo Electrónico:</span>
                    <a href="mailto:{{ $pedido->usuario?->email }}" style="color: #7c5cbf; text-decoration: none;">
                        {{ $pedido->usuario?->email ?? 'No especificado' }}
                    </a>
                </div>
                <div>
                    <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Teléfono:</span>
                    <strong style="color: #352b43;">{{ $pedido->usuario?->telefono ?? 'Sin teléfono' }}</strong>
                </div>
                <div>
                    <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Dirección de Entrega:</span>
                    <p style="color: #503d68; margin: 0; background: #faf7fd; padding: 8px 10px; border-radius: 8px; border: 1px solid #ede5f8;">
                        {{ $pedido->direccion_entrega ?? 'Recogida en tienda / Sin dirección' }}
                    </p>
                </div>
                @if(!empty($pedido->notas))
                <div>
                    <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Notas del Cliente:</span>
                    <p style="color: #8c5700; margin: 0; background: #fff8e7; padding: 8px 10px; border-radius: 8px; border: 1px solid #fed888;">
                        {{ $pedido->notas }}
                    </p>
                </div>
                @endif
                <div>
                    <span style="color: #9b84cc; display: block; font-size: 0.75rem;">Fecha del Pedido:</span>
                    <strong style="color: #503d68;">{{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y \a \l\a\s H:i a') : 'Reciente' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
