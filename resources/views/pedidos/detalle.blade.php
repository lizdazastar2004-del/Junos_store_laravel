@extends('layouts.app')

@section('title', "Detalle del Pedido #{$pedido->id_pedido} - Juno's Store")

@section('content')
<div style="max-width: 960px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Breadcrumb y Botón Volver -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <a href="{{ route('pedidos.historial') }}" class="btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Volver a Mis Pedidos
            </a>
        </div>
        <div style="font-size: 0.88rem; color: var(--texto-claro, #796693);">
            Pedido realizado el <strong>{{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y \a \l\a\s H:i') : 'Reciente' }}</strong>
        </div>
    </div>

    <!-- Tarjeta Principal del Pedido -->
    <div class="card-kawaii" style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(124, 92, 191, 0.08); padding: 2rem; margin-bottom: 2rem; border: 1.5px solid #ebdffc;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1.5px solid #f2e9fc; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 800; color: #9b84cc; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">
                    Comprobante de Compra
                </span>
                <h1 style="font-size: 1.75rem; font-weight: 900; color: #352b43; margin: 0;">
                    Pedido #{{ $pedido->id_pedido }}
                </h1>
            </div>
            <div style="text-align: right;">
                <span class="badge-kawaii" style="background: {{ $pedido->estado_color }}22; color: {{ $pedido->estado_color }}; border: 1px solid {{ $pedido->estado_color }}55; font-size: 0.9rem; padding: 6px 14px; font-weight: 800;">
                    <i class="fas fa-circle me-1" style="font-size: 0.6rem;"></i> {{ $pedido->estado_formateado }}
                </span>
                <div style="font-size: 1.35rem; font-weight: 900; color: #7c5cbf; margin-top: 6px;">
                    ${{ number_format($pedido->total, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Barra de Progreso del Pedido (Stepper Kawaii) -->
        @php
            $estados = ['pendiente' => 1, 'confirmado' => 2, 'en_preparacion' => 3, 'enviado' => 4, 'entregado' => 5];
            $pasoActual = $estados[$pedido->estado] ?? 1;
            $esCancelado = $pedido->estado === 'cancelado';
        @endphp

        @if(!$esCancelado)
        <div style="margin: 2rem 0; padding: 1.25rem; background: #faf7fd; border-radius: 18px; border: 1px solid #ede5f8;">
            <div style="display: flex; justify-content: space-between; position: relative; margin-bottom: 0.5rem;">
                <!-- Línea de fondo -->
                <div style="position: absolute; top: 18px; left: 5%; right: 5%; height: 4px; background: #e3d3f8; z-index: 1;"></div>
                <!-- Línea de progreso activa -->
                <div style="position: absolute; top: 18px; left: 5%; width: {{ max(0, ($pasoActual - 1) * 23.5) }}%; height: 4px; background: #7c5cbf; z-index: 2; transition: width 0.4s ease;"></div>

                <!-- Pasos -->
                <div style="position: relative; z-index: 3; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $pasoActual >= 1 ? '#7c5cbf' : '#ffffff' }}; color: {{ $pasoActual >= 1 ? '#ffffff' : '#9b84cc' }}; border: 2px solid #7c5cbf; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: {{ $pasoActual >= 1 ? '#352b43' : '#9b84cc' }}; display: block;">Recibido</span>
                </div>

                <div style="position: relative; z-index: 3; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $pasoActual >= 2 ? '#7c5cbf' : '#ffffff' }}; color: {{ $pasoActual >= 2 ? '#ffffff' : '#9b84cc' }}; border: 2px solid {{ $pasoActual >= 2 ? '#7c5cbf' : '#d5c8ea' }}; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                        <i class="fas fa-check"></i>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: {{ $pasoActual >= 2 ? '#352b43' : '#9b84cc' }}; display: block;">Confirmado</span>
                </div>

                <div style="position: relative; z-index: 3; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $pasoActual >= 3 ? '#7c5cbf' : '#ffffff' }}; color: {{ $pasoActual >= 3 ? '#ffffff' : '#9b84cc' }}; border: 2px solid {{ $pasoActual >= 3 ? '#7c5cbf' : '#d5c8ea' }}; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: {{ $pasoActual >= 3 ? '#352b43' : '#9b84cc' }}; display: block;">En Prep.</span>
                </div>

                <div style="position: relative; z-index: 3; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $pasoActual >= 4 ? '#7c5cbf' : '#ffffff' }}; color: {{ $pasoActual >= 4 ? '#ffffff' : '#9b84cc' }}; border: 2px solid {{ $pasoActual >= 4 ? '#7c5cbf' : '#d5c8ea' }}; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: {{ $pasoActual >= 4 ? '#352b43' : '#9b84cc' }}; display: block;">Enviado</span>
                </div>

                <div style="position: relative; z-index: 3; text-align: center; flex: 1;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $pasoActual >= 5 ? '#27ae60' : '#ffffff' }}; color: {{ $pasoActual >= 5 ? '#ffffff' : '#9b84cc' }}; border: 2px solid {{ $pasoActual >= 5 ? '#27ae60' : '#d5c8ea' }}; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                        <i class="fas fa-heart"></i>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 700; color: {{ $pasoActual >= 5 ? '#27ae60' : '#9b84cc' }}; display: block;">Entregado</span>
                </div>
            </div>
        </div>
        @else
        <div style="background: #fff5f8; border: 1.5px solid #fed1df; padding: 1rem 1.25rem; border-radius: 16px; margin: 1.5rem 0; display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-times-circle" style="color: #e84393; font-size: 1.5rem;"></i>
            <div>
                <strong style="color: #c2335f; display: block;">Pedido Cancelado</strong>
                <span style="color: #695880; font-size: 0.88rem;">Este pedido ha sido cancelado. Si tienes preguntas, comunícate con nosotros.</span>
            </div>
        </div>
        @endif

        <!-- Grid: Items Comprados + Resumen de Entrega y Pago -->
        <div class="row g-4">
            <!-- Artículos Comprados -->
            <div class="col-lg-7">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #352b43; margin-bottom: 1rem;">
                    🌸 Productos del Pedido ({{ $pedido->detalles->sum('cantidad') }})
                </h3>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    @foreach($pedido->detalles as $item)
                    @php
                        $thumb = $item->producto?->imagenes?->where('es_principal', 1)->first() ?? $item->producto?->imagenes?->first();
                    @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 10px 14px; background: #faf7fd; border-radius: 14px; border: 1px solid #ede5f8;">
                        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                            @if($thumb)
                                <img src="{{ asset($thumb->ruta_imagen) }}" alt="{{ $item->producto?->nombre }}" 
                                     style="width: 48px; height: 48px; border-radius: 10px; object-fit: cover; flex-shrink: 0;"
                                     onerror="this.src='{{ asset('img/icon.png') }}'">
                            @else
                                <div style="width: 48px; height: 48px; border-radius: 10px; background: #ebdffc; display: flex; align-items: center; justify-content: center; color: #7c5cbf; flex-shrink: 0;">
                                    <i class="fas fa-paw"></i>
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <strong style="color: #352b43; font-size: 0.9rem; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item->producto?->nombre ?? 'Producto de la tienda' }}
                                </strong>
                                <span style="font-size: 0.78rem; color: #9b84cc;">
                                    ${{ number_format($item->precio_unitario, 0, ',', '.') }} × {{ $item->cantidad }} ud.
                                </span>
                            </div>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <strong style="color: #7c5cbf; font-size: 0.95rem;">
                                ${{ number_format($item->subtotal ?? ($item->precio_unitario * $item->cantidad), 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Resumen Financiero y Entrega -->
            <div class="col-lg-5">
                <!-- Desglose de Pago -->
                <div style="background: #faf7fd; padding: 1.25rem; border-radius: 18px; border: 1px solid #ede5f8; margin-bottom: 1.25rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 800; color: #352b43; margin-bottom: 0.85rem;">
                        Resumen de Pago
                    </h4>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.88rem; color: #695880;">
                        <span>Subtotal:</span>
                        <strong>${{ number_format($pedido->subtotal, 0, ',', '.') }}</strong>
                    </div>
                    @if($pedido->descuento > 0)
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.88rem; color: #27ae60;">
                        <span>Descuento cupón:</span>
                        <strong>-${{ number_format($pedido->descuento, 0, ',', '.') }}</strong>
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.88rem; color: #695880;">
                        <span>Costo de envío:</span>
                        <strong>
                            @if($pedido->costo_envio > 0)
                                ${{ number_format($pedido->costo_envio, 0, ',', '.') }}
                            @else
                                <span style="color: #27ae60;">Gratis ✨</span>
                            @endif
                        </strong>
                    </div>
                    <hr style="border-color: #d4c2ea; margin: 10px 0;">
                    <div style="display: flex; justify-content: space-between; font-size: 1.15rem; color: #7c5cbf; font-weight: 900;">
                        <span>Total Pagado:</span>
                        <span>${{ number_format($pedido->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Datos de Envío -->
                <div style="background: #ffffff; padding: 1.25rem; border-radius: 18px; border: 1.5px solid #ebdffc;">
                    <h4 style="font-size: 0.95rem; font-weight: 800; color: #352b43; margin-bottom: 0.75rem;">
                        📍 Información de Entrega
                    </h4>
                    <p style="font-size: 0.88rem; color: #503d68; margin-bottom: 0.5rem; line-height: 1.4;">
                        <strong>Dirección:</strong><br>
                        {{ $pedido->direccion_entrega ?? 'No especificada' }}
                    </p>
                    @if(!empty($pedido->notas))
                    <p style="font-size: 0.85rem; color: #8c5700; background: #fff8e7; padding: 8px 10px; border-radius: 8px; border: 1px solid #fed888; margin: 0.5rem 0;">
                        <strong>📝 Notas:</strong> {{ $pedido->notas }}
                    </p>
                    @endif
                    <div style="font-size: 0.82rem; color: #9b84cc; margin-top: 0.5rem;">
                        <strong>Destinatario:</strong> {{ $pedido->usuario?->nombre_completo }}<br>
                        <strong>Teléfono:</strong> {{ $pedido->usuario?->telefono ?? 'Sin registrar' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción Finales -->
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 2rem; padding-top: 1.5rem; border-top: 1.5px solid #f2e9fc;">
            <a href="{{ route('pedidos.historial') }}" class="btn-secondary" style="border-radius: 999px; padding: 8px 20px;">
                <i class="fas fa-list me-1"></i> Ver Todos Mis Pedidos
            </a>
            <a href="{{ route('tienda.index') }}" class="btn-primary" style="border-radius: 999px; padding: 8px 20px;">
                <i class="fas fa-store me-1"></i> Seguir Comprando
            </a>
        </div>
    </div>
</div>
@endsection
