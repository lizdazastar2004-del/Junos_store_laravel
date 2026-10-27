# 📐 Archivo: `resources/views/layouts/app.blade.php` — Layout Maestro Público

## 1. Ficha Técnica

* **Ubicación:** `resources/views/layouts/app.blade.php`
* **Tipo:** Plantilla Maestra Blade (Master Layout).
* **Capa:** Vista / Presentación (V en MVC).
* **Propósito:** Proveer el esqueleto HTML5 unificado para todas las vistas públicas de Juno's Store (Catálogo, Detalle, Login, Registro). Centraliza la carga de hojas de estilo, fuentes de Google Fonts (Nunito), favicons, barra de navegación sticky superior, sistema de alertas flash pastel, inyección de contenido con `@yield('content')` y pie de página kawaii.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    LayoutApp[resources/views/layouts/app.blade.php]

    subgraph "Recursos Centralizados"
        LayoutApp -->|Google Fonts| NunitoFont[Fuente Nunito 400-900]
        LayoutApp -->|Bootstrap 5.3 CDN| BS5[Grid y Utilidades CSS]
        LayoutApp -->|FontAwesome 6 CDN| FA6[Iconografía SVG]
        LayoutApp -->|CSS Propio| KawaiiCSS[public/css/kawaii.css]
    end

    subgraph "Vistas Hijas que Extienden (@extends('layouts.app'))"
        ViewIndex[tienda/index.blade.php] -->|@section('content')| LayoutApp
        ViewDetalle[tienda/detalle.blade.php] -->|@section('content')| LayoutApp
        ViewLogin[auth/login.blade.php] -->|@section('content')| LayoutApp
        ViewRegistro[auth/registro.blade.php] -->|@section('content')| LayoutApp
    end

    subgraph "Puntos de Inyección Blade"
        LayoutApp -->|@yield('title')| HTMLTitle[Título Dinámico de Pestaña]
        LayoutApp -->|@stack('styles')| CustomStyles[CSS específico por vista]
        LayoutApp -->|@yield('content')| MainBody[Cuerpo Central de la Página]
        LayoutApp -->|@stack('scripts')| CustomScripts[JavaScript por vista]
    end
```

---

## 3. Código Fuente y Explicación Detallada por Secciones

### A. Cabecera y Recursos Globales (`<head>`)
```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Juno's Store")</title>

    <!-- Google Fonts: Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & FontAwesome 6 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos kawaii (Definen la identidad pastel de Juno's Store) -->
    <link rel="stylesheet" href="{{ asset('css/kawaii.css') }}">
    
    @stack('styles')
</head>
```
* **Orden de Carga Crítico:** Se incluye Bootstrap 5.3 CDN **antes** de `kawaii.css`. Esto garantiza que los estilos pastel de Juno's Store sobrescriban tipografías, colores primarios, radios de bordes y sombras por defecto de Bootstrap, manteniendo la armonía estética.
* **`@stack('styles')`:** Permite que vistas hijas (como `login.blade.php` o `index.blade.php`) inyecten sus propios archivos CSS complementarios (`home.css`, `login.css`) sin sobrecargar las demás páginas.

---

### B. Header de Navegación Kawaii con Menú Dinámico
```blade
<header class="header-kawaii">
    <div class="header-inner">
        <!-- Logo -->
        <a href="{{ route('tienda.index') }}" class="header-logo">
            <img src="{{ asset('img/icon.png') }}" alt="Juno's Store logo">
            <span>Juno's Store</span>
        </a>

        <!-- Navegación principal -->
        <nav class="header-nav">
            <a href="{{ route('tienda.index') }}" class="nav-link {{ request()->routeIs('tienda.index') && !request()->filled('categoria') && !request()->filled('q') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('tienda.index') }}#productos" class="nav-link">Catálogo</a>
            <a href="{{ route('tienda.index') }}#categorias" class="nav-link">Categorías</a>
        </nav>

        <!-- Acciones de usuario con estado de sesión -->
        <div class="header-actions">
            @auth
                <div class="user-dropdown">
                    <button class="btn-user-menu">
                        <i class="fas fa-paw"></i> {{ auth()->user()->nombre }} ▾
                    </button>
                    <ul class="dropdown-menu">
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt me-2"></i> Panel Admin
                                </a>
                            </li>
                        @endif
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"><i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn-primary btn-sm">
                    <i class="fas fa-user me-1"></i> Iniciar sesión
                </a>
            @endguest
        </div>
    </div>
</header>
```
* **Directivas `@auth` y `@guest`:** Renderizado condicional según el estado de la sesión. Si el usuario está autenticado, muestra su nombre con icono de huella kawaii y menú desplegable; si es administrador, incluye el acceso directo a `/admin/dashboard`; si es un visitante anónimo (`@else` / `@guest`), muestra el botón de iniciar sesión.

---

### C. Alertas Flash Centralizadas
```blade
@if(session('success'))
<div class="alert-kawaii alert-kawaii-success" role="alert">
    <i class="fas fa-heart alert-kawaii-icon"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="alert-kawaii alert-kawaii-error" role="alert">
    <i class="fas fa-exclamation-circle alert-kawaii-icon"></i>
    <span>{{ session('error') }}</span>
</div>
@endif
```
* Al estar ubicadas en el layout maestro, cualquier controlador que ejecute `return redirect()->with('success', '...')` mostrará automáticamente una tarjeta flotante estilizada en tonos pastel con animación suave.

---

### D. Inyección de Contenido y Scripts
```blade
<main class="main-content">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
```
* **`@yield('content')`:** Punto donde se renderiza el código específico de cada vista hija.
* **`@stack('scripts')`:** Pila de scripts donde vistas particulares pueden añadir código JavaScript (como el selector de imágenes de la galería o el alternador de visibilidad de contraseñas).

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Cuál es la diferencia entre `@yield` y `@stack` en Blade?**
   * *Respuesta:* `@yield('nombre')` se utiliza para inyectar una única sección de contenido reemplazable (como `@section('content')`). `@stack('nombre')` permite empujar (*push*) múltiples fragmentos de código repetidamente desde varios componentes o vistas mediante `@push('scripts')`.
2. **¿Por qué el enlace de "Cerrar sesión" se envía mediante un `<form method="POST">` con `@csrf` en vez de un enlace `<a href="/logout">`?**
   * *Respuesta:* Por seguridad contra ataques **CSRF** (*Cross-Site Request Forgery*). Si el logout fuera por `GET`, un sitio malicioso podría forzar el cierre de sesión de la víctima simplemente insertando `<img src="http://tienda.com/logout">`.
3. **¿Cómo se asegura que las clases de Bootstrap no rompan la estética kawaii?**
   * *Respuesta:* Bootstrap se incluye primero, y luego `kawaii.css` redefine las variables CSS (`--primary`, `--radius-lg`, `--shadow-kawaii`), las fuentes (`font-family: 'Nunito'`) y los estilos de botones (`.btn-primary`), manteniendo el soporte de cuadrículas (`row`, `col-*`) con apariencia personalizada.
