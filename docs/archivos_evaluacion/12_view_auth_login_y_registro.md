# 🔐 Vistas de Autenticación: `login.blade.php` y `registro.blade.php`

## 1. Ficha Técnica

* **Ubicaciones:**
  * `resources/views/auth/login.blade.php`
  * `resources/views/auth/registro.blade.php`
* **Tipo:** Vistas Blade (extienden de `layouts.app`).
* **Capa:** Vista / Autenticación (V en MVC).
* **Propósito:** Ofrecer interfaces de acceso y creación de cuenta consistentes con la estética pastel de Juno's Store. Utilizan una tarjeta dividida en dos paneles (*split-card*): formulario a la izquierda con iconos integrados, alternador interactivo para ver/ocultar contraseñas, directiva `@csrf` obligatoria y banner decorativo kawaii a la derecha con la mascota oficial.

---

## 2. Mapa de Conexiones

```mermaid
graph LR
    subgraph "Flujo de Inicio de Sesión"
        RouteLoginGet[GET /login] --> ViewLogin[auth/login.blade.php]
        ViewLogin -->|POST /login + @csrf| AuthCtrlLogin[AuthController@login]
        AuthCtrlLogin -->|Si falla: withErrors + withInput| ViewLogin
    end

    subgraph "Flujo de Registro de Nuevos Clientes"
        RouteRegGet[GET /registro] --> ViewRegistro[auth/registro.blade.php]
        ViewRegistro -->|POST /registro + @csrf| AuthCtrlReg[AuthController@registro]
        AuthCtrlReg -->|Si falla: withErrors + withInput| ViewRegistro
    end
```

---

## 3. Código Fuente y Explicación Detallada: `login.blade.php`

```blade
@extends('layouts.app')

@section('title', "Iniciar Sesión - Juno's Store")

@push('styles')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="auth-container">
    <!-- Panel Izquierdo: Formulario -->
    <div class="auth-form-panel">
        <div class="auth-brand-badge">
            <img src="{{ asset('img/icon.png') }}" alt="" style="width: 32px; height: 32px;">
            <span>Juno's Store</span>
        </div>

        <h1>Iniciar Sesión</h1>

        <!-- Alerta de Errores de Validación -->
        @if($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Campo Correo Electrónico -->
            <div class="auth-input-wrap">
                <i class="fas fa-envelope auth-icon"></i>
                <input type="email" id="email" name="email" 
                       value="{{ old('email') }}" 
                       class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                       placeholder="correo@ejemplo.com" required autofocus>
            </div>

            <!-- Campo Contraseña con Alternador Ver/Ocultar -->
            <div class="auth-input-wrap">
                <i class="fas fa-lock auth-icon"></i>
                <input type="password" id="password" name="password" 
                       class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                       placeholder="Tu contraseña secreta" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <!-- Checkbox Recordarme -->
            <div class="form-check-kawaii">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Mantener sesión iniciada</label>
            </div>

            <button type="submit" class="btn-auth-submit">
                Entrar a mi cuenta <i class="fas fa-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="auth-footer-link">
            ¿No tienes cuenta todavía? <a href="{{ route('registro') }}">Regístrate gratis aquí</a>
        </div>
    </div>

    <!-- Panel Derecho: Ilustración Kawaii -->
    <div class="auth-banner-panel">
        <div class="banner-circle">
            <img src="{{ asset('img/icon.png') }}" alt="Mascota Juno">
        </div>
        <h2>¡Nos alegra verte! ✨</h2>
        <p>Tu papelería y peluches favoritos esperan por ti en tu rincón pastel.</p>
    </div>
</div>
@endsection
```

### Características de Seguridad y UX:
1. **Protección CSRF (`@csrf`):** Genera un `<input type="hidden" name="_token" value="...">` criptográfico que protege el formulario contra falsificación de peticiones en sitios cruzados.
2. **Preservación de Entrada (`old('email')`):** Si el usuario se equivoca al ingresar la contraseña, la función `old('email')` recupera el correo electrónico previamente escrito, evitando que tenga que digitarlo nuevamente.
3. **Indicador Visual de Error (`.is-invalid`):** Si Laravel retorna errores para un campo específico, la clase `.is-invalid` colorea el borde del input en tono frambuesa pastel (`#e57399`) con sombra de advertencia.
4. **Visor de Contraseña Seguro:**
   ```javascript
   function togglePasswordVisibility(fieldId, btn) {
       const field = document.getElementById(fieldId);
       const icon = btn.querySelector('i');
       if (field.type === 'password') {
           field.type = 'text';
           icon.classList.replace('fa-eye', 'fa-eye-slash');
       } else {
           field.type = 'password';
           icon.classList.replace('fa-eye-slash', 'fa-eye');
       }
   }
   ```

---

## 4. Estructura de `registro.blade.php`

En `registro.blade.php`, el formulario se amplía de manera simétrica para capturar:
* `nombre` y `apellido` (en dos columnas paralelas mediante el grid de Bootstrap `row g-2`).
* `email` con validación en tiempo real.
* `telefono` (opcional).
* `password` y `password_confirmation` (activan la regla de Laravel `'password' => 'confirmed'`).
* Selector de confirmación de términos de servicio.

---

## 5. Preguntas Frecuentes para Evaluación

1. **¿Qué sucede si se omite la directiva `@csrf` en el formulario?**
   * *Respuesta:* Laravel rechaza de inmediato la petición HTTP POST arrojando una excepción HTTP 419 (*Page Expired / Token Mismatch*), protegiendo la sesión del usuario contra ataques externos.
2. **¿Cómo funciona el `ViewErrorBag` (`$errors`) en Blade?**
   * *Respuesta:* El middleware de sesión `ShareErrorsFromSession` inyecta automáticamente la variable `$errors` en todas las vistas Blade. Métodos como `$errors->any()`, `$errors->first()` y `$errors->has('email')` permiten consultar si la validación del controlador falló y renderizar mensajes contextuales.
3. **¿Por qué se corrigió `<?= BASE_URL ?>` en los archivos CSS de autenticación?**
   * *Respuesta:* En el proyecto PHP anterior se utilizaban etiquetas cortas de PHP dentro de las hojas CSS (`url('<?= BASE_URL ?>/img/background.jpeg')`), lo cual no es interpretado por el servidor web en Laravel al servir archivos estáticos. Se reemplazó por rutas absolutas limpias `/img/background.jpeg`.
