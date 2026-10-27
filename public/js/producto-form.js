/**
 * producto-form.js — Interactividad del formulario de productos
 * Juno's Store | JS vanilla (sin frameworks)
 *
 * Funcionalidades:
 *  1. Selector dinámico de subcategorías vía fetch
 *  2. Preview de imágenes antes de subir (con botón ×)
 *  3. Modal de confirmación para formularios de eliminación
 */

(function () {
    'use strict';

    // =========================================================================
    // 1. SELECTOR DINÁMICO DE SUBCATEGORÍAS
    // =========================================================================

    const selectCategoria    = document.getElementById('categoria_id');
    const selectSubcategoria = document.getElementById('subcategoria_id');
    const statusSub          = document.getElementById('subcategoria-status');

    /**
     * Carga las subcategorías de una categoría vía fetch y rellena el select.
     * @param {number} categoriaId
     * @param {number|null} seleccionadaId - ID a pre-seleccionar tras cargar
     */
    function cargarSubcategorias(categoriaId, seleccionadaId = null) {
        if (!selectSubcategoria) return;

        if (!categoriaId) {
            selectSubcategoria.innerHTML = '<option value="">— Selecciona primero una categoría —</option>';
            if (statusSub) statusSub.textContent = '';
            return;
        }

        // Indicador de carga
        selectSubcategoria.innerHTML = '<option value="">Cargando…</option>';
        selectSubcategoria.disabled  = true;
        if (statusSub) statusSub.textContent = 'Cargando subcategorías…';

        fetch(BASE_URL + '/admin/subcategorias/' + encodeURIComponent(categoriaId))
            .then(function (res) {
                if (!res.ok) throw new Error('Error HTTP ' + res.status);
                return res.json();
            })
            .then(function (subcategorias) {
                selectSubcategoria.disabled = false;

                if (!Array.isArray(subcategorias) || subcategorias.length === 0) {
                    selectSubcategoria.innerHTML = '<option value="">Sin subcategorías</option>';
                    if (statusSub) statusSub.textContent = 'Esta categoría no tiene subcategorías.';
                    return;
                }

                selectSubcategoria.innerHTML = '<option value="">— Selecciona una subcategoría —</option>';
                subcategorias.forEach(function (sub) {
                    const opt      = document.createElement('option');
                    opt.value      = sub.id;
                    opt.textContent = sub.nombre;
                    if (seleccionadaId && parseInt(sub.id, 10) === parseInt(seleccionadaId, 10)) {
                        opt.selected = true;
                    }
                    selectSubcategoria.appendChild(opt);
                });

                if (statusSub) statusSub.textContent = '';
            })
            .catch(function (err) {
                selectSubcategoria.disabled = false;
                selectSubcategoria.innerHTML = '<option value="">Error al cargar</option>';
                if (statusSub) statusSub.textContent = 'No se pudieron cargar las subcategorías.';
                console.error('[producto-form] Error cargando subcategorías:', err);
            });
    }

    // Evento: cambio en el select de categoría
    if (selectCategoria) {
        selectCategoria.addEventListener('change', function () {
            cargarSubcategorias(this.value, null);
        });

        // En modo editar: cargar subcategorías de la categoría actual y pre-seleccionar
        if (typeof window._categoriaActual !== 'undefined' && window._categoriaActual) {
            cargarSubcategorias(window._categoriaActual, window._subcategoriaActual || null);
        }
    }

    // =========================================================================
    // 2. PREVIEW DE IMÁGENES ANTES DE SUBIR
    // =========================================================================

    const inputImagenes  = document.getElementById('imagenes');
    const previewArea    = document.getElementById('preview-imagenes');

    // Guarda la lista de archivos actualmente seleccionados
    let archivosSeleccionados = [];

    /**
     * Renderiza los thumbnails en el área de preview.
     */
    function renderizarPreviews() {
        if (!previewArea) return;
        previewArea.innerHTML = '';

        archivosSeleccionados.forEach(function (archivo, idx) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'position:relative; display:inline-block;';

                const img = document.createElement('img');
                img.src   = e.target.result;
                img.alt   = archivo.name;
                img.style.cssText = 'width:80px; height:80px; object-fit:cover; border-radius:10px; border:2px solid var(--lavanda); display:block;';

                // Badge "Principal" en la primera imagen
                if (idx === 0) {
                    const badge = document.createElement('span');
                    badge.textContent = '⭐';
                    badge.title       = 'Imagen principal';
                    badge.style.cssText = 'position:absolute; top:2px; left:4px; font-size:0.75rem; pointer-events:none;';
                    wrapper.appendChild(badge);
                }

                // Botón ×
                const btnEliminar = document.createElement('button');
                btnEliminar.type        = 'button';
                btnEliminar.textContent = '×';
                btnEliminar.title       = 'Quitar imagen';
                btnEliminar.style.cssText = [
                    'position:absolute',
                    'top:-6px',
                    'right:-6px',
                    'width:20px',
                    'height:20px',
                    'border-radius:50%',
                    'background:#f5b8c8',
                    'color:#8b1a3a',
                    'border:none',
                    'cursor:pointer',
                    'font-size:0.9rem',
                    'font-weight:800',
                    'line-height:1',
                    'display:flex',
                    'align-items:center',
                    'justify-content:center',
                    'padding:0',
                ].join(';');

                btnEliminar.addEventListener('click', function () {
                    quitarArchivo(idx);
                });

                wrapper.appendChild(img);
                wrapper.appendChild(btnEliminar);
                previewArea.appendChild(wrapper);
            };
            reader.readAsDataURL(archivo);
        });
    }

    /**
     * Elimina un archivo de la lista y actualiza el input + previews.
     * @param {number} index
     */
    function quitarArchivo(index) {
        archivosSeleccionados.splice(index, 1);
        actualizarInputFiles();
        renderizarPreviews();
    }

    /**
     * Reconstruye el FileList del input a partir de archivosSeleccionados.
     * Técnica: DataTransfer API.
     */
    function actualizarInputFiles() {
        if (!inputImagenes) return;
        const dt = new DataTransfer();
        archivosSeleccionados.forEach(function (archivo) {
            dt.items.add(archivo);
        });
        inputImagenes.files = dt.files;
    }

    if (inputImagenes) {
        inputImagenes.addEventListener('change', function () {
            // Acumular archivos (o reemplazar según preferencia)
            archivosSeleccionados = Array.from(this.files);
            renderizarPreviews();
        });
    }

    // =========================================================================
    // 3. MODAL DE CONFIRMACIÓN DE ELIMINACIÓN
    // =========================================================================

    document.querySelectorAll('.form-eliminar-producto').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const fila   = this.closest('tr');
            const nombre = fila
                ? (fila.querySelector('td:nth-child(2) span')?.textContent?.trim() || 'este producto')
                : 'este producto';

            const confirmado = confirm(
                '¿Seguro que deseas eliminar "' + nombre + '"?\n' +
                'Esta acción marcará el producto como eliminado.'
            );

            if (!confirmado) {
                e.preventDefault();
            }
        });
    });

})();
