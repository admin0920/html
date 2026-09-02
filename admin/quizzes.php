<?php
$paginaActiva = 'quizzes';
$tituloPagina = 'Quizzes';
require_once __DIR__ . '/_layout_top.php';

$error = '';
$leccionId = (int) ($_GET['leccion_id'] ?? 0);

// Crear quiz para una lección
if (isset($_POST['crear_quiz']) && csrf_valido()) {
    $lid = (int) $_POST['leccion_id'];
    $titulo = trim($_POST['titulo_quiz'] ?? 'Quiz');
    $existente = db_query_una('SELECT id FROM quizzes WHERE leccion_id = ?', 'i', [$lid]);
    if (!$existente) {
        db_ejecutar('INSERT INTO quizzes (leccion_id, titulo) VALUES (?, ?)', 'is', [$lid, $titulo]);
    }
    redirigir('admin/quizzes.php?leccion_id=' . $lid);
}

// Agregar pregunta
if (isset($_POST['agregar_pregunta']) && csrf_valido()) {
    $quizId = (int) $_POST['quiz_id'];
    $pregunta = trim($_POST['pregunta'] ?? '');
    $opciones = $_POST['opciones'] ?? [];
    $correcta = (int) ($_POST['correcta'] ?? -1);

    if ($pregunta !== '' && count(array_filter($opciones, fn($o) => trim($o) !== '')) >= 2) {
        $preguntaId = db_ejecutar('INSERT INTO quiz_preguntas (quiz_id, pregunta) VALUES (?, ?)', 'is', [$quizId, $pregunta]);
        foreach ($opciones as $i => $texto) {
            $texto = trim($texto);
            if ($texto === '') continue;
            $esCorrecta = ($i == $correcta) ? 1 : 0;
            db_ejecutar('INSERT INTO quiz_opciones (pregunta_id, texto, es_correcta) VALUES (?, ?, ?)', 'isi', [$preguntaId, $texto, $esCorrecta]);
        }
    }
    redirigir('admin/quizzes.php?leccion_id=' . $leccionId);
}

if (isset($_GET['eliminar_pregunta']) && isset($_GET['confirmar'])) {
    db_ejecutar('DELETE FROM quiz_preguntas WHERE id = ?', 'i', [(int) $_GET['eliminar_pregunta']]);
    redirigir('admin/quizzes.php?leccion_id=' . $leccionId);
}

$lecciones = db_query('SELECT l.id, l.titulo, m.titulo AS modulo_titulo FROM lecciones l JOIN modulos m ON m.id = l.modulo_id ORDER BY l.modulo_id, l.orden');
$leccion = $leccionId > 0 ? db_query_una('SELECT * FROM lecciones WHERE id = ?', 'i', [$leccionId]) : null;
$quiz = $leccion ? db_query_una('SELECT * FROM quizzes WHERE leccion_id = ?', 'i', [$leccionId]) : null;
$preguntas = [];
if ($quiz) {
    $preguntas = db_query('SELECT * FROM quiz_preguntas WHERE quiz_id = ? ORDER BY id', 'i', [$quiz['id']]);
    foreach ($preguntas as &$p) {
        $p['opciones'] = db_query('SELECT * FROM quiz_opciones WHERE pregunta_id = ? ORDER BY id', 'i', [$p['id']]);
    }
    unset($p);
}
?>

<h1>Quizzes</h1>

<div class="dp-field dp-mt">
  <label>Selecciona una lección</label>
  <select onchange="if(this.value) window.location='<?= url('admin/quizzes.php') ?>?leccion_id='+this.value">
    <option value="">-- Elegir lección --</option>
    <?php foreach ($lecciones as $l): ?>
      <option value="<?= $l['id'] ?>" <?= $leccionId == $l['id'] ? 'selected' : '' ?>><?= h($l['modulo_titulo']) ?> · <?= h($l['titulo']) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<?php if ($leccion): ?>
  <?php if (!$quiz): ?>
    <div class="dp-card dp-mt">
      <h3>Esta lección no tiene quiz todavía</h3>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="leccion_id" value="<?= $leccion['id'] ?>">
        <div class="dp-field"><label>Título del quiz</label><input type="text" name="titulo_quiz" value="Quiz: <?= h($leccion['titulo']) ?>"></div>
        <button type="submit" name="crear_quiz" class="dp-btn dp-btn-primary">Crear quiz</button>
      </form>
    </div>
  <?php else: ?>
    <div class="dp-card dp-mt">
      <h3><?= h($quiz['titulo']) ?> <a href="<?= url('quiz.php?id=' . $quiz['id']) ?>" target="_blank" style="font-size:.8rem;">(ver)</a></h3>

      <?php foreach ($preguntas as $i => $p): ?>
        <div class="dp-quiz-pregunta">
          <p><strong><?= $i + 1 ?>. <?= h($p['pregunta']) ?></strong>
            <a href="<?= url('admin/quizzes.php?leccion_id=' . $leccionId . '&eliminar_pregunta=' . $p['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar pregunta?')" style="color:#b91c1c;font-size:.8rem;">eliminar</a>
          </p>
          <ul>
            <?php foreach ($p['opciones'] as $o): ?>
              <li><?= h($o['texto']) ?> <?= $o['es_correcta'] ? '✔' : '' ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>

      <h4>Agregar pregunta</h4>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
        <div class="dp-field"><label>Pregunta</label><input type="text" name="pregunta" required></div>
        <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="dp-field dp-flex">
            <input type="radio" name="correcta" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> style="width:auto;">
            <input type="text" name="opciones[]" placeholder="Opción <?= $i + 1 ?><?= $i < 2 ? ' (requerida)' : ' (opcional)' ?>">
          </div>
        <?php endfor; ?>
        <p class="dp-muted" style="font-size:.8rem;">Marca con el punto la opción correcta.</p>
        <button type="submit" name="agregar_pregunta" class="dp-btn dp-btn-primary">Agregar pregunta</button>
      </form>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
