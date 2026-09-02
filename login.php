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
  <img src="<?= url('assets/img/isotipo-web.png') ?>" alt="Dique Programando" class="dp-form-logo">
  <h1>Bienvenido de nuevo</h1>
  <p class="dp-form-sub">Ingresa para continuar aprendiendo</p>
  <?php if ($avisoRedirigido): ?><div class="dp-alert dp-alert-error">Debes iniciar sesión para continuar.</div><?php endif; ?>
  <?php if (isset($_GET['google_error'])): ?><div class="dp-alert dp-alert-error">No se pudo iniciar sesión con Google. Intenta de nuevo o usa tu correo y contraseña.</div><?php endif; ?>
  <?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>

  <?php if (google_login_disponible()): ?>
    <a href="<?= url('auth_google.php') ?>" class="dp-btn dp-btn-google dp-btn-block">
      <svg width="18" height="18" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84c-.21 1.13-.84 2.08-1.79 2.72v2.26h2.9c1.7-1.56 2.69-3.87 2.69-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.81.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.96v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.96A9 9 0 0 0 0 9c0 1.45.35 2.83.96 4.03l2.99-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .96 4.97l2.99 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>
      Continuar con Google
    </a>
    <div class="dp-divisor">o con tu correo</div>
  <?php endif; ?>

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
    <button type="submit" class="dp-btn dp-btn-primary dp-btn-block">Ingresar</button>
  </form>
  <p class="dp-form-footer">¿No tienes cuenta? <a href="<?= url('register.php') ?>">Regístrate gratis</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
