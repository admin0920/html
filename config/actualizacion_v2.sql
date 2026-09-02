-- ============================================================
-- DIQUE PROGRAMANDO — Actualización v2
-- Ejecuta este archivo en phpMyAdmin SOLO si ya tenías la base de
-- datos instalada con la versión anterior (config/database.sql v1).
-- Si vas a instalar desde cero, ignora este archivo: solo necesitas
-- importar config/database.sql, que ya incluye todo esto.
--
-- Agrega: planes de estudio y modo PRO (roadmap), insignias,
-- retos de código, laboratorios prácticos, marcador de juegos,
-- y muchas lecciones nuevas.
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Planes / roadmap / modo PRO en usuarios
-- ------------------------------------------------------------
ALTER TABLE usuarios
  ADD COLUMN plan_ritmo ENUM('relajado','regular','intensivo') NOT NULL DEFAULT 'regular' AFTER rol,
  ADD COLUMN modo_pro TINYINT(1) NOT NULL DEFAULT 0 AFTER plan_ritmo;

-- ------------------------------------------------------------
-- Insignias (logros)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS insignias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(60) NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  icono VARCHAR(10) NOT NULL DEFAULT '🏅',
  condicion_tipo ENUM('lecciones_completadas','racha_dias','curso_completado','todos_cursos','retos_completados','laboratorios_completados','juegos_jugados') NOT NULL,
  condicion_valor INT NOT NULL DEFAULT 1,
  condicion_extra VARCHAR(60) DEFAULT NULL,
  puntos_bonus INT NOT NULL DEFAULT 25,
  PRIMARY KEY (id),
  UNIQUE KEY uq_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS usuario_insignias (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  insignia_id INT UNSIGNED NOT NULL,
  obtenida_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_insignia (usuario_id, insignia_id),
  CONSTRAINT fk_ui_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_ui_insignia FOREIGN KEY (insignia_id) REFERENCES insignias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Retos de código (mini ejercicios con comprobación automática)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS retos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(150) NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  lenguaje ENUM('html','css','js') NOT NULL,
  dificultad ENUM('facil','medio','dificil') NOT NULL DEFAULT 'facil',
  enunciado TEXT NOT NULL,
  html_inicial LONGTEXT,
  css_inicial LONGTEXT,
  js_inicial LONGTEXT,
  comprobacion_js TEXT NOT NULL COMMENT 'Expresión JS que recibe (doc, win) y retorna true/false',
  puntos INT NOT NULL DEFAULT 20,
  orden INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reto_completados (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  reto_id INT UNSIGNED NOT NULL,
  completado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_reto (usuario_id, reto_id),
  CONSTRAINT fk_rc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_rc_reto FOREIGN KEY (reto_id) REFERENCES retos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Laboratorios (proyectos prácticos más grandes)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS laboratorios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(150) NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  categoria VARCHAR(60) NOT NULL DEFAULT 'General',
  dificultad ENUM('facil','medio','dificil') NOT NULL DEFAULT 'medio',
  descripcion TEXT NOT NULL,
  requisitos TEXT NOT NULL COMMENT 'Lista en HTML (ul/li) de requisitos a cumplir',
  html_inicial LONGTEXT,
  css_inicial LONGTEXT,
  js_inicial LONGTEXT,
  puntos INT NOT NULL DEFAULT 40,
  orden INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS laboratorio_completados (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  laboratorio_id INT UNSIGNED NOT NULL,
  completado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_lab (usuario_id, laboratorio_id),
  CONSTRAINT fk_lc_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_lc_lab FOREIGN KEY (laboratorio_id) REFERENCES laboratorios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS laboratorio_soluciones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  laboratorio_id INT UNSIGNED NOT NULL,
  html LONGTEXT,
  css LONGTEXT,
  js LONGTEXT,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_lab_sol (usuario_id, laboratorio_id),
  CONSTRAINT fk_ls_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_ls_lab FOREIGN KEY (laboratorio_id) REFERENCES laboratorios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Marcador de juegos (arcade de aprendizaje)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS juego_puntajes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  juego VARCHAR(60) NOT NULL,
  puntaje INT NOT NULL DEFAULT 0,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_juego (juego),
  CONSTRAINT fk_jp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED: insignias
-- ============================================================
INSERT INTO insignias (codigo, nombre, descripcion, icono, condicion_tipo, condicion_valor, condicion_extra, puntos_bonus) VALUES
('primer-paso', 'Primeros pasos', 'Completa tu primera lección', '🌱', 'lecciones_completadas', 1, NULL, 15),
('racha-3', 'Constancia', 'Mantén una racha de 3 días seguidos', '🔥', 'racha_dias', 3, NULL, 20),
('racha-7', 'Racha de fuego', 'Mantén una racha de 7 días seguidos', '🔥🔥', 'racha_dias', 7, NULL, 40),
('racha-30', 'Imparable', 'Mantén una racha de 30 días seguidos', '🔥🔥🔥', 'racha_dias', 30, NULL, 150),
('diez-lecciones', 'En marcha', 'Completa 10 lecciones', '📗', 'lecciones_completadas', 10, NULL, 30),
('veinte-lecciones', 'Estudiante dedicado', 'Completa 20 lecciones', '📘', 'lecciones_completadas', 20, NULL, 50),
('maestro-html', 'Maestro del HTML', 'Completa el curso de HTML', '🧱', 'curso_completado', 1, 'html', 60),
('maestro-css', 'Maestro del CSS', 'Completa el curso de CSS', '🎨', 'curso_completado', 1, 'css', 60),
('maestro-js', 'Maestro del JS', 'Completa el curso de JavaScript', '⚡', 'curso_completado', 1, 'js', 60),
('full-stack-jr', 'Full Stack Jr.', 'Completa los 3 cursos completos', '🚀', 'todos_cursos', 1, NULL, 200),
('cazador-retos', 'Cazador de retos', 'Completa 5 retos de código', '🎯', 'retos_completados', 5, NULL, 40),
('maestro-retos', 'Maestro de retos', 'Completa 10 retos de código', '🏆', 'retos_completados', 10, NULL, 80),
('constructor', 'Constructor', 'Completa tu primer laboratorio', '🔧', 'laboratorios_completados', 1, NULL, 30),
('arquitecto', 'Arquitecto web', 'Completa 5 laboratorios', '🏗️', 'laboratorios_completados', 5, NULL, 100),
('jugador', 'Jugador dedicado', 'Juega en el arcade al menos 3 veces', '🎮', 'juegos_jugados', 3, NULL, 20);

-- ============================================================
-- SEED: retos de código
-- ============================================================
INSERT INTO retos (slug, titulo, lenguaje, dificultad, enunciado, html_inicial, css_inicial, js_inicial, comprobacion_js, puntos, orden) VALUES
('reto-titulo-parrafo', 'Tu primer título', 'html', 'facil',
 'Crea un <code>&lt;h1&gt;</code> con el texto exacto "Hola Dique Programando" y un <code>&lt;p&gt;</code> con cualquier texto debajo.',
 '<!-- Escribe tu código aquí -->\n',
 '', '',
 'return doc.querySelector("h1") && doc.querySelector("h1").textContent.trim() === "Hola Dique Programando" && doc.querySelector("p") !== null;',
 15, 1),

('reto-lista-compras', 'Lista de compras', 'html', 'facil',
 'Crea una lista desordenada (<code>&lt;ul&gt;</code>) con al menos 3 elementos <code>&lt;li&gt;</code>.',
 '<!-- Escribe tu código aquí -->\n',
 '', '',
 'return doc.querySelectorAll("ul li").length >= 3;',
 15, 2),

('reto-enlace-boton', 'Enlace con estilo de botón', 'css', 'facil',
 'Dale estilo a la clase <code>.boton</code> para que tenga <code>background-color</code> y <code>padding</code> distinto de 0.',
 '<a href="#" class="boton">Haz clic</a>',
 '.boton {\n  /* agrega tus estilos aquí */\n}', '',
 'var el = doc.querySelector(".boton"); if (!el) return false; var s = win.getComputedStyle(el); return s.paddingTop !== "0px" && s.backgroundColor !== "rgba(0, 0, 0, 0)" && s.backgroundColor !== "transparent";',
 20, 3),

('reto-flexbox-centrado', 'Centrado perfecto con Flexbox', 'css', 'medio',
 'Usa Flexbox en <code>.caja</code> para centrar el <code>.circulo</code> vertical y horizontalmente.',
 '<div class="caja"><div class="circulo"></div></div>',
 '.caja {\n  height: 200px;\n  background: #eef2ff;\n  /* agrega display flex y centrado aquí */\n}\n.circulo {\n  width: 50px;\n  height: 50px;\n  border-radius: 50%;\n  background: #6366f1;\n}', '',
 'var el = doc.querySelector(".caja"); if (!el) return false; var s = win.getComputedStyle(el); return s.display === "flex" && (s.justifyContent === "center" || s.alignItems === "center");',
 25, 4),

('reto-suma-funcion', 'Función suma', 'js', 'facil',
 'Declara una función <code>sumar(a, b)</code> que retorne la suma de sus dos parámetros.',
 '', '',
 'function sumar(a, b) {\n  // completa la función\n}',
 'try { return typeof sumar === "function" && sumar(2, 3) === 5 && sumar(-1, 1) === 0; } catch(e) { return false; }',
 20, 5),

('reto-array-pares', 'Filtrar números pares', 'js', 'medio',
 'Declara una función <code>soloPares(arr)</code> que reciba un array de números y retorne solo los pares.',
 '', '',
 'function soloPares(arr) {\n  // usa filter\n}',
 'try { var r = soloPares([1,2,3,4,5,6]); return Array.isArray(r) && r.length === 3 && r.includes(2) && r.includes(4) && r.includes(6); } catch(e) { return false; }',
 25, 6),

('reto-contador-clicks', 'Contador de clics', 'js', 'medio',
 'Hay un botón con id <code>contador</code>. Haz que cada clic aumente en 1 el número mostrado dentro de él, empezando en 0.',
 '<button id="contador">0</button>',
 '', '// agrega tu código aquí\n',
 'var btn = doc.getElementById("contador"); if (!btn) return false; var antes = btn.textContent; btn.click(); btn.click(); return btn.textContent.trim() === "2";',
 25, 7),

('reto-validar-form', 'Validación simple', 'js', 'dificil',
 'Hay un input con id <code>email</code> y un <code>span</code> con id <code>resultado</code>. Al escribir, si el valor contiene "@" muestra "Válido" en el span, si no muestra "Inválido".',
 '<input type="text" id="email">\n<span id="resultado"></span>',
 '', '// agrega tu código aquí (evento input)\n',
 'var input = doc.getElementById("email"); var out = doc.getElementById("resultado"); if (!input || !out) return false; input.value = "hola@test.com"; input.dispatchEvent(new win.Event("input")); var ok1 = out.textContent.trim() === "Válido"; input.value = "hola"; input.dispatchEvent(new win.Event("input")); var ok2 = out.textContent.trim() === "Inválido"; return ok1 && ok2;',
 35, 8);

-- ============================================================
-- SEED: laboratorios (proyectos prácticos)
-- ============================================================
INSERT INTO laboratorios (slug, titulo, categoria, dificultad, descripcion, requisitos, html_inicial, css_inicial, js_inicial, puntos, orden) VALUES
('lab-tarjeta-perfil', 'Tarjeta de perfil personal', 'HTML + CSS', 'facil',
 'Construye una tarjeta de presentación personal con foto, nombre, profesión y redes sociales, usando HTML semántico y CSS.',
 '<ul><li>Debe tener una imagen (puedes usar una URL de placeholder)</li><li>Debe tener tu nombre en un &lt;h2&gt; y tu profesión en un &lt;p&gt;</li><li>Debe tener al menos 2 enlaces a "redes sociales"</li><li>Debe usar box-shadow y border-radius para que se vea como una tarjeta</li></ul>',
 '<div class="tarjeta">\n  <img src="https://placekitten.com/120/120" alt="Foto de perfil">\n  <h2>Tu nombre</h2>\n  <p>Tu profesión</p>\n  <div class="redes">\n    <a href="#">Twitter</a>\n    <a href="#">GitHub</a>\n  </div>\n</div>',
 '.tarjeta {\n  max-width: 280px;\n  margin: 40px auto;\n  text-align: center;\n  padding: 24px;\n  border-radius: 16px;\n  box-shadow: 0 10px 25px rgba(0,0,0,.1);\n  font-family: sans-serif;\n}\n.tarjeta img {\n  border-radius: 50%;\n}', '',
 40, 1),

('lab-galeria-grid', 'Galería de imágenes con Grid', 'CSS', 'medio',
 'Crea una galería de imágenes responsive usando CSS Grid, que se adapte de 1 columna en móvil a 3 columnas en escritorio.',
 '<ul><li>Debe usar <code>display: grid</code></li><li>Debe tener al menos 6 imágenes</li><li>Debe usar <code>@media</code> para cambiar columnas según el ancho</li><li>Las imágenes deben tener <code>object-fit: cover</code></li></ul>',
 '<div class="galeria">\n  <img src="https://placekitten.com/300/300">\n  <img src="https://placekitten.com/301/300">\n  <img src="https://placekitten.com/300/301">\n  <img src="https://placekitten.com/302/300">\n  <img src="https://placekitten.com/300/302">\n  <img src="https://placekitten.com/303/300">\n</div>',
 '.galeria {\n  display: grid;\n  grid-template-columns: 1fr;\n  gap: 10px;\n  padding: 20px;\n}\n.galeria img {\n  width: 100%;\n  height: 180px;\n  object-fit: cover;\n  border-radius: 8px;\n}\n@media (min-width: 700px) {\n  .galeria {\n    grid-template-columns: repeat(3, 1fr);\n  }\n}', '',
 45, 2),

('lab-lista-tareas', 'Lista de tareas (To-Do List)', 'JavaScript', 'medio',
 'Construye una lista de tareas funcional: agregar tarea, marcarla como completada y eliminarla, todo con JavaScript puro.',
 '<ul><li>Un input y botón para agregar tareas</li><li>Al hacer clic en una tarea se marca como completada (tachada)</li><li>Cada tarea debe tener un botón de eliminar</li><li>No se deben poder agregar tareas vacías</li></ul>',
 '<div class="todo">\n  <input type="text" id="nuevaTarea" placeholder="Nueva tarea...">\n  <button id="agregar">Agregar</button>\n  <ul id="listaTareas"></ul>\n</div>',
 '.todo { max-width: 400px; margin: 30px auto; font-family: sans-serif; }\n.todo input { padding: 8px; width: 70%; }\n.todo button { padding: 8px 14px; }\n.todo li { display: flex; justify-content: space-between; padding: 8px; border-bottom: 1px solid #eee; }\n.completada { text-decoration: line-through; color: #999; }',
 '// Implementa aquí la lógica: agregar, completar y eliminar tareas\n',
 50, 3),

('lab-calculadora', 'Mini calculadora', 'JavaScript', 'dificil',
 'Crea una calculadora básica que sume, reste, multiplique y divida dos números ingresados por el usuario.',
 '<ul><li>Dos inputs numéricos y 4 botones de operación (+, -, *, /)</li><li>Un elemento donde se muestre el resultado</li><li>Debe manejar la división por cero mostrando un mensaje de error</li></ul>',
 '<div class="calc">\n  <input type="number" id="num1" placeholder="Número 1">\n  <input type="number" id="num2" placeholder="Número 2">\n  <div class="botones">\n    <button data-op="+">+</button>\n    <button data-op="-">-</button>\n    <button data-op="*">×</button>\n    <button data-op="/">÷</button>\n  </div>\n  <h2 id="resultado">0</h2>\n</div>',
 '.calc { max-width: 300px; margin: 30px auto; text-align: center; font-family: sans-serif; }\n.calc input { width: 45%; padding: 8px; margin-bottom: 10px; }\n.calc button { padding: 10px 16px; margin: 4px; cursor: pointer; }',
 '// Implementa la lógica de la calculadora aquí\n',
 55, 4),

('lab-landing-producto', 'Landing page de producto', 'Proyecto final', 'dificil',
 'Construye una landing page completa para un producto ficticio: encabezado con navegación, sección hero, características, y pie de página.',
 '<ul><li>Debe tener &lt;header&gt;, &lt;main&gt; y &lt;footer&gt; semánticos</li><li>Debe ser responsive (usa Flexbox o Grid)</li><li>Debe tener al menos 3 "características" del producto en tarjetas</li><li>Debe tener un botón de llamada a la acción destacado</li></ul>',
 '<header>\n  <h1>MiProducto</h1>\n  <nav><a href="#">Características</a> <a href="#">Precio</a></nav>\n</header>\n<main>\n  <section class="hero">\n    <h2>El mejor producto para tu vida</h2>\n    <button class="cta">Comprar ahora</button>\n  </section>\n  <section class="caracteristicas">\n    <div class="tarjeta">Rápido</div>\n    <div class="tarjeta">Seguro</div>\n    <div class="tarjeta">Fácil de usar</div>\n  </section>\n</main>\n<footer>\n  <p>&copy; 2026 MiProducto</p>\n</footer>',
 'body { font-family: sans-serif; margin: 0; }\nheader { display: flex; justify-content: space-between; padding: 20px; background: #6366f1; color: white; }\n.hero { text-align: center; padding: 60px 20px; }\n.cta { padding: 14px 28px; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 1rem; }\n.caracteristicas { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; padding: 20px; }\n.tarjeta { background: #f1f5f9; padding: 30px; border-radius: 12px; }\nfooter { text-align: center; padding: 20px; background: #0f172a; color: white; }', '',
 70, 5);

-- ============================================================
-- SEED: nuevos módulos y lecciones (contenido adicional)
-- ============================================================

-- Módulo nuevo para HTML (curso slug 'html')
INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Proyecto y buenas prácticas', 5 FROM cursos c WHERE c.slug = 'html';

SET @mod_html5 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Proyecto y buenas prácticas' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@mod_html5, 'html-multimedia', 'Video, audio e iframes', '<p>HTML permite incrustar contenido multimedia con <code>&lt;video&gt;</code>, <code>&lt;audio&gt;</code> e incrustar otras páginas con <code>&lt;iframe&gt;</code>. Todos aceptan atributos como <code>controls</code>, <code>autoplay</code> (úsalo con cuidado) y <code>width</code>/<code>height</code>.</p>',
'<video controls width="300">\n  <source src="video.mp4" type="video/mp4">\n  Tu navegador no soporta video.\n</video>\n\n<audio controls>\n  <source src="audio.mp3" type="audio/mpeg">\n</audio>\n\n<iframe src="https://www.example.com" width="300" height="150"></iframe>', 1, 6),

(@mod_html5, 'html-seo-meta', 'SEO básico y meta etiquetas', '<p>Las etiquetas <code>&lt;meta&gt;</code> en el <code>&lt;head&gt;</code> ayudan a los buscadores y redes sociales a entender tu página: <code>description</code>, <code>viewport</code> (para responsive) y las etiquetas Open Graph para compartir en redes.</p>',
'<head>\n  <meta charset="UTF-8">\n  <meta name="viewport" content="width=device-width, initial-scale=1.0">\n  <meta name="description" content="Aprende a programar gratis">\n  <meta property="og:title" content="Dique Programando">\n  <title>Mi página</title>\n</head>', 2, 5),

(@mod_html5, 'html-accesibilidad', 'Accesibilidad web (a11y)', '<p>Una página accesible puede ser usada por personas con discapacidad visual, motora, etc. Buenas prácticas: usar <code>alt</code> descriptivo en imágenes, usar etiquetas semánticas, contraste de color suficiente, y atributos <code>aria-*</code> cuando sea necesario.</p>',
'<img src="grafico.png" alt="Gráfico de ventas mostrando un aumento del 20% en 2025">\n<button aria-label="Cerrar ventana">✕</button>\n<nav aria-label="Menú principal">...</nav>', 3, 6),

(@mod_html5, 'html-proyecto-final', 'Proyecto: tu primera página completa', '<p>Es hora de unir todo lo aprendido. Construye una página personal completa combinando header semántico, una sección "sobre mí", una lista de tus habilidades y un formulario de contacto. ¡No hay una única respuesta correcta, experimenta!</p>',
'<header>\n  <h1>Hola, soy [tu nombre]</h1>\n  <nav><a href="#sobre-mi">Sobre mí</a> <a href="#contacto">Contacto</a></nav>\n</header>\n<main>\n  <section id="sobre-mi">\n    <h2>Sobre mí</h2>\n    <p>Escribe algo sobre ti...</p>\n    <ul>\n      <li>HTML</li>\n      <li>CSS</li>\n    </ul>\n  </section>\n  <section id="contacto">\n    <h2>Contacto</h2>\n    <form>\n      <input type="email" placeholder="Tu correo">\n      <button>Enviar</button>\n    </form>\n  </section>\n</main>', 4, 12);

-- Módulo nuevo para CSS (curso slug 'css')
INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'CSS avanzado', 5 FROM cursos c WHERE c.slug = 'css';

SET @mod_css5 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'CSS avanzado' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@mod_css5, 'css-variables', 'Variables CSS (custom properties)', '<p>Las variables CSS (<code>--nombre</code>) permiten reutilizar valores en toda la hoja de estilos y son la base de los temas claro/oscuro. Se definen normalmente en <code>:root</code> y se usan con <code>var(--nombre)</code>.</p>',
'<button class="btn">Botón con variable</button>',
':root {\n  --color-primario: #6366f1;\n  --radio: 10px;\n}\n\n.btn {\n  background: var(--color-primario);\n  border-radius: var(--radio);\n  color: white;\n  padding: 10px 20px;\n  border: none;\n}', 1, 7),

(@mod_css5, 'css-posicionamiento', 'Posicionamiento: relative, absolute, fixed y sticky', '<p>La propiedad <code>position</code> controla cómo se posiciona un elemento: <code>relative</code> (respecto a sí mismo), <code>absolute</code> (respecto al ancestro posicionado más cercano), <code>fixed</code> (respecto a la ventana) y <code>sticky</code> (se "pega" al hacer scroll).</p>',
'<div class="contenedor">\n  <div class="etiqueta">Nuevo</div>\n  <img src="https://placekitten.com/300/200">\n</div>',
'.contenedor {\n  position: relative;\n  width: 300px;\n}\n.etiqueta {\n  position: absolute;\n  top: 10px;\n  right: 10px;\n  background: #ef4444;\n  color: white;\n  padding: 4px 10px;\n  border-radius: 6px;\n}', 2, 8),

(@mod_css5, 'css-pseudo-elementos', 'Pseudo-elementos y pseudo-clases avanzadas', '<p>Los pseudo-elementos <code>::before</code> y <code>::after</code> permiten insertar contenido decorativo sin tocar el HTML. Pseudo-clases como <code>:first-child</code>, <code>:nth-child()</code> y <code>:not()</code> seleccionan elementos según su posición o condición.</p>',
'<ul class="lista">\n  <li>Elemento 1</li>\n  <li>Elemento 2</li>\n  <li>Elemento 3</li>\n</ul>',
'.lista li::before {\n  content: "✔ ";\n  color: #10b981;\n  font-weight: bold;\n}\n\n.lista li:nth-child(odd) {\n  background: #f8fafc;\n}', 3, 7),

(@mod_css5, 'css-proyecto-final', 'Proyecto: tarjeta interactiva', '<p>Combina todo lo aprendido de CSS: variables, position, pseudo-clases, flexbox y transiciones, para construir una tarjeta de producto interactiva con efecto al pasar el mouse.</p>',
'<div class="producto">\n  <span class="etiqueta">Nuevo</span>\n  <img src="https://placekitten.com/250/180">\n  <h3>Producto genial</h3>\n  <p>$49.99</p>\n</div>',
'.producto {\n  position: relative;\n  max-width: 250px;\n  margin: 30px auto;\n  padding: 16px;\n  border-radius: 12px;\n  box-shadow: 0 4px 12px rgba(0,0,0,.08);\n  transition: transform .2s ease;\n  text-align: center;\n}\n.producto:hover {\n  transform: translateY(-6px);\n}\n.etiqueta {\n  position: absolute;\n  top: 10px;\n  left: 10px;\n  background: #6366f1;\n  color: white;\n  padding: 2px 10px;\n  border-radius: 6px;\n  font-size: .75rem;\n}', 4, 10);

-- Módulo nuevo para JS (curso slug 'js')
INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'JavaScript en el mundo real', 5 FROM cursos c WHERE c.slug = 'js';

SET @mod_js5 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'JavaScript en el mundo real' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@mod_js5, 'js-json-localstorage', 'JSON y localStorage', '<p><code>JSON.stringify()</code> convierte objetos JS a texto y <code>JSON.parse()</code> hace lo contrario. <code>localStorage</code> permite guardar datos en el navegador que persisten aunque se cierre la pestaña, ¡ideal para guardar el progreso de un usuario!</p>',
'<button id="guardar">Guardar nombre</button>\n<p id="resultado"></p>',
'const usuario = { nombre: "Ana", nivel: 3 };\n\ndocument.getElementById("guardar").addEventListener("click", () => {\n  localStorage.setItem("usuario", JSON.stringify(usuario));\n  const guardado = JSON.parse(localStorage.getItem("usuario"));\n  document.getElementById("resultado").innerText = `Guardado: ${guardado.nombre}, nivel ${guardado.nivel}`;\n});', 1, 8),

