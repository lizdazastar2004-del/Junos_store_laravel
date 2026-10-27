@extends('layouts.app')

@section('title', 'Mi Carrito - Juno\'s Store')

@section('content')
<script>const BASE_URL = '{{ url('/') }}';</script>

<h1 style="font-size:1.6rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.5rem;">🛒 Mi Carrito</h1>

@if($items->isEmpty())
    <div style="text-align:center;padding:4rem 2rem;">
        <p style="font-size:3rem;margin-bottom:1rem;">🛒</p>
        <p style="font-size:1.2rem;color:var(--texto-claro);margin-bottom:1.5rem;">Tu carrito está vacío.</p>
        <a href="{{ route('tienda.catalogo') }}" class="btn-primary">Explorar el catálogo</a>
    </div>
@else
    <div style="display:grid;grid-template-columns:1fr 360px;gap:2rem;align-items:start;">

        <!-- ══ TABLA DE ÍTEMS ══ -->
        <div>
            <table class="table-kawaii" id="tabla-carrito">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th style="text-align:center;">Cantidad</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr id="fila-{{ $item->id_carrito_detalle }}">
                        <td style="display:flex;align-items:center;gap:.75rem;">
                            @if($item->producto->imagenes->isNotEmpty())
                                <img src="{{ asset($item->producto->imagenes->first()->url_imagen) }}" 
                                     alt="{{ $item->producto->nombre }}"
                                     style="width:56px;height:56px;object-fit:cover;border-radius:10px;" 
                                     loading="lazy">
                            @else
                                <div style="width:56px;height:56px;background:var(--lila);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">📷</div>
                            @endif
                            <div>
                                <strong>{{ $item->producto->nombre }}</strong><br>
                                <span style="font-size:.82rem;color:var(--texto-claro);">
                                    ${{ number_format($item->precio_unitario, 0, ',', '.') }} c/u
                                </span>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <input type="number" class="form-control cantidad-input"
                                   value="{{ $item->cantidad }}" min="1"
                                   data-item-id="{{ $item->id_carrito_detalle }}"
                                   style="width:72px;text-align:center;display:inline-block;">
                        </td>
                        <td style="text-align:right;font-weight:700;color:var(--lavanda-dark);"
                            id="subtotal-{{ $item->id_carrito_detalle }}">
                            ${{ number_format($item->calcularSubtotal(), 0, ',', '.') }}
                        </td>
                        <td>
                            <button class="btn-danger btn-sm eliminar-item" 
                                    data-item-id="{{ $item->id_carrito_detalle }}"
                                    type="button" aria-label="Eliminar">🗑️</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- ══ RESUMEN ══ -->
        <div style="background:#fff;border-radius:var(--radio);box-shadow:var(--sombra);padding:1.5rem;position:sticky;top:90px;">
            <h2 style="font-size:1.1rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:1.25rem;">
                Resumen del pedido
            </h2>

            <div style="display:flex;justify-content:space-between;margin-bottom:.6rem;font-size:.95rem;">
                <span>Subtotal</span>
                <span id="res-subtotal">${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            @if($descuento > 0 && $codigoAplicado)
            <div style="display:flex;justify-content:space-between;margin-bottom:.6rem;font-size:.95rem;color:#27ae60;">
                <span>Descuento ({{ $codigoAplicado->codigo }})</span>
                <span id="res-descuento">−${{ number_format($descuento, 0, ',', '.') }}</span>
            </div>
            @else
            <div style="display:none;justify-content:space-between;margin-bottom:.6rem;font-size:.95rem;color:#27ae60;" 
                 id="res-descuento-fila">
                <span id="res-desc-label">Descuento</span>
                <span id="res-descuento">−$0</span>
            </div>
            @endif

            <div style="display:flex;justify-content:space-between;margin-bottom:.6rem;font-size:.95rem;">
                <span>Envío</span>
                <span id="res-envio">
                    @if($envio > 0)
                        ${{ number_format($envio, 0, ',', '.') }}
                    @else
                        <span style="color:#27ae60;">¡Envío gratis!</span>
                    @endif
                </span>
            </div>

            <hr style="border:none;border-top:1px solid var(--lila);margin:.75rem 0;">
            <div style="display:flex;justify-content:space-between;font-size:1.15rem;font-weight:800;color:var(--lavanda-dark);">
                <span>Total</span>
                <span id="res-total">${{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <!-- Código de descuento -->
            @if(!$codigoAplicado)
            <div style="margin-top:1.25rem;" id="bloque-codigo">
                <label style="font-size:.85rem;font-weight:700;display:block;margin-bottom:.4rem;">
                    Código de descuento
                </label>
                <div style="display:flex;gap:.5rem;">
                    <input type="text" id="input-codigo" class="form-control" 
                           placeholder="Ingresa tu código" style="flex:1;">
                    <button type="button" id="btn-aplicar-codigo" class="btn-secondary btn-sm">Aplicar</button>
                </div>
                <p id="msg-codigo" style="font-size:.82rem;margin-top:.35rem;"></p>
            </div>
            @else
            <p style="font-size:.85rem;color:#27ae60;margin-top:1rem;">
                ✅ Código <strong>{{ $codigoAplicado->codigo }}</strong> aplicado.
            </p>
            @endif

            <a href="{{ url('/checkout') }}" class="btn-primary" 
               style="width:100%;justify-content:center;margin-top:1.25rem;display:flex;">
                ✅ Confirmar Pedido
            </a>
            <a href="{{ route('tienda.catalogo') }}" class="btn-secondary" 
               style="width:100%;justify-content:center;margin-top:.5rem;display:flex;">
                ← Seguir comprando
            </a>
        </div>

    </div>
@endif

<script src="{{ asset('js/carrito.js') }}"></script>

<!-- CSRF Token para peticiones AJAX -->
<script>
    // Añadir token CSRF a todas las peticiones AJAX
    window.csrfToken = '{{ csrf_token() }}';
</script>
@endsection
