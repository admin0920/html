<?php
$paginaActiva = 'modulos';
$tituloPagina = 'Módulos';
require_once __DIR__ . '/_layout_top.php';

$error = '';
$accion = $_GET['accion'] ?? 'listar';
$id = (int) ($_GET['id'] ?? 0);
$cursoFiltro = (int) ($_GET['curso_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $cursoId = (int) ($_POST['curso_id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $orden = (int) ($_POST['orden'] ?? 0);
    $idPost = (int) ($_POST['id'] ?? 0);

    if ($cursoId <= 0 || $titulo === '') {
        $error = 'Curso y título son obligatorios.';
    } else {
        if ($idPost > 0) {
            db_ejecutar('UPDATE modulos SET curso_id=?, titulo=?, orden=? WHERE id=?', 'isii', [$cursoId, $titulo, $orden, $idPost]);
        } else {
            db_ejecutar('INSERT INTO modulos (curso_id, titulo, orden) VALUES (?,?,?)', 'isi', [$cursoId, $titulo, $orden]);
        }
        redirigir('admin/modulos.php' . ($cursoFiltro ? '?curso_id=' . $cursoFiltro : ''));
    }
}

if ($accion === 'eliminar' && $id > 0 && isset($_GET['confirmar'])) {
    db_ejecutar('DELETE FROM modulos WHERE id = ?', 'i', [$id]);
    redirigir('admin/modulos.php');
}

$moduloEditar = $accion === 'editar' && $id > 0 ? db_query_una('SELECT * FROM modulos WHERE id = ?', 'i', [$id]) : null;
$cursos = db_query('SELECT id, titulo FROM cursos ORDER BY orden ASC');

$sql = 'SELECT m.*, c.titulo AS curso_titulo FROM modulos m JOIN cursos c ON c.id = m.curso_id';
$params = [];
$tipos = '';
if ($cursoFiltro > 0) {
    $sql .= ' WHERE m.curso_id = ?';
    $tipos = 'i';
    $params = [$cursoFiltro];
}
$sql .= ' ORDER BY c.orden ASC, m.orden ASC';
$modulos = db_query($sql, $tipos, $params);
?>

<h1>Módulos</h1>
<?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="dp-card dp-mt">
  <h3><?= $moduloEditar ? 'Editar módulo' : 'Nuevo módulo' ?></h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int) ($moduloEditar['id'] ?? 0) ?>">
    <div class="dp-grid" style="grid-template-columns:1fr 2fr 1fr;">
      <div class="dp-field">
        <label>Curso</label>
        <select name="curso_id" required>
          <option value="">Selecciona...</option>
          <?php foreach ($cursos as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($moduloEditar['curso_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= h($c['titulo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dp-field"><label>Título</label><input type="text" name="titulo" required value="<?= h($moduloEditar['titulo'] ?? '') ?>"></div>
      <div class="dp-field"><label>Orden</label><input type="number" name="orden" value="<?= (int) ($moduloEditar['orden'] ?? 0) ?>"></div>
    </div>
    <button type="submit" class="dp-btn dp-btn-primary"><?= $moduloEditar ? 'Guardar cambios' : 'Crear módulo' ?></button>
    <?php if ($moduloEditar): ?><a href="<?= url('admin/modulos.php') ?>" class="dp-btn dp-btn-outline">Cancelar</a><?php endif; ?>
  </form>
</div>

<div class="dp-flex dp-mt">
  <form method="get">
    <select name="curso_id" onchange="this.form.submit()">
      <option value="">Todos los cursos</option>
      <?php foreach ($cursos as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $cursoFiltro == $c['id'] ? 'selected' : '' ?>><?= h($c['titulo']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<table class="dp-table dp-mt">
  <thead><tr><th>Orden</th><th>Módulo</th><th>Curso</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($modulos as $m): ?>
      <tr>
        <td><?= (int) $m['orden'] ?></td>
        <td><?= h($m['titulo']) ?></td>
        <td><?= h($m['curso_titulo']) ?></td>
        <td>
          <a href="<?= url('admin/modulos.php?accion=editar&id=' . $m['id']) ?>">Editar</a> ·
          <a href="<?= url('admin/lecciones.php?modulo_id=' . $m['id']) ?>">Ver lecciones</a> ·
          <a href="<?= url('admin/modulos.php?accion=eliminar&id=' . $m['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar este módulo y sus lecciones?')" style="color:#b91c1c;">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
