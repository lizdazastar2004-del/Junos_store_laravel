<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Juno's Store")</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icon.png') }}">

    <!-- Google Fonts: Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & FontAwesome 6 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos kawaii (Definen la identidad pastel de Juno's Store) -->
    <link rel="stylesheet" href="{{ asset('css/kawaii.css') }}">
    
    @stack('styles')
</head>
<body class="kawaii-bg">

<!-- ══════════════════ HEADER ══════════════════ -->
<header class="header-kawaii">
    <div class="header-inner">
        <!-- Logo -->
        <a href="{{ route('tienda.index') }}" class="header-logo">
            <img src="{{ asset('img/icon.png') }}" alt="Juno's Store logo"
                 style="width:38px;height:38px;object-fit:contain;border-radius:10px;">
            <span>Juno's Store</span>
        </a>

        <!-- Navegación principal -->
        <nav class="header-nav">
            <a href="{{ route('tienda.index') }}" class="nav-link {{ request()->routeIs('tienda.index') && !request()->filled('categoria') && !request()->filled('q') ? 'active' : '' }}">Inicio</a>
            <a href="{{ route('tienda.index') }}#productos" class="nav-link">Catálogo</a>
            <a href="{{ route('tienda.index') }}#categorias" class="nav-link">Categorías</a>
            @auth
                <a href="{{ route('cliente.dashboard') }}" class="nav-link {{ request()->routeIs('cliente.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-heart me-1" style="font-size: 0.85rem; color: #e84393;"></i> Mi Cuenta
                </a>
                <a href="{{ route('pedidos.historial') }}" class="nav-link {{ request()->routeIs('pedidos.*') ? 'active' : '' }}">
                    <i class="fas fa-box-open me-1" style="font-size: 0.85rem;"></i> Mis Pedidos
                </a>
            @endauth
        </nav>

        <!-- Acciones de usuario -->
        <div class="header-actions">
            @php
                $headerCartCount = 0;
                if (auth()->check()) {
                    $userCarrito = \App\Models\Carrito::where('id_usuario', auth()->id())->whereIn('estado', ['activo', 'ACTIVO'])->first();
                    $headerCartCount = $userCarrito ? \App\Models\CarritoDetalle::where('id_carrito', $userCarrito->id_carrito)->sum('cantidad') : session('carrito_count', 0);
                } else {
                    $headerCartCount = session('carrito_count', 0);
                }
            @endphp

            <!-- Ícono del carrito (SIEMPRE VISIBLE) -->
            <a href="{{ route('carrito.index') }}" class="btn-icon-cart" title="Mi Carrito de Compras">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge" id="cart-count" style="{{ $headerCartCount > 0 ? '' : 'display: none;' }}">
                    {{ $headerCartCount }}
                </span>
            </a>

            @auth
                <!-- Menú dropdown si hay sesión activa -->
                <div class="user-dropdown">
                    <button class="btn-user-menu" id="userMenuBtn" type="button" aria-expanded="false" aria-haspopup="true">
                        <i class="fas fa-paw" style="color: #9b84cc;"></i>
                        <span>{{ auth()->user()->nombre }}</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem; opacity: 0.7;"></i>
                    </button>
                    <ul class="dropdown-menu" id="userMenuDropdown" role="menu">
                        <li>
                            <a href="{{ route('cliente.dashboard') }}" role="menuitem">
                                <i class="fas fa-heart me-2" style="color: #e84393;"></i> Mi Cuenta (Portal)
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pedidos.historial') }}" role="menuitem">
                                <i class="fas fa-box-open me-2" style="color: #7c5cbf;"></i> Mis Pedidos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('carrito.index') }}" role="menuitem">
                                <i class="fas fa-shopping-cart me-2" style="color: #e84393;"></i> Mi Carrito
                            </a>
                        </li>
                        <li class="dropdown-divider"></li>
                        @if(auth()->user()->isAdmin())
                            <li>
                                <a href="{{ route('admin.dashboard') }}" role="menuitem">
                                    <i class="fas fa-tachometer-alt me-2" style="color: #7c5cbf;"></i> Panel Admin
                                </a>
                            </li>
                            <li class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" role="menuitem" style="color: #c2335f; width: 100%; border: none; background: none; text-align: left; padding: 8px 12px; font-weight: 700; cursor: pointer;">
                                    <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

                <!-- Botón de Salida Rápida Directa -->
                <form action="{{ route('logout') }}" method="POST" class="d-inline" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn-logout-direct" title="Cerrar sesión inmediatamente">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="d-none d-md-inline">Salir</span>
                    </button>
                </form>
            @else
                <!-- Botón login si no hay sesión -->
                <a href="{{ route('login') }}" class="btn-primary btn-sm">
                    <i class="fas fa-user me-1"></i> Iniciar sesión
                </a>
            @endguest
        </div>
    </div>
</header>

<!-- ══════════════════ MENSAJES FLASH ══════════════════ -->
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

<!-- ══════════════════ CONTENIDO PRINCIPAL ══════════════════ -->
<main class="main-content">
    @yield('content')
</main>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer class="footer-kawaii">
    <div class="footer-inner">

        <!-- Logo -->
        <a href="{{ route('tienda.index') }}" class="footer-brand">
            <img src="{{ asset('img/icon.png') }}"
                 alt="Juno's Store"
                 style="width:36px;height:36px;object-fit:contain;border-radius:10px;">
            <span>Juno's Store</span>
        </a>

        <!-- Tagline -->
        <p class="footer-copy">
            &copy; {{ date('Y') }} Juno's Store &mdash; Papelería & Accesorios Pastel hecha con cariño
        </p>

        <!-- Nav -->
        <nav class="footer-nav" aria-label="Navegación del pie de página">
            <a href="{{ route('tienda.index') }}">Inicio</a>
            <a href="{{ route('tienda.index') }}#productos">Catálogo</a>
            <a href="{{ route('tienda.index') }}#categorias">Categorías</a>
            @guest
                <a href="{{ route('login') }}">Mi cuenta</a>
            @else
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
                @endif
            @endguest
        </nav>

    </div>
</footer>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Dropdown de usuario (vanilla JS infalible) -->
<script>
(function () {
    const btn = document.getElementById('userMenuBtn') || document.querySelector('.btn-user-menu');
    if (!btn) return;
    const menu = document.getElementById('userMenuDropdown') || btn.nextElementSibling;
    if (!menu) return;

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = menu.classList.contains('show') || menu.classList.contains('open');
        btn.setAttribute('aria-expanded', String(!isOpen));
        if (isOpen) {
            menu.classList.remove('show', 'open');
        } else {
            menu.classList.add('show', 'open');
        }
    });

    document.addEventListener('click', function (e) {
        if (!btn.contains(e.target) && !menu.contains(e.target)) {
            btn.setAttribute('aria-expanded', 'false');
            menu.classList.remove('show', 'open');
        }
    });
})();
</script>

@stack('scripts')

</body>
</html>
