<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Panel Admin - Juno's Store")</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icon.png') }}">

    <!-- Google Fonts: Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Estilos Kawaii Base & Admin Kawaii -->
    <link rel="stylesheet" href="{{ asset('css/kawaii.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-kawaii.css') }}">

    @stack('styles')
</head>
<body class="admin-layout">

<!-- Backdrop para versión móvil -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

<!-- ══════════════════ SIDEBAR KAWAII ══════════════════ -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand -->
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('img/icon.png') }}" alt="" style="width: 36px; height: 36px; object-fit: contain;">
        <span>Juno's Store</span>
    </a>
    <span class="sidebar-role">Panel Administrativo</span>

    @php
        $sidebarPendientes = \App\Models\Pedido::where('estado', 'pendiente')->count();
        $sidebarStockBajo = \App\Models\Producto::whereRaw('stock_actual <= stock_minimo')->count();
    @endphp

    <!-- Navegación -->
    <nav class="sidebar-nav">
        <span class="sidebar-section-label">Gestión Principal</span>
        <a href="{{ route('admin.dashboard') }}" 
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt" style="width: 20px;"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.productos.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.productos.*') ? 'active' : '' }}">
            <i class="fas fa-box-open" style="width: 20px;"></i>
            <span>Productos</span>
            @if($sidebarStockBajo > 0)
                <span class="badge-kawaii badge-kawaii-pink ms-auto" style="padding: 2px 7px; font-size: 0.68rem;" title="{{ $sidebarStockBajo }} productos con stock crítico">
                    {{ $sidebarStockBajo }}
                </span>
            @endif
        </a>
        <a href="{{ route('admin.pedidos.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.pedidos.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag" style="width: 20px;"></i>
            <span>Pedidos / Ventas</span>
            @if($sidebarPendientes > 0)
                <span class="badge-kawaii badge-kawaii-yellow ms-auto" style="padding: 2px 7px; font-size: 0.68rem; background: #fff1d8; color: #d68910;" title="{{ $sidebarPendientes }} pedidos pendientes de despacho">
                    {{ $sidebarPendientes }}
                </span>
            @endif
        </a>
        <a href="{{ route('admin.usuarios.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
            <i class="fas fa-users" style="width: 20px;"></i>
            <span>Clientes</span>
        </a>

        <span class="sidebar-section-label">Tienda Pública</span>
        <a href="{{ route('tienda.index') }}" target="_blank" class="sidebar-link">
            <i class="fas fa-store" style="width: 20px;"></i>
            <span>Ver Tienda</span>
            <i class="fas fa-external-link-alt ms-auto" style="font-size: 0.75rem; opacity: 0.7;"></i>
        </a>
    </nav>

    <!-- Footer Sidebar con Cerrar Sesión -->
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="sidebar-logout" style="width: 100%; border: none; cursor: pointer; text-align: left;">
                <i class="fas fa-sign-out-alt" style="width: 20px;"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>

<!-- ══════════════════ WRAPPER CONTENIDO PRINCIPAL ══════════════════ -->
<div class="admin-wrapper">
    <!-- Header Admin -->
    <header class="admin-header">
        <!-- Toggle button para móvil -->
        <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Abrir menú lateral">
            <i class="fas fa-bars"></i>
        </button>

        <div class="admin-user-nav ms-auto">
            <a href="{{ route('tienda.index') }}" target="_blank" class="btn-pill d-none d-sm-inline-flex align-items-center gap-1">
                <i class="fas fa-eye"></i>
                <span>Visitar Tienda</span>
            </a>

            <!-- Perfil del usuario autenticado -->
            <div class="admin-profile">
                <div class="admin-profile-info">
                    <span class="admin-profile-name">{{ auth()->user()->nombre_completo }}</span>
                    <span class="admin-profile-email">{{ auth()->user()->email }}</span>
                </div>
                <img src="{{ asset('img/admin_avatar.jpg') }}" 
                     alt="{{ auth()->user()->nombre }}" 
                     class="admin-avatar"
                     onerror="this.src='{{ asset('img/icon.png') }}'">
            </div>

            <!-- Botón de Salir directo en Header Admin -->
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-kawaii-outline" style="padding: 6px 12px; font-size: 0.82rem; border-radius: 999px; color: #c2335f; border-color: #fcc2d7;" title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="d-none d-md-inline ms-1">Salir</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Alertas Flash -->
    @if(session('success'))
    <div class="alert-kawaii alert-kawaii-success" role="alert" style="width: 100%; margin: 0 0 1.5rem 0;">
        <i class="fas fa-check-circle alert-kawaii-icon"></i>
        <div>
            <strong>¡Excelente!</strong>
            <div>{{ session('success') }}</div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert-kawaii alert-kawaii-error" role="alert" style="width: 100%; margin: 0 0 1.5rem 0;">
        <i class="fas fa-exclamation-triangle alert-kawaii-icon"></i>
        <div>
            <strong>Atención:</strong>
            <div>{{ session('error') }}</div>
        </div>
    </div>
    @endif

    <!-- Contenido Dinámico -->
    <main>
        @yield('content')
    </main>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script para toggle del menú móvil -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar && backdrop) {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    }
}
</script>

@stack('scripts')
</body>
</html>
