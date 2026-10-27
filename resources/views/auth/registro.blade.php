@extends('layouts.app')

@section('title', "Crear Cuenta - Juno's Store")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<style>
.auth-container.register-mode {
    width: min(1040px, calc(100% - 2.5rem));
    min-height: 600px;
}
.auth-container.register-mode .auth-form-panel form,
.auth-container.register-mode .auth-form-panel > .auth-or,
.auth-container.register-mode .auth-form-panel > .btn-auth-secondary,
.auth-container.register-mode .auth-form-panel > .auth-alert {
    width: min(100%, 460px);
}
.auth-container.register-mode .auth-form-panel h1 {
    width: min(100%, 460px);
}
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}
@media (max-width: 600px) {
    .form-grid-2 {
        grid-template-columns: 1fr;
        gap: 0;
    }
}
.auth-input-wrap {
    position: relative;
    margin-bottom: 0.85rem;
}
.auth-input-wrap .auth-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9b84cc;
    font-size: 0.95rem;
    pointer-events: none;
    z-index: 2;
}
.auth-input-wrap input {
    width: 100%;
    border: 1.5px solid #d8cce8;
    border-radius: 999px;
    padding: 10px 42px 10px 42px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    color: #352b43;
    background: #faf8ff;
    outline: none;
    transition: all 0.2s ease;
}
.auth-input-wrap input:focus {
    border-color: #9b84cc;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(155, 132, 204, 0.2);
}
.auth-input-wrap input.is-invalid {
    border-color: #e57399;
    box-shadow: 0 0 0 3px rgba(229, 115, 153, 0.18);
}
.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #b7a6cb;
    font-size: 0.95rem;
    padding: 4px;
    z-index: 2;
}
.toggle-password:hover {
    color: #7c5cbf;
}
.benefit-list {
    list-style: none;
    padding: 0;
    margin: 1.25rem 0 0;
    text-align: left;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    position: relative;
    z-index: 2;
}
.benefit-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    font-size: 0.86rem;
    font-weight: 700;
    color: #5c3d8f;
    background: rgba(255, 255, 255, 0.5);
    padding: 7px 14px;
    border-radius: 12px;
}
.benefit-item i {
    color: #8652ca;
    font-size: 0.95rem;
}
</style>
@endpush

@section('content')
<div class="auth-container register-mode">
    <!-- Panel Izquierdo: Formulario -->
    <div class="auth-form-panel">
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 0.35rem;">
            <img src="{{ asset('img/icon.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;">
            <span style="font-weight: 800; color: #7c5cbf; font-size: 1.05rem;">Juno's Store</span>
        </div>

        <h1 style="margin-bottom: 0.5rem;">Crear Cuenta</h1>
        <p style="color: #8a7a9a; font-size: 0.88rem; font-weight: 600; margin-bottom: 1.25rem; text-align: center;">
            Únete y descubre nuestras colecciones pastel
        </p>

        @if($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('registro') }}">
            @csrf

            <!-- Nombre y Apellido -->
            <div class="form-grid-2">
                <div class="auth-input-wrap">
                    <i class="fas fa-user auth-icon"></i>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre') }}" 
                           class="{{ $errors->has('nombre') ? 'is-invalid' : '' }}"
                           placeholder="Nombre *" 
                           required 
                           autofocus>
                </div>
                <div class="auth-input-wrap">
                    <i class="fas fa-user auth-icon"></i>
                    <input type="text" 
                           id="apellido" 
                           name="apellido" 
                           value="{{ old('apellido') }}" 
                           class="{{ $errors->has('apellido') ? 'is-invalid' : '' }}"
                           placeholder="Apellido *" 
                           required>
                </div>
            </div>

            <!-- Correo Electrónico -->
            <div class="auth-input-wrap">
                <i class="fas fa-envelope auth-icon"></i>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                       placeholder="Correo electrónico *" 
                       required>
            </div>

            <!-- Teléfono -->
            <div class="auth-input-wrap">
                <i class="fas fa-phone auth-icon"></i>
                <input type="text" 
                       id="telefono" 
                       name="telefono" 
                       value="{{ old('telefono') }}" 
                       class="{{ $errors->has('telefono') ? 'is-invalid' : '' }}"
                       placeholder="Teléfono (opcional)">
            </div>

            <!-- Contraseñas -->
            <div class="form-grid-2">
                <div class="auth-input-wrap">
                    <i class="fas fa-lock auth-icon"></i>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Contraseña *" 
                           required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)" title="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="auth-input-wrap">
                    <i class="fas fa-check-double auth-icon"></i>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           placeholder="Confirmar *" 
                           required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)" title="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Botón Registrarme -->
            <button type="submit" class="btn-auth-primary" style="margin-top: 0.5rem;">
                <i class="fas fa-heart me-1"></i> Registrarme ahora
            </button>
        </form>

        <div class="auth-or">o</div>

        <!-- Botón Volver a Login -->
        <a href="{{ route('login') }}" class="btn-auth-secondary">
            <i class="fas fa-sign-in-alt me-1"></i> ¿Ya tienes cuenta? Inicia sesión
        </a>
    </div>

    <!-- Panel Derecho: Decoración Pastel Kawaii -->
    <div class="auth-decoration-panel">
        <img src="{{ asset('img/icon.png') }}" class="deco-bg-icon top-right" alt="" aria-hidden="true">
        <img src="{{ asset('img/icon.png') }}" class="deco-bg-icon bottom-left" alt="" aria-hidden="true">

        <h2>¡Sé parte de<br>Juno's Store!</h2>
        
        <img src="{{ asset('img/icon.png') }}" class="deco-mascot" alt="Mascota Juno's Store" style="width: 180px;">

        <ul class="benefit-list">
            <li class="benefit-item">
                <i class="fas fa-sparkles"></i>
                <span>Descuentos y novedades exclusivas</span>
            </li>
            <li class="benefit-item">
                <i class="fas fa-box-heart"></i>
                <span>Historial de pedidos rápido y fácil</span>
            </li>
            <li class="benefit-item">
                <i class="fas fa-paw"></i>
                <span>Catálogo kawaii siempre actualizado</span>
            </li>
        </ul>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush

