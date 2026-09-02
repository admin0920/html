<?php
$paginaActiva = 'laboratorios';
$tituloPagina = 'Laboratorios';
require_once __DIR__ . '/_layout_top.php';

$error = '';
$accion = $_GET['accion'] ?? 'listar';
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $slug = trim($_POST['slug'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $categoria = trim($_POST['categoria'] ?? 'General');
    $dificultad = $_POST['dificultad'] ?? 'medio';
    $descripcion = $_POST['descripcion'] ?? '';
    $requisitos = $_POST['requisitos'] ?? '';
    $htmlInicial = $_POST['html_inicial'] ?? '';
    $cssInicial = $_POST['css_inicial'] ?? '';
    $jsInicial = $_POST['js_inicial'] ?? '';
    $puntos = (int) ($_POST['puntos'] ?? 40);
    $orden = (int) ($_POST['orden'] ?? 0);
    $idPost = (int) ($_POST['id'] ?? 0);

    if ($slug === '' || $titulo === '') {
        $error = 'Slug y título son obligatorios.';
    } else {
        if ($idPost > 0) {
            db_ejecutar(
                'UPDATE laboratorios SET slug=?, titulo=?, categoria=?, dificultad=?, descripcion=?, requisitos=?, html_inicial=?, css_inicial=?, js_inicial=?, puntos=?, orden=? WHERE id=?',
                'sssssssssiii',
                [$slug, $titulo, $categoria, $dificultad, $descripcion, $requisitos, $htmlInicial, $cssInicial, $jsInicial, $puntos, $orden, $idPost]
            );
        } else {
            db_ejecutar(
                'INSERT INTO laboratorios (slug, titulo, categoria, dificultad, descripcion, requisitos, html_inicial, css_inicial, js_inicial, puntos, orden) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
                'sssssssssii',
                [$slug, $titulo, $categoria, $dificultad, $descripcion, $requisitos, $htmlInicial, $cssInicial, $jsInicial, $puntos, $orden]
            );
        }
        redirigir('admin/laboratorios.php');
    }
}

if ($accion === 'eliminar' && $id > 0 && isset($_GET['confirmar'])) {
    db_ejecutar('DELETE FROM laboratorios WHERE id = ?', 'i', [$id]);
    redirigir('admin/laboratorios.php');
}

$labEditar = $accion === 'editar' && $id > 0 ? db_query_una('SELECT * FROM laboratorios WHERE id = ?', 'i', [$id]) : null;
$laboratorios = db_query('SELECT * FROM laboratorios ORDER BY orden ASC');
?>

<h1>Laboratorios</h1>
<?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="dp-card dp-mt">
  <h3><?= $labEditar ? 'Editar laboratorio' : 'Nuevo laboratorio' ?></h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int) ($labEditar['id'] ?? 0) ?>">
    <div class="dp-grid" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr;">
      <div class="dp-field"><label>Slug</label><input type="text" name="slug" required value="<?= h($labEditar['slug'] ?? '') ?>"></div>
      <div class="dp-field"><label>Categoría</label><input type="text" name="categoria" value="<?= h($labEditar['categoria'] ?? 'General') ?>"></div>
      <div class="dp-field"><label>Dificultad</label>
        <select name="dificultad">
          <?php foreach (['facil', 'medio', 'dificil'] as $d): ?>
            <option value="<?= $d ?>" <?= ($labEditar['dificultad'] ?? 'medio') === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dp-field"><label>Puntos</label><input type="number" name="puntos" value="<?= (int) ($labEditar['puntos'] ?? 40) ?>"></div>
      <div class="dp-field"><label>Orden</label><input type="number" name="orden" value="<?= (int) ($labEditar['orden'] ?? 0) ?>"></div>
    </div>
    <div class="dp-field"><label>Título</label><input type="text" name="titulo" required value="<?= h($labEditar['titulo'] ?? '') ?>"></div>
    <div class="dp-field"><label>Descripción</label><textarea name="descripcion" rows="2"><?= h($labEditar['descripcion'] ?? '') ?></textarea></div>
    <div class="dp-field"><label>Requisitos (HTML: usa &lt;ul&gt;&lt;li&gt;)</label><textarea name="requisitos" rows="4"><?= h($labEditar['requisitos'] ?? '') ?></textarea></div>
    <div class="dp-grid" style="grid-template-columns:1fr 1fr 1fr;">
      <div class="dp-field"><label>HTML inicial</label><textarea name="html_inicial" rows="5"><?= h($labEditar['html_inicial'] ?? '') ?></textarea></div>
      <div class="dp-field"><label>CSS inicial</label><textarea name="css_inicial" rows="5"><?= h($labEditar['css_inicial'] ?? '') ?></textarea></div>
      <div class="dp-field"><label>JS inicial</label><textarea name="js_inicial" rows="5"><?= h($labEditar['js_inicial'] ?? '') ?></textarea></div>
    </div>
    <button type="submit" class="dp-btn dp-btn-primary"><?= $labEditar ? 'Guardar cambios' : 'Crear laboratorio' ?></button>
    <?php if ($labEditar): ?><a href="<?= url('admin/laboratorios.php') ?>" class="dp-btn dp-btn-outline">Cancelar</a><?php endif; ?>
  </form>
</div>

<table class="dp-table dp-mt">
  <thead><tr><th>Orden</th><th>Laboratorio</th><th>Categoría</th><th>Dificultad</th><th>Puntos</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($laboratorios as $l): ?>
      <tr>
        <td><?= (int) $l['orden'] ?></td>
        <td><?= h($l['titulo']) ?></td>
        <td><?= h($l['categoria']) ?></td>
        <td><?= ucfirst($l['dificultad']) ?></td>
        <td><?= (int) $l['puntos'] ?></td>
        <td>
          <a href="<?= url('laboratorio_detalle.php?slug=' . urlencode($l['slug'])) ?>" target="_blank">Ver</a> ·
          <a href="<?= url('admin/laboratorios.php?accion=editar&id=' . $l['id']) ?>">Editar</a> ·
          <a href="<?= url('admin/laboratorios.php?accion=eliminar&id=' . $l['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar este laboratorio?')" style="color:#b91c1c;">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
