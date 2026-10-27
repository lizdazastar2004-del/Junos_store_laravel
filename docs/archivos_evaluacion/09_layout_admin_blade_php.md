# 📐 Archivo: `resources/views/layouts/admin.blade.php` — Layout Maestro del Panel Administrativo

## 1. Ficha Técnica

* **Ubicación:** `resources/views/layouts/admin.blade.php`
* **Tipo:** Plantilla Maestra Administrativa Blade.
* **Capa:** Vista / Presentación Administrativa (V en MVC).
* **Propósito:** Proporcionar la estructura y diseño del panel de administración (Dashboard, Listado de Productos, Creación y Edición). Sustituye la barra azul básica de Bootstrap por una arquitectura moderna con barra lateral *glassmorphic* (vidrio esmerilado pastel), cabecera superior con perfil de administrador, alertas flash, cajón retráctil (*drawer*) para dispositivos móviles y enlace directo a la tienda pública.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    LayoutAdmin[resources/views/layouts/admin.blade.php]

    subgraph "Recursos Estilísticos"
        LayoutAdmin --> KawaiiCSS[public/css/kawaii.css]
        LayoutAdmin --> AdminKawaiiCSS[public/css/admin-kawaii.css]
        LayoutAdmin --> BS5[Bootstrap 5.3 CDN]
        LayoutAdmin --> FA6[FontAwesome 6.4 CDN]
    end

    subgraph "Vistas Administrativas Hijas (@extends('layouts.admin'))"
        ViewDash[admin/dashboard.blade.php] -->|@section('content')| LayoutAdmin
        ViewProdIndex[admin/productos/index.blade.php] -->|@section('content')| LayoutAdmin
        ViewProdCreate[admin/productos/crear.blade.php] -->|@section('content')| LayoutAdmin
        ViewProdEdit[admin/productos/editar.blade.php] -->|@section('content')| LayoutAdmin
    end

    subgraph "Datos del Usuario en Sesión"
        AuthSession[auth()->user()] -->|nombre_completo| AdminHeader[Perfil en Cabecera]
        AuthSession -->|email| AdminHeader
        AuthSession -->|admin_avatar.jpg| AdminAvatar[Avatar Pastel]
    end
```

---

## 3. Código Fuente y Explicación Detallada por Componentes

### A. Barra Lateral Retráctil (*Sidebar Kawaii*)
```blade
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand / Logotipo -->
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('img/icon.png') }}" alt="" style="width: 36px; height: 36px; object-fit: contain;">
        <span>Juno's Store</span>
    </a>
    <span class="sidebar-role">Panel Administrativo</span>

    <!-- Navegación con detección de ruta activa -->
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
        </a>

        <span class="sidebar-section-label">Tienda Pública</span>
        <a href="{{ route('tienda.index') }}" target="_blank" class="sidebar-link">
            <i class="fas fa-store" style="width: 20px;"></i>
            <span>Ver Tienda</span>
            <i class="fas fa-external-link-alt ms-auto" style="font-size: 0.75rem; opacity: 0.7;"></i>
        </a>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="fas fa-sign-out-alt" style="width: 20px;"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
```
* **Detección Dinámica de Rutas Activas:**
  * `request()->routeIs('admin.dashboard') ? 'active' : ''` resalta el botón de Dashboard si la URL actual corresponde a esa ruta.
  * `request()->routeIs('admin.productos.*') ? 'active' : ''` utiliza el comodín `*` para mantener activo el botón "Productos" ya sea que el administrador esté en el listado (`admin.productos.index`), creando (`admin.productos.create`) o editando (`admin.productos.edit`).
* **Enlace a la Tienda Pública con `target="_blank"`:** Permite al administrador verificar cómo se ven los cambios en la tienda sin perder su sesión de trabajo en el panel.

---

### B. Barra Superior y Perfil de Usuario
```blade
<header class="admin-header">
    <button type="button" class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Abrir menú lateral">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-user-nav ms-auto">
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
    </div>
</header>
```
* Despliega el nombre completo y correo del administrador logueado usando el accesor `$usuario->nombre_completo`.
* Cuenta con un fallback de imagen con `onerror` en el avatar para garantizar que siempre se muestre un icono válido.

---

### C. Alertas Flash y Scripts de Adaptabilidad Móvil
```blade
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        if (sidebar) sidebar.classList.toggle('open');
        if (backdrop) backdrop.classList.toggle('show');
    }
</script>
```
* Un script ligero y reactivo que abre y cierra la barra lateral en pantallas móviles sin depender de librerías pesadas de terceros.

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Cómo se logró que la barra lateral permanezca activa al editar un producto?**
   * *Respuesta:* Mediante el método `request()->routeIs('admin.productos.*')`. Al utilizar el asterisco como comodín, cualquier sub-ruta nombrada bajo el espacio de nombres `admin.productos` activa automáticamente la clase CSS `.active`.
2. **¿Por qué se usan dos hojas de estilo (`kawaii.css` y `admin-kawaii.css`)?**
   * *Respuesta:* Para mantener el principio de modularidad y reutilización. `kawaii.css` contiene los tokens globales (colores pastel, variables tipográficas, botones, inputs y alertas), mientras que `admin-kawaii.css` define las reglas específicas del panel (grid de la barra lateral, tarjetas KPI y tablas administrativas).
3. **¿Cómo se garantiza la adaptabilidad en dispositivos móviles?**
   * *Respuesta:* En pantallas pequeñas (< 992px), la barra lateral se oculta con `transform: translateX(-100%)`. Al presionar el botón `.sidebar-toggle`, se agrega la clase `.open` junto con un telón semitransparente (`.sidebar-backdrop`), permitiendo una experiencia de usuario táctil nativa.
