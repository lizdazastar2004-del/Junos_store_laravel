# 📊 Archivo: `resources/views/admin/dashboard.blade.php` — Panel de Control Administrativo

## 1. Ficha Técnica

* **Ubicación:** `resources/views/admin/dashboard.blade.php`
* **Tipo:** Vista Blade (extiende de `layouts.admin`).
* **Capa:** Vista / Panel Administrativo (V en MVC).
* **Propósito:** Ofrecer una visión ejecutiva e intuitiva de la tienda en tiempo real. Presenta un banner de bienvenida personalizado, 4 tarjetas de indicadores clave (*KPI cards*: total productos, productos activos, productos con stock bajo y total categorías), atajos de gestión rápida hacia el catálogo y un resumen del inventario más reciente con estado de existencias.

---

## 2. Mapa de Conexiones

```mermaid
graph TD
    RouteDash[Route: admin.dashboard] --> ViewDash[admin/dashboard.blade.php]
    ViewDash -->|@extends| LayoutAdmin[layouts/admin.blade.php]

    subgraph "Consultas a Modelos en Tiempo Real"
        ViewDash -->|Producto::count()| KPI1[Total Productos]
        ViewDash -->|Producto::where('activo', 1)->count()| KPI2[Productos Activos]
        ViewDash -->|whereRaw('stock_actual <= stock_minimo')| KPI3[Alerta Stock Bajo]
        ViewDash -->|Categoria::count()| KPI4[Categorías Registradas]
        ViewDash -->|Producto::latest()->take(5)| RecentList[Lista de 5 Productos Recientes]
    end

    subgraph "Navegación de Accesos Directos"
        ViewDash -->|route('admin.productos.create')| ViewCreate[Crear Producto]
        ViewDash -->|route('admin.productos.index')| ViewIndex[Inventario General]
        ViewDash -->|route('tienda.index')| ViewStore[Tienda Pública]
    end
```

---

## 3. Código Fuente y Explicación Detallada de Componentes

### A. Banner Hero de Bienvenida
```blade
<div class="dash-hero">
    <div class="dash-hero-content">
        <span class="dash-eyebrow">Resumen Administrativo</span>
        <h1>¡Hola, {{ auth()->user()->nombre }}! ✨</h1>
        <p>
            Bienvenida a la consola de Juno's Store. Aquí puedes gestionar tu inventario de productos, 
            monitorear el stock y revisar el estado general de tu tienda pastel.
        </p>
    </div>
    <img src="{{ asset('img/dashboard_box_cat.jpg') }}" 
         alt="Gatito kawaii" 
         class="dash-hero-img"
         onerror="this.src='{{ asset('img/icon.png') }}'">
</div>
```
* **Diseño y Cohesión de Marca:** Utiliza un degradado envolvente lila con bordes redondeados a 24px y la ilustración de la mascota oficial en caja de regalo.

---

### B. Cuadrícula de Indicadores Clave (*KPIs Pastel*)
```blade
<div class="kpi-grid">
    <!-- Total Productos -->
    <div class="kpi-card">
        <div class="kpi-icon icon-purple"><i class="fas fa-boxes"></i></div>
        <div class="kpi-info">
            <span class="kpi-label">Total Productos</span>
            <span class="kpi-value">{{ \App\Models\Producto::count() }}</span>
            <span class="kpi-note">En base de datos</span>
        </div>
    </div>

    <!-- Activos en Tienda -->
    <div class="kpi-card">
        <div class="kpi-icon icon-green"><i class="fas fa-check-circle"></i></div>
        <div class="kpi-info">
            <span class="kpi-label">Productos Activos</span>
            <span class="kpi-value">{{ \App\Models\Producto::where('activo', 1)->count() }}</span>
            <span class="kpi-note">Disponibles al público</span>
        </div>
    </div>

    <!-- Alerta de Stock Bajo -->
    <div class="kpi-card">
        <div class="kpi-icon icon-pink"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="kpi-info">
            <span class="kpi-label">Stock Bajo</span>
            <span class="kpi-value">{{ \App\Models\Producto::whereRaw('stock_actual <= stock_minimo')->count() }}</span>
            <span class="kpi-note" style="color: #e8648c; font-weight: 700;">Revisar existencias</span>
        </div>
    </div>

    <!-- Categorías -->
    <div class="kpi-card">
        <div class="kpi-icon icon-yellow"><i class="fas fa-tags"></i></div>
        <div class="kpi-info">
            <span class="kpi-label">Categorías</span>
            <span class="kpi-value">{{ \App\Models\Categoria::count() }}</span>
            <span class="kpi-note">Secciones activas</span>
        </div>
    </div>
</div>
```
* **Cálculo de Stock Crítico:** La cláusula `whereRaw('stock_actual <= stock_minimo')` compara dinámicamente dos columnas de la tabla `productos` para alertar inmediatamente al administrador sobre productos que están por agotarse.
* **Paleta Armónica:** Cada tarjeta utiliza un contenedor de icono circular en color pastel suave (`icon-purple`, `icon-green`, `icon-pink`, `icon-yellow`).

