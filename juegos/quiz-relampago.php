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
    $puntaje = max(0, min(2000, (int) ($_POST['puntaje'] ?? 0)));
    registrar_puntaje_juego($usuario['id'], 'quiz-relampago', $puntaje);
    $insignias = evaluar_insignias($usuario['id']);
    echo json_encode(['ok' => true, 'insignias' => $insignias]);
    exit;
}

$tabla = mejores_puntajes_juego('quiz-relampago', 5);
$tituloPagina = 'Quiz relámpago — ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<section class="dp-section" style="padding-top:30px;">
  <div class="dp-container" style="max-width:700px;">
    <p><a href="<?= url('juegos.php') ?>">← Arcade</a></p>
    <h1 class="dp-center">⚡ Quiz relámpago</h1>
    <p class="dp-section-sub">Verdadero o falso, 8 segundos por pregunta. Encadena respuestas correctas para ganar combo.</p>

    <div class="dp-card dp-flex-between">
      <div>Pregunta: <strong id="ronda">1</strong>/<span id="totalRondas">12</span></div>
      <div>⏱ <strong id="tiempo">8</strong>s</div>
      <div>Combo: <strong id="combo">0</strong>🔥</div>
      <div>Puntaje: <strong id="puntaje">0</strong></div>
    </div>

    <div class="dp-card dp-mt" id="tarjetaJuego">
      <h2 class="dp-center" id="pregunta" style="min-height:70px;">Cargando...</h2>
      <div class="dp-flex" style="justify-content:center;gap:20px;">
        <button type="button" id="btnVerdadero" class="dp-btn dp-btn-secundario dp-btn-lg">✔ Verdadero</button>
        <button type="button" id="btnFalso" class="dp-btn dp-btn-outline dp-btn-lg">✗ Falso</button>
      </div>
      <p id="mensajeRonda" class="dp-mt dp-center"></p>
    </div>

    <div id="pantallaFinal" class="dp-card dp-mt" hidden>
      <h2 class="dp-center">🎉 ¡Juego terminado!</h2>
      <p class="dp-center">Puntaje final: <strong id="puntajeFinal">0</strong></p>
      <p class="dp-center"><a href="<?= url('juegos/quiz-relampago.php') ?>" class="dp-btn dp-btn-primary">Jugar de nuevo</a></p>
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
let PREGUNTAS = [
  { p: 'HTML significa HyperText Markup Language.', r: true },
  { p: 'CSS se usa para darle estructura a una página web.', r: false },
  { p: 'La etiqueta <img> necesita cierre </img>.', r: false },
  { p: 'En JavaScript, "let" permite declarar variables que pueden cambiar de valor.', r: true },
  { p: 'flex y grid son valores válidos para la propiedad display en CSS.', r: true },
  { p: 'Los comentarios en JS se escriben con <!-- -->.', r: false },
  { p: 'El atributo alt en <img> mejora la accesibilidad.', r: true },
  { p: 'JSON.parse() convierte un objeto JS en texto.', r: false },
  { p: 'position: sticky combina comportamiento de relative y fixed.', r: true },
  { p: 'Un array en JS se crea con llaves { }.', r: false },
  { p: 'addEventListener sirve para escuchar eventos como clics.', r: true },
  { p: 'localStorage borra los datos guardados al cerrar el navegador.', r: false },
  { p: '<h1> a <h6> son etiquetas de encabezado en HTML.', r: true },
  { p: 'CSS Grid es un sistema de layout unidimensional.', r: false },
  { p: 'Las funciones flecha (=>) son una forma de escribir funciones en JS.', r: true },
];

function barajar(arr) {
  const c = arr.slice();
  for (let i = c.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [c[i], c[j]] = [c[j], c[i]];
  }
  return c;
}

PREGUNTAS = barajar(PREGUNTAS).slice(0, 12);

let ronda = 0;
let puntaje = 0;
let combo = 0;
let tiempoRonda = 8;
let cronometro = null;
let respondida = false;

const elRonda = document.getElementById('ronda');
const elTiempo = document.getElementById('tiempo');
const elPuntaje = document.getElementById('puntaje');
const elCombo = document.getElementById('combo');
const elPregunta = document.getElementById('pregunta');
const elMensaje = document.getElementById('mensajeRonda');

document.getElementById('totalRondas').textContent = PREGUNTAS.length;

function cargarRonda() {
  if (ronda >= PREGUNTAS.length) { terminar(); return; }
  respondida = false;
  elPregunta.textContent = PREGUNTAS[ronda].p;
  elMensaje.textContent = '';
  elRonda.textContent = ronda + 1;
  tiempoRonda = 8;
  elTiempo.textContent = tiempoRonda;

  clearInterval(cronometro);
  cronometro = setInterval(() => {
    tiempoRonda--;
    elTiempo.textContent = tiempoRonda;
    if (tiempoRonda <= 0) {
      clearInterval(cronometro);
      responder(null);
    }
  }, 1000);
}

function responder(valor) {
  if (respondida) return;
  respondida = true;
  clearInterval(cronometro);
  const ok = valor === PREGUNTAS[ronda].r;
  if (ok) {
    combo++;
    const bonus = 10 + tiempoRonda * 2 + (combo >= 3 ? 15 : 0);
    puntaje += bonus;
    elMensaje.innerHTML = '<span style="color:#15803d;font-weight:600;">✔ ¡Correcto! +' + bonus + (combo >= 3 ? ' (combo x' + combo + ')' : '') + '</span>';
  } else {
    combo = 0;
    elMensaje.innerHTML = '<span style="color:#b91c1c;">✗ Incorrecto. Era: ' + (PREGUNTAS[ronda].r ? 'Verdadero' : 'Falso') + '</span>';
  }
  elPuntaje.textContent = puntaje;
  elCombo.textContent = combo;
  ronda++;
  setTimeout(cargarRonda, 700);
}

document.getElementById('btnVerdadero').addEventListener('click', () => responder(true));
document.getElementById('btnFalso').addEventListener('click', () => responder(false));

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
  fetch(<?= json_encode(url('juegos/quiz-relampago.php')) ?>, { method: 'POST', body: body });
  <?php endif; ?>
}

cargarRonda();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
