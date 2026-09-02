<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$quizId = (int) ($_GET['id'] ?? 0);
$quiz = db_query_una('SELECT q.*, l.slug AS leccion_slug FROM quizzes q JOIN lecciones l ON l.id = q.leccion_id WHERE q.id = ?', 'i', [$quizId]);

if (!$quiz) {
    http_response_code(404);
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="dp-empty"><h2>Quiz no encontrado</h2></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$usuario = usuario_actual();
$preguntas = db_query('SELECT * FROM quiz_preguntas WHERE quiz_id = ? ORDER BY orden ASC', 'i', [$quizId]);
foreach ($preguntas as &$p) {
    $p['opciones'] = db_query('SELECT * FROM quiz_opciones WHERE pregunta_id = ? ORDER BY id ASC', 'i', [$p['id']]);
}
unset($p);

$resultado = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $correctas = 0;
    foreach ($preguntas as $p) {
        $opcionElegida = (int) ($_POST['pregunta_' . $p['id']] ?? 0);
        foreach ($p['opciones'] as $o) {
            if ($o['id'] == $opcionElegida && $o['es_correcta']) {
                $correctas++;
            }
        }
    }
    $resultado = ['correctas' => $correctas, 'total' => count($preguntas)];

    if ($usuario) {
        db_ejecutar('INSERT INTO quiz_resultados (usuario_id, quiz_id, puntaje, total) VALUES (?, ?, ?, ?)', 'iiii', [$usuario['id'], $quizId, $correctas, count($preguntas)]);
        sumar_puntos($usuario['id'], $correctas * 5);
    }
}

$tituloPagina = $quiz['titulo'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section">
  <div class="dp-container" style="max-width:700px;">
    <div class="dp-contenido-leccion">
      <p><a href="<?= url('leccion.php?slug=' . urlencode($quiz['leccion_slug'])) ?>">← Volver a la lección</a></p>
      <h1>🧠 <?= h($quiz['titulo']) ?></h1>

      <?php if ($resultado): ?>
        <div class="dp-quiz-resultado">
          <div class="dp-puntaje"><?= $resultado['correctas'] ?>/<?= $resultado['total'] ?></div>
          <p class="dp-muted">
            <?php if ($resultado['correctas'] === $resultado['total']): ?>
              ¡Excelente! Respondiste todo correctamente. 🎉
            <?php elseif ($resultado['correctas'] > 0): ?>
              ¡Buen trabajo! Sigue repasando para mejorar.
            <?php else: ?>
              No te preocupes, repasa la lección e inténtalo de nuevo.
            <?php endif; ?>
          </p>
          <?php if ($usuario): ?><p class="dp-muted">+<?= $resultado['correctas'] * 5 ?> puntos</p><?php endif; ?>
          <a href="<?= url('quiz.php?id=' . $quizId) ?>" class="dp-btn dp-btn-outline">Reintentar</a>
          <a href="<?= url('leccion.php?slug=' . urlencode($quiz['leccion_slug'])) ?>" class="dp-btn dp-btn-primary">Volver a la lección</a>
        </div>
      <?php else: ?>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <?php foreach ($preguntas as $i => $p): ?>
            <div class="dp-quiz-pregunta">
              <p><strong><?= $i + 1 ?>. <?= h($p['pregunta']) ?></strong></p>
              <?php foreach ($p['opciones'] as $o): ?>
                <label class="dp-quiz-opcion">
                  <input type="radio" name="pregunta_<?= $p['id'] ?>" value="<?= $o['id'] ?>" required>
                  <?= h($o['texto']) ?>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
          <?php if (empty($preguntas)): ?>
            <p class="dp-muted">Este quiz todavía no tiene preguntas.</p>
          <?php else: ?>
            <button type="submit" class="dp-btn dp-btn-primary dp-btn-lg">Enviar respuestas</button>
          <?php endif; ?>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
