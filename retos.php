<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$usuario = usuario_actual();
$retos = obtener_retos();

$iconosLenguaje = ['html' => '🧱', 'css' => '🎨', 'js' => '⚡'];
$colorDificultad = ['facil' => '#10b981', 'medio' => '#f59e0b', 'dificil' => '#ef4444'];

$tituloPagina = 'Retos de código — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container">
    <h1 class="dp-center">🎯 Retos de código</h1>
    <p class="dp-section-sub">Mini ejercicios con comprobación automática. Resuelve el reto y el sistema verifica tu código al instante.</p>

    <div class="dp-grid">
      <?php foreach ($retos as $reto): ?>
        <?php $completado = $usuario && reto_completado($usuario['id'], $reto['id']); ?>
        <a href="<?= url('reto.php?slug=' . urlencode($reto['slug'])) ?>" class="dp-card" style="text-decoration:none;color:inherit;">
          <div class="dp-flex-between">
            <span class="dp-card-icon"><?= $iconosLenguaje[$reto['lenguaje']] ?? '💻' ?></span>
            <?php if ($completado): ?><span style="color:#10b981;font-size:1.4rem;">✔</span><?php endif; ?>
          </div>
          <h3><?= h($reto['titulo']) ?></h3>
          <p class="dp-muted"><?= strip_tags($reto['enunciado']) ?></p>
          <div class="dp-flex-between">
            <span class="dp-badge" style="background:<?= $colorDificultad[$reto['dificultad']] ?>22;color:<?= $colorDificultad[$reto['dificultad']] ?>;"><?= ucfirst($reto['dificultad']) ?></span>
            <span class="dp-muted" style="font-size:.85rem;">+<?= (int) $reto['puntos'] ?> pts</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (!$usuario): ?>
      <p class="dp-empty"><a href="<?= url('login.php') ?>">Inicia sesión</a> para guardar tu progreso en los retos.</p>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
