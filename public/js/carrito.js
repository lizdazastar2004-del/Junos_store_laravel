/**
 * carrito.js — Carrito de compras: actualizaciones AJAX
 * Juno's Store | JS vanilla
 */
(function () {
  'use strict';

  var baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL : '';

  function formatCOP(n) {
    return '$' + Number(n).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function postJSON(url, data) {
    // Añadir CSRF token a los datos
    if (typeof csrfToken !== 'undefined') {
      data._token = csrfToken;
    }
    
    return fetch(baseUrl + url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: new URLSearchParams(data).toString()
    }).then(function (r) { return r.json(); });
  }

  function actualizarResumen(data) {
    var elSub   = document.getElementById('res-subtotal');
    var elDesc  = document.getElementById('res-descuento');
    var elEnvio = document.getElementById('res-envio');
    var elTotal = document.getElementById('res-total');
    var badge   = document.getElementById('cart-count');

    if (elSub)   elSub.textContent   = formatCOP(data.subtotal);
    if (elTotal) elTotal.textContent = formatCOP(data.total);
    if (badge)   badge.textContent   = data.count || 0;

    if (elDesc && data.descuento > 0) {
      elDesc.textContent = '−' + formatCOP(data.descuento);
      var fila = document.getElementById('res-descuento-fila');
      if (fila) fila.style.display = '';
    }

    if (elEnvio) {
      if (data.envio > 0) {
        elEnvio.innerHTML = formatCOP(data.envio);
      } else {
        elEnvio.innerHTML = '<span style="color:#27ae60;">¡Envío gratis!</span>';
      }
    }
  }

  /* ── actualizar cantidades ──────────────────────────────────────── */
  document.querySelectorAll('.cantidad-input').forEach(function (input) {
    var timer;
    input.addEventListener('input', function () {
      clearTimeout(timer);
      var itemId   = this.dataset.itemId;
      var cantidad = parseInt(this.value, 10);
      if (isNaN(cantidad) || cantidad < 1) return;
      var self = this;
      timer = setTimeout(function () {
        postJSON('/carrito/actualizar', { item_id: itemId, cantidad: cantidad })
          .then(function (data) {
            if (!data.ok) {
              alert(data.error || 'Error al actualizar.');
              return;
            }
            // Actualizar subtotal de la línea
            var sub = document.getElementById('subtotal-' + itemId);
            // No tenemos precio en el JS — refrescar la página si falla
            actualizarResumen(data);
          })
          .catch(function () { location.reload(); });
      }, 600);
    });
  });

  /* ── eliminar ítem ──────────────────────────────────────────────── */
  document.querySelectorAll('.eliminar-item').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var itemId = this.dataset.itemId;
      if (!confirm('¿Quitar este producto del carrito?')) return;
      postJSON('/carrito/eliminar', { item_id: itemId })
        .then(function (data) {
          if (!data.ok) { alert(data.error || 'Error.'); return; }
          var fila = document.getElementById('fila-' + itemId);
          if (fila) fila.remove();
          actualizarResumen(data);
          // Si carrito vacío, recargar para mostrar mensaje
          if ((data.count || 0) === 0) location.reload();
        })
        .catch(function () { location.reload(); });
    });
  });

  /* ── aplicar código de descuento ───────────────────────────────── */
  var btnCodigo = document.getElementById('btn-aplicar-codigo');
  if (btnCodigo) {
    btnCodigo.addEventListener('click', function () {
      var input = document.getElementById('input-codigo');
      var msg   = document.getElementById('msg-codigo');
      var cod   = input ? input.value.trim() : '';
      if (!cod) { if (msg) { msg.textContent = 'Ingresa un código.'; msg.style.color = '#c0392b'; } return; }

      postJSON('/carrito/descuento', { codigo: cod })
        .then(function (data) {
          if (!data.ok) {
            if (msg) { msg.textContent = data.error || 'Código inválido.'; msg.style.color = '#c0392b'; }
            return;
          }
          actualizarResumen(data);
          if (msg) { msg.textContent = data.mensaje || '¡Descuento aplicado!'; msg.style.color = '#27ae60'; }
          if (input) input.disabled = true;
          if (btnCodigo) btnCodigo.disabled = true;
        })
        .catch(function () { if (msg) msg.textContent = 'Error de conexión.'; });
    });
  }

  /* ── badge del carrito en el header ────────────────────────────── */
  // Se actualiza automáticamente a través de actualizarResumen()

})();
