<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';
$lab = obtener_laboratorio_por_slug($slug);

if (!$lab) {
    http_response_code(404);
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="dp-empty"><h2>Laboratorio no encontrado</h2><p><a href="' . url('laboratorio.php') . '">Volver</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$usuario = usuario_actual();

if ($usuario && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    if (isset($_POST['guardar'])) {
        guardar_solucion_laboratorio($usuario['id'], $lab['id'], $_POST['html'] ?? '', $_POST['css'] ?? '', $_POST['js'] ?? '');
        redirigir('laboratorio_detalle.php?slug=' . urlencode($slug) . '&guardado=1');
    }
    if (isset($_POST['completar'])) {
        guardar_solucion_laboratorio($usuario['id'], $lab['id'], $_POST['html'] ?? '', $_POST['css'] ?? '', $_POST['js'] ?? '');
        $esNuevo = marcar_laboratorio_completado($usuario['id'], $lab);
        $insignias = $esNuevo ? evaluar_insignias($usuario['id']) : [];
        $_SESSION['dp_insignias_nuevas'] = $insignias;
        redirigir('laboratorio_detalle.php?slug=' . urlencode($slug) . '&completado=1');
    }
}

$solucionGuardada = $usuario ? obtener_solucion_laboratorio($usuario['id'], $lab['id']) : null;
$completado = $usuario && laboratorio_completado($usuario['id'], $lab['id']);

$html = $solucionGuardada['html'] ?? $lab['html_inicial'] ?? '';
$css = $solucionGuardada['css'] ?? $lab['css_inicial'] ?? '';
$js = $solucionGuardada['js'] ?? $lab['js_inicial'] ?? '';

$insigniasNuevas = $_SESSION['dp_insignias_nuevas'] ?? [];
unset($_SESSION['dp_insignias_nuevas']);

$necesitaEditor = true;
$tituloPagina = $lab['titulo'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="dp-container" style="padding:20px 20px 0;">
  <p><a href="<?= url('laboratorio.php') ?>">← Todos los laboratorios</a></p>
</div>

<div class="dp-container">
  <div class="dp-curso-layout">
    <aside class="dp-sidebar">
      <h4>🔬 <?= h($lab['titulo']) ?></h4>
      <p class="dp-muted" style="font-size:.85rem;"><?= h($lab['categoria']) ?> · +<?= (int) $lab['puntos'] ?> pts</p>
      <p><?= h($lab['descripcion']) ?></p>
      <h4>Requisitos</h4>
      <div style="font-size:.88rem;"><?= $lab['requisitos'] ?></div>

      <?php if (isset($_GET['guardado'])): ?><div class="dp-alert dp-alert-success dp-mt">Progreso guardado ✔</div><?php endif; ?>
      <?php if (isset($_GET['completado'])): ?>
        <div class="dp-alert dp-alert-success dp-mt">¡Laboratorio completado! 🎉</div>
        <?php if (!empty($insigniasNuevas)): ?>
          <?php foreach ($insigniasNuevas as $ins): ?>
            <div class="dp-alert dp-alert-success">🏅 Insignia: <?= h($ins['icono'] . ' ' . $ins['nombre']) ?></div>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($completado): ?>
        <p class="dp-mt" style="color:#15803d;font-weight:600;">✔ Completado</p>
      <?php endif; ?>
    </aside>

    <div class="dp-contenido-leccion" style="padding:0;overflow:hidden;">
      <form method="post" id="labForm">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="html" id="inputHtml">
        <input type="hidden" name="css" id="inputCss">
        <input type="hidden" name="js" id="inputJs">

        <div class="dp-editor" style="border:none;border-radius:0;">
          <div class="dp-editor-tabs">
            <button type="button" class="activa" data-tab="html">HTML</button>
            <button type="button" data-tab="css">CSS</button>
            <button type="button" data-tab="js">JS</button>
          </div>
          <div class="dp-editor-panel activa" data-panel="html"><textarea class="dp-code" data-code="html" spellcheck="false" style="min-height:280px;"><?= h($html) ?></textarea></div>
          <div class="dp-editor-panel" data-panel="css"><textarea class="dp-code" data-code="css" spellcheck="false" style="min-height:280px;"><?= h($css) ?></textarea></div>
          <div class="dp-editor-panel" data-panel="js"><textarea class="dp-code" data-code="js" spellcheck="false" style="min-height:280px;"><?= h($js) ?></textarea></div>
          <div class="dp-editor-toolbar">
            <span>✏️ Construye tu proyecto · <span class="dp-atajo-teclado">Ctrl+Enter</span> para ejecutar</span>
            <button type="button" class="dp-btn dp-btn-secundario" data-accion="ejecutar" style="padding:6px 14px;">▶ Ejecutar</button>
          </div>
          <div class="dp-preview-wrap"><iframe title="Resultado" style="height:360px;"></iframe></div>
        </div>

        <div class="dp-flex-between" style="padding:20px;">
          <?php if ($usuario): ?>
            <button type="submit" name="guardar" class="dp-btn dp-btn-outline">💾 Guardar progreso</button>
            <button type="submit" name="completar" class="dp-btn dp-btn-primary" onclick="return confirm('¿Confirmas que cumpliste los requisitos y quieres marcarlo como completado?')">✔ Marcar como completado</button>
          <?php else: ?>
            <a href="<?= url('login.php') ?>" class="dp-btn dp-btn-primary">Inicia sesión para guardar tu progreso</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('labForm').addEventListener('submit', function () {
  document.getElementById('inputHtml').value = dpValorEditor(document.querySelector('[data-code="html"]'));
  document.getElementById('inputCss').value = dpValorEditor(document.querySelector('[data-code="css"]'));
  document.getElementById('inputJs').value = dpValorEditor(document.querySelector('[data-code="js"]'));
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
