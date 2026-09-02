<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requiere_login();
$usuario = usuario_actual();
$cursos = obtener_cursos();

$resultadosQuiz = db_query(
    'SELECT qr.puntaje, qr.total, qr.realizado_en, q.titulo FROM quiz_resultados qr JOIN quizzes q ON q.id = qr.quiz_id WHERE qr.usuario_id = ? ORDER BY qr.realizado_en DESC LIMIT 5',
    'i',
    [$usuario['id']]
);

$totalLeccionesCompletadas = db_query_una('SELECT COUNT(*) AS n FROM progreso WHERE usuario_id = ?', 'i', [$usuario['id']])['n'] ?? 0;

$tituloPagina = 'Mi perfil — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section">
  <div class="dp-container">
    <?php if (isset($_GET['bienvenida'])): ?>
      <div class="dp-alert dp-alert-success">¡Bienvenido/a a Dique Programando, <?= h($usuario['nombre']) ?>! 🎉 Empieza tu primer curso cuando quieras.</div>
    <?php endif; ?>

    <div class="dp-perfil-header">
      <div class="dp-avatar"><?= h(mb_strtoupper(mb_substr($usuario['nombre'], 0, 1))) ?></div>
      <div>
        <h1 style="margin:0;"><?= h($usuario['nombre']) ?></h1>
        <p class="dp-muted" style="margin:4px 0 0;"><?= h($usuario['email']) ?></p>
        <div class="dp-stats">
          <div class="dp-stat"><b><?= (int) $usuario['puntos'] ?></b><span>Puntos</span></div>
          <div class="dp-stat"><b><?= (int) $usuario['racha_dias'] ?> 🔥</b><span>Racha (días)</span></div>
          <div class="dp-stat"><b><?= (int) $totalLeccionesCompletadas ?></b><span>Lecciones completadas</span></div>
        </div>
      </div>
    </div>

    <h2>Tu progreso por curso</h2>
    <div class="dp-grid dp-mt">
      <?php foreach ($cursos as $curso): ?>
        <?php $p = progreso_curso($usuario['id'], $curso['id']); ?>
        <div class="dp-card">
          <div class="dp-card-icon"><?= h($curso['icono']) ?></div>
          <h3><?= h($curso['titulo']) ?></h3>
          <div class="dp-progress-bar"><div style="width:<?= $p ?>%"></div></div>
          <p class="dp-muted" style="font-size:.85rem;"><?= $p ?>% completado</p>
          <a href="<?= url('curso.php?slug=' . urlencode($curso['slug'])) ?>" class="dp-btn dp-btn-outline">Continuar</a>
        </div>
      <?php endforeach; ?>
    </div>

    <h2 class="dp-mt">Últimos resultados de quizzes</h2>
    <?php if (empty($resultadosQuiz)): ?>
      <p class="dp-muted">Aún no has realizado ningún quiz. <a href="<?= url('cursos.php') ?>">Empieza una lección</a>.</p>
    <?php else: ?>
      <table class="dp-table">
        <thead><tr><th>Quiz</th><th>Resultado</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach ($resultadosQuiz as $r): ?>
            <tr>
              <td><?= h($r['titulo']) ?></td>
              <td><?= (int) $r['puntaje'] ?>/<?= (int) $r['total'] ?></td>
              <td><?= tiempo_relativo($r['realizado_en']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
