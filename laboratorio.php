<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$usuario = usuario_actual();
$laboratorios = obtener_laboratorios();
$colorDificultad = ['facil' => '#10b981', 'medio' => '#f59e0b', 'dificil' => '#ef4444'];

$tituloPagina = 'Laboratorio — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container">
    <h1 class="dp-center">🔬 Laboratorio de práctica</h1>
    <p class="dp-section-sub">Proyectos reales para aplicar todo lo aprendido: construye, guarda tu avance y marca como completado.</p>

    <div class="dp-grid">
      <?php foreach ($laboratorios as $lab): ?>
        <?php $completado = $usuario && laboratorio_completado($usuario['id'], $lab['id']); ?>
        <a href="<?= url('laboratorio_detalle.php?slug=' . urlencode($lab['slug'])) ?>" class="dp-card" style="text-decoration:none;color:inherit;">
          <div class="dp-flex-between">
            <span class="dp-card-icon">🧪</span>
            <?php if ($completado): ?><span style="color:#10b981;font-size:1.4rem;">✔</span><?php endif; ?>
          </div>
          <h3><?= h($lab['titulo']) ?></h3>
          <p class="dp-muted" style="font-size:.85rem;"><?= h($lab['categoria']) ?></p>
          <p class="dp-muted"><?= h($lab['descripcion']) ?></p>
          <div class="dp-flex-between">
            <span class="dp-badge" style="background:<?= $colorDificultad[$lab['dificultad']] ?>22;color:<?= $colorDificultad[$lab['dificultad']] ?>;"><?= ucfirst($lab['dificultad']) ?></span>
            <span class="dp-muted" style="font-size:.85rem;">+<?= (int) $lab['puntos'] ?> pts</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (!$usuario): ?>
      <p class="dp-empty"><a href="<?= url('login.php') ?>">Inicia sesión</a> para guardar tu progreso en los laboratorios.</p>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
