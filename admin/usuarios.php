<?php
$paginaActiva = 'usuarios';
$tituloPagina = 'Usuarios';
require_once __DIR__ . '/_layout_top.php';

$mensaje = '';

if (isset($_POST['cambiar_rol']) && csrf_valido()) {
    $idUsuario = (int) $_POST['usuario_id'];
    if ($idUsuario !== $usuario['id']) { // evita que el admin se quite el rol a sí mismo
        $nuevoRol = $_POST['rol'] === 'admin' ? 'admin' : 'usuario';
        db_ejecutar('UPDATE usuarios SET rol = ? WHERE id = ?', 'si', [$nuevoRol, $idUsuario]);
    }
    redirigir('admin/usuarios.php');
}

if (isset($_GET['eliminar']) && isset($_GET['confirmar'])) {
    $idEliminar = (int) $_GET['eliminar'];
    if ($idEliminar !== $usuario['id']) {
        db_ejecutar('DELETE FROM usuarios WHERE id = ?', 'i', [$idEliminar]);
    }
    redirigir('admin/usuarios.php');
}

$usuarios = db_query('SELECT id, nombre, email, rol, puntos, racha_dias, creado_en FROM usuarios ORDER BY id DESC');
?>

<h1>Usuarios</h1>
<table class="dp-table dp-mt">
  <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Puntos</th><th>Racha</th><th>Registrado</th><th></th></tr></thead>
  <tbody>
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= h($u['nombre']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><span class="dp-badge <?= $u['rol'] === 'admin' ? 'dp-badge-admin' : 'dp-badge-usuario' ?>"><?= h($u['rol']) ?></span></td>
        <td><?= (int) $u['puntos'] ?></td>
        <td><?= (int) $u['racha_dias'] ?> 🔥</td>
        <td><?= tiempo_relativo($u['creado_en']) ?></td>
        <td>
          <?php if ($u['id'] !== $usuario['id']): ?>
            <form method="post" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
              <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
              <input type="hidden" name="rol" value="<?= $u['rol'] === 'admin' ? 'usuario' : 'admin' ?>">
              <button type="submit" name="cambiar_rol" class="dp-btn dp-btn-outline" style="padding:4px 10px;font-size:.8rem;">
                <?= $u['rol'] === 'admin' ? 'Quitar admin' : 'Hacer admin' ?>
              </button>
            </form>
            <a href="<?= url('admin/usuarios.php?eliminar=' . $u['id'] . '&confirmar=1') ?>" onclick="return confirm('¿Eliminar este usuario y todo su progreso?')" style="color:#b91c1c;font-size:.85rem;">Eliminar</a>
          <?php else: ?>
            <span class="dp-muted" style="font-size:.8rem;">(tú)</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php require_once __DIR__ . '/_layout_bottom.php'; ?>
