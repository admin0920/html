-- ============================================================
-- DIQUE PROGRAMANDO - Esquema de base de datos
-- Compatible con MySQL 5.x / MariaDB (InfinityFree)
-- Importar este archivo completo desde phpMyAdmin (InfinityFree -> vPanel -> MySQL Databases -> phpMyAdmin)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  avatar VARCHAR(255) DEFAULT NULL,
  puntos INT NOT NULL DEFAULT 0,
  racha_dias INT NOT NULL DEFAULT 0,
  ultima_actividad DATE DEFAULT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Cursos (HTML, CSS, JS, etc.)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cursos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT,
  icono VARCHAR(20) DEFAULT '📘',
  color VARCHAR(20) DEFAULT '#6366f1',
  nivel ENUM('basico','intermedio','avanzado','completo') NOT NULL DEFAULT 'completo',
  orden INT NOT NULL DEFAULT 0,
  publicado TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Módulos (agrupan lecciones dentro de un curso)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS modulos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  curso_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_curso (curso_id),
  CONSTRAINT fk_modulos_curso FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Lecciones
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS lecciones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  modulo_id INT UNSIGNED NOT NULL,
  slug VARCHAR(150) NOT NULL,
  titulo VARCHAR(180) NOT NULL,
  contenido LONGTEXT NOT NULL,
  codigo_html LONGTEXT DEFAULT NULL,
  codigo_css LONGTEXT DEFAULT NULL,
  codigo_js LONGTEXT DEFAULT NULL,
  orden INT NOT NULL DEFAULT 0,
  minutos_estimados INT NOT NULL DEFAULT 5,
  PRIMARY KEY (id),
  KEY idx_modulo (modulo_id),
  UNIQUE KEY uq_slug (slug),
  CONSTRAINT fk_lecciones_modulo FOREIGN KEY (modulo_id) REFERENCES modulos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Progreso de usuario por lección
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS progreso (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  leccion_id INT UNSIGNED NOT NULL,
  completado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuario_leccion (usuario_id, leccion_id),
  CONSTRAINT fk_progreso_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_progreso_leccion FOREIGN KEY (leccion_id) REFERENCES lecciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Quizzes (uno opcional por lección)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS quizzes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  leccion_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(180) NOT NULL DEFAULT 'Quiz',
  PRIMARY KEY (id),
  KEY idx_leccion (leccion_id),
  CONSTRAINT fk_quizzes_leccion FOREIGN KEY (leccion_id) REFERENCES lecciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_preguntas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  quiz_id INT UNSIGNED NOT NULL,
  pregunta TEXT NOT NULL,
  orden INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_quiz (quiz_id),
  CONSTRAINT fk_preguntas_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_opciones (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pregunta_id INT UNSIGNED NOT NULL,
  texto VARCHAR(255) NOT NULL,
  es_correcta TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_pregunta (pregunta_id),
  CONSTRAINT fk_opciones_pregunta FOREIGN KEY (pregunta_id) REFERENCES quiz_preguntas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_resultados (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  quiz_id INT UNSIGNED NOT NULL,
  puntaje INT NOT NULL,
  total INT NOT NULL,
  realizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_resultados_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_resultados_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Proyectos guardados del sandbox (editor en vivo)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS proyectos (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id INT UNSIGNED NOT NULL,
  titulo VARCHAR(150) NOT NULL DEFAULT 'Mi proyecto',
  html LONGTEXT,
  css LONGTEXT,
  js LONGTEXT,
  publico TINYINT(1) NOT NULL DEFAULT 0,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_usuario (usuario_id),
  CONSTRAINT fk_proyectos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- DATOS INICIALES (seed)
-- ============================================================

-- Usuario admin por defecto: email admin@diqueprogramando.com / password: Admin123!
-- (el hash corresponde a Admin123! generado con password_hash PASSWORD_DEFAULT)
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Administrador', 'admin@diqueprogramando.com', '$2y$12$MrhMDQqx./5I2nqeBV/S7exluUEvFzzRs6./T2rmA.n.xopkat7Ty', 'admin');

INSERT INTO cursos (slug, titulo, descripcion, icono, color, nivel, orden) VALUES
('html', 'HTML desde 0', 'Aprende a estructurar páginas web con HTML, desde las etiquetas básicas hasta formularios y HTML semántico avanzado.', '🧱', '#e34c26', 'completo', 1),
('css', 'CSS desde 0', 'Dale estilo a tus páginas: colores, layout con Flexbox y Grid, animaciones y diseño responsive.', '🎨', '#2965f1', 'completo', 2),
('js', 'JavaScript desde 0', 'Programa la interactividad de la web: variables, funciones, DOM, eventos, async/await y más.', '⚡', '#f0db4f', 'completo', 3);

-- ---------------- Módulos y lecciones: HTML ----------------
INSERT INTO modulos (curso_id, titulo, orden) VALUES
(1, 'Fundamentos de HTML', 1),
(1, 'Texto, listas y enlaces', 2),
(1, 'Imágenes, formularios y tablas', 3),
(1, 'HTML semántico y avanzado', 4);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(1, 'html-que-es', '¿Qué es HTML?',
'<p>HTML (<strong>HyperText Markup Language</strong>) es el lenguaje que usamos para crear la estructura de las páginas web. No es un lenguaje de programación: es un lenguaje de <em>marcado</em>, es decir, usa <strong>etiquetas</strong> para decirle al navegador qué es cada cosa (un título, un párrafo, una imagen, etc.).</p><p>Toda página HTML sigue este esqueleto básico:</p>',
'<!DOCTYPE html>\n<html lang="es">\n<head>\n  <meta charset="UTF-8">\n  <title>Mi primera página</title>\n</head>\n<body>\n  <h1>¡Hola, mundo!</h1>\n  <p>Esta es mi primera página web.</p>\n</body>\n</html>', 1, 6),

(1, 'html-etiquetas', 'Etiquetas y estructura', '<p>Las etiquetas (o <em>tags</em>) casi siempre van en pares: una de apertura <code>&lt;p&gt;</code> y una de cierre <code>&lt;/p&gt;</code>. Todo lo que va entre ellas es el <strong>contenido</strong> del elemento.</p><ul><li><code>&lt;html&gt;</code>: elemento raíz del documento</li><li><code>&lt;head&gt;</code>: metadatos, título, enlaces a CSS</li><li><code>&lt;body&gt;</code>: contenido visible de la página</li><li><code>&lt;h1&gt;</code> a <code>&lt;h6&gt;</code>: títulos, de mayor a menor importancia</li><li><code>&lt;p&gt;</code>: párrafo de texto</li></ul>',
'<h1>Título principal</h1>\n<h2>Subtítulo</h2>\n<p>Un párrafo de ejemplo con <b>texto en negrita</b> y <i>texto en cursiva</i>.</p>', 2, 7),

(2, 'html-listas-enlaces', 'Listas y enlaces', '<p>Las listas permiten organizar información. Hay dos tipos principales:</p><ul><li><code>&lt;ul&gt;</code>: lista desordenada (con viñetas)</li><li><code>&lt;ol&gt;</code>: lista ordenada (numerada)</li></ul><p>Cada elemento de la lista va dentro de <code>&lt;li&gt;</code>.</p><p>Para crear enlaces usamos <code>&lt;a href="..."&gt;</code>. El atributo <code>href</code> indica hacia dónde apunta el enlace.</p>',
'<h2>Mis lenguajes favoritos</h2>\n<ul>\n  <li>HTML</li>\n  <li>CSS</li>\n  <li>JavaScript</li>\n</ul>\n\n<h2>Pasos para aprender</h2>\n<ol>\n  <li>Aprender HTML</li>\n  <li>Aprender CSS</li>\n  <li>Aprender JavaScript</li>\n</ol>\n\n<p>Visita <a href="https://www.diqueprogramando.com" target="_blank">Dique Programando</a> para más contenido.</p>', 1, 6),

(3, 'html-imagenes', 'Imágenes y multimedia', '<p>Para insertar una imagen usamos la etiqueta <code>&lt;img&gt;</code>, que no tiene cierre y necesita el atributo <code>src</code> (la ruta de la imagen) y <code>alt</code> (texto alternativo, importante para accesibilidad y SEO).</p>',
'<img src="https://placekitten.com/300/200" alt="Un gatito de ejemplo" width="300">\n\n<figure>\n  <img src="https://placekitten.com/250/180" alt="Otro gatito">\n  <figcaption>Un gatito relajado</figcaption>\n</figure>', 1, 5),

(3, 'html-formularios', 'Formularios', '<p>Los formularios permiten recolectar datos del usuario. Se crean con <code>&lt;form&gt;</code> y dentro usamos <code>&lt;input&gt;</code>, <code>&lt;textarea&gt;</code>, <code>&lt;select&gt;</code> y <code>&lt;button&gt;</code>.</p><p>El atributo <code>type</code> de <code>&lt;input&gt;</code> define qué tipo de dato se pide: <code>text</code>, <code>email</code>, <code>password</code>, <code>number</code>, <code>checkbox</code>, <code>radio</code>, etc.</p>',
'<form>\n  <label for="nombre">Nombre:</label>\n  <input type="text" id="nombre" name="nombre" placeholder="Tu nombre">\n\n  <label for="correo">Correo:</label>\n  <input type="email" id="correo" name="correo" required>\n\n  <label for="mensaje">Mensaje:</label>\n  <textarea id="mensaje" name="mensaje"></textarea>\n\n  <button type="submit">Enviar</button>\n</form>', 2, 8),

(3, 'html-tablas', 'Tablas', '<p>Las tablas se usan para mostrar datos tabulares (no para maquetar). Se construyen con <code>&lt;table&gt;</code>, filas <code>&lt;tr&gt;</code>, encabezados <code>&lt;th&gt;</code> y celdas <code>&lt;td&gt;</code>.</p>',
'<table border="1">\n  <thead>\n    <tr>\n      <th>Lenguaje</th>\n      <th>Nivel</th>\n    </tr>\n  </thead>\n  <tbody>\n    <tr>\n      <td>HTML</td>\n      <td>Básico</td>\n    </tr>\n    <tr>\n      <td>JavaScript</td>\n      <td>Avanzado</td>\n    </tr>\n  </tbody>\n</table>', 3, 6),

(4, 'html-semantico', 'HTML semántico', '<p>El HTML semántico usa etiquetas que describen el <strong>significado</strong> del contenido, no solo su apariencia. Esto mejora la accesibilidad y el SEO.</p><ul><li><code>&lt;header&gt;</code>: cabecera de la página o sección</li><li><code>&lt;nav&gt;</code>: menú de navegación</li><li><code>&lt;main&gt;</code>: contenido principal</li><li><code>&lt;section&gt;</code>: sección temática</li><li><code>&lt;article&gt;</code>: contenido independiente (un post, una noticia)</li><li><code>&lt;aside&gt;</code>: contenido secundario</li><li><code>&lt;footer&gt;</code>: pie de página</li></ul>',
'<header>\n  <h1>Mi Blog</h1>\n  <nav>\n    <a href="#">Inicio</a>\n    <a href="#">Artículos</a>\n  </nav>\n</header>\n<main>\n  <article>\n    <h2>Mi primer artículo</h2>\n    <p>Contenido del artículo...</p>\n  </article>\n</main>\n<footer>\n  <p>&copy; 2026 Dique Programando</p>\n</footer>', 1, 8),

(4, 'html-atributos-avanzados', 'Atributos globales y accesibilidad', '<p>Existen atributos que se pueden usar en casi cualquier etiqueta: <code>id</code>, <code>class</code>, <code>style</code>, <code>title</code>, <code>data-*</code> (para guardar datos personalizados) y los atributos <code>aria-*</code> para accesibilidad.</p>',
'<button data-accion="guardar" aria-label="Guardar cambios" title="Guardar">💾</button>\n<div id="tarjeta" class="tarjeta destacada" data-usuario="123">\n  Contenido con id y varias clases\n</div>', 2, 7);

-- ---------------- Módulos y lecciones: CSS ----------------
INSERT INTO modulos (curso_id, titulo, orden) VALUES
(2, 'Fundamentos de CSS', 1),
(2, 'Caja, colores y tipografía', 2),
(2, 'Flexbox y Grid', 3),
(2, 'Responsive y animaciones', 4);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(5, 'css-que-es', '¿Qué es CSS?', '<p>CSS (<strong>Cascading Style Sheets</strong>) sirve para darle estilo a nuestro HTML: colores, tamaños, espaciados, posiciones, etc. Se puede aplicar de tres formas: en línea (<code>style</code>), interna (<code>&lt;style&gt;</code> en el head) o externa (archivo <code>.css</code> enlazado con <code>&lt;link&gt;</code>).</p>',
'<h1 class="titulo">Hola CSS</h1>\n<p>Este texto tendrá estilo.</p>',
'.titulo {\n  color: #6366f1;\n  text-align: center;\n}\n\np {\n  font-size: 18px;\n  color: #333;\n}', 1, 6),

(5, 'css-selectores', 'Selectores CSS', '<p>Los selectores indican a qué elementos se aplica un estilo: por etiqueta (<code>p</code>), por clase (<code>.mi-clase</code>), por id (<code>#mi-id</code>), o combinados. También existen selectores de estado como <code>:hover</code> y <code>:focus</code>.</p>',
'<button class="btn">Pasa el mouse</button>',
'.btn {\n  background: #6366f1;\n  color: white;\n  padding: 10px 20px;\n  border: none;\n  border-radius: 8px;\n  cursor: pointer;\n}\n\n.btn:hover {\n  background: #4f46e5;\n}', 2, 6),

(6, 'css-box-model', 'El modelo de caja (Box Model)', '<p>Todo elemento HTML es una caja rectangular compuesta por: <strong>content</strong> (contenido), <strong>padding</strong> (espacio interno), <strong>border</strong> (borde) y <strong>margin</strong> (espacio externo). Entender esto es clave para maquetar bien.</p>',
'<div class="caja">Contenido</div>',
'.caja {\n  width: 200px;\n  padding: 20px;\n  border: 4px solid #6366f1;\n  margin: 30px;\n  background: #eef2ff;\n  box-sizing: border-box;\n}', 1, 8),

(6, 'css-colores-tipografia', 'Colores y tipografía', '<p>Los colores se pueden definir con nombres (<code>red</code>), hexadecimal (<code>#ff0000</code>), <code>rgb()</code> o <code>hsl()</code>. Para tipografía usamos <code>font-family</code>, <code>font-size</code>, <code>font-weight</code> y <code>line-height</code>.</p>',
'<h1>Título con estilo</h1>\n<p>Texto de párrafo legible y bien espaciado.</p>',
'body {\n  font-family: "Segoe UI", sans-serif;\n}\n\nh1 {\n  color: hsl(243, 75%, 59%);\n  font-weight: 800;\n}\n\np {\n  color: rgb(60, 60, 60);\n  line-height: 1.6;\n}', 2, 7),

(7, 'css-flexbox', 'Flexbox', '<p>Flexbox es un sistema de layout unidimensional muy usado para alinear elementos en fila o columna. Se activa con <code>display: flex</code> en el contenedor padre.</p><p>Propiedades clave: <code>justify-content</code> (eje principal), <code>align-items</code> (eje cruzado), <code>gap</code> (espacio entre elementos).</p>',
'<div class="contenedor">\n  <div class="item">1</div>\n  <div class="item">2</div>\n  <div class="item">3</div>\n</div>',
'.contenedor {\n  display: flex;\n  justify-content: space-between;\n  align-items: center;\n  gap: 10px;\n  background: #f3f4f6;\n  padding: 20px;\n}\n\n.item {\n  background: #6366f1;\n  color: white;\n  padding: 20px;\n  border-radius: 8px;\n}', 1, 10),

(7, 'css-grid', 'CSS Grid', '<p>Grid es un sistema de layout bidimensional (filas y columnas), ideal para maquetar páginas completas. Se activa con <code>display: grid</code> y se definen columnas con <code>grid-template-columns</code>.</p>',
'<div class="grid">\n  <div class="celda">A</div>\n  <div class="celda">B</div>\n  <div class="celda">C</div>\n  <div class="celda">D</div>\n</div>',
'.grid {\n  display: grid;\n  grid-template-columns: repeat(2, 1fr);\n  gap: 12px;\n}\n\n.celda {\n  background: #6366f1;\n  color: white;\n  padding: 30px;\n  text-align: center;\n  border-radius: 8px;\n}', 2, 10),

(8, 'css-responsive', 'Diseño responsive con Media Queries', '<p>El diseño responsive adapta la página a distintos tamaños de pantalla usando <strong>media queries</strong>. Así podemos cambiar estilos según el ancho del dispositivo.</p>',
'<div class="tarjeta">Soy responsive</div>',
'.tarjeta {\n  background: #6366f1;\n  color: white;\n  padding: 40px;\n  font-size: 20px;\n}\n\n@media (max-width: 600px) {\n  .tarjeta {\n    font-size: 14px;\n    padding: 15px;\n  }\n}', 1, 9),

(8, 'css-animaciones', 'Transiciones y animaciones', '<p>Con <code>transition</code> podemos animar cambios suaves entre estados (como <code>:hover</code>). Con <code>@keyframes</code> creamos animaciones más complejas usando <code>animation</code>.</p>',
'<div class="box">Hover / Animación</div>',
'.box {\n  width: 150px;\n  padding: 20px;\n  background: #6366f1;\n  color: white;\n  transition: transform 0.3s ease, background 0.3s ease;\n  animation: aparecer 1s ease;\n}\n\n.box:hover {\n  transform: scale(1.1);\n  background: #4338ca;\n}\n\n@keyframes aparecer {\n  from { opacity: 0; transform: translateY(20px); }\n  to { opacity: 1; transform: translateY(0); }\n}', 2, 8);

-- ---------------- Módulos y lecciones: JS ----------------
INSERT INTO modulos (curso_id, titulo, orden) VALUES
(3, 'Fundamentos de JavaScript', 1),
(3, 'Funciones y estructuras de control', 2),
(3, 'El DOM y eventos', 3),
(3, 'Asincronía y JavaScript avanzado', 4);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(9, 'js-que-es', '¿Qué es JavaScript?', '<p>JavaScript es el lenguaje de programación que le da <strong>interactividad</strong> a las páginas web: puede reaccionar a clics, validar formularios, modificar el contenido en tiempo real, hacer peticiones a servidores y mucho más.</p><p>Se puede incluir con <code>&lt;script&gt;</code> dentro del HTML o en un archivo externo <code>.js</code>.</p>',
'<button id="saludar">Saludar</button>',
'document.getElementById("saludar").addEventListener("click", function () {\n  alert("¡Hola desde JavaScript!");\n});', 1, 6),

(9, 'js-variables-tipos', 'Variables y tipos de datos', '<p>Las variables se declaran con <code>let</code> (valor que cambia), <code>const</code> (valor constante) o <code>var</code> (forma antigua, evitar). Los tipos básicos son: <code>string</code>, <code>number</code>, <code>boolean</code>, <code>array</code>, <code>object</code>, <code>null</code> y <code>undefined</code>.</p>',
'<div id="salida"></div>',
'let nombre = "Ana";\nconst edad = 25;\nlet esEstudiante = true;\nlet lenguajes = ["HTML", "CSS", "JS"];\nlet persona = { nombre: nombre, edad: edad };\n\ndocument.getElementById("salida").innerText =\n  `${nombre} tiene ${edad} años y estudia: ${esEstudiante}`;\nconsole.log(lenguajes, persona);', 1, 8),

(10, 'js-funciones', 'Funciones', '<p>Las funciones agrupan código reutilizable. Se pueden declarar de varias formas: función tradicional, función anónima y <strong>arrow function</strong> (función flecha), esta última muy usada en JS moderno.</p>',
'<div id="resultado"></div>',
'function sumar(a, b) {\n  return a + b;\n}\n\nconst multiplicar = (a, b) => a * b;\n\nconst resultado = sumar(5, 3) + multiplicar(2, 4);\ndocument.getElementById("resultado").innerText = "Resultado: " + resultado;', 1, 8),

(10, 'js-condicionales-bucles', 'Condicionales y bucles', '<p>Los condicionales (<code>if</code>, <code>else if</code>, <code>else</code>, <code>switch</code>) permiten tomar decisiones. Los bucles (<code>for</code>, <code>while</code>, <code>for...of</code>) permiten repetir código.</p>',
'<div id="lista"></div>',
'const numeros = [1, 2, 3, 4, 5, 6];\nlet salida = "";\n\nfor (const n of numeros) {\n  if (n % 2 === 0) {\n    salida += n + " es par<br>";\n  } else {\n    salida += n + " es impar<br>";\n  }\n}\n\ndocument.getElementById("lista").innerHTML = salida;', 2, 9),

(11, 'js-dom', 'Manipulación del DOM', '<p>El DOM (<em>Document Object Model</em>) es la representación en árbol del HTML que JavaScript puede leer y modificar. Usamos <code>document.querySelector</code>, <code>getElementById</code>, <code>createElement</code>, y propiedades como <code>innerText</code> e <code>innerHTML</code>.</p>',
'<h2 id="titulo">Título original</h2>\n<button id="cambiar">Cambiar título</button>',
'const titulo = document.getElementById("titulo");\nconst boton = document.getElementById("cambiar");\n\nboton.addEventListener("click", () => {\n  titulo.innerText = "¡Título cambiado con JS!";\n  titulo.style.color = "#6366f1";\n});', 1, 9),

(11, 'js-eventos', 'Eventos', '<p>Los eventos permiten reaccionar a acciones del usuario: <code>click</code>, <code>input</code>, <code>submit</code>, <code>keydown</code>, <code>mouseover</code>, etc. Se escuchan con <code>addEventListener</code>.</p>',
'<input type="text" id="campo" placeholder="Escribe algo...">\n<p id="eco"></p>',
'const campo = document.getElementById("campo");\nconst eco = document.getElementById("eco");\n\ncampo.addEventListener("input", (e) => {\n  eco.innerText = "Escribiste: " + e.target.value;\n});', 2, 7),

(12, 'js-arrays-objetos', 'Arrays y objetos avanzados', '<p>Los arrays tienen métodos muy potentes: <code>map</code>, <code>filter</code>, <code>reduce</code>, <code>forEach</code>, <code>find</code>. Son fundamentales en JavaScript moderno para transformar datos sin bucles manuales.</p>',
'<ul id="lista"></ul>',
'const productos = [\n  { nombre: "Teclado", precio: 20 },\n  { nombre: "Mouse", precio: 10 },\n  { nombre: "Monitor", precio: 150 }\n];\n\nconst caros = productos.filter(p => p.precio > 15);\nconst nombres = caros.map(p => `${p.nombre} - $${p.precio}`);\n\nconst lista = document.getElementById("lista");\nnombres.forEach(n => {\n  const li = document.createElement("li");\n  li.innerText = n;\n  lista.appendChild(li);\n});', 1, 10),

(12, 'js-async', 'Asincronía: Promesas y async/await', '<p>JavaScript es de un solo hilo, pero maneja tareas que tardan (como peticiones a un servidor) mediante <strong>Promesas</strong>. La sintaxis moderna <code>async/await</code> hace este código más legible que usar <code>.then()</code> encadenados.</p>',
'<button id="cargar">Cargar datos</button>\n<p id="datos"></p>',
'async function cargarDatos() {\n  document.getElementById("datos").innerText = "Cargando...";\n  await new Promise(resolve => setTimeout(resolve, 1000));\n  document.getElementById("datos").innerText = "¡Datos cargados con éxito!";\n}\n\ndocument.getElementById("cargar").addEventListener("click", cargarDatos);', 2, 10);

-- ---------------- Quiz de ejemplo ----------------
INSERT INTO quizzes (leccion_id, titulo) VALUES (1, 'Quiz: ¿Qué es HTML?');
INSERT INTO quiz_preguntas (quiz_id, pregunta, orden) VALUES
(1, '¿Qué significa HTML?', 1),
(1, '¿HTML es un lenguaje de programación?', 2);

INSERT INTO quiz_opciones (pregunta_id, texto, es_correcta) VALUES
(1, 'HyperText Markup Language', 1),
(1, 'High Tech Modern Language', 0),
(1, 'Home Tool Markup Language', 0),
(2, 'Sí, es un lenguaje de programación', 0),
(2, 'No, es un lenguaje de marcado', 1);
