// ============================================================
// DIQUE PROGRAMANDO — JS global
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  // Menú móvil
  var burger = document.getElementById('dpBurger');
  var nav = document.getElementById('dpNav');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      nav.classList.toggle('abierto');
    });
  }

  // Tabs + editores de código (HTML/CSS/JS) en todo el sitio
  document.querySelectorAll('.dp-editor').forEach(initEditor);
});

function dpModoParaLenguaje(lenguaje) {
  if (lenguaje === 'html') return 'htmlmixed';
  if (lenguaje === 'css') return 'css';
  if (lenguaje === 'js') return 'javascript';
  return 'text/plain';
}

/** Devuelve el valor actual de un textarea, usando CodeMirror si está activo en él. */
function dpValorEditor(textarea) {
  if (!textarea) return '';
  return textarea.cmInstance ? textarea.cmInstance.getValue() : textarea.value;
}

function initEditor(editor) {
  var tabs = editor.querySelectorAll('.dp-editor-tabs button');
  var panels = editor.querySelectorAll('.dp-editor-panel');
  var scope = editor.closest('.dp-sandbox-layout') || editor;
  var iframe = scope.querySelector('iframe');
  var runBtn = editor.querySelector('[data-accion="ejecutar"]');
  var htmlArea = editor.querySelector('[data-code="html"]');
  var cssArea = editor.querySelector('[data-code="css"]');
  var jsArea = editor.querySelector('[data-code="js"]');

  // Consola tipo DevTools: se usa la del HTML si existe, o se crea automáticamente bajo la vista previa
  var consola = scope.querySelector('.dp-consola-salida');
  if (!consola && iframe) {
    var contenedorPreview = scope.querySelector('.dp-preview-wrap') || scope.querySelector('.dp-sandbox-preview');
    if (contenedorPreview) {
      consola = document.createElement('div');
      consola.className = 'dp-consola-salida';
      contenedorPreview.appendChild(consola);
    }
  }
  // Botón "Limpiar consola" en la barra de herramientas del editor, si hay barra
  var barraHerramientas = editor.querySelector('.dp-editor-toolbar');
  if (barraHerramientas && consola && !barraHerramientas.querySelector('[data-accion="limpiar-consola"]')) {
    var derecha = barraHerramientas.querySelector('.dp-editor-toolbar-derecha');
    if (!derecha) {
      derecha = document.createElement('span');
      derecha.className = 'dp-editor-toolbar-derecha';
      var existente = barraHerramientas.querySelector('[data-accion="ejecutar"]');
      if (existente) { barraHerramientas.appendChild(derecha); derecha.appendChild(existente); }
    }
    var btnLimpiar = document.createElement('button');
    btnLimpiar.type = 'button';
    btnLimpiar.className = 'dp-btn dp-btn-sm dp-btn-outline';
    btnLimpiar.dataset.accion = 'limpiar-consola';
    btnLimpiar.style.color = '#cbd5e1';
    btnLimpiar.style.borderColor = '#334155';
    btnLimpiar.style.background = 'transparent';
    btnLimpiar.textContent = '🗑 Consola';
    derecha.insertBefore(btnLimpiar, derecha.firstChild);
  }

  // --- Convertir un textarea en editor CodeMirror (números de línea, resaltado, etc.) ---
  // Importante: se inicializa "perezosamente" (solo cuando su panel ya es visible),
  // porque CodeMirror mide mal el tamaño si nace dentro de un contenedor display:none.
  function inicializarCodeMirror(area) {
    if (!area || area.cmInstance || typeof CodeMirror === 'undefined') return;
    var cm = CodeMirror.fromTextArea(area, {
      mode: dpModoParaLenguaje(area.dataset.code),
      theme: 'dracula',
      lineNumbers: true,
      tabSize: 2,
      indentUnit: 2,
      lineWrapping: true,
      matchBrackets: true,
      extraKeys: {
        'Ctrl-Enter': function () { ejecutar(); },
        'Cmd-Enter': function () { ejecutar(); },
      },
    });
    area.cmInstance = cm;
    var timeout;
    cm.on('change', function () {
      clearTimeout(timeout);
      timeout = setTimeout(ejecutar, 500);
    });
  }

  // El panel activo por defecto (normalmente HTML) ya es visible: se inicializa ya mismo.
  panels.forEach(function (panel) {
    if (panel.classList.contains('activa')) {
      inicializarCodeMirror(panel.querySelector('textarea'));
    }
  });

  // --- Tabs HTML / CSS / JS ---
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('activa'); });
      panels.forEach(function (p) { p.classList.remove('activa'); });
      tab.classList.add('activa');
      var panel = editor.querySelector('[data-panel="' + tab.dataset.tab + '"]');
      if (panel) panel.classList.add('activa');
      // Recién ahora que el panel es visible: inicializa (o refresca) su CodeMirror
      var area = panel ? panel.querySelector('textarea') : null;
      if (area) {
        if (!area.cmInstance) {
          inicializarCodeMirror(area);
          setTimeout(function () { area.cmInstance && area.cmInstance.refresh(); }, 0);
        } else {
          setTimeout(function () { area.cmInstance.refresh(); area.cmInstance.focus(); }, 0);
        }
      }
    });
  });

  if (!iframe) return;

  function escaparHtmlConsola(valor) {
    var div = document.createElement('div');
    div.textContent = valor;
    return div.innerHTML;
  }

  function formatearArgConsola(arg) {
    if (typeof arg === 'object') {
      try { return JSON.stringify(arg); } catch (e) { return String(arg); }
    }
    return String(arg);
  }

  function limpiarConsola() {
    if (consola) consola.innerHTML = '';
  }

  function ejecutar() {
    var html = dpValorEditor(htmlArea);
    var css = dpValorEditor(cssArea);
    var js = dpValorEditor(jsArea);

    limpiarConsola();

    var scriptCaptura =
      '<script>' +
      '(function(){' +
      'function enviar(nivel, args) { try { parent.postMessage({ dpConsola: true, nivel: nivel, args: args.map(function(a){ try { return typeof a === "object" ? JSON.parse(JSON.stringify(a)) : a; } catch(e) { return String(a); } }) }, "*"); } catch(e) {} }' +
      'var metodosOriginales = {};' +
      '["log","warn","error","info"].forEach(function(nivel){' +
      '  metodosOriginales[nivel] = console[nivel];' +
      '  console[nivel] = function(){ enviar(nivel, Array.prototype.slice.call(arguments)); metodosOriginales[nivel].apply(console, arguments); };' +
      '});' +
      'window.onerror = function(mensaje, url, linea, col){ enviar("error", [mensaje + " (línea " + linea + ")"]); return false; };' +
      '})();' +
      '<\/script>';

    var doc = '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
      '<style>body{font-family:sans-serif;padding:16px;color:#1e293b;}' + css + '</style>' +
      '</head><body>' + scriptCaptura + html +
      '<script>' + js + '<\/script>' +
      '</body></html>';

    iframe.srcdoc = doc;
  }

  window.addEventListener('message', function (evento) {
    if (!evento.data || !evento.data.dpConsola || !consola || evento.source !== iframe.contentWindow) return;
    var linea = document.createElement('div');
    linea.className = 'dp-consola-linea dp-consola-' + evento.data.nivel;
    var textoArgs = evento.data.args.map(formatearArgConsola).join(' ');
    linea.innerHTML = '<span class="dp-consola-prefijo">' + (evento.data.nivel === 'error' ? '✗' : '›') + '</span> ' + escaparHtmlConsola(textoArgs);
    consola.appendChild(linea);
    consola.scrollTop = consola.scrollHeight;
  });

  if (runBtn) {
    runBtn.addEventListener('click', ejecutar);
  }

  var btnLimpiarConsola = scope.querySelector('[data-accion="limpiar-consola"]');
  if (btnLimpiarConsola) {
    btnLimpiarConsola.addEventListener('click', limpiarConsola);
  }

  // Fallback sin CodeMirror (por si el CDN no cargó): textarea normal con Tab funcional
  [htmlArea, cssArea, jsArea].forEach(function (area) {
    if (!area || typeof CodeMirror !== 'undefined') return;
    var timeout;
    area.addEventListener('input', function () {
      clearTimeout(timeout);
      timeout = setTimeout(ejecutar, 500);
    });
    area.addEventListener('keydown', function (e) {
      if (e.key === 'Tab') {
        e.preventDefault();
        var start = area.selectionStart, end = area.selectionEnd;
        area.value = area.value.substring(0, start) + '  ' + area.value.substring(end);
        area.selectionStart = area.selectionEnd = start + 2;
      }
    });
  });

  // Primera ejecución
  ejecutar();
}
