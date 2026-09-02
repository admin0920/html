<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (usuario_actual()) {
    redirigir('perfil.php');
}

$error = '';
$avisoRedirigido = isset($_GET['redirigido']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $error = 'Sesión expirada, intenta de nuevo.';
    } else {
        $r = iniciar_sesion_usuario(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
        if ($r['ok']) {
            redirigir('perfil.php');
        }
        $error = $r['error'];
    }
}

$tituloPagina = 'Ingresar — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="dp-form-box">
  <h1>Ingresar</h1>
  <?php if ($avisoRedirigido): ?><div class="dp-alert dp-alert-error">Debes iniciar sesión para continuar.</div><?php endif; ?>
  <?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <div class="dp-field">
      <label for="email">Correo electrónico</label>
      <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
    </div>
    <div class="dp-field">
      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="dp-btn dp-btn-primary" style="width:100%;">Ingresar</button>
  </form>
  <p class="dp-form-footer">¿No tienes cuenta? <a href="<?= url('register.php') ?>">Regístrate gratis</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
