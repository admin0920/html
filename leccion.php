<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';
$leccion = obtener_leccion_por_slug($slug);

if (!$leccion) {
    http_response_code(404);
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="dp-empty"><h2>Lección no encontrada</h2><p><a href="' . url('cursos.php') . '">Volver a cursos</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$usuario = usuario_actual();
$curso = db_query_una('SELECT * FROM cursos WHERE id = ?', 'i', [$leccion['curso_id']]);
$modulos = obtener_modulos_con_lecciones($leccion['curso_id']);

$moduloActual = db_query_una('SELECT orden FROM modulos WHERE id = ?', 'i', [$leccion['modulo_id']]);
$adyacentes = lecciones_adyacentes($leccion['curso_id'], (int) $moduloActual['orden'], (int) $leccion['orden']);

$mensaje = '';
if ($usuario && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completar']) && csrf_valido()) {
    marcar_leccion_completada($usuario['id'], $leccion['id']);
    redirigir('leccion.php?slug=' . urlencode($slug) . '&completada=1');
}

$completada = $usuario && leccion_completada($usuario['id'], $leccion['id']);
$quiz = db_query_una('SELECT * FROM quizzes WHERE leccion_id = ?', 'i', [$leccion['id']]);

$tituloPagina = $leccion['titulo'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="dp-container">
  <div class="dp-curso-layout">
    <aside class="dp-sidebar">
      <p style="margin-top:0;"><a href="<?= url('curso.php?slug=' . urlencode($curso['slug'])) ?>">← <?= h($curso['titulo']) ?></a></p>
      <?php foreach ($modulos as $modulo): ?>
        <h4><?= h($modulo['titulo']) ?></h4>
        <ul>
          <?php foreach ($modulo['lecciones'] as $l): ?>
            <?php $comp = $usuario && leccion_completada($usuario['id'], $l['id']); ?>
            <li>
              <a href="<?= url('leccion.php?slug=' . urlencode($l['slug'])) ?>" class="<?= $l['id'] == $leccion['id'] ? 'activa' : '' ?>">
                <?php if ($comp): ?><span class="dp-check">✔</span><?php else: ?>⭕<?php endif; ?>
                <?= h($l['titulo']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </aside>

    <div class="dp-contenido-leccion">
      <p class="dp-muted" style="font-size:.85rem;"><?= h($leccion['modulo_titulo']) ?> · ⏱ <?= (int) $leccion['minutos_estimados'] ?> min</p>
      <h1><?= h($leccion['titulo']) ?></h1>

      <?php if (isset($_GET['completada'])): ?>
        <div class="dp-alert dp-alert-success">¡Lección marcada como completada! +10 puntos 🎉</div>
      <?php endif; ?>

      <?= $leccion['contenido'] /* contenido HTML controlado por el admin */ ?>

      <?php if ($leccion['codigo_html'] || $leccion['codigo_css'] || $leccion['codigo_js']): ?>
        <div class="dp-editor">
          <div class="dp-editor-tabs">
            <button type="button" class="activa" data-tab="html">HTML</button>
            <button type="button" data-tab="css">CSS</button>
            <button type="button" data-tab="js">JS</button>
          </div>
          <div class="dp-editor-panel activa" data-panel="html">
            <textarea class="dp-code" data-code="html" spellcheck="false"><?= h($leccion['codigo_html'] ?? '') ?></textarea>
          </div>
          <div class="dp-editor-panel" data-panel="css">
            <textarea class="dp-code" data-code="css" spellcheck="false"><?= h($leccion['codigo_css'] ?? '') ?></textarea>
          </div>
          <div class="dp-editor-panel" data-panel="js">
            <textarea class="dp-code" data-code="js" spellcheck="false"><?= h($leccion['codigo_js'] ?? '') ?></textarea>
          </div>
          <div class="dp-editor-toolbar">
            <span>✏️ Edita el código y mira el resultado en vivo</span>
            <button type="button" class="dp-btn dp-btn-secundario" data-accion="ejecutar" style="padding:6px 14px;">▶ Ejecutar</button>
          </div>
          <div class="dp-preview-wrap"><iframe title="Resultado"></iframe></div>
        </div>
      <?php endif; ?>

      <div class="dp-flex-between dp-mt">
        <div>
          <?php if ($adyacentes['anterior']): ?>
            <a href="<?= url('leccion.php?slug=' . urlencode($adyacentes['anterior']['slug'])) ?>" class="dp-btn dp-btn-outline">← Anterior</a>
          <?php endif; ?>
        </div>
        <div class="dp-flex">
          <?php if ($quiz): ?>
            <a href="<?= url('quiz.php?id=' . (int) $quiz['id']) ?>" class="dp-btn dp-btn-outline">🧠 Hacer quiz</a>
          <?php endif; ?>

          <?php if ($usuario): ?>
            <?php if (!$completada): ?>
              <form method="post">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <button type="submit" name="completar" class="dp-btn dp-btn-secundario">Marcar como completada ✔</button>
              </form>
            <?php else: ?>
              <span class="dp-btn dp-btn-outline" style="cursor:default;">✔ Completada</span>
            <?php endif; ?>
          <?php else: ?>
            <a href="<?= url('login.php') ?>" class="dp-btn dp-btn-secundario">Inicia sesión para guardar progreso</a>
          <?php endif; ?>

          <?php if ($adyacentes['siguiente']): ?>
            <a href="<?= url('leccion.php?slug=' . urlencode($adyacentes['siguiente']['slug'])) ?>" class="dp-btn dp-btn-primary">Siguiente →</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
