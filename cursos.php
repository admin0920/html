<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$usuario = usuario_actual();
$cursos = obtener_cursos();
$tituloPagina = 'Cursos — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section">
  <div class="dp-container">
    <h2>Todos los cursos</h2>
    <p class="dp-section-sub">Elige por dónde empezar. Recomendado: HTML → CSS → JavaScript.</p>
    <div class="dp-grid">
      <?php foreach ($cursos as $curso): ?>
        <?php $modulos = obtener_modulos_con_lecciones($curso['id']);
        $totalLecciones = 0;
        foreach ($modulos as $m) { $totalLecciones += count($m['lecciones']); } ?>
        <div class="dp-card">
          <div class="dp-card-icon"><?= h($curso['icono']) ?></div>
          <h3><?= h($curso['titulo']) ?></h3>
          <p><?= h($curso['descripcion']) ?></p>
          <p class="dp-muted" style="font-size:.85rem;"><?= count($modulos) ?> módulos · <?= $totalLecciones ?> lecciones</p>
          <?php if ($usuario): ?>
            <?php $p = progreso_curso($usuario['id'], $curso['id']); ?>
            <div class="dp-progress-bar"><div style="width:<?= (int) $p ?>%"></div></div>
            <p class="dp-muted" style="font-size:.8rem;margin:0 0 12px;"><?= (int) $p ?>% completado</p>
          <?php endif; ?>
          <a href="<?= url('curso.php?slug=' . urlencode($curso['slug'])) ?>" class="dp-btn dp-btn-primary">Ver curso</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
