// ============================================================
// DIQUE PROGRAMANDO — lógica de comprobación de retos
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  var cfg = window.DP_RETO;
  if (!cfg) return;

  var iframe = document.getElementById('retoIframe');
  var btn = document.getElementById('btnComprobar');
  var resultadoBox = document.getElementById('retoResultado');
  var estadoBox = document.getElementById('retoEstado');

  function mostrarMensaje(html, tipo) {
    resultadoBox.innerHTML = '<div class="dp-alert dp-alert-' + tipo + '">' + html + '</div>';
  }

  btn.addEventListener('click', function () {
    var doc = iframe.contentDocument;
    var win = iframe.contentWindow;

    if (!doc || !win) {
      mostrarMensaje('No se pudo leer el resultado, intenta de nuevo.', 'error');
      return;
    }

    var pasa = false;
    try {
      var verificar = new Function('doc', 'win', cfg.comprobacion);
      pasa = !!verificar(doc, win);
    } catch (e) {
      mostrarMensaje('Tu código tiene un error: <code>' + (e.message || e) + '</code>', 'error');
      return;
    }

    if (!pasa) {
      mostrarMensaje('Todavía no cumple lo pedido. Revisa el enunciado e inténtalo de nuevo. 💪', 'error');
      return;
    }

    if (!cfg.logueado) {
      mostrarMensaje('¡Correcto! 🎉 Inicia sesión para guardar tu progreso y ganar puntos.', 'success');
      return;
    }

    var body = new URLSearchParams();
    body.set('completar', '1');
    body.set('csrf_token', cfg.csrf);

    fetch(cfg.completarUrl, { method: 'POST', body: body })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.ok) {
          mostrarMensaje('¡Correcto! Pero no se pudo guardar tu progreso.', 'success');
          return;
        }
        if (data.nuevo) {
          mostrarMensaje('¡Correcto! 🎉 +puntos guardados.', 'success');
          estadoBox.innerHTML = '<span style="color:#15803d;font-weight:600;">✔ ¡Ya completaste este reto!</span>';
          if (data.insignias && data.insignias.length) {
            var nombres = data.insignias.map(function (i) { return i.icono + ' ' + i.nombre; }).join(', ');
            mostrarMensaje('¡Correcto! 🎉 +puntos guardados.<br>🏅 Nueva insignia desbloqueada: ' + nombres, 'success');
          }
        } else {
          mostrarMensaje('¡Correcto! (ya lo habías completado antes)', 'success');
        }
      })
      .catch(function () {
        mostrarMensaje('¡Correcto! Pero hubo un problema de conexión al guardar.', 'success');
      });
  });
});
