/**
 * catalogo.js — Catálogo público: filtros AJAX, búsqueda, galería
 * Juno's Store | JS vanilla
 */
(function () {
  'use strict';

  /* ── helpers ─────────────────────────────────────────────────────── */
  function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
  }

  function formatPrecio(n) {
    return '$' + Number(n).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function debounce(fn, ms) {
    let t;
    return function () {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, arguments), ms);
    };
  }

  /* ── render tarjeta ─────────────────────────────────────────────── */
  function renderTarjeta(p) {
    const agotado = (parseInt(p.stock_disponible, 10) || 0) <= 0;
    const img = p.imagen_principal
      ? '<img src="' + esc(p.imagen_principal) + '" alt="' + esc(p.nombre) + '" loading="lazy">'
      : '<div style="height:200px;display:flex;align-items:center;justify-content:center;background:var(--lila);font-size:3rem;" aria-hidden="true">📷</div>';
    return '<div class="product-card">' +
      img +
      '<div class="product-card-body">' +
        '<p class="product-name">' + esc(p.nombre) + '</p>' +
        '<p class="price">' + formatPrecio(p.precio) + '</p>' +
        '<p style="font-size:.82rem;color:var(--texto-claro);">' + esc(p.subcategoria_nombre || '') + '</p>' +
        (agotado ? '<span class="badge badge-cancelado">Agotado</span>' : '') +
        '<div class="product-actions">' +
          '<a href="' + BASE_URL + '/producto/' + p.id + '" class="btn-primary btn-sm">Ver Producto</a>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  /* ── actualizar grid ────────────────────────────────────────────── */
  function actualizarGrid(productos) {
    const grid    = document.getElementById('productos-grid');
    const msgSin  = document.getElementById('msg-sin-resultados');
    const msgCort = document.getElementById('msg-busqueda-corta');
    if (!grid) return;
    if (msgCort) msgCort.style.display = 'none';
    if (!productos.length) {
      grid.innerHTML = '';
      if (msgSin) msgSin.style.display = '';
    } else {
      if (msgSin) msgSin.style.display = 'none';
      grid.innerHTML = productos.map(renderTarjeta).join('');
    }
  }

  function getSubsActivas() {
    return Array.from(document.querySelectorAll('.filtro-sub:checked')).map(function (c) { return c.value; });
  }

  function fetchFiltrado(termino, subs) {
    const params = new URLSearchParams();
    subs.forEach(function (id) { params.append('subcategorias[]', id); });
    if (termino) params.set('termino', termino);
    return fetch(BASE_URL + '/catalogo/filtrar?' + params.toString())
      .then(function (r) { return r.ok ? r.json() : []; })
      .catch(function () { return []; });
  }

  /* ── búsqueda ────────────────────────────────────────────────────── */
  var busquedaInput = document.getElementById('busqueda-input');
  var busquedaHint  = document.getElementById('busqueda-hint');
  var msgCorta      = document.getElementById('msg-busqueda-corta');

  if (busquedaInput) {
    var handleBusqueda = debounce(function () {
      var termino = busquedaInput.value.trim();
      if (termino.length > 0 && termino.length < 3) {
        if (busquedaHint) busquedaHint.style.display = '';
        if (msgCorta)     msgCorta.style.display = '';
        return;
      }
      if (busquedaHint) busquedaHint.style.display = 'none';
      if (msgCorta)     msgCorta.style.display = 'none';
      fetchFiltrado(termino.length >= 3 ? termino : '', getSubsActivas()).then(actualizarGrid);
    }, 300);
    busquedaInput.addEventListener('input', handleBusqueda);
  }

  /* ── filtros subcategoría ───────────────────────────────────────── */
  document.querySelectorAll('.filtro-sub').forEach(function (cb) {
    cb.addEventListener('change', debounce(function () {
      var termino = busquedaInput ? busquedaInput.value.trim() : '';
      fetchFiltrado(termino.length >= 3 ? termino : '', getSubsActivas()).then(actualizarGrid);
    }, 300));
  });

  /* ── limpiar filtros ────────────────────────────────────────────── */
  var btnLimpiar = document.getElementById('btn-limpiar');
  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', function () {
      document.querySelectorAll('.filtro-sub').forEach(function (c) { c.checked = false; });
      if (busquedaInput) busquedaInput.value = '';
      if (busquedaHint)  busquedaHint.style.display = 'none';
      if (msgCorta)      msgCorta.style.display = 'none';
      fetchFiltrado('', []).then(actualizarGrid);
    });
  }

  /* ── galería imagen (detalle) ───────────────────────────────────── */
  var imgPrincipal = document.getElementById('img-principal');
  var miniaturas   = Array.from(document.querySelectorAll('.miniatura'));
  var imgActual    = 0;

  function mostrarImagen(idx) {
    if (!imgPrincipal || !miniaturas.length) return;
    imgActual = (idx % miniaturas.length + miniaturas.length) % miniaturas.length;
    imgPrincipal.src = miniaturas[imgActual].dataset.src;
    miniaturas.forEach(function (m, i) {
      m.style.border = i === imgActual ? '2px solid var(--lavanda-dark)' : '2px solid transparent';
    });
  }

  miniaturas.forEach(function (m, i) {
    m.addEventListener('click', function () { mostrarImagen(i); });
  });

  var btnAnt = document.getElementById('btn-anterior');
  var btnSig = document.getElementById('btn-siguiente');
  if (btnAnt) btnAnt.addEventListener('click', function () { mostrarImagen(imgActual - 1); });
  if (btnSig) btnSig.addEventListener('click', function () { mostrarImagen(imgActual + 1); });
  if (miniaturas.length) mostrarImagen(0);

})();
