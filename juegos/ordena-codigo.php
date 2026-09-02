<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$usuario = usuario_actual();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_puntaje'])) {
    header('Content-Type: application/json');
    if (!$usuario || !csrf_valido()) {
        echo json_encode(['ok' => false]);
        exit;
    }
    $puntaje = max(0, min(1000, (int) ($_POST['puntaje'] ?? 0)));
    registrar_puntaje_juego($usuario['id'], 'ordena-codigo', $puntaje);
    $insignias = evaluar_insignias($usuario['id']);
    echo json_encode(['ok' => true, 'insignias' => $insignias]);
    exit;
}

$tabla = mejores_puntajes_juego('ordena-codigo', 5);
$tituloPagina = 'Ordena el código — ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container" style="max-width:700px;">
    <p><a href="<?= url('juegos.php') ?>">← Arcade</a></p>
    <h1 class="dp-center">🧩 Ordena el código</h1>
    <p class="dp-section-sub">Coloca las líneas en el orden correcto usando los botones ↑ ↓. ¡Cuanto más rápido, más puntos!</p>

    <div class="dp-card dp-flex-between">
      <div>Ronda: <strong id="ronda">1</strong>/<span id="totalRondas">6</span></div>
      <div>⏱ <strong id="tiempo">0</strong>s</div>
      <div>Puntaje: <strong id="puntaje">0</strong></div>
    </div>

    <div class="dp-card dp-mt" id="tarjetaJuego">
      <p class="dp-muted" id="consigna">Cargando...</p>
      <ol id="listaCodigo" class="dp-juego-lista"></ol>
      <button type="button" id="btnComprobarOrden" class="dp-btn dp-btn-primary dp-btn-lg">Comprobar orden</button>
      <p id="mensajeRonda" class="dp-mt"></p>
    </div>

    <div id="pantallaFinal" class="dp-card dp-mt" hidden>
      <h2 class="dp-center">🎉 ¡Juego terminado!</h2>
      <p class="dp-center">Puntaje final: <strong id="puntajeFinal">0</strong></p>
      <p class="dp-center"><a href="<?= url('juegos/ordena-codigo.php') ?>" class="dp-btn dp-btn-primary">Jugar de nuevo</a></p>
    </div>

    <h3 class="dp-mt">🏆 Mejores puntajes</h3>
    <table class="dp-table">
      <thead><tr><th>Jugador</th><th>Puntaje</th></tr></thead>
      <tbody>
        <?php foreach ($tabla as $t): ?>
          <tr><td><?= h($t['nombre']) ?></td><td><?= (int) $t['puntaje'] ?></td></tr>
        <?php endforeach; ?>
        <?php if (empty($tabla)): ?><tr><td colspan="2" class="dp-muted">Sé el primero en anotar un puntaje.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
const RONDAS = [
  { consigna: 'Ordena una página HTML básica', lineas: ['<html>', '<head><title>Mi página</title></head>', '<body>', '<h1>Hola</h1>', '</body>', '</html>'] },
  { consigna: 'Ordena una función que suma dos números', lineas: ['function sumar(a, b) {', '  return a + b;', '}', 'console.log(sumar(2, 3));'] },
  { consigna: 'Ordena un bucle for que cuenta hasta 5', lineas: ['for (let i = 1; i <= 5; i++) {', '  console.log(i);', '}'] },
  { consigna: 'Ordena una regla CSS para un botón', lineas: ['.boton {', '  background: blue;', '  color: white;', '  padding: 10px;', '}'] },
  { consigna: 'Ordena un formulario simple', lineas: ['<form>', '  <input type="text">', '  <button>Enviar</button>', '</form>'] },
  { consigna: 'Ordena una condicional if/else', lineas: ['if (edad >= 18) {', '  console.log("Mayor de edad");', '} else {', '  console.log("Menor de edad");', '}'] },
];

let ronda = 0;
let puntaje = 0;
let segundos = 0;
let cronometro = null;
let actual = [];
let correcto = [];

const elLista = document.getElementById('listaCodigo');
const elConsigna = document.getElementById('consigna');
const elRonda = document.getElementById('ronda');
const elTiempo = document.getElementById('tiempo');
const elPuntaje = document.getElementById('puntaje');
const elMensaje = document.getElementById('mensajeRonda');

document.getElementById('totalRondas').textContent = RONDAS.length;

function barajar(arr) {
  const copia = arr.slice();
  for (let i = copia.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copia[i], copia[j]] = [copia[j], copia[i]];
  }
  return copia;
}

function render() {
  elLista.innerHTML = '';
  actual.forEach((linea, i) => {
    const li = document.createElement('li');
    li.className = 'dp-juego-item';
    li.innerHTML = '<code></code><span class="dp-juego-controles"><button type="button" data-dir="-1">↑</button><button type="button" data-dir="1">↓</button></span>';
    li.querySelector('code').textContent = linea;
    li.querySelector('[data-dir="-1"]').addEventListener('click', () => mover(i, -1));
    li.querySelector('[data-dir="1"]').addEventListener('click', () => mover(i, 1));
    elLista.appendChild(li);
  });
}

function mover(i, dir) {
  const j = i + dir;
  if (j < 0 || j >= actual.length) return;
  [actual[i], actual[j]] = [actual[j], actual[i]];
  render();
}

function cargarRonda() {
  if (ronda >= RONDAS.length) {
    terminar();
    return;
  }
  const r = RONDAS[ronda];
  correcto = r.lineas;
  actual = barajar(r.lineas);
  elConsigna.textContent = r.consigna;
  elRonda.textContent = ronda + 1;
  elMensaje.textContent = '';
  render();
}

document.getElementById('btnComprobarOrden').addEventListener('click', () => {
  const ok = actual.every((linea, i) => linea === correcto[i]);
  if (ok) {
    const bonus = Math.max(10, 50 - segundos);
    puntaje += bonus;
    elPuntaje.textContent = puntaje;
    elMensaje.innerHTML = '<span style="color:#15803d;font-weight:600;">✔ ¡Correcto! +' + bonus + ' puntos</span>';
    ronda++;
    setTimeout(cargarRonda, 900);
  } else {
    elMensaje.innerHTML = '<span style="color:#b91c1c;">✗ Todavía no es el orden correcto, sigue intentando.</span>';
  }
});

function terminar() {
  clearInterval(cronometro);
  document.getElementById('tarjetaJuego').hidden = true;
  document.getElementById('pantallaFinal').hidden = false;
  document.getElementById('puntajeFinal').textContent = puntaje;

  <?php if ($usuario): ?>
  const body = new URLSearchParams();
  body.set('guardar_puntaje', '1');
  body.set('puntaje', puntaje);
  body.set('csrf_token', <?= json_encode(csrf_token()) ?>);
  fetch(<?= json_encode(url('juegos/ordena-codigo.php')) ?>, { method: 'POST', body: body });
  <?php endif; ?>
}

cronometro = setInterval(() => { segundos++; elTiempo.textContent = segundos; }, 1000);
cargarRonda();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
