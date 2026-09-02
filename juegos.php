<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$usuario = usuario_actual();
$tituloPagina = 'Arcade de juegos — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';

$juegos = [
    ['slug' => 'ordena-codigo', 'icono' => '🧩', 'nombre' => 'Ordena el código', 'desc' => 'Arrastra y ordena las líneas de código en el orden correcto antes de que se acabe el tiempo.'],
    ['slug' => 'detective-bugs', 'icono' => '🕵️', 'nombre' => 'Detective de bugs', 'desc' => 'Encuentra el error escondido en el código lo más rápido posible.'],
    ['slug' => 'quiz-relampago', 'icono' => '⚡', 'nombre' => 'Quiz relámpago', 'desc' => 'Responde tantas preguntas como puedas antes de que se agote el reloj.'],
];
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container">
    <h1 class="dp-center">🎮 Arcade de Dique Programando</h1>
    <p class="dp-section-sub">Aprende jugando. Gana puntos, sube en las tablas de posiciones y desbloquea insignias.</p>

    <div class="dp-grid">
      <?php foreach ($juegos as $j): ?>
        <div class="dp-card">
          <div class="dp-card-icon"><?= $j['icono'] ?></div>
          <h3><?= h($j['nombre']) ?></h3>
          <p><?= h($j['desc']) ?></p>
          <a href="<?= url('juegos/' . $j['slug'] . '.php') ?>" class="dp-btn dp-btn-primary">Jugar ahora</a>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (!$usuario): ?>
      <p class="dp-empty"><a href="<?= url('login.php') ?>">Inicia sesión</a> para guardar tus puntajes en las tablas de posiciones.</p>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
