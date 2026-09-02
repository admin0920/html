<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug = $_GET['slug'] ?? '';
$curso = obtener_curso_por_slug($slug);

if (!$curso) {
    http_response_code(404);
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="dp-empty"><h2>Curso no encontrado</h2><p><a href="' . url('cursos.php') . '">Volver a cursos</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$usuario = usuario_actual();
$modulos = obtener_modulos_con_lecciones($curso['id']);
$progreso = $usuario ? progreso_curso($usuario['id'], $curso['id']) : 0;
$tituloPagina = $curso['titulo'] . ' — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-hero" style="padding:40px 0;">
  <div class="dp-container">
    <h1><?= h($curso['icono']) ?> <?= h($curso['titulo']) ?></h1>
    <p><?= h($curso['descripcion']) ?></p>
    <?php if ($usuario): ?>
      <div style="max-width:400px;margin:0 auto;">
        <div class="dp-progress-bar" style="background:rgba(255,255,255,.25);"><div style="width:<?= $progreso ?>%;background:white;"></div></div>
        <p style="font-size:.85rem;margin-top:6px;"><?= $progreso ?>% completado</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="dp-container">
  <div class="dp-curso-layout">
    <aside class="dp-sidebar">
      <?php foreach ($modulos as $modulo): ?>
        <h4><?= h($modulo['titulo']) ?></h4>
        <ul>
          <?php foreach ($modulo['lecciones'] as $leccion): ?>
            <?php $completada = $usuario && leccion_completada($usuario['id'], $leccion['id']); ?>
            <li>
              <a href="<?= url('leccion.php?slug=' . urlencode($leccion['slug'])) ?>">
                <?php if ($completada): ?><span class="dp-check">✔</span><?php else: ?>⭕<?php endif; ?>
                <?= h($leccion['titulo']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
      <?php if (empty($modulos)): ?>
        <p class="dp-muted">Este curso todavía no tiene contenido.</p>
      <?php endif; ?>
    </aside>

    <div class="dp-contenido-leccion">
      <h1>Contenido del curso</h1>
      <p class="dp-muted">Selecciona una lección en el menú para comenzar. Recomendamos seguir el orden de los módulos.</p>
      <?php if (!empty($modulos) && !empty($modulos[0]['lecciones'])): ?>
        <a class="dp-btn dp-btn-primary dp-btn-lg" href="<?= url('leccion.php?slug=' . urlencode($modulos[0]['lecciones'][0]['slug'])) ?>">
          <?= $progreso > 0 ? 'Continuar aprendiendo' : 'Comenzar curso' ?> →
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
