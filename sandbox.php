<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$usuario = usuario_actual();
$proyecto = null;
$mensaje = '';

// Cargar un proyecto guardado
if ($usuario && isset($_GET['proyecto'])) {
    $proyecto = db_query_una('SELECT * FROM proyectos WHERE id = ? AND usuario_id = ?', 'ii', [(int) $_GET['proyecto'], $usuario['id']]);
}

// Guardar proyecto (AJAX-less, POST normal)
if ($usuario && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $titulo = trim($_POST['titulo'] ?? 'Mi proyecto') ?: 'Mi proyecto';
    $html = $_POST['html'] ?? '';
    $css = $_POST['css'] ?? '';
    $js = $_POST['js'] ?? '';
    $id = (int) ($_POST['proyecto_id'] ?? 0);

    if ($id > 0) {
        db_ejecutar('UPDATE proyectos SET titulo=?, html=?, css=?, js=? WHERE id=? AND usuario_id=?', 'ssssii', [$titulo, $html, $css, $js, $id, $usuario['id']]);
    } else {
        $id = db_ejecutar('INSERT INTO proyectos (usuario_id, titulo, html, css, js) VALUES (?,?,?,?,?)', 'issss', [$usuario['id'], $titulo, $html, $css, $js]);
    }
    redirigir('sandbox.php?proyecto=' . $id . '&guardado=1');
}

$misProyectos = $usuario ? db_query('SELECT id, titulo, actualizado_en FROM proyectos WHERE usuario_id = ? ORDER BY actualizado_en DESC LIMIT 10', 'i', [$usuario['id']]) : [];

$defaultHtml = "<h1>¡Hola, Sandbox!</h1>\n<p>Escribe tu código y mira el resultado a la derecha.</p>\n<button id=\"btn\">Haz clic</button>";
$defaultCss = "body {\n  font-family: sans-serif;\n  text-align: center;\n  padding-top: 40px;\n}\nbutton {\n  background: #6366f1;\n  color: white;\n  border: none;\n  padding: 10px 20px;\n  border-radius: 8px;\n  cursor: pointer;\n}";
$defaultJs = "document.getElementById('btn').addEventListener('click', function () {\n  alert('¡Funciona!');\n});";

$tituloPagina = 'Sandbox — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="dp-sandbox-bar">
  <strong>🧪 Sandbox</strong>
  <?php if ($usuario): ?>
    <form method="post" class="dp-flex" style="flex:1;">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
      <input type="hidden" name="proyecto_id" value="<?= $proyecto['id'] ?? 0 ?>">
      <input type="text" name="titulo" placeholder="Nombre de tu proyecto" value="<?= h($proyecto['titulo'] ?? '') ?>">
      <button type="submit" class="dp-btn dp-btn-primary" style="padding:8px 16px;">💾 Guardar</button>
    </form>
    <?php if (!empty($misProyectos)): ?>
      <select onchange="if(this.value) window.location='<?= url('sandbox.php') ?>?proyecto='+this.value" style="padding:8px;border-radius:8px;border:1px solid var(--borde);">
        <option value="">Mis proyectos...</option>
        <?php foreach ($misProyectos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= (($proyecto['id'] ?? 0) == $p['id']) ? 'selected' : '' ?>><?= h($p['titulo']) ?></option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>
    <?php if (isset($_GET['guardado'])): ?><span style="color:#15803d;font-size:.85rem;">✔ Guardado</span><?php endif; ?>
  <?php else: ?>
    <span class="dp-muted" style="font-size:.85rem;">Practica libremente. <a href="<?= url('login.php') ?>">Inicia sesión</a> para guardar tus proyectos.</span>
  <?php endif; ?>
</div>

<div class="dp-sandbox-layout">
  <div class="dp-sandbox-editors">
    <div class="dp-editor" style="display:flex;flex-direction:column;flex:1;">
      <div class="dp-editor-tabs">
        <button type="button" class="activa" data-tab="html">HTML</button>
        <button type="button" data-tab="css">CSS</button>
        <button type="button" data-tab="js">JS</button>
      </div>
      <div class="dp-editor-panel activa" data-panel="html" style="flex:1;display:flex;">
        <textarea class="dp-code" data-code="html" spellcheck="false"><?= h($proyecto['html'] ?? $defaultHtml) ?></textarea>
      </div>
      <div class="dp-editor-panel" data-panel="css" style="flex:1;">
        <textarea class="dp-code" data-code="css" spellcheck="false"><?= h($proyecto['css'] ?? $defaultCss) ?></textarea>
      </div>
      <div class="dp-editor-panel" data-panel="js" style="flex:1;">
        <textarea class="dp-code" data-code="js" spellcheck="false"><?= h($proyecto['js'] ?? $defaultJs) ?></textarea>
      </div>
    </div>
  </div>
  <div class="dp-sandbox-preview">
    <iframe title="Resultado en vivo"></iframe>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
