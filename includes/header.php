<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$usuario = usuario_actual();
$tituloPagina = $tituloPagina ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($tituloPagina) ?></title>
<meta name="description" content="Aprende HTML, CSS y JavaScript desde cero hasta avanzado, gratis y en español.">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🚀</text></svg>">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
</head>
<body>
<header class="dp-navbar">
  <div class="dp-container dp-navbar-inner">
    <a href="<?= url('index.php') ?>" class="dp-brand">💻 Dique <span>Programando</span></a>
    <nav class="dp-nav" id="dpNav">
      <a href="<?= url('index.php') ?>">Inicio</a>
      <a href="<?= url('cursos.php') ?>">Cursos</a>
      <a href="<?= url('sandbox.php') ?>">Sandbox 🧪</a>
      <?php if ($usuario): ?>
        <a href="<?= url('perfil.php') ?>">Mi perfil</a>
        <?php if ($usuario['rol'] === 'admin'): ?>
          <a href="<?= url('admin/index.php') ?>">Admin ⚙️</a>
        <?php endif; ?>
        <a href="<?= url('logout.php') ?>" class="dp-btn dp-btn-outline">Salir</a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>">Ingresar</a>
        <a href="<?= url('register.php') ?>" class="dp-btn dp-btn-primary">Crear cuenta</a>
      <?php endif; ?>
    </nav>
    <button class="dp-burger" id="dpBurger" aria-label="Abrir menú">☰</button>
  </div>
</header>
<main>