(@mod_js5, 'js-clases-poo', 'Programación orientada a objetos: clases', '<p>Las <code>class</code> son una forma moderna de crear "moldes" para objetos. Tienen un <code>constructor</code> para inicializar propiedades y métodos para definir comportamiento. Se instancian con <code>new</code>.</p>',
'<div id="salida"></div>',
'class Personaje {\n  constructor(nombre, vida) {\n    this.nombre = nombre;\n    this.vida = vida;\n  }\n  atacar() {\n    return `${this.nombre} ataca!`;\n  }\n}\n\nconst heroe = new Personaje("Guerrero", 100);\ndocument.getElementById("salida").innerText = heroe.atacar() + " Vida: " + heroe.vida;', 2, 9),

(@mod_js5, 'js-fetch-api', 'Consumir APIs con fetch', '<p><code>fetch()</code> permite pedir datos a servidores externos (APIs). Devuelve una <em>Promesa</em>, así que se usa junto a <code>async/await</code> o <code>.then()</code>. Es la base de cualquier aplicación web moderna que consume datos.</p>',
'<button id="cargar">Cargar chiste</button>\n<p id="chiste"></p>',
'async function cargarChiste() {\n  document.getElementById("chiste").innerText = "Cargando...";\n  try {\n    const respuesta = await fetch("https://official-joke-api.appspot.com/random_joke");\n    const datos = await respuesta.json();\n    document.getElementById("chiste").innerText = datos.setup + " — " + datos.punchline;\n  } catch (error) {\n    document.getElementById("chiste").innerText = "No se pudo cargar (revisa tu conexión).";\n  }\n}\n\ndocument.getElementById("cargar").addEventListener("click", cargarChiste);', 3, 9),

