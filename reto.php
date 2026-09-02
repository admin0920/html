<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';
$reto = obtener_reto_por_slug($slug);

if (!$reto) {
    http_response_code(404);
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="dp-empty"><h2>Reto no encontrado</h2><p><a href="' . url('retos.php') . '">Volver a retos</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$usuario = usuario_actual();

// Endpoint AJAX: marcar el reto como completado tras validarlo en el navegador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completar'])) {
    header('Content-Type: application/json');
    if (!$usuario || !csrf_valido()) {
        echo json_encode(['ok' => false, 'error' => 'No autorizado']);
        exit;
    }
    $esNuevo = marcar_reto_completado($usuario['id'], $reto);
    $insigniasNuevas = $esNuevo ? evaluar_insignias($usuario['id']) : [];
    echo json_encode(['ok' => true, 'nuevo' => $esNuevo, 'insignias' => $insigniasNuevas]);
    exit;
}

$completado = $usuario && reto_completado($usuario['id'], $reto['id']);

$tituloPagina = $reto['titulo'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section" style="padding-top:20px;">
  <div class="dp-container">
    <p><a href="<?= url('retos.php') ?>">← Todos los retos</a></p>
    <div class="dp-contenido-leccion">
      <div class="dp-flex-between">
        <h1 style="margin:0;">🎯 <?= h($reto['titulo']) ?></h1>
        <span class="dp-badge dp-badge-admin"><?= strtoupper($reto['lenguaje']) ?> · <?= ucfirst($reto['dificultad']) ?></span>
      </div>
      <p><?= $reto['enunciado'] ?></p>

      <div class="dp-editor" id="retoEditor">
        <div class="dp-editor-tabs">
          <button type="button" class="activa" data-tab="html">HTML</button>
          <button type="button" data-tab="css">CSS</button>
          <button type="button" data-tab="js">JS</button>
        </div>
        <div class="dp-editor-panel activa" data-panel="html"><textarea class="dp-code" data-code="html" spellcheck="false"><?= h($reto['html_inicial'] ?? '') ?></textarea></div>
        <div class="dp-editor-panel" data-panel="css"><textarea class="dp-code" data-code="css" spellcheck="false"><?= h($reto['css_inicial'] ?? '') ?></textarea></div>
        <div class="dp-editor-panel" data-panel="js"><textarea class="dp-code" data-code="js" spellcheck="false"><?= h($reto['js_inicial'] ?? '') ?></textarea></div>
        <div class="dp-editor-toolbar">
          <span>✏️ Escribe tu solución · <span class="dp-atajo-teclado">Ctrl+Enter</span> para ejecutar</span>
          <button type="button" class="dp-btn dp-btn-secundario" data-accion="ejecutar" style="padding:6px 14px;">▶ Ejecutar</button>
        </div>
        <div class="dp-preview-wrap"><iframe title="Resultado" id="retoIframe"></iframe></div>
      </div>

      <div class="dp-mt dp-flex-between">
        <button type="button" id="btnComprobar" class="dp-btn dp-btn-primary dp-btn-lg">✅ Comprobar solución</button>
        <span id="retoEstado">
          <?php if ($completado): ?><span style="color:#15803d;font-weight:600;">✔ ¡Ya completaste este reto!</span><?php endif; ?>
        </span>
      </div>
      <div id="retoResultado" class="dp-mt"></div>
    </div>
  </div>
</section>

<script>
window.DP_RETO = {
  comprobacion: <?= json_encode($reto['comprobacion_js']) ?>,
  logueado: <?= $usuario ? 'true' : 'false' ?>,
  csrf: <?= json_encode(csrf_token()) ?>,
  completarUrl: <?= json_encode(url('reto.php?slug=' . urlencode($reto['slug']))) ?>
};
</script>
<script src="<?= url('assets/js/retos.js') ?>"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
