<?php
$paginaActiva = 'inicio';
$tituloPagina = 'Dashboard';
require_once __DIR__ . '/_layout_top.php';

$totalUsuarios = db_query_una('SELECT COUNT(*) AS n FROM usuarios')['n'] ?? 0;
$totalCursos = db_query_una('SELECT COUNT(*) AS n FROM cursos')['n'] ?? 0;
$totalLecciones = db_query_una('SELECT COUNT(*) AS n FROM lecciones')['n'] ?? 0;
$totalCompletadas = db_query_una('SELECT COUNT(*) AS n FROM progreso')['n'] ?? 0;
$ultimosUsuarios = db_query('SELECT nombre, email, creado_en FROM usuarios ORDER BY id DESC LIMIT 5');
?>

<h1>Dashboard</h1>
<div class="dp-grid">
  <div class="dp-card"><h3>👥 <?= (int) $totalUsuarios ?></h3><p>Usuarios registrados</p></div>
  <div class="dp-card"><h3>📘 <?= (int) $totalCursos ?></h3><p>Cursos</p></div>
  <div class="dp-card"><h3>📝 <?= (int) $totalLecciones ?></h3><p>Lecciones</p></div>
  <div class="dp-card"><h3>✔ <?= (int) $totalCompletadas ?></h3><p>Lecciones completadas (total)</p></div>
</div>

<h2 class="dp-mt">Últimos usuarios registrados</h2>
<table class="dp-table">
  <thead><tr><th>Nombre</th><th>Email</th><th>Registrado</th></tr></thead>
  <tbody>
    <?php foreach ($ultimosUsuarios as $u): ?>
      <tr><td><?= h($u['nombre']) ?></td><td><?= h($u['email']) ?></td><td><?= tiempo_relativo($u['creado_en']) ?></td></tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
