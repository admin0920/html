<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (usuario_actual()) {
    redirigir('perfil.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $error = 'Sesión expirada, intenta de nuevo.';
    } else {
        $r = registrar_usuario(trim($_POST['nombre'] ?? ''), trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
        if ($r['ok']) {
            redirigir('perfil.php?bienvenida=1');
        }
        $error = $r['error'];
    }
}

$tituloPagina = 'Crear cuenta — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="dp-form-box">
  <h1>Crear cuenta</h1>
  <?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="dp-field">
      <label for="nombre">Nombre</label>
      <input type="text" id="nombre" name="nombre" required value="<?= h($_POST['nombre'] ?? '') ?>">
    </div>
    <div class="dp-field">
      <label for="email">Correo electrónico</label>
      <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
    </div>
    <div class="dp-field">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required minlength="6">
    </div>
    <button type="submit" class="dp-btn dp-btn-primary" style="width:100%;">Crear cuenta</button>
  </form>
  <p class="dp-form-footer">¿Ya tienes cuenta? <a href="<?= url('login.php') ?>">Inicia sesión</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
