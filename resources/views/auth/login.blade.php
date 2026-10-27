@extends('layouts.app')

@section('title', "Iniciar Sesión - Juno's Store")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<style>
.auth-input-wrap {
    position: relative;
    margin-bottom: 1.1rem;
}
.auth-input-wrap .auth-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9b84cc;
    font-size: 1rem;
    pointer-events: none;
    z-index: 2;
}
.auth-input-wrap input {
    width: 100%;
    border: 1.5px solid #d8cce8;
    border-radius: 999px;
    padding: 11px 44px 11px 42px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.92rem;
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
    font-size: 1rem;
    padding: 4px;
    z-index: 2;
}
.toggle-password:hover {
    color: #7c5cbf;
}
</style>
@endpush

@section('content')
<div class="auth-container">
    <!-- Panel Izquierdo: Formulario -->
    <div class="auth-form-panel">
        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 0.5rem;">
            <img src="{{ asset('img/icon.png') }}" alt="" style="width: 32px; height: 32px; object-fit: contain;">
            <span style="font-weight: 800; color: #7c5cbf; font-size: 1.05rem;">Juno's Store</span>
        </div>

        <h1>Iniciar Sesión</h1>

        @if($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Correo Electrónico -->
            <div class="auth-input-wrap">
                <i class="fas fa-envelope auth-icon"></i>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                       placeholder="Correo electrónico" 
                       required 
                       autofocus>
            </div>

            <!-- Contraseña -->
            <div class="auth-input-wrap">
                <i class="fas fa-lock auth-icon"></i>
                <input type="password" 
                       id="password" 
                       name="password" 
                       class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                       placeholder="Contraseña" 
                       required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)" title="Mostrar/ocultar contraseña">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <!-- Recordarme -->
            <div class="auth-remember">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Recordar mi sesión</label>
            </div>

            <!-- Botón Ingresar -->
            <button type="submit" class="btn-auth-primary">
                <i class="fas fa-sign-in-alt me-1"></i> Entrar a mi cuenta
            </button>
        </form>

        <div class="auth-or">o</div>

        <!-- Botón Registro -->
        <a href="{{ route('registro') }}" class="btn-auth-secondary">
            <i class="fas fa-user-plus me-1"></i> ¿No tienes cuenta? Regístrate
        </a>
    </div>

    <!-- Panel Derecho: Decoración Pastel Kawaii -->
    <div class="auth-decoration-panel">
        <img src="{{ asset('img/icon.png') }}" class="deco-bg-icon top-right" alt="" aria-hidden="true">
        <img src="{{ asset('img/icon.png') }}" class="deco-bg-icon bottom-left" alt="" aria-hidden="true">

        <h2>¡Bienvenida a<br>Juno's Store!</h2>
        
        <img src="{{ asset('img/icon.png') }}" class="deco-mascot" alt="Mascota Juno's Store">

        <p style="color: #5c3d8f; font-weight: 700; font-size: 0.92rem; margin-top: 1.25rem; max-width: 280px; position: relative; z-index: 2;">
            Encuentra papelería, peluches y accesorios con la más tierna estética pastel.
        </p>
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

