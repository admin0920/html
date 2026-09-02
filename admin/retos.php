<?php
$paginaActiva = 'retos';
$tituloPagina = 'Retos';
require_once __DIR__ . '/_layout_top.php';

$error = '';
$accion = $_GET['accion'] ?? 'listar';
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $slug = trim($_POST['slug'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $lenguaje = $_POST['lenguaje'] ?? 'html';
    $dificultad = $_POST['dificultad'] ?? 'facil';
    $enunciado = $_POST['enunciado'] ?? '';
    $htmlInicial = $_POST['html_inicial'] ?? '';
    $cssInicial = $_POST['css_inicial'] ?? '';
    $jsInicial = $_POST['js_inicial'] ?? '';
    $comprobacion = $_POST['comprobacion_js'] ?? '';
    $puntos = (int) ($_POST['puntos'] ?? 20);
    $orden = (int) ($_POST['orden'] ?? 0);
    $idPost = (int) ($_POST['id'] ?? 0);

    if ($slug === '' || $titulo === '' || $comprobacion === '') {
        $error = 'Slug, título y comprobación son obligatorios.';
    } else {
        if ($idPost > 0) {
            db_ejecutar(
                'UPDATE retos SET slug=?, titulo=?, lenguaje=?, dificultad=?, enunciado=?, html_inicial=?, css_inicial=?, js_inicial=?, comprobacion_js=?, puntos=?, orden=? WHERE id=?',
                'sssssssssiii',
                [$slug, $titulo, $lenguaje, $dificultad, $enunciado, $htmlInicial, $cssInicial, $jsInicial, $comprobacion, $puntos, $orden, $idPost]
            );
        } else {
            db_ejecutar(
                'INSERT INTO retos (slug, titulo, lenguaje, dificultad, enunciado, html_inicial, css_inicial, js_inicial, comprobacion_js, puntos, orden) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                'sssssssssii',
                [$slug, $titulo, $lenguaje, $dificultad, $enunciado, $htmlInicial, $cssInicial, $jsInicial, $comprobacion, $puntos, $orden]
            );
        }
        redirigir('admin/retos.php');
    }
}

if ($accion === 'eliminar' && $id > 0 && isset($_GET['confirmar'])) {
    db_ejecutar('DELETE FROM retos WHERE id = ?', 'i', [$id]);
    redirigir('admin/retos.php');
}

$retoEditar = $accion === 'editar' && $id > 0 ? db_query_una('SELECT * FROM retos WHERE id = ?', 'i', [$id]) : null;
$retos = db_query('SELECT * FROM retos ORDER BY orden ASC');
?>

<h1>Retos de código</h1>
<?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="dp-card dp-mt">
  <h3><?= $retoEditar ? 'Editar reto' : 'Nuevo reto' ?></h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int) ($retoEditar['id'] ?? 0) ?>">
    <div class="dp-grid" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr;">
      <div class="dp-field"><label>Slug</label><input type="text" name="slug" required value="<?= h($retoEditar['slug'] ?? '') ?>"></div>
      <div class="dp-field"><label>Lenguaje</label>
        <select name="lenguaje">
          <?php foreach (['html', 'css', 'js'] as $lg): ?>
            <option value="<?= $lg ?>" <?= ($retoEditar['lenguaje'] ?? 'html') === $lg ? 'selected' : '' ?>><?= strtoupper($lg) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dp-field"><label>Dificultad</label>
        <select name="dificultad">
          <?php foreach (['facil', 'medio', 'dificil'] as $d): ?>
            <option value="<?= $d ?>" <?= ($retoEditar['dificultad'] ?? 'facil') === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dp-field"><label>Puntos</label><input type="number" name="puntos" value="<?= (int) ($retoEditar['puntos'] ?? 20) ?>"></div>
      <div class="dp-field"><label>Orden</label><input type="number" name="orden" value="<?= (int) ($retoEditar['orden'] ?? 0) ?>"></div>
    </div>
    <div class="dp-field"><label>Título</label><input type="text" name="titulo" required value="<?= h($retoEditar['titulo'] ?? '') ?>"></div>
    <div class="dp-field"><label>Enunciado (HTML permitido)</label><textarea name="enunciado" rows="3"><?= h($retoEditar['enunciado'] ?? '') ?></textarea></div>
    <div class="dp-grid" style="grid-template-columns:1fr 1fr 1fr;">
      <div class="dp-field"><label>HTML inicial</label><textarea name="html_inicial" rows="5"><?= h($retoEditar['html_inicial'] ?? '') ?></textarea></div>
      <div class="dp-field"><label>CSS inicial</label><textarea name="css_inicial" rows="5"><?= h($retoEditar['css_inicial'] ?? '') ?></textarea></div>
      <div class="dp-field"><label>JS inicial</label><textarea name="js_inicial" rows="5"><?= h($retoEditar['js_inicial'] ?? '') ?></textarea></div>
    </div>
    <div class="dp-field">
      <label>Comprobación (JS: recibe <code>doc</code> y <code>win</code>, debe hacer <code>return true/false</code>)</label>
      <textarea name="comprobacion_js" rows="3" placeholder='return doc.querySelector("h1") !== null;'><?= h($retoEditar['comprobacion_js'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="dp-btn dp-btn-primary"><?= $retoEditar ? 'Guardar cambios' : 'Crear reto' ?></button>
    <?php if ($retoEditar): ?><a href="<?= url('admin/retos.php') ?>" class="dp-btn dp-btn-outline">Cancelar</a><?php endif; ?>
  </form>
</div>

<table class="dp-table dp-mt">
  <thead><tr><th>Orden</th><th>Reto</th><th>Lenguaje</th><th>Dificultad</th><th>Puntos</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($retos as $r): ?>
      <tr>
        <td><?= (int) $r['orden'] ?></td>
        <td><?= h($r['titulo']) ?></td>
        <td><?= strtoupper($r['lenguaje']) ?></td>
        <td><?= ucfirst($r['dificultad']) ?></td>
        <td><?= (int) $r['puntos'] ?></td>
        <td>
          <a href="<?= url('reto.php?slug=' . urlencode($r['slug'])) ?>" target="_blank">Ver</a> ·
          <a href="<?= url('admin/retos.php?accion=editar&id=' . $r['id']) ?>">Editar</a> ·
          <a href="<?= url('admin/retos.php?accion=eliminar&id=' . $r['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar este reto?')" style="color:#b91c1c;">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