(@mod_js5, 'js-manejo-errores', 'Manejo de errores con try/catch', '<p>Cuando el código puede fallar (una API caída, datos inválidos), usamos <code>try/catch</code> para "atrapar" el error y evitar que la aplicación se rompa por completo, mostrando un mensaje amigable en su lugar.</p>',
'<button id="dividir">Dividir</button>\n<p id="resultado"></p>',
'function dividir(a, b) {\n  if (b === 0) {\n    throw new Error("No se puede dividir entre 0");\n  }\n  return a / b;\n}\n\ndocument.getElementById("dividir").addEventListener("click", () => {\n  try {\n    const resultado = dividir(10, 0);\n    document.getElementById("resultado").innerText = "Resultado: " + resultado;\n  } catch (error) {\n    document.getElementById("resultado").innerText = "Error: " + error.message;\n  }\n});', 4, 7),

(@mod_js5, 'js-proyecto-final', 'Proyecto: lista de tareas con memoria', '<p>Construye una lista de tareas que use <code>localStorage</code> para recordar las tareas aunque recargues la página. Combina DOM, eventos, arrays y JSON: ¡todo lo que has aprendido en este curso!</p>',
'<input type="text" id="tarea" placeholder="Nueva tarea">\n<button id="agregar">Agregar</button>\n<ul id="lista"></ul>',
'let tareas = JSON.parse(localStorage.getItem("tareas") || "[]");\n\nfunction render() {\n  const lista = document.getElementById("lista");\n  lista.innerHTML = "";\n  tareas.forEach((t, i) => {\n    const li = document.createElement("li");\n    li.textContent = t;\n    lista.appendChild(li);\n  });\n  localStorage.setItem("tareas", JSON.stringify(tareas));\n}\n\ndocument.getElementById("agregar").addEventListener("click", () => {\n  const input = document.getElementById("tarea");\n  if (input.value.trim() === "") return;\n  tareas.push(input.value.trim());\n  input.value = "";\n  render();\n});\n\nrender();', 5, 12);
