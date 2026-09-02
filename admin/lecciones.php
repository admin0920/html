<?php
$paginaActiva = 'lecciones';
$tituloPagina = 'Lecciones';
require_once __DIR__ . '/_layout_top.php';

$error = '';
$accion = $_GET['accion'] ?? 'listar';
$id = (int) ($_GET['id'] ?? 0);
$moduloFiltro = (int) ($_GET['modulo_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $moduloId = (int) ($_POST['modulo_id'] ?? 0);
    $slug = trim($_POST['slug'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $contenido = $_POST['contenido'] ?? '';
    $codigoHtml = $_POST['codigo_html'] ?? '';
    $codigoCss = $_POST['codigo_css'] ?? '';
    $codigoJs = $_POST['codigo_js'] ?? '';
    $orden = (int) ($_POST['orden'] ?? 0);
    $minutos = (int) ($_POST['minutos_estimados'] ?? 5);
    $idPost = (int) ($_POST['id'] ?? 0);

    if ($moduloId <= 0 || $slug === '' || $titulo === '') {
        $error = 'Módulo, slug y título son obligatorios.';
    } else {
        if ($idPost > 0) {
            db_ejecutar(
                'UPDATE lecciones SET modulo_id=?, slug=?, titulo=?, contenido=?, codigo_html=?, codigo_css=?, codigo_js=?, orden=?, minutos_estimados=? WHERE id=?',
                'issssssiii',
                [$moduloId, $slug, $titulo, $contenido, $codigoHtml, $codigoCss, $codigoJs, $orden, $minutos, $idPost]
            );
        } else {
            db_ejecutar(
                'INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, codigo_js, orden, minutos_estimados) VALUES (?,?,?,?,?,?,?,?,?)',
                'issssssii',
                [$moduloId, $slug, $titulo, $contenido, $codigoHtml, $codigoCss, $codigoJs, $orden, $minutos]
            );
        }
        redirigir('admin/lecciones.php' . ($moduloFiltro ? '?modulo_id=' . $moduloFiltro : ''));
    }
}

if ($accion === 'eliminar' && $id > 0 && isset($_GET['confirmar'])) {
    db_ejecutar('DELETE FROM lecciones WHERE id = ?', 'i', [$id]);
    redirigir('admin/lecciones.php');
}

$leccionEditar = $accion === 'editar' && $id > 0 ? db_query_una('SELECT * FROM lecciones WHERE id = ?', 'i', [$id]) : null;
$modulos = db_query('SELECT m.id, m.titulo, c.titulo AS curso_titulo FROM modulos m JOIN cursos c ON c.id = m.curso_id ORDER BY c.orden, m.orden');

$sql = 'SELECT l.*, m.titulo AS modulo_titulo FROM lecciones l JOIN modulos m ON m.id = l.modulo_id';
$params = [];
$tipos = '';
if ($moduloFiltro > 0) {
    $sql .= ' WHERE l.modulo_id = ?';
    $tipos = 'i';
    $params = [$moduloFiltro];
}
$sql .= ' ORDER BY l.modulo_id, l.orden';
$lecciones = db_query($sql, $tipos, $params);
?>

<h1>Lecciones</h1>
<?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="dp-card dp-mt">
  <h3><?= $leccionEditar ? 'Editar lección' : 'Nueva lección' ?></h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int) ($leccionEditar['id'] ?? 0) ?>">
    <div class="dp-grid" style="grid-template-columns:2fr 1fr 1fr 1fr;">
      <div class="dp-field">
        <label>Módulo</label>
        <select name="modulo_id" required>
          <option value="">Selecciona...</option>
          <?php foreach ($modulos as $m): ?>
            <option value="<?= $m['id'] ?>" <?= ($leccionEditar['modulo_id'] ?? 0) == $m['id'] ? 'selected' : '' ?>><?= h($m['curso_titulo']) ?> · <?= h($m['titulo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dp-field"><label>Slug (url única)</label><input type="text" name="slug" required value="<?= h($leccionEditar['slug'] ?? '') ?>"></div>
      <div class="dp-field"><label>Orden</label><input type="number" name="orden" value="<?= (int) ($leccionEditar['orden'] ?? 0) ?>"></div>
      <div class="dp-field"><label>Minutos</label><input type="number" name="minutos_estimados" value="<?= (int) ($leccionEditar['minutos_estimados'] ?? 5) ?>"></div>
    </div>
    <div class="dp-field"><label>Título</label><input type="text" name="titulo" required value="<?= h($leccionEditar['titulo'] ?? '') ?>"></div>
    <div class="dp-field">
      <label>Contenido (HTML permitido: p, ul, li, code, strong, etc.)</label>
      <textarea name="contenido" rows="8"><?= h($leccionEditar['contenido'] ?? '') ?></textarea>
    </div>
    <div class="dp-grid" style="grid-template-columns:1fr 1fr 1fr;">
      <div class="dp-field"><label>Código de ejemplo — HTML</label><textarea name="codigo_html" rows="6"><?= h($leccionEditar['codigo_html'] ?? '') ?></textarea></div>
      <div class="dp-field"><label>Código de ejemplo — CSS</label><textarea name="codigo_css" rows="6"><?= h($leccionEditar['codigo_css'] ?? '') ?></textarea></div>
      <div class="dp-field"><label>Código de ejemplo — JS</label><textarea name="codigo_js" rows="6"><?= h($leccionEditar['codigo_js'] ?? '') ?></textarea></div>
    </div>
    <button type="submit" class="dp-btn dp-btn-primary"><?= $leccionEditar ? 'Guardar cambios' : 'Crear lección' ?></button>
    <?php if ($leccionEditar): ?><a href="<?= url('admin/lecciones.php') ?>" class="dp-btn dp-btn-outline">Cancelar</a><?php endif; ?>
  </form>
</div>

<table class="dp-table dp-mt">
  <thead><tr><th>Orden</th><th>Lección</th><th>Módulo</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($lecciones as $l): ?>
      <tr>
        <td><?= (int) $l['orden'] ?></td>
        <td><?= h($l['titulo']) ?></td>
        <td><?= h($l['modulo_titulo']) ?></td>
        <td>
          <a href="<?= url('leccion.php?slug=' . urlencode($l['slug'])) ?>" target="_blank">Ver</a> ·
          <a href="<?= url('admin/lecciones.php?accion=editar&id=' . $l['id']) ?>">Editar</a> ·
          <a href="<?= url('admin/quizzes.php?leccion_id=' . $l['id']) ?>">Quiz</a> ·
          <a href="<?= url('admin/lecciones.php?accion=eliminar&id=' . $l['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar esta lección?')" style="color:#b91c1c;">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
