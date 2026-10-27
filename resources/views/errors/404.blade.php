@extends('layouts.app')

@section('title', "Página No Encontrada - Juno's Store")

@section('content')
<div style="max-width: 620px; margin: 4rem auto; text-align: center; padding: 0 1rem;">
    <div class="card-kawaii" style="background: #ffffff; border-radius: 28px; box-shadow: 0 12px 35px rgba(124, 92, 191, 0.08); padding: 3.5rem 2rem; border: 1.5px solid #ebdffc;">
        <div style="font-size: 4rem; line-height: 1; margin-bottom: 1rem;">🌸🐾</div>
        
        <span class="badge-kawaii badge-kawaii-pink" style="font-size: 0.85rem; padding: 4px 14px; margin-bottom: 1rem;">
            Error 404
        </span>
        
        <h1 style="font-size: 1.85rem; font-weight: 900; color: #352b43; margin-top: 0.5rem; margin-bottom: 0.75rem;">
            ¡Ups! Página extraviada
        </h1>
        
        <p style="color: #796693; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
            El enlace que intentas abrir no existe o cambió de lugar. No te preocupes, 
            puedes regresar al catálogo y seguir descubriendo nuestras cositas kawaii.
        </p>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('tienda.index') }}" class="btn-primary" style="padding: 10px 24px; border-radius: 999px; text-decoration: none; font-weight: 800;">
                <i class="fas fa-home me-1"></i> Ir al Inicio
            </a>
            <a href="{{ route('tienda.index') }}#productos" class="btn-secondary" style="padding: 10px 24px; border-radius: 999px; text-decoration: none; font-weight: 800;">
                <i class="fas fa-store me-1"></i> Explorar Catálogo
            </a>
        </div>
    </div>
</div>
@endsection
