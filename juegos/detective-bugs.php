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
    registrar_puntaje_juego($usuario['id'], 'detective-bugs', $puntaje);
    $insignias = evaluar_insignias($usuario['id']);
    echo json_encode(['ok' => true, 'insignias' => $insignias]);
    exit;
}

$tabla = mejores_puntajes_juego('detective-bugs', 5);
$tituloPagina = 'Detective de bugs — ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container" style="max-width:700px;">
    <p><a href="<?= url('juegos.php') ?>">← Arcade</a></p>
    <h1 class="dp-center">🕵️ Detective de bugs</h1>
    <p class="dp-section-sub">Cada snippet tiene un error. Elige qué está mal antes de que se acabe el tiempo.</p>

    <div class="dp-card dp-flex-between">
      <div>Ronda: <strong id="ronda">1</strong>/<span id="totalRondas">8</span></div>
      <div>⏱ <strong id="tiempo">15</strong>s</div>
      <div>Puntaje: <strong id="puntaje">0</strong></div>
    </div>

    <div class="dp-card dp-mt" id="tarjetaJuego">
      <pre class="dp-juego-codigo"><code id="codigoBug"></code></pre>
      <div id="opcionesBug" class="dp-juego-opciones"></div>
      <p id="mensajeRonda" class="dp-mt"></p>
    </div>

    <div id="pantallaFinal" class="dp-card dp-mt" hidden>
      <h2 class="dp-center">🎉 ¡Juego terminado!</h2>
      <p class="dp-center">Puntaje final: <strong id="puntajeFinal">0</strong></p>
      <p class="dp-center"><a href="<?= url('juegos/detective-bugs.php') ?>" class="dp-btn dp-btn-primary">Jugar de nuevo</a></p>
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
  { codigo: '<p>Texto sin cerrar\n<button>Enviar</b>', opciones: ['Falta cerrar el <p>', 'El botón usa </b> en vez de </button>', 'Falta el <html>'], correcta: 1 },
  { codigo: 'function saludar(nombre) {\n  return "Hola " nombre;\n}', opciones: ['Falta el operador + para concatenar', 'La función no tiene nombre', 'Falta el punto y coma final'], correcta: 0 },
  { codigo: '.boton {\n  color: white\n  background: blue;\n}', opciones: ['background no es una propiedad válida', 'Falta punto y coma después de white', 'color debe ir después de background'], correcta: 1 },
  { codigo: 'let x = 5\nif (x = 10) {\n  console.log("Es 10");\n}', opciones: ['Debería usar == o === en vez de =', 'Falta declarar x con const', 'console.log está mal escrito'], correcta: 0 },
  { codigo: '<ul>\n  <li>Uno</li>\n  <p>Dos</p>\n</ul>', opciones: ['Falta cerrar <ul>', '<p> no debería estar dentro de <ul>, debería ser <li>', 'Falta el atributo type'], correcta: 1 },
  { codigo: 'const numeros = [1, 2, 3];\nnumeros.push(4)\nconsole.log(numeros.lenght);', opciones: ['push no existe en arrays', 'Debería ser "length", no "lenght"', 'Los arrays no pueden tener 4 elementos'], correcta: 1 },
  { codigo: '<img src="foto.jpg">\n<a herf="https://ejemplo.com">Link</a>', opciones: ['Falta el atributo alt en la imagen', 'El atributo debería ser "href", no "herf"', 'Falta cerrar la etiqueta <a>'], correcta: 1 },
  { codigo: 'function esPar(n) {\n  if (n % 2 = 0) {\n    return true;\n  }\n  return false;\n}', opciones: ['Debería usar == o === en vez de =', 'return true está de más', 'n % 2 no es válido en JS'], correcta: 0 },
];

let ronda = 0;
let puntaje = 0;
let tiempoRonda = 15;
let cronometro = null;

const elRonda = document.getElementById('ronda');
const elTiempo = document.getElementById('tiempo');
const elPuntaje = document.getElementById('puntaje');
const elCodigo = document.getElementById('codigoBug');
const elOpciones = document.getElementById('opcionesBug');
const elMensaje = document.getElementById('mensajeRonda');

document.getElementById('totalRondas').textContent = RONDAS.length;

function cargarRonda() {
  if (ronda >= RONDAS.length) { terminar(); return; }
  const r = RONDAS[ronda];
  elCodigo.textContent = r.codigo;
  elOpciones.innerHTML = '';
  elMensaje.textContent = '';
  elRonda.textContent = ronda + 1;
  tiempoRonda = 15;
  elTiempo.textContent = tiempoRonda;

  r.opciones.forEach((op, i) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'dp-btn dp-btn-outline';
    btn.style.display = 'block';
    btn.style.width = '100%';
    btn.style.marginBottom = '8px';
    btn.style.textAlign = 'left';
    btn.textContent = op;
    btn.addEventListener('click', () => responder(i));
    elOpciones.appendChild(btn);
  });

  clearInterval(cronometro);
  cronometro = setInterval(() => {
    tiempoRonda--;
    elTiempo.textContent = tiempoRonda;
    if (tiempoRonda <= 0) {
      clearInterval(cronometro);
      elMensaje.innerHTML = '<span style="color:#b91c1c;">⏱ Se acabó el tiempo</span>';
      ronda++;
      setTimeout(cargarRonda, 900);
    }
  }, 1000);
}

function responder(i) {
  clearInterval(cronometro);
  const r = RONDAS[ronda];
  if (i === r.correcta) {
    const bonus = 10 + tiempoRonda;
    puntaje += bonus;
    elPuntaje.textContent = puntaje;
    elMensaje.innerHTML = '<span style="color:#15803d;font-weight:600;">✔ ¡Correcto! +' + bonus + ' puntos</span>';
  } else {
    elMensaje.innerHTML = '<span style="color:#b91c1c;">✗ No era esa. La respuesta correcta era: "' + r.opciones[r.correcta] + '"</span>';
  }
  ronda++;
  setTimeout(cargarRonda, 1200);
}

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
  fetch(<?= json_encode(url('juegos/detective-bugs.php')) ?>, { method: 'POST', body: body });
  <?php endif; ?>
}

cargarRonda();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
