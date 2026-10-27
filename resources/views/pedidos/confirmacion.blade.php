@extends('layouts.app')

@section('title', '¡Pedido Confirmado! - Juno\'s Store')

@section('content')
<div style="max-width:640px;margin:3rem auto;text-align:center;">
    <div style="background:#fff;border-radius:20px;box-shadow:0 8px 30px rgba(201,184,232,.4);padding:3rem 2rem;">
        <div style="font-size:4rem;margin-bottom:1rem;">🎉</div>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:.5rem;">
            ¡Pedido confirmado!
        </h1>
        <p style="color:var(--texto-claro);margin-bottom:1.5rem;">
            Tu pedido <strong>#{{ $pedido->id_pedido }}</strong> ha sido registrado correctamente.<br>
            Recibirás un correo de confirmación en breve. 💌
        </p>

        <div style="background:#f8f5ff;border-radius:var(--radio);padding:1.25rem;text-align:left;margin-bottom:1.5rem;">
            <h3 style="font-size:1rem;font-weight:800;color:var(--lavanda-dark);margin-bottom:.75rem;">Resumen del Pedido</h3>
            
            @foreach($pedido->detalles as $detalle)
            <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.3rem;">
                <span>{{ $detalle->producto->nombre }} ×{{ $detalle->cantidad }}</span>
                <span>${{ number_format($detalle->subtotal, 0, ',', '.') }}</span>
            </div>
            @endforeach
            
            <hr style="border:none;border-top:1px solid var(--lila);margin:.75rem 0;">
            
            <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.3rem;">
                <span>Subtotal</span>
                <span>${{ number_format($pedido->subtotal, 0, ',', '.') }}</span>
            </div>
            
            @if($pedido->descuento > 0)
            <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.3rem;color:#27ae60;">
                <span>Descuento</span>
                <span>−${{ number_format($pedido->descuento, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:.3rem;">
                <span>Envío</span>
                <span>
                    @if($pedido->costo_envio > 0)
                        ${{ number_format($pedido->costo_envio, 0, ',', '.') }}
                    @else
                        <span style="color:#27ae60;">Gratis</span>
                    @endif
                </span>
            </div>
            
            <hr style="border:none;border-top:1px solid var(--lila);margin:.75rem 0;">
            
            <div style="display:flex;justify-content:space-between;font-weight:800;color:var(--lavanda-dark);">
                <span>Total</span>
                <span>${{ number_format($pedido->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div style="background:#fff5e6;border-left:4px solid #f39c12;padding:1rem;border-radius:8px;margin-bottom:1.5rem;text-align:left;">
            <p style="font-size:.9rem;margin:0;color:#856404;">
                <strong>📍 Dirección de entrega:</strong><br>
                {{ $pedido->direccion_entrega }}
            </p>
            @if($pedido->notas)
            <p style="font-size:.9rem;margin-top:.75rem;margin-bottom:0;color:#856404;">
                <strong>📝 Notas:</strong> {{ $pedido->notas }}
            </p>
            @endif
        </div>

        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('pedidos.detalle', ['id' => $pedido->id_pedido]) }}" class="btn-primary">
                Ver mi pedido
            </a>
            <a href="{{ route('tienda.catalogo') }}" class="btn-secondary">
                Seguir comprando
            </a>
        </div>
    </div>
</div>
@endsection
