@extends('layouts.app')

@section('title', 'Mis Pedidos - Juno\'s Store')

@section('content')
<div style="max-width: 960px; margin: 2rem auto; padding: 0 1rem;">
    <!-- Encabezado de la Sección -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <span style="font-size: 0.8rem; text-transform: uppercase; font-weight: 800; color: #9b84cc; letter-spacing: 0.05em; display: block; margin-bottom: 4px;">
                Área de Cliente
            </span>
            <h1 style="font-size: 1.8rem; font-weight: 900; color: #352b43; margin: 0; display: flex; align-items: center; gap: 10px;">
                <span>📦 Mis Pedidos</span>
            </h1>
        </div>
        <a href="{{ route('tienda.index') }}" class="btn-secondary btn-sm" style="border-radius: 999px; text-decoration: none;">
            <i class="fas fa-store me-1"></i> Ir al Catálogo
        </a>
    </div>

    @if($pedidos->isEmpty())
        <!-- Empty State Kawaii -->
        <div class="card-kawaii" style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(124, 92, 191, 0.08); padding: 4rem 2rem; text-align: center; border: 1.5px solid #ebdffc;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #faf4ff; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #7c5cbf; font-size: 2.2rem;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h3 style="font-size: 1.35rem; font-weight: 800; color: #352b43; margin-bottom: 0.5rem;">
                Aún no tienes pedidos registrados
            </h3>
            <p style="color: #796693; font-size: 0.95rem; max-width: 440px; margin: 0 auto 1.75rem;">
                ¡Descubre nuestros hermosos artículos de papelería, libretas y accesorios pastel para estrenar tu primer pedido! ✨
            </p>
            <a href="{{ route('tienda.index') }}" class="btn-primary" style="padding: 10px 24px; border-radius: 999px; text-decoration: none; font-weight: 800;">
                <i class="fas fa-heart me-1"></i> Explorar Tienda Juno's
            </a>
        </div>
    @else
        <!-- Lista de Tarjetas de Pedidos -->
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            @foreach($pedidos as $pedido)
            <div class="card-kawaii" style="background: #ffffff; border-radius: 20px; box-shadow: 0 6px 20px rgba(124, 92, 191, 0.06); padding: 1.5rem; border: 1.5px solid #f0e6fc; transition: all 0.2s ease;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid #f6f0fd; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 style="font-size: 1.15rem; font-weight: 800; color: #352b43; margin: 0;">
                                Pedido #{{ $pedido->id_pedido }}
                            </h3>
                            <span class="badge-kawaii" style="background: {{ $pedido->estado_color }}22; color: {{ $pedido->estado_color }}; border: 1px solid {{ $pedido->estado_color }}55; font-size: 0.75rem; padding: 3px 10px;">
                                {{ $pedido->estado_formateado }}
                            </span>
                        </div>
                        <span style="font-size: 0.8rem; color: #9b84cc; display: block; margin-top: 4px;">
                            {{ $pedido->creado_en ? $pedido->creado_en->format('d/m/Y \a \l\a\s H:i') : 'Reciente' }}
                        </span>
                    </div>

                    <div style="text-align: right;">
                        <span style="font-size: 0.78rem; color: #9b84cc; display: block;">Total del Pedido</span>
                        <strong style="font-size: 1.25rem; font-weight: 900; color: #7c5cbf;">
                            ${{ number_format($pedido->total, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <!-- Preview de Artículos -->
                <div style="background: #faf7fd; border-radius: 14px; padding: 1rem; margin-bottom: 1.25rem; border: 1px solid #ede5f8;">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @foreach($pedido->detalles->take(3) as $detalle)
                        @php
                            $thumb = $detalle->producto?->imagenes?->where('es_principal', 1)->first() ?? $detalle->producto?->imagenes?->first();
                        @endphp
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                @if($thumb)
                                    <img src="{{ asset($thumb->ruta_imagen) }}" 
                                         alt="{{ $detalle->producto?->nombre }}"
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px; flex-shrink: 0;"
                                         onerror="this.src='{{ asset('img/icon.png') }}'">
                                @else
                                    <div style="width: 40px; height: 40px; background: #ebdffc; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; color: #7c5cbf; flex-shrink: 0;">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                @endif
                                <div style="min-width: 0;">
                                    <span style="font-size: 0.88rem; font-weight: 700; color: #352b43; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $detalle->producto?->nombre ?? 'Producto' }}
                                    </span>
                                    <small style="color: #9b84cc;">Cantidad: {{ $detalle->cantidad }}</small>
                                </div>
                            </div>
                            <span style="font-size: 0.85rem; font-weight: 700; color: #695880; flex-shrink: 0;">
                                ${{ number_format($detalle->subtotal ?? ($detalle->precio_unitario * $detalle->cantidad), 0, ',', '.') }}
                            </span>
                        </div>
                        @endforeach

                        @if($pedido->detalles->count() > 3)
                        <div style="font-size: 0.8rem; color: #7c5cbf; font-weight: 700; padding-top: 4px; border-top: 1px dashed #e5d8f7;">
                            + {{ $pedido->detalles->count() - 3 }} artículo(s) más en este pedido
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Footer de la Tarjeta con Botón de Ver Detalles -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="font-size: 0.82rem; color: #796693;">
                        <i class="fas fa-map-marker-alt me-1" style="color: #e84393;"></i>
                        <span>Entrega: {{ Str::limit($pedido->direccion_entrega ?? 'Sin dirección especificada', 35) }}</span>
                    </div>

                    <a href="{{ route('pedidos.detalle', $pedido->id_pedido) }}" class="btn-primary btn-sm" style="border-radius: 999px; text-decoration: none; padding: 6px 16px; font-weight: 800;">
                        <span>Ver Detalle del Pedido</span> <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Paginación -->
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $pedidos->links() }}
        </div>
    @endif
</div>
@endsection
