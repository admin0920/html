<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$usuario = usuario_actual();
$cursos = obtener_cursos();
$tituloPagina = SITE_NAME . ' — Aprende HTML, CSS y JavaScript desde 0';
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-hero">
  <div class="dp-container">
    <h1>Aprende a programar desde 0 🚀</h1>
    <p>Cursos gratuitos de HTML, CSS y JavaScript en español, con lecciones interactivas, editor de código en vivo y ejercicios prácticos.</p>
    <div class="dp-hero-actions">
      <a href="<?= url('cursos.php') ?>" class="dp-btn dp-btn-secundario dp-btn-lg">Ver cursos</a>
      <?php if (!$usuario): ?>
        <a href="<?= url('register.php') ?>" class="dp-btn dp-btn-outline dp-btn-lg">Crear cuenta gratis</a>
      <?php else: ?>
        <a href="<?= url('sandbox.php') ?>" class="dp-btn dp-btn-outline dp-btn-lg">Abrir Sandbox 🧪</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="dp-section">
  <div class="dp-container">
    <h2>Nuestros cursos</h2>
    <p class="dp-section-sub">Desde las primeras etiquetas hasta programación asíncrona avanzada.</p>
    <div class="dp-grid">
      <?php foreach ($cursos as $curso): ?>
        <div class="dp-card">
          <div class="dp-card-icon"><?= h($curso['icono']) ?></div>
          <h3><?= h($curso['titulo']) ?></h3>
          <p><?= h($curso['descripcion']) ?></p>
          <?php if ($usuario): ?>
            <?php $p = progreso_curso($usuario['id'], $curso['id']); ?>
            <div class="dp-progress-bar"><div style="width:<?= (int) $p ?>%"></div></div>
            <p class="dp-muted" style="font-size:.8rem;margin:0 0 12px;"><?= (int) $p ?>% completado</p>
          <?php endif; ?>
          <a href="<?= url('curso.php?slug=' . urlencode($curso['slug'])) ?>" class="dp-btn dp-btn-primary">Empezar curso</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="dp-section" style="background:white;border-top:1px solid var(--borde);border-bottom:1px solid var(--borde);">
  <div class="dp-container">
    <h2>Funciones para aprender más rápido</h2>
    <p class="dp-section-sub">Todo lo que necesitas para practicar de verdad, no solo leer teoría.</p>
    <div class="dp-grid">
      <div class="dp-card">
        <div class="dp-card-icon">🧪</div>
        <h3>Editor y vista previa en vivo</h3>
        <p>Cada lección incluye un editor HTML/CSS/JS con resultado en tiempo real, para que veas al instante lo que programas.</p>
      </div>
      <div class="dp-card">
        <div class="dp-card-icon">✅</div>
        <h3>Seguimiento de progreso</h3>
        <p>Marca lecciones como completadas y mira tu avance por curso con barras de progreso.</p>
      </div>
      <div class="dp-card">
        <div class="dp-card-icon">🧠</div>
        <h3>Quizzes de repaso</h3>
        <p>Pon a prueba lo aprendido con cuestionarios cortos al final de cada lección.</p>
      </div>
      <div class="dp-card">
        <div class="dp-card-icon">🔥</div>
        <h3>Racha y puntos</h3>
        <p>Gana puntos por cada lección completada y mantén tu racha de días de estudio activa.</p>
      </div>
      <div class="dp-card">
        <div class="dp-card-icon">🖥️</div>
        <h3>Sandbox libre</h3>
        <p>Un playground tipo CodePen para practicar libremente con HTML, CSS y JS sin límites.</p>
      </div>
      <div class="dp-card">
        <div class="dp-card-icon">📱</div>
        <h3>100% responsive</h3>
        <p>Estudia desde el computador, tablet o celular, con una interfaz adaptada a cualquier pantalla.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
