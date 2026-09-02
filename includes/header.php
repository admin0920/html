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
<link rel="icon" type="image/png" sizes="32x32" href="<?= url('assets/img/favicon-32.png') ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?= url('assets/img/favicon-16.png') ?>">
<link rel="icon" type="image/png" sizes="192x192" href="<?= url('assets/img/favicon-192.png') ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= url('assets/img/favicon-180.png') ?>">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="dp-navbar">
  <div class="dp-container dp-navbar-inner">
    <a href="<?= url('index.php') ?>" class="dp-brand">
      <img src="<?= url('assets/img/isotipo-web.png') ?>" alt="Dique Programando" class="dp-brand-icono">
      Dique <span>Programando</span>
    </a>
    <nav class="dp-nav" id="dpNav">
      <a href="<?= url('cursos.php') ?>">Cursos</a>
      <a href="<?= url('roadmap.php') ?>">Roadmap 🗺️</a>
      <a href="<?= url('retos.php') ?>">Retos 🎯</a>
      <a href="<?= url('laboratorio.php') ?>">Laboratorio 🔬</a>
      <a href="<?= url('juegos.php') ?>">Juegos 🎮</a>
      <a href="<?= url('sandbox.php') ?>">Sandbox 🧪</a>
      <?php if ($usuario): ?>
        <a href="<?= url('perfil.php') ?>">Mi perfil</a>
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
