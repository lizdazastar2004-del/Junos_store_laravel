@extends('layouts.app')

@section('title', "Acceso Restringido - Juno's Store")

@section('content')
<div style="max-width: 620px; margin: 4rem auto; text-align: center; padding: 0 1rem;">
    <div class="card-kawaii" style="background: #ffffff; border-radius: 28px; box-shadow: 0 12px 35px rgba(124, 92, 191, 0.08); padding: 3.5rem 2rem; border: 1.5px solid #fed1df;">
        <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem;">🔒👑</div>
        
        <span class="badge-kawaii badge-kawaii-pink" style="font-size: 0.85rem; padding: 4px 14px; margin-bottom: 1rem;">
            Acceso 403 Prohibido
        </span>
        
        <h1 style="font-size: 1.85rem; font-weight: 900; color: #352b43; margin-top: 0.5rem; margin-bottom: 0.75rem;">
            Área Exclusiva de Administradores
        </h1>
        
        <p style="color: #796693; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
            Esta sección requiere credenciales con rol administrativo de Juno's Store. 
            Si eres cliente, puedes navegar por tu catálogo y ver tus pedidos.
        </p>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('tienda.index') }}" class="btn-primary" style="padding: 10px 24px; border-radius: 999px; text-decoration: none; font-weight: 800;">
                <i class="fas fa-store me-1"></i> Ir a la Tienda
            </a>
            @auth
            <a href="{{ route('pedidos.historial') }}" class="btn-secondary" style="padding: 10px 24px; border-radius: 999px; text-decoration: none; font-weight: 800;">
                <i class="fas fa-box-open me-1"></i> Ver Mis Pedidos
            </a>
            @else
            <a href="{{ route('login') }}" class="btn-secondary" style="padding: 10px 24px; border-radius: 999px; text-decoration: none; font-weight: 800;">
                <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión Admin
            </a>
            @endauth
        </div>
    </div>
</div>
@endsection
