<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requiere_admin();
$usuario = usuario_actual();
$tituloPagina = ($tituloPagina ?? 'Admin') . ' — ' . SITE_NAME;
$paginaActiva = $paginaActiva ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($tituloPagina) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>
<header class="dp-navbar">
  <div class="dp-container dp-navbar-inner">
    <a href="<?= url('index.php') ?>" class="dp-brand">💻 Dique <span>Programando</span> · Admin</a>
    <nav class="dp-nav">
      <a href="<?= url('index.php') ?>">Ver sitio</a>
      <a href="<?= url('logout.php') ?>" class="dp-btn dp-btn-outline">Salir</a>
    </nav>
  </div>
</header>
<div class="dp-admin-layout">
  <aside class="dp-admin-sidebar">
    <a href="<?= url('admin/index.php') ?>" class="<?= $paginaActiva === 'inicio' ? 'activa' : '' ?>">📊 Dashboard</a>
    <a href="<?= url('admin/cursos.php') ?>" class="<?= $paginaActiva === 'cursos' ? 'activa' : '' ?>">📘 Cursos</a>
    <a href="<?= url('admin/modulos.php') ?>" class="<?= $paginaActiva === 'modulos' ? 'activa' : '' ?>">🗂 Módulos</a>
    <a href="<?= url('admin/lecciones.php') ?>" class="<?= $paginaActiva === 'lecciones' ? 'activa' : '' ?>">📝 Lecciones</a>
    <a href="<?= url('admin/quizzes.php') ?>" class="<?= $paginaActiva === 'quizzes' ? 'activa' : '' ?>">🧠 Quizzes</a>
    <a href="<?= url('admin/retos.php') ?>" class="<?= $paginaActiva === 'retos' ? 'activa' : '' ?>">🎯 Retos</a>
    <a href="<?= url('admin/laboratorios.php') ?>" class="<?= $paginaActiva === 'laboratorios' ? 'activa' : '' ?>">🔬 Laboratorios</a>
    <a href="<?= url('admin/usuarios.php') ?>" class="<?= $paginaActiva === 'usuarios' ? 'activa' : '' ?>">👥 Usuarios</a>
  </aside>
  <div class="dp-admin-content">
