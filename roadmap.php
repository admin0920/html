<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

requiere_login();
$usuario = usuario_actual();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    if (isset($_POST['cambiar_plan'])) {
        actualizar_plan_usuario($usuario['id'], $_POST['plan_ritmo'] ?? 'regular');
    }
    if (isset($_POST['alternar_pro'])) {
        alternar_modo_pro($usuario['id'], !$usuario['modo_pro']);
    }
    redirigir('roadmap.php');
}

$cursos = obtener_cursos();
$tituloPagina = 'Tu Roadmap — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container">
    <h1 class="dp-center">🗺️ Tu Roadmap de aprendizaje</h1>
    <p class="dp-section-sub">Tu ruta personalizada, lección a lección. Elige tu ritmo o desbloquea todo con el Modo PRO.</p>

    <div class="dp-card dp-mt" style="max-width:700px;margin-left:auto;margin-right:auto;">
      <div class="dp-flex-between" style="flex-wrap:wrap;gap:16px;">
        <form method="post" class="dp-flex">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <label for="plan_ritmo" style="font-weight:600;">Tu plan:</label>
          <select name="plan_ritmo" id="plan_ritmo" onchange="this.form.submit()" <?= $usuario['modo_pro'] ? 'disabled' : '' ?>>
            <option value="relajado" <?= $usuario['plan_ritmo'] === 'relajado' ? 'selected' : '' ?>>🐢 Relajado</option>
            <option value="regular" <?= $usuario['plan_ritmo'] === 'regular' ? 'selected' : '' ?>>🚶 Regular</option>
            <option value="intensivo" <?= $usuario['plan_ritmo'] === 'intensivo' ? 'selected' : '' ?>>🏃 Intensivo</option>
          </select>
          <input type="hidden" name="cambiar_plan" value="1">
        </form>

        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
          <input type="hidden" name="alternar_pro" value="1">
          <button type="submit" class="dp-btn <?= $usuario['modo_pro'] ? 'dp-btn-secundario' : 'dp-btn-outline' ?>">
            <?= $usuario['modo_pro'] ? '✅ Modo PRO activado' : '🚀 Activar Modo PRO' ?>
          </button>
        </form>
      </div>
      <p class="dp-muted dp-mt" style="margin-bottom:0;font-size:.85rem;">
        <?php if ($usuario['modo_pro']): ?>
          En <strong>Modo PRO</strong> todas las lecciones están desbloqueadas sin esperar. ¡Avanza a tu propio ritmo!
        <?php else: ?>
          Plan actual: <strong><?= h(nombre_plan($usuario['plan_ritmo'])) ?></strong>. Las lecciones se van desbloqueando con el tiempo, o activa el Modo PRO para desbloquearlas todas ya.
        <?php endif; ?>
      </p>
    </div>

    <?php foreach ($cursos as $curso): ?>
      <?php $roadmap = roadmap_curso($usuario, $curso['id']); ?>
      <h2 class="dp-mt"><?= h($curso['icono']) ?> <?= h($curso['titulo']) ?></h2>
      <div class="dp-roadmap-track">
        <?php foreach ($roadmap as $l): ?>
          <?php
            $clase = 'dp-roadmap-nodo dp-roadmap-' . $l['estado'];
            $icono = $l['estado'] === 'completada' ? '✔' : ($l['estado'] === 'disponible' ? '▶' : '🔒');
          ?>
          <?php if ($l['estado'] === 'bloqueada'): ?>
            <div class="<?= $clase ?>" title="Disponible en ~<?= $l['dias_para_desbloquear'] ?> día(s)">
              <span class="dp-roadmap-icono"><?= $icono ?></span>
              <span class="dp-roadmap-titulo"><?= h($l['titulo']) ?></span>
              <span class="dp-roadmap-meta">Disponible en ~<?= $l['dias_para_desbloquear'] ?> día(s)</span>
            </div>
          <?php else: ?>
            <a href="<?= url('leccion.php?slug=' . urlencode($l['slug'])) ?>" class="<?= $clase ?>">
              <span class="dp-roadmap-icono"><?= $icono ?></span>
              <span class="dp-roadmap-titulo"><?= h($l['titulo']) ?></span>
              <span class="dp-roadmap-meta"><?= h($l['modulo_titulo']) ?> · ⏱ <?= (int) $l['minutos_estimados'] ?> min</span>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
