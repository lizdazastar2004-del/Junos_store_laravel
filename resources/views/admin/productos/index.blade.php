@extends('layouts.admin')

@section('title', "Gestión de Productos - Panel Admin Juno's Store")

@section('content')
<!-- Encabezado de la página -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1" style="font-size: 1.6rem; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-boxes" style="color: #7a4adc;"></i>
            <span>Inventario de Productos</span>
        </h1>
        <p style="color: #796693; margin: 0; font-size: 0.9rem;">
            Administra los artículos disponibles en el catálogo de Juno's Store
        </p>
    </div>
    
    <a href="{{ route('admin.productos.create') }}" class="btn-new-item">
        <i class="fas fa-plus-circle"></i>
        <span>Nuevo Producto</span>
    </a>
</div>

<!-- Tarjeta de Tabla -->
<div class="admin-card">
    <div class="admin-card-header">
        <div class="d-flex align-items-center gap-2">
            <h2 style="font-size: 1.1rem; margin: 0; color: #3c205e;">
                <i class="fas fa-list-ul" style="color: #8b52e0;"></i> Listado Completo
            </h2>
            <span class="badge-kawaii badge-kawaii-purple" style="font-size: 0.72rem;">
                {{ $productos->total() }} registrados
            </span>
        </div>
        
        <a href="{{ route('tienda.index') }}#productos" target="_blank" class="btn-pill">
            <i class="fas fa-external-link-alt me-1"></i> Ver en Tienda
        </a>
    </div>

    <div class="admin-card-body p-0">
        <div class="table-responsive">
            <table class="table-kawaii-admin">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th style="text-align: right; width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $producto)
                    <tr>
                        <!-- ID -->
                        <td>
                            <span style="font-weight: 800; color: #9b84cc; font-size: 0.85rem;">#{{ $producto->id_producto }}</span>
                        </td>

                        <!-- Producto (Foto + Nombre + SKU) -->
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                @php
                                    $thumb = $producto->imagenes->where('es_principal', 1)->first() 
                                          ?? $producto->imagenes->first();
                                @endphp

                                @if($thumb)
                                    <img src="{{ asset($thumb->ruta_imagen) }}" 
                                         alt="{{ $producto->nombre }}" 
                                         class="table-thumb"
                                         loading="lazy">
                                @else
                                    <div class="table-thumb d-flex align-items-center justify-content-center" style="color: #9b84cc;">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                @endif

                                <div>
                                    <strong style="color: #33214d; font-size: 0.95rem; display: block; line-height: 1.3;">
                                        {{ $producto->nombre }}
                                    </strong>
                                    @if($producto->sku)
                                        <span style="font-family: monospace; font-size: 0.78rem; color: #796693; background: #faf6fe; padding: 2px 6px; border-radius: 6px; border: 1px solid #ede4f8;">
                                            {{ $producto->sku }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Categoría & Subcategoría -->
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="badge-kawaii badge-kawaii-purple" style="align-self: flex-start; font-size: 0.7rem;">
                                    {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                                </span>
                                @if($producto->subcategoria)
                                    <span style="font-size: 0.78rem; color: #796693; font-weight: 600;">
                                        {{ $producto->subcategoria->nombre }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Precio -->
                        <td>
                            <span style="font-weight: 800; color: #7a4adc; font-size: 1.05rem;">
                                ${{ number_format($producto->precio, 2, ',', '.') }}
                            </span>
                        </td>

                        <!-- Stock -->
                        <td>
                            @if($producto->stock_actual <= 0)
                                <span class="badge-kawaii badge-kawaii-pink" style="font-size: 0.72rem;">
                                    <i class="fas fa-times-circle"></i> Agotado (0)
                                </span>
                            @elseif($producto->stock_actual <= $producto->stock_minimo)
                                <span class="badge-kawaii badge-kawaii-pink" style="font-size: 0.72rem;">
                                    <i class="fas fa-exclamation-triangle"></i> Bajo: {{ $producto->stock_actual }}
                                </span>
                            @else
                                <span class="badge-kawaii badge-kawaii-mint" style="font-size: 0.72rem;">
                                    <i class="fas fa-check-circle"></i> {{ $producto->stock_actual }} unid.
                                </span>
                            @endif
                        </td>

                        <!-- Estado Activo/Inactivo -->
                        <td>
                            @if($producto->activo)
                                <span class="badge-kawaii badge-kawaii-mint" style="font-size: 0.72rem;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Activo
                                </span>
                            @else
                                <span class="badge-kawaii badge-kawaii-gray" style="font-size: 0.72rem;">
                                    <i class="fas fa-circle" style="font-size: 0.5rem;"></i> Inactivo
                                </span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td style="text-align: right;">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.productos.edit', $producto->id_producto) }}" 
                                   class="btn-action-edit"
                                   title="Editar producto">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                
                                <form action="{{ route('admin.productos.destroy', $producto->id_producto) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('¿Estás segura de desactivar este producto?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action-delete" title="Desactivar producto">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div style="padding: 2rem; color: #796693;">
                                <i class="fas fa-box-open mb-3" style="font-size: 3.5rem; color: #d5c3ec;"></i>
                                <h4 style="font-weight: 800; color: #59407a;">No hay productos registrados</h4>
                                <p style="font-size: 0.9rem; margin-bottom: 1.5rem;">Comienza agregando tu primer producto kawaii al catálogo.</p>
                                <a href="{{ route('admin.productos.create') }}" class="btn-new-item">
                                    <i class="fas fa-plus-circle"></i> Crear Primer Producto
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($productos->hasPages())
        <div class="p-4 d-flex justify-content-center" style="background: #faf7fd; border-top: 1px solid #ede4f8;">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