---

### C. Paneles de Gestión Rápida e Inventario Reciente
```blade
<div class="panels-grid">
    <div class="dash-panel">
        <div class="panel-header">
            <h2>Gestión Rápida</h2>
            <span class="badge-kawaii badge-kawaii-purple">Atajos</span>
        </div>
        <div class="d-flex flex-column gap-2 mt-auto">
            <a href="{{ route('admin.productos.create') }}" class="btn-new-item">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Producto
            </a>
            <a href="{{ route('admin.productos.index') }}" class="btn-kawaii-secondary">
                <i class="fas fa-list"></i> Ver Todos los Productos
            </a>
            <a href="{{ route('tienda.index') }}" target="_blank" class="btn-kawaii-outline">
                <i class="fas fa-store"></i> Abrir Tienda Pública
            </a>
        </div>
    </div>

    <div class="dash-panel">
        <div class="panel-header">
            <h2>Últimos Agregados</h2>
            <a href="{{ route('admin.productos.index') }}" class="btn-pill">Ver todos &rarr;</a>
        </div>
        @php
            $ultimos = \App\Models\Producto::with('categoria')->orderBy('id_producto', 'desc')->take(4)->get();
        @endphp
        <div class="recent-list">
            @foreach($ultimos as $item)
            <div class="recent-item">
                <div class="recent-icon"><i class="fas fa-box"></i></div>
                <div class="recent-info">
                    <span class="recent-name">{{ $item->nombre }}</span>
                    <span class="recent-meta">{{ $item->categoria->nombre ?? 'Sin categoría' }} &bull; ${{ number_format($item->precio, 2, ',', '.') }}</span>
                </div>
                <span class="badge-kawaii {{ $item->stock_actual > 0 ? 'badge-kawaii-purple' : 'badge-kawaii-pink' }}">
                    {{ $item->stock_actual }} u.
                </span>
            </div>
            @endforeach
        </div>
    </div>
</div>
```

---

## 4. Preguntas Frecuentes para Evaluación

1. **¿Cómo se calculan las métricas de las tarjetas KPI?**
   * *Respuesta:* Directamente mediante el ORM Eloquent con funciones agregadas SQL optimizadas (`COUNT(*)`), evitando traer colecciones enteras a la memoria RAM de PHP.
2. **¿Por qué se utiliza `whereRaw('stock_actual <= stock_minimo')`?**
   * *Respuesta:* Porque requiere comparar los valores de dos columnas diferentes en la misma fila (`stock_actual` versus `stock_minimo`). `whereRaw` genera la sentencia SQL `WHERE stock_actual <= stock_minimo` de forma directa y eficiente.
3. **¿Cómo se adapta este Dashboard a pantallas móviles o tablets?**
   * *Respuesta:* En `admin-kawaii.css`, `.kpi-grid` y `.panels-grid` utilizan CSS Grid con `grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))`. En pantallas menores a 768px, las 4 tarjetas se apilan automáticamente en 1 o 2 columnas sin desbordamiento horizontal.
