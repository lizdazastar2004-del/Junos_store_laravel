@extends('layouts.admin')

@section('title', "Crear Producto - Panel Admin Juno's Store")

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <!-- Botón Volver -->
        <div class="mb-3">
            <a href="{{ route('admin.productos.index') }}" class="btn-pill" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al Inventario</span>
            </a>
        </div>

        <!-- Tarjeta del Formulario -->
        <div class="admin-card">
            <div class="admin-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: #ebdffc; display: flex; align-items: center; justify-content: center; color: #7a4adc;">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.2rem; margin: 0; color: #3c205e;">Crear Nuevo Producto</h2>
                        <span style="font-size: 0.8rem; color: #796693;">Completa la información para agregarlo a la tienda</span>
                    </div>
                </div>
            </div>

            <div class="admin-card-body">
                <form method="POST" action="{{ route('admin.productos.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- 1. INFORMACIÓN BÁSICA -->
                    <div style="border-bottom: 1px solid #ede4f8; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h5 style="color: #60458f; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                            <i class="fas fa-info-circle me-1"></i> Información Básica
                        </h5>

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Producto <span style="color: #e8648c;">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}" 
                                   placeholder="Ej: Libreta Gatito Pastel A5"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="4" 
                                      placeholder="Detalles sobre materiales, tamaño, colores y encanto del producto...">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 2. CLASIFICACIÓN -->
                    <div style="border-bottom: 1px solid #ede4f8; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h5 style="color: #60458f; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                            <i class="fas fa-tags me-1"></i> Categorización
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="id_categoria" class="form-label">Categoría <span style="color: #e8648c;">*</span></label>
                                <select class="form-select @error('id_categoria') is-invalid @enderror" 
                                        id="id_categoria" 
                                        name="id_categoria" 
                                        required>
                                    <option value="">Selecciona una categoría...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id_categoria }}" {{ old('id_categoria') == $cat->id_categoria ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="id_subcategoria" class="form-label">Subcategoría <span style="color: #e8648c;">*</span></label>
                                <select class="form-select @error('id_subcategoria') is-invalid @enderror" 
                                        id="id_subcategoria" 
                                        name="id_subcategoria" 
                                        required>
                                    <option value="">Selecciona una subcategoría...</option>
                                    @foreach($subcategorias as $sub)
                                        <option value="{{ $sub->id_subcategoria }}" 
                                                data-categoria="{{ $sub->id_categoria }}"
                                                {{ old('id_subcategoria') == $sub->id_subcategoria ? 'selected' : '' }}>
                                            {{ $sub->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_subcategoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="id_marca" class="form-label">Marca (opcional)</label>
                                <select class="form-select @error('id_marca') is-invalid @enderror" 
                                        id="id_marca" 
                                        name="id_marca">
                                    <option value="">Sin marca específica / Propia</option>
                                    @foreach($marcas as $marca)
                                        <option value="{{ $marca->id_marca }}" {{ old('id_marca') == $marca->id_marca ? 'selected' : '' }}>
                                            {{ $marca->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_marca')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 3. PRECIO E INVENTARIO -->
                    <div style="border-bottom: 1px solid #ede4f8; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h5 style="color: #60458f; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                            <i class="fas fa-calculator me-1"></i> Precios e Inventario
                        </h5>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="precio" class="form-label">Precio ($) <span style="color: #e8648c;">*</span></label>
                                <input type="number" 
                                       class="form-control @error('precio') is-invalid @enderror" 
                                       id="precio" 
                                       name="precio" 
                                       value="{{ old('precio') }}" 
                                       step="0.01" 
                                       min="0" 
                                       placeholder="0.00" 
                                       required>
                                @error('precio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="stock_actual" class="form-label">Stock Actual <span style="color: #e8648c;">*</span></label>
                                <input type="number" 
                                       class="form-control @error('stock_actual') is-invalid @enderror" 
                                       id="stock_actual" 
                                       name="stock_actual" 
                                       value="{{ old('stock_actual', 0) }}" 
                                       min="0" 
                                       required>
                                @error('stock_actual')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="stock_minimo" class="form-label">Stock Mínimo <span style="color: #e8648c;">*</span></label>
                                <input type="number" 
                                       class="form-control @error('stock_minimo') is-invalid @enderror" 
                                       id="stock_minimo" 
                                       name="stock_minimo" 
                                       value="{{ old('stock_minimo', 5) }}" 
                                       min="0" 
                                       required>
                                @error('stock_minimo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="sku" class="form-label">Código SKU</label>
                                <input type="text" 
                                       class="form-control @error('sku') is-invalid @enderror" 
                                       id="sku" 
                                       name="sku" 
                                       value="{{ old('sku') }}" 
                                       placeholder="Dejar vacío para generar automáticamente (ej: JUNO-123456)">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 4. FOTOGRAFÍA DEL PRODUCTO -->
                    <div style="border-bottom: 1px solid #ede4f8; padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
                        <h5 style="color: #60458f; font-weight: 800; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                            <i class="fas fa-camera me-1"></i> Fotografía del Producto
                        </h5>

                        <div class="row align-items-center g-3">
                            <div class="col-md-8">
                                <label for="imagen" class="form-label">Subir Imagen (JPG, PNG, WebP o SVG)</label>
                                <input type="file" 
                                       class="form-control @error('imagen') is-invalid @enderror" 
                                       id="imagen" 
                                       name="imagen" 
                                       accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                       onchange="previewProductImage(this)">
                                <small style="color: #796693; font-size: 0.8rem; display: block; margin-top: 4px;">
                                    Tamaño máximo recomendado: 3 MB. Se guardará como la imagen principal del catálogo.
                                </small>
                                @error('imagen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 text-center">
                                <div id="preview-wrapper" style="width: 100px; height: 100px; border-radius: 16px; border: 2px dashed #d8caec; background: #faf7fd; margin: 0 auto; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                    <img id="img-preview" src="#" alt="Vista previa" style="display: none; width: 100%; height: 100%; object-fit: contain;">
                                    <span id="preview-placeholder" style="color: #b5a4d4; font-size: 0.78rem; text-align: center; padding: 6px;">
                                        <i class="fas fa-image" style="font-size: 1.5rem; display: block; margin-bottom: 4px;"></i> Sin imagen
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. VISIBILIDAD -->
                    <div style="background: #faf7fd; padding: 1.25rem; border-radius: 16px; border: 1px solid #ede4f8; margin-bottom: 2rem;">
                        <div class="form-check d-flex align-items-center gap-2 m-0">
                            <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" {{ old('activo', '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="activo" style="color: #352b43;">
                                Producto visible y activo en la tienda virtual
                            </label>
                        </div>
                        <small style="color: #796693; display: block; margin-left: 28px; margin-top: 4px;">
                            Si está desmarcado, el producto no aparecerá en el catálogo público ni en búsquedas.
                        </small>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn-new-item" style="border: none; cursor: pointer;">
                            <i class="fas fa-save"></i>
                            <span>Guardar Producto</span>
                        </button>

                        <a href="{{ route('admin.productos.index') }}" class="btn-kawaii-secondary">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewProductImage(input) {
    const preview = document.getElementById('img-preview');
    const placeholder = document.getElementById('preview-placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const catSelect = document.getElementById('id_categoria');
    const subcatSelect = document.getElementById('id_subcategoria');

    function filterSubcategories() {
        const catId = catSelect.value;
        const currentSubVal = subcatSelect.value;
        let hasSelectedVisible = false;

        Array.from(subcatSelect.options).forEach(opt => {
            if (!opt.value) return; // Mantener opción por defecto
            const subCatId = opt.getAttribute('data-categoria');
            if (!catId || subCatId === catId) {
                opt.style.display = '';
                if (opt.value === currentSubVal) hasSelectedVisible = true;
            } else {
                opt.style.display = 'none';
            }
        });

        if (!hasSelectedVisible && catId) {
            subcatSelect.value = '';
        }
    }

    if (catSelect && subcatSelect) {
        catSelect.addEventListener('change', filterSubcategories);
        filterSubcategories();
    }
});
</script>
@endpush

