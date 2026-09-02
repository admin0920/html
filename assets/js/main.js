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

  // Tabs de los editores (HTML/CSS/JS)
  document.querySelectorAll('.dp-editor').forEach(initEditor);
});

function initEditor(editor) {
  var tabs = editor.querySelectorAll('.dp-editor-tabs button');
  var panels = editor.querySelectorAll('.dp-editor-panel');
  var iframe = editor.querySelector('iframe');

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) { t.classList.remove('activa'); });
      panels.forEach(function (p) { p.classList.remove('activa'); });
      tab.classList.add('activa');
      var panel = editor.querySelector('[data-panel="' + tab.dataset.tab + '"]');
      if (panel) panel.classList.add('activa');
    });
  });

  if (!iframe) return;

  var runBtn = editor.querySelector('[data-accion="ejecutar"]');
  var htmlArea = editor.querySelector('[data-code="html"]');
  var cssArea = editor.querySelector('[data-code="css"]');
  var jsArea = editor.querySelector('[data-code="js"]');

  function ejecutar() {
    var html = htmlArea ? htmlArea.value : '';
    var css = cssArea ? cssArea.value : '';
    var js = jsArea ? jsArea.value : '';

    var doc = '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
      '<style>body{font-family:sans-serif;padding:16px;color:#1e293b;}' + css + '</style>' +
      '</head><body>' + html +
      '<script>window.onerror=function(msg){document.body.insertAdjacentHTML("beforeend","<pre style=\\"color:red;white-space:pre-wrap\\">"+msg+"</pre>");};<\/script>' +
      '<script>' + js + '<\/script>' +
      '</body></html>';

    iframe.srcdoc = doc;
  }

  if (runBtn) {
    runBtn.addEventListener('click', ejecutar);
  }

  [htmlArea, cssArea, jsArea].forEach(function (area) {
    if (!area) return;
    var timeout;
    area.addEventListener('input', function () {
      clearTimeout(timeout);
      timeout = setTimeout(ejecutar, 500);
    });
    // Soporta indentación con Tab dentro del textarea
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
