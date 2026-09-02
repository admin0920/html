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

$totalLeccionesCompletadas = contar_lecciones_completadas($usuario['id']);
$insigniasGanadas = obtener_insignias_usuario($usuario['id']);
$todasInsignias = obtener_todas_insignias();
$idsGanadas = array_column($insigniasGanadas, 'id');
$retosCompletados = contar_retos_completados($usuario['id']);
$labsCompletados = contar_laboratorios_completados($usuario['id']);

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
          <div class="dp-stat"><b><?= (int) $totalLeccionesCompletadas ?></b><span>Lecciones</span></div>
          <div class="dp-stat"><b><?= $retosCompletados ?></b><span>Retos</span></div>
          <div class="dp-stat"><b><?= $labsCompletados ?></b><span>Laboratorios</span></div>
          <div class="dp-stat"><b><?= count($insigniasGanadas) ?>/<?= count($todasInsignias) ?></b><span>Insignias</span></div>
        </div>
        <p class="dp-mt" style="margin-bottom:0;">
          <span class="dp-badge dp-badge-admin"><?= $usuario['modo_pro'] ? '🚀 Modo PRO' : h(nombre_plan($usuario['plan_ritmo'])) ?></span>
          <a href="<?= url('roadmap.php') ?>" style="margin-left:10px;font-size:.85rem;">Ver / cambiar plan →</a>
        </p>
      </div>
    </div>

    <h2>🏅 Insignias</h2>
    <div class="dp-badges-grid dp-mt">
      <?php foreach ($todasInsignias as $ins): ?>
        <?php $obtenida = in_array($ins['id'], $idsGanadas, true); ?>
        <div class="dp-badge-chip <?= $obtenida ? 'dp-badge-obtenida' : 'dp-badge-bloqueada' ?>" title="<?= h($ins['descripcion']) ?>">
          <span class="dp-badge-icono"><?= $obtenida ? h($ins['icono']) : '🔒' ?></span>
          <span class="dp-badge-nombre"><?= h($ins['nombre']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <h2 class="dp-mt">Tu progreso por curso</h2>
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
