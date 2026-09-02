<?php
$paginaActiva = 'cursos';
$tituloPagina = 'Cursos';
require_once __DIR__ . '/_layout_top.php';

$error = '';
$accion = $_GET['accion'] ?? 'listar';
$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido()) {
    $slug = trim($_POST['slug'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $icono = trim($_POST['icono'] ?? '📘');
    $nivel = $_POST['nivel'] ?? 'completo';
    $orden = (int) ($_POST['orden'] ?? 0);
    $publicado = isset($_POST['publicado']) ? 1 : 0;
    $idPost = (int) ($_POST['id'] ?? 0);

    if ($slug === '' || $titulo === '') {
        $error = 'Slug y título son obligatorios.';
    } else {
        if ($idPost > 0) {
            db_ejecutar('UPDATE cursos SET slug=?, titulo=?, descripcion=?, icono=?, nivel=?, orden=?, publicado=? WHERE id=?', 'sssssiii', [$slug, $titulo, $descripcion, $icono, $nivel, $orden, $publicado, $idPost]);
        } else {
            db_ejecutar('INSERT INTO cursos (slug, titulo, descripcion, icono, nivel, orden, publicado) VALUES (?,?,?,?,?,?,?)', 'sssssii', [$slug, $titulo, $descripcion, $icono, $nivel, $orden, $publicado]);
        }
        redirigir('admin/cursos.php');
    }
}

if ($accion === 'eliminar' && $id > 0 && isset($_GET['confirmar'])) {
    db_ejecutar('DELETE FROM cursos WHERE id = ?', 'i', [$id]);
    redirigir('admin/cursos.php');
}

$cursoEditar = $accion === 'editar' && $id > 0 ? db_query_una('SELECT * FROM cursos WHERE id = ?', 'i', [$id]) : null;
$cursos = db_query('SELECT * FROM cursos ORDER BY orden ASC');
?>

<div class="dp-toolbar">
  <h1>Cursos</h1>
</div>

<?php if ($error): ?><div class="dp-alert dp-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="dp-card dp-mt">
  <h3><?= $cursoEditar ? 'Editar curso' : 'Nuevo curso' ?></h3>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <input type="hidden" name="id" value="<?= (int) ($cursoEditar['id'] ?? 0) ?>">
    <div class="dp-grid" style="grid-template-columns:1fr 1fr;">
      <div class="dp-field"><label>Slug (url)</label><input type="text" name="slug" required value="<?= h($cursoEditar['slug'] ?? '') ?>" placeholder="html"></div>
      <div class="dp-field"><label>Título</label><input type="text" name="titulo" required value="<?= h($cursoEditar['titulo'] ?? '') ?>"></div>
    </div>
    <div class="dp-field"><label>Descripción</label><textarea name="descripcion" rows="2"><?= h($cursoEditar['descripcion'] ?? '') ?></textarea></div>
    <div class="dp-grid" style="grid-template-columns:1fr 1fr 1fr;">
      <div class="dp-field"><label>Ícono (emoji)</label><input type="text" name="icono" value="<?= h($cursoEditar['icono'] ?? '📘') ?>"></div>
      <div class="dp-field"><label>Nivel</label>
        <select name="nivel">
          <?php foreach (['basico', 'intermedio', 'avanzado', 'completo'] as $n): ?>
            <option value="<?= $n ?>" <?= ($cursoEditar['nivel'] ?? 'completo') === $n ? 'selected' : '' ?>><?= ucfirst($n) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="dp-field"><label>Orden</label><input type="number" name="orden" value="<?= (int) ($cursoEditar['orden'] ?? 0) ?>"></div>
    </div>
    <div class="dp-field">
      <label><input type="checkbox" name="publicado" style="width:auto;" <?= ($cursoEditar['publicado'] ?? 1) ? 'checked' : '' ?>> Publicado</label>
    </div>
    <button type="submit" class="dp-btn dp-btn-primary"><?= $cursoEditar ? 'Guardar cambios' : 'Crear curso' ?></button>
    <?php if ($cursoEditar): ?><a href="<?= url('admin/cursos.php') ?>" class="dp-btn dp-btn-outline">Cancelar</a><?php endif; ?>
  </form>
</div>

<h2 class="dp-mt">Todos los cursos</h2>
<table class="dp-table">
  <thead><tr><th>Orden</th><th>Curso</th><th>Slug</th><th>Nivel</th><th>Publicado</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($cursos as $c): ?>
      <tr>
        <td><?= (int) $c['orden'] ?></td>
        <td><?= h($c['icono']) ?> <?= h($c['titulo']) ?></td>
        <td><?= h($c['slug']) ?></td>
        <td><?= h($c['nivel']) ?></td>
        <td><?= $c['publicado'] ? '✔' : '—' ?></td>
        <td>
          <a href="<?= url('admin/cursos.php?accion=editar&id=' . $c['id']) ?>">Editar</a> ·
          <a href="<?= url('admin/cursos.php?accion=eliminar&id=' . $c['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar este curso y todo su contenido?')" style="color:#b91c1c;">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
