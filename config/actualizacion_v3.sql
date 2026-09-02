-- ============================================================
-- DIQUE PROGRAMANDO - Actualización v3
-- Ejecuta esto SOLO si ya tenías la plataforma instalada con
-- una versión anterior (v1 o v2). Si instalas desde cero, solo
-- necesitas importar config/database.sql (ya incluye todo esto).
--
-- Agrega: login con Google (columna google_id), más de 50
-- lecciones por curso (antes 37, ahora 153), y 12 retos nuevos.
-- ============================================================

SET NAMES utf8mb4;

-- Login con Google: el password ahora puede ser NULL (cuentas
-- creadas solo con Google no tienen contraseña).
ALTER TABLE usuarios
  MODIFY password_hash VARCHAR(255) DEFAULT NULL,
  ADD COLUMN google_id VARCHAR(64) DEFAULT NULL AFTER password_hash,
  ADD UNIQUE KEY uq_google_id (google_id);

-- ============================================================
-- Lecciones adicionales de HTML (curso slug 'html')
-- 39 lecciones nuevas repartidas en 8 módulos nuevos
-- ============================================================

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Texto y formato avanzado', 6 FROM cursos c WHERE c.slug = 'html';
SET @m6 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Texto y formato avanzado' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m6, 'html-estructura-completa', 'Estructura completa de un documento HTML', '<p>Todo documento HTML válido empieza con <code>&lt;!DOCTYPE html&gt;</code>, que le dice al navegador que use el estándar HTML5. Luego va la etiqueta raíz <code>&lt;html&gt;</code>, que contiene <code>&lt;head&gt;</code> (metadatos invisibles) y <code>&lt;body&gt;</code> (lo que se ve).</p><p>Dentro de <code>&lt;head&gt;</code> siempre deberías incluir el <code>charset</code>, el <code>viewport</code> y un <code>&lt;title&gt;</code> descriptivo.</p>',
'<!DOCTYPE html>\n<html lang="es">\n<head>\n  <meta charset="UTF-8">\n  <meta name="viewport" content="width=device-width, initial-scale=1.0">\n  <title>Mi sitio</title>\n</head>\n<body>\n  <h1>Contenido visible aquí</h1>\n</body>\n</html>', 1, 6),

(@m6, 'html-comentarios', 'Comentarios en HTML', '<p>Los comentarios se escriben con <code>&lt;!-- así --&gt;</code> y el navegador los ignora por completo: sirven para dejarte notas a ti mismo o a tu equipo sin que se vean en la página.</p><p>Son muy útiles para marcar el cierre de secciones grandes o explicar por qué cierto código está ahí.</p>',
'<!-- Inicio del encabezado -->\n<header>\n  <h1>Mi sitio</h1>\n</header>\n<!-- Fin del encabezado -->\n\n<!-- TODO: agregar el menú de navegación aquí -->', 2, 4),

(@m6, 'html-encabezados-jerarquia', 'Encabezados: jerarquía y buenas prácticas', '<p>Los encabezados <code>&lt;h1&gt;</code> a <code>&lt;h6&gt;</code> no son solo para hacer texto grande: definen la <strong>jerarquía</strong> del contenido, algo que usan los lectores de pantalla y los buscadores para entender tu página.</p><p>Regla de oro: usa un solo <code>&lt;h1&gt;</code> por página, y no te saltes niveles (de h2 no pases directo a h4).</p>',
'<h1>Título del artículo</h1>\n<h2>Introducción</h2>\n<h2>Desarrollo</h2>\n<h3>Primer punto</h3>\n<h3>Segundo punto</h3>\n<h2>Conclusión</h2>', 3, 5),

(@m6, 'html-br-hr', 'Saltos de línea y separadores (br, hr)', '<p><code>&lt;br&gt;</code> inserta un salto de línea dentro de un mismo párrafo (úsalo con moderación, casi siempre CSS es mejor opción para espaciar). <code>&lt;hr&gt;</code> dibuja una línea horizontal que representa un cambio temático.</p>',
'<p>Calle Falsa 123<br>Springfield<br>Ciudad</p>\n<hr>\n<p>Sección nueva después del separador.</p>', 4, 4),

(@m6, 'html-texto-especial', 'Texto especial: negrita, cursiva, resaltado y tachado', '<p>Además de <code>&lt;strong&gt;</code> (importancia) y <code>&lt;em&gt;</code> (énfasis), HTML tiene <code>&lt;mark&gt;</code> para resaltar texto, <code>&lt;del&gt;</code> para texto eliminado, <code>&lt;ins&gt;</code> para texto insertado y <code>&lt;small&gt;</code> para texto secundario.</p>',
'<p>Precio: <del>$50.000</del> <ins>$35.000</ins></p>\n<p>Busca la palabra <mark>importante</mark> en el texto.</p>\n<p><small>Términos y condiciones aplican.</small></p>', 5, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Listas y enlaces avanzados', 7 FROM cursos c WHERE c.slug = 'html';
SET @m7 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Listas y enlaces avanzados' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m7, 'html-listas-definicion', 'Listas de definición (dl, dt, dd)', '<p>Para pares término-descripción (como un glosario) existe un tercer tipo de lista: <code>&lt;dl&gt;</code> (lista de definiciones), con <code>&lt;dt&gt;</code> para el término y <code>&lt;dd&gt;</code> para su descripción.</p>',
'<dl>\n  <dt>HTML</dt>\n  <dd>Lenguaje de marcado para estructurar páginas web.</dd>\n  <dt>CSS</dt>\n  <dd>Lenguaje para dar estilo a las páginas web.</dd>\n</dl>', 1, 5),

(@m7, 'html-listas-anidadas', 'Listas anidadas', '<p>Una lista puede contener otra lista completa dentro de uno de sus <code>&lt;li&gt;</code>, lo que sirve para representar submenús o estructuras jerárquicas como un índice de contenidos.</p>',
'<ul>\n  <li>Frontend\n    <ul>\n      <li>HTML</li>\n      <li>CSS</li>\n      <li>JavaScript</li>\n    </ul>\n  </li>\n  <li>Backend\n    <ul>\n      <li>PHP</li>\n      <li>MySQL</li>\n    </ul>\n  </li>\n</ul>', 2, 5),

(@m7, 'html-enlaces-target-download', 'Enlaces: atributos target y download', '<p>El atributo <code>target="_blank"</code> abre el enlace en una pestaña nueva (siempre combínalo con <code>rel="noopener noreferrer"</code> por seguridad). El atributo <code>download</code> hace que el navegador descargue el archivo en vez de navegar a él.</p>',
'<a href="https://ejemplo.com" target="_blank" rel="noopener noreferrer">Abrir en pestaña nueva</a>\n<a href="manual.pdf" download>Descargar manual (PDF)</a>', 3, 5),

(@m7, 'html-enlaces-anclas', 'Enlaces internos con anclas (#id)', '<p>Puedes crear enlaces que salten a una parte específica de la misma página usando <code>#id</code>, donde <code>id</code> es el atributo <code>id</code> del elemento destino. Es la base de los menús de "ir a sección".</p>',
'<nav>\n  <a href="#seccion1">Ir a la Sección 1</a>\n  <a href="#seccion2">Ir a la Sección 2</a>\n</nav>\n\n<h2 id="seccion1">Sección 1</h2>\n<p>Contenido...</p>\n<h2 id="seccion2">Sección 2</h2>\n<p>Contenido...</p>', 4, 5),

(@m7, 'html-enlaces-mailto-tel', 'Enlaces mailto y tel', '<p>Con <code>href="mailto:correo@ejemplo.com"</code> el enlace abre el cliente de correo del usuario listo para escribir. Con <code>href="tel:+573001234567"</code> en móviles se abre la app de teléfono lista para llamar.</p>',
'<a href="mailto:contacto@diqueprogramando.com">Escríbenos</a>\n<a href="tel:+573001234567">Llámanos: 300 123 4567</a>', 5, 4);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Imágenes y gráficos', 8 FROM cursos c WHERE c.slug = 'html';
SET @m8 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Imágenes y gráficos' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m8, 'html-imagenes-responsivas', 'Imágenes responsivas con srcset', '<p>El atributo <code>srcset</code> permite ofrecer varias versiones de una misma imagen en distintos tamaños, y el navegador elige automáticamente la más adecuada según la pantalla del usuario, ahorrando datos en móviles.</p>',
'<img\n  src="foto-800.jpg"\n  srcset="foto-400.jpg 400w, foto-800.jpg 800w, foto-1200.jpg 1200w"\n  sizes="(max-width: 600px) 400px, 800px"\n  alt="Paisaje de montaña">', 1, 7),

(@m8, 'html-figure-figcaption', 'Figure y figcaption', '<p><code>&lt;figure&gt;</code> agrupa contenido visual (imagen, gráfico, código) junto con su leyenda usando <code>&lt;figcaption&gt;</code>. Es más semántico que un simple <code>&lt;div&gt;</code> con una imagen y un párrafo.</p>',
'<figure>\n  <img src="https://placekitten.com/300/200" alt="Gato durmiendo en un sofá">\n  <figcaption>Un gato disfrutando de su siesta favorita.</figcaption>\n</figure>', 2, 5),

(@m8, 'html-svg-intro', 'Introducción a SVG', '<p>SVG (Scalable Vector Graphics) es un formato de imagen vectorial que se puede escribir directamente en HTML. A diferencia de un JPG o PNG, se puede escalar a cualquier tamaño sin perder calidad y se puede animar con CSS o JS.</p>',
'<svg width="120" height="120" viewBox="0 0 100 100">\n  <circle cx="50" cy="50" r="40" fill="#6366f1" />\n  <text x="50" y="55" text-anchor="middle" fill="white" font-size="14">Hola</text>\n</svg>', 3, 6),

(@m8, 'html-canvas-intro', 'Introducción a Canvas', '<p><code>&lt;canvas&gt;</code> crea un lienzo en blanco donde se puede dibujar dinámicamente usando JavaScript: gráficos, animaciones, incluso juegos. A diferencia de SVG, el canvas es una imagen "de píxeles" que se dibuja con código.</p>',
'<canvas id="miLienzo" width="200" height="100" style="border:1px solid #ccc;"></canvas>\n<script>\n  const ctx = document.getElementById("miLienzo").getContext("2d");\n  ctx.fillStyle = "#6366f1";\n  ctx.fillRect(20, 20, 150, 60);\n</script>', 4, 6),

(@m8, 'html-favicon', 'Favicon e iconos del sitio', '<p>El favicon es el pequeño ícono que aparece en la pestaña del navegador. Se define con una etiqueta <code>&lt;link rel="icon"&gt;</code> dentro del <code>&lt;head&gt;</code>, normalmente apuntando a un archivo <code>.ico</code> o <code>.png</code>.</p>',
'<head>\n  <link rel="icon" type="image/png" href="/favicon.png">\n  <title>Mi sitio</title>\n</head>', 5, 3);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Tablas y datos', 9 FROM cursos c WHERE c.slug = 'html';
SET @m9 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Tablas y datos' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m9, 'html-tablas-colspan-rowspan', 'Tablas: colspan y rowspan', '<p><code>colspan</code> hace que una celda ocupe varias columnas, y <code>rowspan</code> hace que ocupe varias filas. Son claves para tablas con encabezados combinados o celdas que agrupan varias filas.</p>',
'<table border="1">\n  <tr>\n    <th colspan="2">Datos del estudiante</th>\n  </tr>\n  <tr>\n    <td rowspan="2">Nombre</td>\n    <td>Ana Torres</td>\n  </tr>\n  <tr>\n    <td>Edad: 22</td>\n  </tr>\n</table>', 1, 6),

(@m9, 'html-tablas-accesibles', 'Tablas accesibles y buenas prácticas', '<p>Una tabla accesible usa <code>&lt;th scope="col"&gt;</code> o <code>scope="row"</code> para indicar si un encabezado corresponde a una columna o una fila, y un <code>&lt;caption&gt;</code> para describir de qué trata la tabla completa.</p>',
'<table border="1">\n  <caption>Ventas del primer trimestre</caption>\n  <tr>\n    <th scope="col">Mes</th>\n    <th scope="col">Ventas</th>\n  </tr>\n  <tr>\n    <th scope="row">Enero</th>\n    <td>$1.200</td>\n  </tr>\n</table>', 2, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Formularios avanzados', 10 FROM cursos c WHERE c.slug = 'html';
SET @m10 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Formularios avanzados' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m10, 'html-inputs-texto-profundidad', 'Inputs de texto en profundidad', '<p>Además de <code>placeholder</code>, los inputs de texto aceptan <code>maxlength</code> (límite de caracteres), <code>readonly</code> (visible pero no editable) y <code>autocomplete</code> (sugerencias del navegador).</p>',
'<input type="text" placeholder="Usuario" maxlength="20" autocomplete="username">\n<input type="text" value="No editable" readonly>', 1, 5),

(@m10, 'html-inputs-especiales', 'Inputs especiales: date, color, range, number', '<p>HTML5 trae inputs con interfaz nativa del navegador: <code>type="date"</code> muestra un calendario, <code>type="color"</code> un selector de color, <code>type="range"</code> un deslizador, y <code>type="number"</code> solo acepta números.</p>',
'<label>Fecha: <input type="date"></label><br>\n<label>Color favorito: <input type="color"></label><br>\n<label>Volumen: <input type="range" min="0" max="100"></label><br>\n<label>Edad: <input type="number" min="0" max="120"></label>', 2, 6),

(@m10, 'html-checkbox-radio', 'Checkbox y radio buttons', '<p><code>type="checkbox"</code> permite elegir una o varias opciones independientes; <code>type="radio"</code> con el mismo atributo <code>name</code> permite elegir solo una opción de un grupo. Usa <code>checked</code> para marcar una por defecto.</p>',
'<p>¿Qué lenguajes conoces?</p>\n<label><input type="checkbox" name="lenguajes" value="html"> HTML</label>\n<label><input type="checkbox" name="lenguajes" value="css"> CSS</label>\n\n<p>Nivel:</p>\n<label><input type="radio" name="nivel" value="principiante" checked> Principiante</label>\n<label><input type="radio" name="nivel" value="avanzado"> Avanzado</label>', 3, 6),

(@m10, 'html-select-datalist', 'Select, option y datalist', '<p><code>&lt;select&gt;</code> crea un menú desplegable con opciones fijas (<code>&lt;option&gt;</code>). <code>&lt;datalist&gt;</code> es distinto: da sugerencias mientras el usuario escribe libremente en un input normal.</p>',
'<label>País:\n  <select name="pais">\n    <option value="co">Colombia</option>\n    <option value="mx">México</option>\n    <option value="ar">Argentina</option>\n  </select>\n</label>\n\n<label>Navegador:\n  <input list="navegadores">\n  <datalist id="navegadores">\n    <option value="Chrome">\n    <option value="Firefox">\n    <option value="Safari">\n  </datalist>\n</label>', 4, 6),

(@m10, 'html-textarea', 'Textarea', '<p><code>&lt;textarea&gt;</code> es un campo de texto de varias líneas, ideal para comentarios o mensajes largos. Se controla su tamaño con los atributos <code>rows</code> y <code>cols</code>, o mejor aún, con CSS.</p>',
'<label for="mensaje">Tu mensaje:</label>\n<textarea id="mensaje" name="mensaje" rows="4" cols="40" placeholder="Escribe aquí..."></textarea>', 5, 4),

(@m10, 'html-validacion-nativa', 'Validación nativa de formularios (HTML5)', '<p>Sin escribir una sola línea de JavaScript, HTML5 valida formularios con atributos como <code>required</code>, <code>pattern</code> (expresión regular), <code>min</code>/<code>max</code>, y tipos como <code>email</code>. El navegador muestra el mensaje de error automáticamente.</p>',
'<form>\n  <input type="email" required placeholder="Correo obligatorio">\n  <input type="text" pattern="[A-Za-z]{3,}" title="Mínimo 3 letras" placeholder="Solo letras">\n  <input type="number" min="18" max="99" placeholder="Edad (18-99)">\n  <button>Enviar</button>\n</form>', 6, 6),

(@m10, 'html-fieldset-legend', 'Fieldset y legend', '<p><code>&lt;fieldset&gt;</code> agrupa visualmente y semánticamente varios campos relacionados de un formulario, y <code>&lt;legend&gt;</code> le pone un título a ese grupo. Mejora mucho la accesibilidad en formularios largos.</p>',
'<fieldset>\n  <legend>Datos de contacto</legend>\n  <label>Nombre: <input type="text"></label><br>\n  <label>Correo: <input type="email"></label>\n</fieldset>', 7, 4);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Estructura semántica avanzada', 11 FROM cursos c WHERE c.slug = 'html';
SET @m11 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Estructura semántica avanzada' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m11, 'html-div-vs-span', 'Div vs span: cuándo usar cada uno', '<p><code>&lt;div&gt;</code> es un contenedor de <strong>bloque</strong> genérico (ocupa toda la línea), y <code>&lt;span&gt;</code> es un contenedor <strong>en línea</strong> genérico (solo ocupa el espacio de su contenido). Úsalos solo cuando ninguna etiqueta semántica encaja mejor.</p>',
'<div class="tarjeta">\n  <p>Precio: <span class="destacado">$49.99</span></p>\n</div>', 1, 4),

(@m11, 'html-bloque-vs-linea', 'Elementos de bloque vs en línea', '<p>Los elementos de <strong>bloque</strong> (<code>div</code>, <code>p</code>, <code>h1</code>) empiezan en una nueva línea y ocupan todo el ancho disponible. Los elementos <strong>en línea</strong> (<code>span</code>, <code>a</code>, <code>strong</code>) solo ocupan el espacio de su contenido y fluyen junto al texto.</p>',
'<p>Este es un <strong>elemento en línea</strong> dentro de un párrafo.</p>\n<div>Este div es de bloque y ocupa toda la línea.</div>\n<div>Este otro div empieza en una línea nueva.</div>', 2, 5),

(@m11, 'html-header-footer-profundidad', 'Header y footer en profundidad', '<p><code>&lt;header&gt;</code> y <code>&lt;footer&gt;</code> no son exclusivos de toda la página: también pueden usarse dentro de un <code>&lt;article&gt;</code> o <code>&lt;section&gt;</code> para marcar la cabecera o el pie de esa sección específica.</p>',
'<article>\n  <header>\n    <h2>Título del artículo</h2>\n    <p>Por Ana Torres, 2 de enero</p>\n  </header>\n  <p>Contenido del artículo...</p>\n  <footer>\n    <p>Categoría: Tecnología</p>\n  </footer>\n</article>', 3, 5),

(@m11, 'html-nav-main', 'Nav y main', '<p><code>&lt;nav&gt;</code> agrupa los enlaces de navegación principal del sitio (no todos los enlaces necesitan estar dentro de un nav). <code>&lt;main&gt;</code> marca el contenido único y principal de la página, y solo debe haber uno por página.</p>',
'<nav>\n  <a href="/">Inicio</a>\n  <a href="/cursos">Cursos</a>\n  <a href="/contacto">Contacto</a>\n</nav>\n<main>\n  <h1>Bienvenido</h1>\n  <p>Este es el contenido principal de la página.</p>\n</main>', 4, 5),

(@m11, 'html-section-vs-article', 'Section vs article', '<p><code>&lt;article&gt;</code> es contenido que tiene sentido por sí solo, aunque lo saques de la página (un post de blog, una noticia). <code>&lt;section&gt;</code> agrupa contenido relacionado temáticamente pero que no es independiente por sí mismo.</p>',
'<section>\n  <h2>Últimos artículos</h2>\n  <article>\n    <h3>Cómo aprender HTML</h3>\n    <p>Resumen del artículo...</p>\n  </article>\n  <article>\n    <h3>Introducción a CSS Grid</h3>\n    <p>Resumen del artículo...</p>\n  </article>\n</section>', 5, 6),

(@m11, 'html-aside', 'Aside: contenido complementario', '<p><code>&lt;aside&gt;</code> representa contenido relacionado pero secundario al contenido principal: una barra lateral, publicidad, enlaces relacionados o una biografía del autor.</p>',
'<main>\n  <article>\n    <h2>Cómo aprender a programar</h2>\n    <p>Contenido del artículo...</p>\n  </article>\n  <aside>\n    <h3>Artículos relacionados</h3>\n    <ul>\n      <li><a href="#">Aprende CSS en 2026</a></li>\n      <li><a href="#">JavaScript para principiantes</a></li>\n    </ul>\n  </aside>\n</main>', 6, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'HTML avanzado y accesibilidad', 12 FROM cursos c WHERE c.slug = 'html';
SET @m12 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'HTML avanzado y accesibilidad' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m12, 'html-data-atributos', 'Atributos data-* personalizados', '<p>Los atributos <code>data-*</code> te dejan guardar información personalizada en cualquier elemento HTML, para que luego JavaScript la lea con <code>elemento.dataset</code>. Son la forma correcta de guardar datos extra sin inventar atributos no estándar.</p>',
'<button data-producto-id="42" data-precio="19.99">Agregar al carrito</button>\n\n<script>\n  const btn = document.querySelector("button");\n  console.log(btn.dataset.productoId, btn.dataset.precio);\n</script>', 1, 5),

(@m12, 'html-entidades', 'Entidades HTML', '<p>Algunos caracteres tienen significado especial en HTML (como <code>&lt;</code> o <code>&gt;</code>), así que para mostrarlos literalmente se usan <strong>entidades</strong>: <code>&amp;lt;</code>, <code>&amp;gt;</code>, <code>&amp;amp;</code>, <code>&amp;copy;</code>, <code>&amp;nbsp;</code> (espacio que no se rompe).</p>',
'<p>Para escribir una etiqueta usa &lt;p&gt; y para el símbolo & usa &amp;amp;</p>\n<p>&copy; 2026 Todos los derechos reservados</p>', 2, 4),

(@m12, 'html-lang-internacionalizacion', 'Atributo lang e internacionalización', '<p>El atributo <code>lang</code> en <code>&lt;html&gt;</code> le dice al navegador y a los lectores de pantalla en qué idioma está el contenido, mejorando la pronunciación y ayudando a los traductores automáticos.</p>',
'<html lang="es">\n  <body>\n    <p>Este párrafo está en español.</p>\n    <p lang="en">This paragraph is in English.</p>\n  </body>\n</html>', 3, 4),

(@m12, 'html-aria-avanzado', 'Roles ARIA y accesibilidad avanzada', '<p>Cuando el HTML semántico no alcanza (por ejemplo, componentes interactivos personalizados), los atributos <code>aria-*</code> y <code>role</code> describen el propósito de un elemento a las tecnologías de asistencia: <code>role="alert"</code>, <code>aria-expanded</code>, <code>aria-hidden</code>, etc.</p>',
'<button aria-expanded="false" aria-controls="menu">Abrir menú</button>\n<ul id="menu" role="menu" aria-hidden="true">\n  <li role="menuitem">Inicio</li>\n  <li role="menuitem">Contacto</li>\n</ul>\n<div role="alert">¡Cambios guardados correctamente!</div>', 4, 6),

(@m12, 'html-template', 'Elemento template', '<p><code>&lt;template&gt;</code> guarda fragmentos de HTML que el navegador no renderiza directamente, pero que JavaScript puede clonar y mostrar cuando lo necesite. Es útil para generar listas dinámicas sin repetir HTML a mano en el JS.</p>',
'<template id="tarjeta-usuario">\n  <div class="tarjeta">\n    <p class="nombre"></p>\n  </div>\n</template>\n\n<div id="contenedor"></div>\n\n<script>\n  const plantilla = document.getElementById("tarjeta-usuario");\n  const clon = plantilla.content.cloneNode(true);\n  clon.querySelector(".nombre").textContent = "Ana Torres";\n  document.getElementById("contenedor").appendChild(clon);\n</script>', 5, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Proyectos y buenas prácticas HTML', 13 FROM cursos c WHERE c.slug = 'html';
SET @m13 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'html' AND m.titulo = 'Proyectos y buenas prácticas HTML' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, orden, minutos_estimados) VALUES
(@m13, 'html-validacion-w3c', 'Validación W3C y buenas prácticas', '<p>El validador oficial del W3C (validator.w3.org) revisa que tu HTML no tenga errores de sintaxis: etiquetas sin cerrar, atributos mal escritos, anidación incorrecta. Validar tu código regularmente evita bugs difíciles de encontrar en CSS y JS.</p><p>Buenas prácticas: cierra siempre tus etiquetas, usa minúsculas, indenta tu código de forma consistente y usa comillas en los atributos.</p>',
'<!-- Bien -->\n<img src="foto.jpg" alt="Descripción">\n\n<!-- Evitar -->\n<IMG SRC=foto.jpg>', 1, 5),

(@m13, 'html-proyecto-curriculum', 'Proyecto: currículum en HTML', '<p>Construye tu propio currículum (CV) usando solo HTML semántico: un <code>header</code> con tu nombre y foto, secciones de "Experiencia", "Educación" y "Habilidades" usando listas, y un <code>footer</code> con tus datos de contacto.</p>',
'<header>\n  <h1>Tu Nombre</h1>\n  <p>Desarrollador/a Web Junior</p>\n</header>\n<main>\n  <section>\n    <h2>Experiencia</h2>\n    <article>\n      <h3>Puesto - Empresa</h3>\n      <p>2024 - Presente</p>\n    </article>\n  </section>\n  <section>\n    <h2>Habilidades</h2>\n    <ul>\n      <li>HTML</li>\n      <li>CSS</li>\n    </ul>\n  </section>\n</main>\n<footer>\n  <p>correo@ejemplo.com</p>\n</footer>', 2, 15),

(@m13, 'html-proyecto-recetas', 'Proyecto: página de recetas', '<p>Crea una página para una receta de cocina usando: una imagen del platillo con <code>figure</code>/<code>figcaption</code>, una lista ordenada para los pasos, una lista desordenada para los ingredientes, y una tabla con la información nutricional.</p>',
'<article>\n  <h1>Pasta al pesto</h1>\n  <figure>\n    <img src="https://placekitten.com/400/250" alt="Plato de pasta al pesto">\n    <figcaption>Pasta al pesto casera</figcaption>\n  </figure>\n  <h2>Ingredientes</h2>\n  <ul>\n    <li>200g de pasta</li>\n    <li>Salsa pesto</li>\n  </ul>\n  <h2>Pasos</h2>\n  <ol>\n    <li>Hervir la pasta</li>\n    <li>Mezclar con el pesto</li>\n  </ol>\n</article>', 3, 15),

(@m13, 'html-proyecto-perfil-social', 'Proyecto: perfil de red social estático', '<p>Construye la maqueta HTML de un perfil de red social: foto de portada, avatar, nombre y biografía, una lista de estadísticas (seguidores, publicaciones) y una cuadrícula de publicaciones usando <code>figure</code> para cada imagen.</p>',
'<header>\n  <img src="https://placekitten.com/600/200" alt="Portada">\n  <img src="https://placekitten.com/100/100" alt="Avatar">\n  <h1>@usuario</h1>\n  <p>Desarrollador/a apasionado por el código.</p>\n</header>\n<section aria-label="Estadísticas">\n  <span>120 publicaciones</span>\n  <span>3.4k seguidores</span>\n</section>\n<section aria-label="Publicaciones">\n  <figure><img src="https://placekitten.com/150/150" alt="Publicación 1"></figure>\n  <figure><img src="https://placekitten.com/151/150" alt="Publicación 2"></figure>\n</section>', 4, 15);

-- ============================================================
-- Lecciones adicionales de CSS (curso slug 'css')
-- 39 lecciones nuevas repartidas en 7 módulos nuevos
-- ============================================================

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Selectores y cascada avanzados', 10 FROM cursos c WHERE c.slug = 'css';
SET @c10 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Selectores y cascada avanzados' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c10, 'css-selectores-atributo', 'Selectores de atributo', '<p>Puedes seleccionar elementos según sus atributos: <code>[type="email"]</code> selecciona inputs de tipo email, <code>[href^="https"]</code> los enlaces que empiezan con https, <code>[class*="btn"]</code> los que contienen "btn" en su clase.</p>',
'<input type="text" placeholder="Texto">\n<input type="email" placeholder="Email">\n<a href="https://ejemplo.com">Externo</a>\n<a href="/interno">Interno</a>',
'input[type="email"] {\n  border-color: #10b981;\n}\n\na[href^="https"] {\n  color: green;\n}\n\na[href^="https"]::after {\n  content: " ↗";\n}', 1, 6),

(@c10, 'css-combinadores', 'Combinadores: descendiente, hijo directo, hermano', '<p>El espacio selecciona <strong>cualquier descendiente</strong> (<code>div p</code>), <code>&gt;</code> selecciona solo <strong>hijos directos</strong> (<code>div &gt; p</code>), <code>+</code> selecciona el <strong>hermano inmediato siguiente</strong>, y <code>~</code> todos los <strong>hermanos siguientes</strong>.</p>',
'<div class="caja">\n  <p>Hijo directo</p>\n  <span><p>Nieto, no hijo directo</p></span>\n</div>\n<h2>Título</h2>\n<p>Justo después del h2</p>\n<p>Otro párrafo</p>',
'.caja > p {\n  color: blue;\n}\n\nh2 + p {\n  font-weight: bold;\n}', 2, 6),

(@c10, 'css-especificidad', 'Especificidad CSS', '<p>Cuando varias reglas aplican al mismo elemento, gana la más <strong>específica</strong>. El orden de fuerza (de menor a mayor) es: etiquetas &lt; clases/atributos/pseudo-clases &lt; IDs &lt; <code>style</code> en línea &lt; <code>!important</code>.</p>',
'<p id="unico" class="texto">Hola</p>',
'p { color: black; }\n.texto { color: blue; }\n#unico { color: red; } /* Esta gana: el ID es más específico */', 3, 6),

(@c10, 'css-cascada-herencia', 'Cascada y herencia', '<p>CSS significa "Cascading Style Sheets": cuando hay conflicto, gana la regla más específica o la última declarada. Algunas propiedades como <code>color</code> y <code>font-family</code> se <strong>heredan</strong> a los hijos automáticamente; otras como <code>border</code> no.</p>',
'<div class="contenedor">\n  <p>Este párrafo hereda el color del contenedor.</p>\n</div>',
'.contenedor {\n  color: #6366f1;\n  font-family: sans-serif;\n}', 4, 5),

(@c10, 'css-unidades-medida', 'Unidades de medida (px, %, em, rem, vh/vw)', '<p><code>px</code> es un valor fijo. <code>%</code> es relativo al contenedor padre. <code>em</code> es relativo al tamaño de fuente del elemento padre. <code>rem</code> es relativo al tamaño de fuente raíz (más predecible). <code>vh</code>/<code>vw</code> son relativos al alto/ancho de la ventana.</p>',
'<div class="caja">Caja de ejemplo</div>',
'html { font-size: 16px; }\n.caja {\n  width: 50vw;\n  padding: 2rem;\n  font-size: 1.2em;\n}', 5, 7);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Color, tipografía y fondos avanzados', 11 FROM cursos c WHERE c.slug = 'css';
SET @c11 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Color, tipografía y fondos avanzados' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c11, 'css-gradientes', 'Gradientes CSS', '<p>Con <code>linear-gradient()</code> y <code>radial-gradient()</code> puedes crear degradados de color sin necesidad de imágenes, útiles para fondos, botones y overlays. Se usan directamente en la propiedad <code>background</code>.</p>',
'<div class="banner">Banner con degradado</div>',
'.banner {\n  height: 150px;\n  background: linear-gradient(135deg, #6366f1, #ec4899);\n  color: white;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  font-size: 1.5rem;\n}', 1, 6),

(@c11, 'css-font-face-google-fonts', '@font-face y Google Fonts', '<p>Para usar tipografías personalizadas, puedes enlazar Google Fonts con un <code>&lt;link&gt;</code> en el HTML, o definir tu propia fuente con <code>@font-face</code> apuntando a un archivo <code>.woff2</code> propio.</p>',
'<!-- En el head del HTML -->\n<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">\n\n<h1>Título con Poppins</h1>',
'h1 {\n  font-family: "Poppins", sans-serif;\n}', 2, 6),

(@c11, 'css-alineacion-texto', 'Alineación y espaciado de texto', '<p><code>text-align</code> alinea el texto horizontalmente (left, center, right, justify). <code>text-transform</code> cambia mayúsculas/minúsculas. <code>text-indent</code> agrega sangría a la primera línea de un párrafo.</p>',
'<p class="centrado">Texto centrado</p>\n<p class="mayusculas">este texto se verá en mayúsculas</p>',
'.centrado { text-align: center; }\n.mayusculas { text-transform: uppercase; }', 3, 5),

(@c11, 'css-line-height-letter-spacing', 'Line-height y letter-spacing', '<p><code>line-height</code> controla el espacio vertical entre líneas de texto (mejora mucho la legibilidad de párrafos largos). <code>letter-spacing</code> controla el espacio entre letras, útil para títulos en mayúsculas.</p>',
'<h2 class="titulo">TÍTULO ESPACIADO</h2>\n<p class="parrafo">Un párrafo largo se lee mucho mejor cuando tiene suficiente espacio entre líneas, en vez de estar todo apretado.</p>',
'.titulo {\n  letter-spacing: 3px;\n}\n.parrafo {\n  line-height: 1.7;\n}', 4, 5),

(@c11, 'css-fondos-avanzados', 'Fondos avanzados (multiple backgrounds, background-size)', '<p>Puedes combinar varias imágenes de fondo separándolas por comas, y controlar su tamaño con <code>background-size: cover</code> (llena el espacio recortando) o <code>contain</code> (se ve completa sin recortar).</p>',
'<div class="hero">Sección con fondo</div>',
'.hero {\n  height: 200px;\n  background-image: url("https://placekitten.com/600/300");\n  background-size: cover;\n  background-position: center;\n}', 5, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Display y posicionamiento avanzado', 12 FROM cursos c WHERE c.slug = 'css';
SET @c12 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Display y posicionamiento avanzado' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c12, 'css-display-tipos', 'Display: block, inline, inline-block y none', '<p><code>display</code> define cómo se comporta un elemento en el layout: <code>block</code> ocupa toda la línea, <code>inline</code> fluye con el texto (sin poder tener width/height), <code>inline-block</code> fluye pero sí acepta width/height, y <code>none</code> lo oculta por completo del documento.</p>',
'<span class="caja">Soy inline-block</span>\n<span class="caja">Otro más</span>',
'.caja {\n  display: inline-block;\n  width: 100px;\n  height: 60px;\n  background: #6366f1;\n  color: white;\n  margin: 5px;\n}', 1, 6),

(@c12, 'css-z-index', 'Z-index y contexto de apilamiento', '<p><code>z-index</code> controla qué elemento se dibuja encima de cuál cuando se superponen. Solo funciona en elementos con <code>position</code> distinto de <code>static</code> (relative, absolute, fixed o sticky).</p>',
'<div class="caja roja">Detrás</div>\n<div class="caja azul">Adelante</div>',
'.caja {\n  position: absolute;\n  width: 100px;\n  height: 100px;\n}\n.roja {\n  background: #ef4444;\n  top: 20px;\n  left: 20px;\n  z-index: 1;\n}\n.azul {\n  background: #6366f1;\n  top: 50px;\n  left: 50px;\n  z-index: 2;\n}', 2, 6),

(@c12, 'css-overflow', 'Overflow y scroll', '<p><code>overflow</code> controla qué pasa cuando el contenido no cabe en su contenedor: <code>visible</code> (por defecto, se desborda), <code>hidden</code> (se recorta), <code>scroll</code> (siempre muestra barra), <code>auto</code> (barra solo si hace falta).</p>',
'<div class="caja">Este es un texto muy largo que definitivamente no va a caber dentro de la caja pequeña que definimos con CSS, así que necesitará scroll.</div>',
'.caja {\n  width: 200px;\n  height: 80px;\n  overflow: auto;\n  border: 1px solid #ccc;\n  padding: 8px;\n}', 3, 5),

(@c12, 'css-float', 'Float (y por qué ya casi no se usa)', '<p><code>float</code> se usaba antes de Flexbox y Grid para crear columnas, haciendo que un elemento "flote" a la izquierda o derecha y el texto lo rodee. Hoy en día se usa sobre todo para envolver texto alrededor de una imagen.</p>',
'<div class="articulo">\n  <img class="flotante" src="https://placekitten.com/150/150" alt="Gato">\n  <p>Este texto va a rodear la imagen flotante, como en un periódico o revista clásica, fluyendo naturalmente a su alrededor.</p>\n</div>',
'.flotante {\n  float: left;\n  margin-right: 12px;\n  border-radius: 8px;\n}', 4, 5),

(@c12, 'css-object-fit', 'Object-fit y object-position', '<p><code>object-fit</code> controla cómo una imagen o video se ajusta dentro de un contenedor de tamaño fijo: <code>cover</code> (llena recortando), <code>contain</code> (se ve completa), <code>fill</code> (se estira, puede deformar).</p>',
'<img class="miniatura" src="https://placekitten.com/400/200" alt="Gato">',
'.miniatura {\n  width: 150px;\n  height: 150px;\n  object-fit: cover;\n  object-position: center;\n  border-radius: 8px;\n}', 5, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Flexbox y Grid en profundidad', 13 FROM cursos c WHERE c.slug = 'css';
SET @c13 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Flexbox y Grid en profundidad' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c13, 'css-flex-grow-shrink-basis', 'Flexbox: grow, shrink y basis', '<p><code>flex-grow</code> define cuánto crece un item para llenar espacio sobrante. <code>flex-shrink</code> define cuánto se encoge si no hay espacio. <code>flex-basis</code> define su tamaño inicial antes de repartir espacio. <code>flex: 1</code> es un atajo muy común para "ocupa el espacio disponible".</p>',
'<div class="contenedor">\n  <div class="item chico">Fijo</div>\n  <div class="item grande">Crece</div>\n</div>',
'.contenedor { display: flex; }\n.chico { flex: 0 0 100px; background: #94a3b8; }\n.grande { flex: 1; background: #6366f1; color: white; }', 1, 7),

(@c13, 'css-flex-alineacion-avanzada', 'Flexbox: alineación avanzada (align-content, wrap)', '<p><code>flex-wrap: wrap</code> permite que los items pasen a una nueva línea si no caben. <code>align-content</code> alinea esas líneas completas dentro del contenedor cuando sobra espacio vertical.</p>',
'<div class="contenedor">\n  <div class="item">1</div><div class="item">2</div><div class="item">3</div>\n  <div class="item">4</div><div class="item">5</div>\n</div>',
'.contenedor {\n  display: flex;\n  flex-wrap: wrap;\n  align-content: space-between;\n  height: 200px;\n  gap: 8px;\n}\n.item {\n  background: #6366f1;\n  color: white;\n  padding: 20px;\n  flex: 1 1 100px;\n}', 2, 7),

(@c13, 'css-grid-template-areas', 'Grid: grid-template-areas', '<p><code>grid-template-areas</code> permite "dibujar" el layout con nombres, asignando cada zona a un elemento con <code>grid-area</code>. Es la forma más visual e intuitiva de maquetar una página completa con Grid.</p>',
'<div class="layout">\n  <header>Header</header>\n  <nav>Nav</nav>\n  <main>Main</main>\n  <footer>Footer</footer>\n</div>',
'.layout {\n  display: grid;\n  grid-template-areas:\n    "header header"\n    "nav main"\n    "footer footer";\n  grid-template-columns: 150px 1fr;\n  gap: 10px;\n}\nheader { grid-area: header; background: #6366f1; color: white; }\nnav { grid-area: nav; background: #eef2ff; }\nmain { grid-area: main; background: #f8fafc; }\nfooter { grid-area: footer; background: #0f172a; color: white; }', 3, 8),

(@c13, 'css-grid-auto-fit-fill', 'Grid: auto-fit y auto-fill', '<p>Combinando <code>repeat(auto-fit, minmax(200px, 1fr))</code> puedes crear una cuadrícula que se adapte automáticamente al ancho disponible, agregando o quitando columnas sin necesidad de escribir media queries.</p>',
'<div class="tarjetas">\n  <div class="tarjeta">1</div>\n  <div class="tarjeta">2</div>\n  <div class="tarjeta">3</div>\n  <div class="tarjeta">4</div>\n</div>',
'.tarjetas {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));\n  gap: 12px;\n}\n.tarjeta {\n  background: #6366f1;\n  color: white;\n  padding: 30px;\n  text-align: center;\n  border-radius: 8px;\n}', 4, 7),

(@c13, 'css-flexbox-vs-grid', 'Flexbox vs Grid: cuándo usar cada uno', '<p>Regla práctica: usa <strong>Flexbox</strong> para layouts en una sola dirección (una fila o una columna, como un menú o una barra de botones). Usa <strong>Grid</strong> cuando necesites controlar filas y columnas al mismo tiempo (el layout completo de una página).</p>',
'<nav class="menu">\n  <a href="#">Inicio</a>\n  <a href="#">Cursos</a>\n  <a href="#">Contacto</a>\n</nav>',
'.menu {\n  display: flex; /* una sola fila: Flexbox es ideal */\n  gap: 16px;\n}', 5, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Interactividad y estados', 14 FROM cursos c WHERE c.slug = 'css';
SET @c14 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Interactividad y estados' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c14, 'css-pseudoclases-estado', 'Pseudo-clases: hover, focus, active, disabled', '<p><code>:hover</code> se activa al pasar el mouse, <code>:focus</code> cuando un input está seleccionado (clave para accesibilidad con teclado), <code>:active</code> mientras se hace clic, y <code>:disabled</code> para elementos deshabilitados.</p>',
'<button>Normal</button>\n<input type="text" placeholder="Haz focus aquí">\n<button disabled>Deshabilitado</button>',
'button:hover { background: #4338ca; }\ninput:focus { border-color: #6366f1; outline: 2px solid #c7d2fe; }\nbutton:active { transform: scale(0.97); }\nbutton:disabled { opacity: 0.5; cursor: not-allowed; }', 1, 6),

(@c14, 'css-nth-child-avanzado', 'nth-child y selectores estructurales avanzados', '<p><code>:nth-child(2n)</code> selecciona elementos pares, <code>:nth-child(2n+1)</code> los impares, <code>:first-child</code>/<code>:last-child</code> el primero/último, y <code>:only-child</code> cuando es hijo único. Muy útil para tablas y listas con estilo alternado.</p>',
'<ul class="lista">\n  <li>Uno</li><li>Dos</li><li>Tres</li><li>Cuatro</li>\n</ul>',
'.lista li:nth-child(even) {\n  background: #f1f5f9;\n}\n.lista li:first-child {\n  font-weight: bold;\n}', 2, 6),

(@c14, 'css-transformaciones-2d', 'Transformaciones 2D', '<p><code>transform</code> permite mover (<code>translate</code>), girar (<code>rotate</code>), escalar (<code>scale</code>) e inclinar (<code>skew</code>) elementos sin afectar el flujo del documento, y combina perfecto con <code>transition</code> para animarlas.</p>',
'<div class="caja">Pasa el mouse</div>',
'.caja {\n  width: 120px;\n  padding: 20px;\n  background: #6366f1;\n  color: white;\n  transition: transform 0.3s ease;\n}\n.caja:hover {\n  transform: rotate(5deg) scale(1.1);\n}', 3, 6),

(@c14, 'css-transformaciones-3d', 'Transformaciones 3D básicas', '<p>Con <code>perspective</code> en el contenedor padre y <code>rotateX</code>/<code>rotateY</code> en el hijo, puedes crear efectos de profundidad 3D, como una tarjeta que se voltea al pasar el mouse.</p>',
'<div class="escena"><div class="tarjeta-3d">Pasa el mouse</div></div>',
'.escena { perspective: 600px; }\n.tarjeta-3d {\n  width: 150px;\n  padding: 30px;\n  background: #6366f1;\n  color: white;\n  text-align: center;\n  transition: transform 0.5s ease;\n}\n.tarjeta-3d:hover {\n  transform: rotateY(180deg);\n}', 4, 7),

(@c14, 'css-backdrop-filter', 'Backdrop-filter y efecto glassmorphism', '<p><code>backdrop-filter: blur()</code> difumina lo que está <em>detrás</em> de un elemento semi-transparente, creando el popular efecto "glassmorphism" (vidrio esmerilado) usado en interfaces modernas.</p>',
'<div class="fondo">\n  <div class="cristal">Efecto vidrio</div>\n</div>',
'.fondo {\n  height: 200px;\n  background: linear-gradient(135deg, #6366f1, #ec4899);\n  display: flex;\n  align-items: center;\n  justify-content: center;\n}\n.cristal {\n  padding: 20px 40px;\n  background: rgba(255,255,255,0.2);\n  backdrop-filter: blur(10px);\n  border-radius: 12px;\n  color: white;\n  border: 1px solid rgba(255,255,255,0.3);\n}', 5, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Diseño responsive avanzado', 15 FROM cursos c WHERE c.slug = 'css';
SET @c15 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Diseño responsive avanzado' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c15, 'css-mobile-first', 'Diseño mobile-first', '<p>La estrategia mobile-first significa escribir primero el CSS para pantallas pequeñas, y luego usar <code>min-width</code> en media queries para ir agregando estilos a medida que la pantalla crece. Suele dar como resultado CSS más simple y liviano.</p>',
'<div class="tarjeta">Tarjeta</div>',
'.tarjeta {\n  padding: 12px; /* estilo base para móvil */\n}\n\n@media (min-width: 768px) {\n  .tarjeta { padding: 24px; }\n}\n@media (min-width: 1200px) {\n  .tarjeta { padding: 40px; }\n}', 1, 6),

(@c15, 'css-clamp-min-max', 'clamp(), min() y max()', '<p><code>clamp(min, preferido, max)</code> permite que un valor (como <code>font-size</code>) crezca de forma fluida entre un mínimo y un máximo, sin necesidad de escribir múltiples media queries.</p>',
'<h1 class="titulo">Título fluido</h1>',
'.titulo {\n  font-size: clamp(1.5rem, 4vw, 3rem);\n}', 2, 6),

(@c15, 'css-aspect-ratio', 'Aspect-ratio', '<p><code>aspect-ratio</code> fija la proporción ancho/alto de un elemento (por ejemplo, 16/9 para videos), evitando que el contenido "salte" mientras carga una imagen o iframe.</p>',
'<div class="video-wrapper">\n  <iframe src="about:blank" title="Video de ejemplo"></iframe>\n</div>',
'.video-wrapper {\n  width: 100%;\n  aspect-ratio: 16 / 9;\n}\n.video-wrapper iframe {\n  width: 100%;\n  height: 100%;\n  border: none;\n}', 3, 5),

(@c15, 'css-grid-responsive-auto-fit', 'Grid responsive con auto-fit', '<p>Combinar Grid con <code>auto-fit</code> y <code>minmax()</code> es de las formas más elegantes de lograr una cuadrícula totalmente responsive sin escribir ni una sola media query, como vimos antes pero ahora aplicado a un caso real de galería.</p>',
'<div class="galeria">\n  <img src="https://placekitten.com/200/200">\n  <img src="https://placekitten.com/201/200">\n  <img src="https://placekitten.com/202/200">\n</div>',
'.galeria {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));\n  gap: 8px;\n}\n.galeria img {\n  width: 100%;\n  border-radius: 8px;\n}', 4, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Efectos y detalles finales', 16 FROM cursos c WHERE c.slug = 'css';
SET @c16 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'Efectos y detalles finales' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c16, 'css-box-shadow-avanzado', 'box-shadow avanzado', '<p><code>box-shadow</code> acepta varios valores: desplazamiento horizontal, vertical, difuminado, expansión y color. Puedes combinar varias sombras separadas por comas para efectos más ricos, e incluso usar <code>inset</code> para sombras internas.</p>',
'<div class="tarjeta">Tarjeta con sombra</div>',
'.tarjeta {\n  padding: 30px;\n  border-radius: 12px;\n  box-shadow: 0 10px 25px rgba(0,0,0,.1), 0 4px 6px rgba(0,0,0,.05);\n}', 1, 5),

(@c16, 'css-text-shadow', 'text-shadow', '<p><code>text-shadow</code> agrega sombra al texto con la misma sintaxis que box-shadow (sin expansión): desplazamiento X, Y, difuminado y color. Útil para mejorar la legibilidad de texto sobre imágenes.</p>',
'<h1 class="titulo">Texto con sombra</h1>',
'.titulo {\n  color: white;\n  text-shadow: 2px 2px 4px rgba(0,0,0,.5);\n}', 2, 4),

(@c16, 'css-filtros', 'Filtros CSS (filter)', '<p>La propiedad <code>filter</code> aplica efectos visuales como en Instagram: <code>blur()</code>, <code>brightness()</code>, <code>grayscale()</code>, <code>contrast()</code>. Se puede aplicar a cualquier elemento, no solo imágenes.</p>',
'<img class="foto" src="https://placekitten.com/200/200" alt="Gato">',
'.foto {\n  filter: grayscale(80%) brightness(1.1);\n  transition: filter 0.3s ease;\n}\n.foto:hover {\n  filter: none;\n}', 3, 5),

(@c16, 'css-modo-oscuro', 'Modo oscuro con variables CSS', '<p>Combinando variables CSS con la media query <code>prefers-color-scheme</code>, puedes crear un modo oscuro automático que respeta la preferencia del sistema operativo del usuario, sin necesidad de JavaScript.</p>',
'<div class="tarjeta">Cambia el tema de tu sistema para ver el efecto</div>',
':root {\n  --fondo: white;\n  --texto: black;\n}\n@media (prefers-color-scheme: dark) {\n  :root {\n    --fondo: #0f172a;\n    --texto: white;\n  }\n}\n.tarjeta {\n  background: var(--fondo);\n  color: var(--texto);\n  padding: 20px;\n}', 4, 7),

(@c16, 'css-scrollbar-personalizada', 'Scrollbar personalizada', '<p>Con las propiedades no estándar (pero muy soportadas) <code>::-webkit-scrollbar</code> puedes personalizar el color y grosor de la barra de scroll en navegadores basados en Chromium, dando un toque de marca a tu sitio.</p>',
'<div class="caja-scroll">Contenido largo que necesita scroll para verse completo dentro de esta caja de altura fija...</div>',
'.caja-scroll {\n  height: 100px;\n  overflow-y: scroll;\n  padding: 10px;\n}\n.caja-scroll::-webkit-scrollbar {\n  width: 8px;\n}\n.caja-scroll::-webkit-scrollbar-thumb {\n  background: #6366f1;\n  border-radius: 4px;\n}', 5, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'CSS profesional y proyectos', 17 FROM cursos c WHERE c.slug = 'css';
SET @c17 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'css' AND m.titulo = 'CSS profesional y proyectos' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_css, orden, minutos_estimados) VALUES
(@c17, 'css-estilizar-formularios', 'Estilizar formularios', '<p>Los inputs tienen estilos por defecto muy distintos entre navegadores. <code>appearance: none</code> quita ese estilo nativo para que puedas diseñarlo tú mismo con border, padding, background y estados <code>:focus</code>.</p>',
'<input type="text" class="campo" placeholder="Nombre">\n<button class="boton">Enviar</button>',
'.campo {\n  appearance: none;\n  border: 2px solid #e2e8f0;\n  border-radius: 10px;\n  padding: 10px 14px;\n  font-size: 1rem;\n}\n.campo:focus {\n  border-color: #6366f1;\n  outline: none;\n}\n.boton {\n  background: #6366f1;\n  color: white;\n  border: none;\n  padding: 10px 20px;\n  border-radius: 10px;\n}', 1, 6),

(@c17, 'css-reset-normalize', 'CSS Reset y normalize', '<p>Cada navegador aplica estilos por defecto distintos (márgenes, tamaños de fuente). Un "reset" los elimina todos para partir de cero; "normalize" los hace consistentes entre navegadores sin eliminarlos por completo. Ambos evitan sorpresas de diseño.</p>',
'<h1>Título</h1>\n<p>Párrafo de ejemplo.</p>',
'* {\n  margin: 0;\n  padding: 0;\n  box-sizing: border-box;\n}\nbody {\n  font-family: sans-serif;\n  line-height: 1.5;\n}', 2, 5),

(@c17, 'css-metodologia-bem', 'Metodología BEM', '<p>BEM (Block, Element, Modifier) es una convención de nombres para clases CSS que evita conflictos y hace el código más predecible: <code>.tarjeta</code> (bloque), <code>.tarjeta__titulo</code> (elemento), <code>.tarjeta--destacada</code> (modificador).</p>',
'<div class="tarjeta tarjeta--destacada">\n  <h3 class="tarjeta__titulo">Producto</h3>\n  <p class="tarjeta__precio">$29.99</p>\n</div>',
'.tarjeta { border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; }\n.tarjeta__titulo { font-size: 1.2rem; margin-bottom: 8px; }\n.tarjeta--destacada { border-color: #6366f1; box-shadow: 0 4px 12px rgba(99,102,241,.2); }', 3, 6),

(@c17, 'css-proyecto-tarjeta-producto', 'Proyecto: rediseño de tarjeta de producto', '<p>Combina todo lo aprendido: box-shadow, border-radius, transiciones, flexbox y pseudo-clases, para construir una tarjeta de producto profesional con imagen, precio, badge de descuento y botón con efecto hover.</p>',
'<div class="producto">\n  <span class="badge">-20%</span>\n  <img src="https://placekitten.com/250/180" alt="Producto">\n  <h3>Zapatillas Runner</h3>\n  <p class="precio"><del>$80</del> $64</p>\n  <button class="comprar">Agregar al carrito</button>\n</div>',
'.producto {\n  position: relative;\n  max-width: 250px;\n  margin: 20px auto;\n  padding: 16px;\n  border-radius: 14px;\n  box-shadow: 0 8px 20px rgba(0,0,0,.08);\n  text-align: center;\n  transition: transform .2s ease;\n}\n.producto:hover { transform: translateY(-4px); }\n.badge {\n  position: absolute;\n  top: 10px;\n  left: 10px;\n  background: #ef4444;\n  color: white;\n  padding: 2px 8px;\n  border-radius: 6px;\n  font-size: .75rem;\n}\n.comprar {\n  background: #6366f1;\n  color: white;\n  border: none;\n  padding: 10px 16px;\n  border-radius: 8px;\n  width: 100%;\n  margin-top: 10px;\n  cursor: pointer;\n  transition: background .2s ease;\n}\n.comprar:hover { background: #4338ca; }', 4, 15),

(@c17, 'css-proyecto-landing-completa', 'Proyecto: landing page responsive completa', '<p>El proyecto final del curso: construye una landing page de una sola página combinando Flexbox y Grid, con header fijo, sección hero con gradiente, una cuadrícula de características responsive y un footer, todo adaptable a móvil con media queries.</p>',
'<header class="header">MiMarca</header>\n<section class="hero">\n  <h1>Bienvenido a MiMarca</h1>\n  <button class="cta">Empezar</button>\n</section>\n<section class="caracteristicas">\n  <div class="tarjeta">Rápido</div>\n  <div class="tarjeta">Seguro</div>\n  <div class="tarjeta">Simple</div>\n</section>\n<footer class="footer">&copy; 2026 MiMarca</footer>',
'* { box-sizing: border-box; margin: 0; }\nbody { font-family: sans-serif; }\n.header { padding: 16px; background: #0f172a; color: white; position: sticky; top: 0; }\n.hero {\n  padding: 60px 20px;\n  text-align: center;\n  background: linear-gradient(135deg, #6366f1, #ec4899);\n  color: white;\n}\n.cta { padding: 12px 24px; border: none; border-radius: 8px; background: white; color: #6366f1; font-weight: bold; }\n.caracteristicas {\n  display: grid;\n  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));\n  gap: 16px;\n  padding: 30px;\n}\n.tarjeta { background: #f1f5f9; padding: 30px; text-align: center; border-radius: 10px; }\n.footer { text-align: center; padding: 20px; background: #0f172a; color: white; }', 5, 20);

-- ============================================================
-- Lecciones adicionales de JavaScript (curso slug 'js')
-- 38 lecciones nuevas repartidas en 8 módulos nuevos
-- ============================================================

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Operadores y sintaxis moderna', 13 FROM cursos c WHERE c.slug = 'js';
SET @j13 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'Operadores y sintaxis moderna' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j13, 'js-operadores-aritmeticos', 'Operadores aritméticos', '<p>JavaScript tiene los operadores matemáticos básicos: <code>+</code>, <code>-</code>, <code>*</code>, <code>/</code>, <code>%</code> (módulo o resto) y <code>**</code> (potencia). También existen los atajos <code>+=</code>, <code>-=</code>, <code>++</code> y <code>--</code> para modificar una variable sobre sí misma.</p>',
'<p id="salida"></p>',
'let a = 10;\nlet b = 3;\n\nconsole.log(a + b, a - b, a * b, a / b, a % b, a ** 2);\n\na++; // equivale a a = a + 1\ndocument.getElementById("salida").innerText = "a ahora vale: " + a;', 1, 5),

(@j13, 'js-operadores-comparacion', 'Operadores de comparación (== vs ===)', '<p><code>==</code> compara valores convirtiendo tipos si es necesario ("5" == 5 es true), mientras que <code>===</code> compara valor <strong>y</strong> tipo sin conversión ("5" === 5 es false). Casi siempre deberías usar <code>===</code> para evitar comportamientos inesperados.</p>',
'<p id="salida"></p>',
'console.log(5 == "5");   // true (compara solo el valor)\nconsole.log(5 === "5");  // false (compara valor y tipo)\nconsole.log(5 !== "5");  // true\n\ndocument.getElementById("salida").innerText = (10 >= 10) + " / " + (10 > 10);', 2, 5),

(@j13, 'js-operadores-logicos', 'Operadores lógicos', '<p><code>&&</code> (Y) es true solo si ambos lados son true. <code>||</code> (O) es true si al menos uno es true. <code>!</code> invierte un valor booleano. Se usan muchísimo dentro de condicionales para combinar varias condiciones.</p>',
'<p id="salida"></p>',
'const edad = 20;\nconst tieneCarnet = true;\n\nconst puedeConducir = edad >= 18 && tieneCarnet;\ndocument.getElementById("salida").innerText = "¿Puede conducir? " + puedeConducir;', 3, 5),

(@j13, 'js-template-literals', 'Template literals', '<p>Los <em>template literals</em> (comillas invertidas <code>` `</code>) permiten insertar variables directamente en un string con <code>${variable}</code>, y también escribir strings de varias líneas sin concatenar con <code>+</code>.</p>',
'<p id="salida"></p>',
'const nombre = "Ana";\nconst edad = 25;\n\nconst mensaje = `Hola, me llamo ${nombre} y tengo ${edad} años.\nEste es un string de varias líneas.`;\n\ndocument.getElementById("salida").innerText = mensaje;', 4, 5),

(@j13, 'js-parametros-default-rest', 'Parámetros por defecto y rest', '<p>Puedes dar un valor por defecto a un parámetro con <code>function f(x = 10)</code>, que se usa si no se pasa ese argumento. El operador rest (<code>...args</code>) agrupa un número indefinido de argumentos en un array.</p>',
'<p id="salida"></p>',
'function saludar(nombre = "invitado") {\n  return `Hola, ${nombre}`;\n}\n\nfunction sumarTodos(...numeros) {\n  return numeros.reduce((total, n) => total + n, 0);\n}\n\ndocument.getElementById("salida").innerText = saludar() + " | Suma: " + sumarTodos(1, 2, 3, 4);', 5, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Control de flujo avanzado', 14 FROM cursos c WHERE c.slug = 'js';
SET @j14 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'Control de flujo avanzado' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j14, 'js-switch-case', 'Switch case', '<p><code>switch</code> compara una misma variable contra varios valores posibles, siendo más legible que una larga cadena de <code>if/else if</code> cuando hay muchas opciones. No olvides el <code>break</code> en cada caso para evitar que "se caiga" al siguiente.</p>',
'<p id="salida"></p>',
'const dia = 3;\nlet nombre;\n\nswitch (dia) {\n  case 1: nombre = "Lunes"; break;\n  case 2: nombre = "Martes"; break;\n  case 3: nombre = "Miércoles"; break;\n  default: nombre = "Día inválido";\n}\n\ndocument.getElementById("salida").innerText = nombre;', 1, 5),

(@j14, 'js-while-do-while', 'Bucle while y do-while', '<p><code>while</code> repite código mientras una condición sea verdadera (revisa la condición antes de ejecutar). <code>do...while</code> es igual pero ejecuta el bloque <strong>al menos una vez</strong> antes de revisar la condición.</p>',
'<p id="salida"></p>',
'let contador = 0;\nlet resultado = "";\n\nwhile (contador < 5) {\n  resultado += contador + " ";\n  contador++;\n}\n\ndocument.getElementById("salida").innerText = resultado;', 2, 5),

(@j14, 'js-for-of-vs-for-in', 'for...of vs for...in', '<p><code>for...of</code> recorre los <strong>valores</strong> de un array o string (lo más usado). <code>for...in</code> recorre las <strong>claves</strong> de un objeto (o los índices de un array, pero no es lo recomendado para arrays).</p>',
'<p id="salida"></p>',
'const frutas = ["manzana", "pera", "uva"];\nfor (const fruta of frutas) {\n  console.log(fruta);\n}\n\nconst persona = { nombre: "Ana", edad: 25 };\nlet texto = "";\nfor (const clave in persona) {\n  texto += `${clave}: ${persona[clave]} `;\n}\n\ndocument.getElementById("salida").innerText = texto;', 3, 6),

(@j14, 'js-arrow-functions-profundidad', 'Funciones flecha en profundidad', '<p>Las funciones flecha (<code>=&gt;</code>) son más cortas y, a diferencia de las funciones tradicionales, no tienen su propio <code>this</code> (heredan el del contexto donde se definieron), lo que las hace ideales dentro de callbacks y métodos de arrays.</p>',
'<p id="salida"></p>',
'const cuadrado = n => n * n;\nconst sumar = (a, b) => a + b;\nconst saludar = () => "¡Hola!";\n\ndocument.getElementById("salida").innerText = `${cuadrado(4)}, ${sumar(2,3)}, ${saludar()}`;', 4, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Arrays y objetos en profundidad', 15 FROM cursos c WHERE c.slug = 'js';
SET @j15 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'Arrays y objetos en profundidad' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j15, 'js-arrays-metodos-basicos', 'Arrays: push, pop, shift, slice y splice', '<p><code>push</code> agrega al final, <code>pop</code> quita el último, <code>shift</code> quita el primero, <code>unshift</code> agrega al inicio. <code>slice</code> extrae una copia sin modificar el original; <code>splice</code> modifica el array original insertando o eliminando elementos.</p>',
'<p id="salida"></p>',
'let frutas = ["manzana", "pera"];\nfrutas.push("uva");\nfrutas.unshift("kiwi");\n\nconst copia = frutas.slice(1, 3);\nfrutas.splice(1, 1, "mango");\n\ndocument.getElementById("salida").innerText = frutas.join(", ") + " | copia: " + copia.join(", ");', 1, 7),

(@j15, 'js-arrays-find-some-every', 'Arrays: find, some y every', '<p><code>find</code> retorna el primer elemento que cumple una condición (o undefined). <code>some</code> retorna true si <strong>al menos uno</strong> cumple la condición. <code>every</code> retorna true solo si <strong>todos</strong> la cumplen.</p>',
'<p id="salida"></p>',
'const numeros = [4, 9, 15, 22, 30];\n\nconst primerMayor10 = numeros.find(n => n > 10);\nconst hayImpares = numeros.some(n => n % 2 !== 0);\nconst todosPositivos = numeros.every(n => n > 0);\n\ndocument.getElementById("salida").innerText = `${primerMayor10}, ${hayImpares}, ${todosPositivos}`;', 2, 6),

(@j15, 'js-objetos-propiedades-metodos', 'Objetos: propiedades y métodos', '<p>Un objeto puede tener funciones como propiedades, llamadas <strong>métodos</strong>. Se accede a las propiedades con notación de punto (<code>obj.prop</code>) o con corchetes (<code>obj["prop"]</code>), esta última útil cuando el nombre es dinámico.</p>',
'<p id="salida"></p>',
'const persona = {\n  nombre: "Carlos",\n  saludar() {\n    return `Hola, soy ${this.nombre}`;\n  }\n};\n\nconst propiedad = "nombre";\ndocument.getElementById("salida").innerText = persona.saludar() + " | " + persona[propiedad];', 3, 6),

(@j15, 'js-this-objetos', 'El valor de "this" en objetos', '<p>Dentro de un método de objeto, <code>this</code> hace referencia al objeto que llamó al método. Cuidado: si usas una función flecha como método, <code>this</code> NO apuntará al objeto, sino al contexto exterior.</p>',
'<p id="salida"></p>',
'const contador = {\n  valor: 0,\n  incrementar() {\n    this.valor++;\n    return this.valor;\n  }\n};\n\ncontador.incrementar();\ncontador.incrementar();\ndocument.getElementById("salida").innerText = "Valor: " + contador.incrementar();', 4, 6),

(@j15, 'js-destructuring', 'Destructuring de arrays y objetos', '<p>El <em>destructuring</em> permite "desempacar" valores de arrays u objetos en variables individuales de forma muy compacta: <code>const [a, b] = array</code> o <code>const {nombre, edad} = objeto</code>.</p>',
'<p id="salida"></p>',
'const persona = { nombre: "Sofía", edad: 30, ciudad: "Bogotá" };\nconst { nombre, ciudad } = persona;\n\nconst colores = ["rojo", "verde", "azul"];\nconst [primero, , tercero] = colores;\n\ndocument.getElementById("salida").innerText = `${nombre} vive en ${ciudad}. Colores: ${primero}, ${tercero}`;', 5, 6),

(@j15, 'js-spread-operator', 'Spread operator', '<p>El operador spread (<code>...</code>) "expande" los elementos de un array u objeto. Sirve para copiar arrays/objetos sin mutarlos, combinarlos, o pasarlos como argumentos individuales a una función.</p>',
'<p id="salida"></p>',
'const numeros1 = [1, 2, 3];\nconst numeros2 = [4, 5];\nconst combinados = [...numeros1, ...numeros2];\n\nconst base = { nombre: "Ana" };\nconst extendido = { ...base, edad: 25 };\n\ndocument.getElementById("salida").innerText = combinados.join(",") + " | " + JSON.stringify(extendido);', 6, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'DOM avanzado', 16 FROM cursos c WHERE c.slug = 'js';
SET @j16 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'DOM avanzado' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j16, 'js-dom-crear-eliminar', 'Crear y eliminar elementos del DOM', '<p><code>document.createElement()</code> crea un elemento nuevo en memoria, que luego insertas con <code>appendChild()</code> o <code>append()</code>. Para quitarlo, usa <code>elemento.remove()</code>.</p>',
'<ul id="lista"></ul>\n<button id="agregar">Agregar item</button>',
'let contador = 1;\n\ndocument.getElementById("agregar").addEventListener("click", () => {\n  const li = document.createElement("li");\n  li.textContent = "Item " + contador++;\n\n  const btnBorrar = document.createElement("button");\n  btnBorrar.textContent = "x";\n  btnBorrar.onclick = () => li.remove();\n\n  li.appendChild(btnBorrar);\n  document.getElementById("lista").appendChild(li);\n});', 1, 7),

(@j16, 'js-dom-modificar-estilos', 'Modificar estilos desde JavaScript', '<p>Puedes cambiar el CSS de un elemento directamente con <code>elemento.style.propiedad</code> (en camelCase, ej. <code>backgroundColor</code>). Para cambios más grandes, es mejor alternar una clase CSS en vez de escribir estilos uno por uno.</p>',
'<div id="caja" style="width:100px;height:100px;background:#6366f1;"></div>\n<button id="cambiar">Cambiar estilo</button>',
'document.getElementById("cambiar").addEventListener("click", () => {\n  const caja = document.getElementById("caja");\n  caja.style.background = "#10b981";\n  caja.style.borderRadius = "50%";\n  caja.style.transform = "rotate(45deg)";\n});', 2, 5),

(@j16, 'js-classlist', 'classList: add, remove y toggle', '<p><code>classList</code> es la forma moderna de manipular clases CSS desde JS: <code>add()</code> agrega una clase, <code>remove()</code> la quita, <code>toggle()</code> la agrega si no está o la quita si ya está (perfecto para menús desplegables).</p>',
'<div id="caja" class="normal">Haz clic en el botón</div>\n<button id="toggle">Alternar clase</button>\n<style>.activa { background: #6366f1; color: white; padding: 10px; }</style>',
'document.getElementById("toggle").addEventListener("click", () => {\n  document.getElementById("caja").classList.toggle("activa");\n});', 3, 5),

(@j16, 'js-event-bubbling-delegacion', 'Event bubbling y delegación de eventos', '<p>Cuando haces clic en un elemento, el evento "burbujea" hacia sus elementos padre. La <strong>delegación de eventos</strong> aprovecha esto: en vez de poner un listener en cada hijo, pones uno solo en el padre y revisas <code>event.target</code> para saber en cuál se hizo clic.</p>',
'<ul id="lista">\n  <li>Manzana</li>\n  <li>Pera</li>\n  <li>Uva</li>\n</ul>\n<p id="resultado"></p>',
'document.getElementById("lista").addEventListener("click", (evento) => {\n  if (evento.target.tagName === "LI") {\n    document.getElementById("resultado").textContent = "Clickeaste: " + evento.target.textContent;\n  }\n});', 4, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'JavaScript asíncrono en profundidad', 17 FROM cursos c WHERE c.slug = 'js';
SET @j17 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'JavaScript asíncrono en profundidad' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j17, 'js-formularios-validacion', 'Formularios y validación con JavaScript', '<p>Escuchando el evento <code>submit</code> de un <code>&lt;form&gt;</code> y usando <code>event.preventDefault()</code>, puedes validar los datos con JavaScript antes de procesarlos, mostrando mensajes de error personalizados.</p>',
'<form id="formulario">\n  <input type="text" id="nombre" placeholder="Tu nombre">\n  <button type="submit">Enviar</button>\n</form>\n<p id="mensaje"></p>',
'document.getElementById("formulario").addEventListener("submit", (evento) => {\n  evento.preventDefault();\n  const nombre = document.getElementById("nombre").value.trim();\n  const mensaje = document.getElementById("mensaje");\n\n  if (nombre.length < 3) {\n    mensaje.textContent = "El nombre debe tener al menos 3 letras.";\n    mensaje.style.color = "red";\n  } else {\n    mensaje.textContent = "¡Formulario enviado, " + nombre + "!";\n    mensaje.style.color = "green";\n  }\n});', 1, 7),

(@j17, 'js-promesas-profundidad', 'Promesas en profundidad', '<p>Una <code>Promise</code> representa un valor que estará disponible en el futuro, con tres estados: pendiente, cumplida (<code>resolve</code>) o rechazada (<code>reject</code>). Se manejan con <code>.then()</code> para el éxito y <code>.catch()</code> para errores.</p>',
'<button id="iniciar">Iniciar tarea</button>\n<p id="resultado"></p>',
'function tareaLenta() {\n  return new Promise((resolve, reject) => {\n    setTimeout(() => {\n      const exito = Math.random() > 0.3;\n      exito ? resolve("¡Tarea completada!") : reject("Algo salió mal");\n    }, 1000);\n  });\n}\n\ndocument.getElementById("iniciar").addEventListener("click", () => {\n  document.getElementById("resultado").textContent = "Cargando...";\n  tareaLenta()\n    .then(mensaje => document.getElementById("resultado").textContent = mensaje)\n    .catch(error => document.getElementById("resultado").textContent = "Error: " + error);\n});', 2, 8),

(@j17, 'js-modulos-es6', 'Módulos ES6 (import/export)', '<p>Los módulos permiten dividir tu código en varios archivos: cada archivo <code>export</code>a lo que otros necesiten usar, y lo importas con <code>import</code>. Requiere cargar el script con <code>type="module"</code> en el HTML.</p>',
'<p id="salida"></p>\n<!-- En un proyecto real, esto viviría en archivos separados: -->\n<!-- utilidades.js: export function saludar(nombre) { return `Hola, ${nombre}`; } -->\n<!-- app.js: import { saludar } from "./utilidades.js"; -->',
'// Simulación en un solo archivo para el editor:\nfunction saludar(nombre) {\n  return `Hola, ${nombre}`;\n}\n\ndocument.getElementById("salida").innerText = saludar("Mundo") + " (así se vería usando import/export en archivos reales)";', 3, 6);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Conceptos avanzados de JS', 18 FROM cursos c WHERE c.slug = 'js';
SET @j18 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'Conceptos avanzados de JS' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j18, 'js-closures', 'Closures', '<p>Un <em>closure</em> ocurre cuando una función "recuerda" las variables de su entorno exterior, incluso después de que esa función exterior ya terminó de ejecutarse. Es la base de patrones como contadores privados y funciones fábrica.</p>',
'<p id="salida"></p>',
'function crearContador() {\n  let cuenta = 0;\n  return function () {\n    cuenta++;\n    return cuenta;\n  };\n}\n\nconst contador = crearContador();\ndocument.getElementById("salida").innerText = contador() + ", " + contador() + ", " + contador();', 1, 7),

(@j18, 'js-scope-hoisting', 'Scope y hoisting', '<p>El <em>scope</em> (alcance) determina dónde una variable es accesible. <code>let</code> y <code>const</code> tienen scope de bloque; <code>var</code> tiene scope de función. El <em>hoisting</em> es el comportamiento de JS de "elevar" las declaraciones al inicio de su scope antes de ejecutar el código.</p>',
'<p id="salida"></p>',
'console.log(typeof mensaje); // "undefined", no error: var se "eleva"\nvar mensaje = "Hola";\n\nif (true) {\n  let local = "Solo existo aquí dentro";\n  console.log(local);\n}\n// console.log(local); // Error: local no existe fuera del bloque\n\ndocument.getElementById("salida").innerText = "Revisa la consola para ver el resultado";', 2, 7),

(@j18, 'js-call-apply-bind', 'call, apply y bind', '<p><code>call()</code> y <code>apply()</code> ejecutan una función indicando manualmente qué será <code>this</code> dentro de ella (la diferencia es cómo se pasan los argumentos). <code>bind()</code> no ejecuta la función, sino que devuelve una nueva función con <code>this</code> ya fijado.</p>',
'<p id="salida"></p>',
'function saludar() {\n  return `Hola, soy ${this.nombre}`;\n}\n\nconst persona = { nombre: "Luis" };\n\nconst resultado1 = saludar.call(persona);\nconst saludarLuis = saludar.bind(persona);\n\ndocument.getElementById("salida").innerText = resultado1 + " | " + saludarLuis();', 3, 6),

(@j18, 'js-herencia-clases', 'Herencia con clases (extends y super)', '<p>Una clase puede heredar de otra con <code>extends</code>, reutilizando sus propiedades y métodos. Dentro del constructor de la clase hija, <code>super()</code> llama al constructor de la clase padre para inicializar lo que ella necesita.</p>',
'<p id="salida"></p>',
'class Animal {\n  constructor(nombre) {\n    this.nombre = nombre;\n  }\n  hacerSonido() {\n    return `${this.nombre} hace un sonido`;\n  }\n}\n\nclass Perro extends Animal {\n  hacerSonido() {\n    return `${this.nombre} ladra`;\n  }\n}\n\nconst miPerro = new Perro("Rex");\ndocument.getElementById("salida").innerText = miPerro.hacerSonido();', 4, 7),

(@j18, 'js-cookies', 'Cookies con JavaScript', '<p>Las cookies son pequeños datos que se guardan en el navegador y se envían al servidor en cada petición, a diferencia de localStorage que solo vive en el navegador. Se leen y escriben con <code>document.cookie</code>.</p>',
'<button id="guardar">Guardar cookie</button>\n<button id="leer">Leer cookie</button>\n<p id="resultado"></p>',
'document.getElementById("guardar").addEventListener("click", () => {\n  document.cookie = "usuario=Ana; max-age=3600; path=/";\n});\n\ndocument.getElementById("leer").addEventListener("click", () => {\n  document.getElementById("resultado").textContent = document.cookie;\n});', 5, 6),

(@j18, 'js-expresiones-regulares', 'Expresiones regulares básicas', '<p>Las expresiones regulares (<code>/patrón/</code>) describen patrones de texto para buscar o validar strings, como verificar si un correo tiene formato válido. <code>test()</code> retorna true/false, y <code>match()</code> extrae las coincidencias.</p>',
'<p id="salida"></p>',
'const regexEmail = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;\n\nconsole.log(regexEmail.test("hola@test.com")); // true\nconsole.log(regexEmail.test("no-es-email"));   // false\n\nconst texto = "Tengo 3 gatos y 2 perros";\nconst numeros = texto.match(/\\d+/g);\ndocument.getElementById("salida").innerText = "Números encontrados: " + numeros.join(", ");', 6, 7);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'Utilidades del lenguaje', 19 FROM cursos c WHERE c.slug = 'js';
SET @j19 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'Utilidades del lenguaje' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j19, 'js-settimeout-setinterval', 'setTimeout y setInterval', '<p><code>setTimeout(fn, ms)</code> ejecuta una función una sola vez después de cierto tiempo. <code>setInterval(fn, ms)</code> la ejecuta repetidamente cada cierto intervalo, hasta que uses <code>clearInterval()</code> para detenerlo.</p>',
'<p id="reloj">00</p>\n<button id="detener">Detener</button>',
'let segundos = 0;\nconst elemento = document.getElementById("reloj");\n\nconst intervalo = setInterval(() => {\n  segundos++;\n  elemento.textContent = String(segundos).padStart(2, "0");\n}, 1000);\n\ndocument.getElementById("detener").addEventListener("click", () => {\n  clearInterval(intervalo);\n});', 1, 6),

(@j19, 'js-manipulacion-strings', 'Manipulación de strings', '<p>Los strings tienen muchos métodos útiles: <code>toUpperCase()</code>/<code>toLowerCase()</code>, <code>trim()</code> (quita espacios), <code>includes()</code>, <code>replace()</code>, <code>split()</code> (convierte a array) y <code>padStart()</code>/<code>padEnd()</code>.</p>',
'<p id="salida"></p>',
'const texto = "  Hola Mundo  ";\n\nconsole.log(texto.trim().toUpperCase());\nconsole.log(texto.includes("Mundo"));\nconsole.log("2026-01-05".split("-"));\n\ndocument.getElementById("salida").innerText = texto.trim().replace("Mundo", "Dique Programando");', 2, 6),

(@j19, 'js-math-numeros', 'Math y números', '<p>El objeto global <code>Math</code> ofrece funciones matemáticas: <code>Math.round()</code>, <code>Math.floor()</code>, <code>Math.ceil()</code>, <code>Math.random()</code> (entre 0 y 1), <code>Math.max()</code>/<code>Math.min()</code>. Muy usado para generar números aleatorios entre un rango.</p>',
'<button id="generar">Generar número (1-100)</button>\n<p id="resultado"></p>',
'document.getElementById("generar").addEventListener("click", () => {\n  const numero = Math.floor(Math.random() * 100) + 1;\n  document.getElementById("resultado").textContent = "Número: " + numero;\n});', 3, 5),

(@j19, 'js-fechas-date', 'Fechas con el objeto Date', '<p><code>new Date()</code> crea un objeto con la fecha y hora actual. Tiene métodos como <code>getFullYear()</code>, <code>getMonth()</code> (ojo: empieza en 0), <code>getDate()</code>, y se puede formatear fácilmente con <code>toLocaleDateString()</code>.</p>',
'<p id="salida"></p>',
'const ahora = new Date();\n\nconst formateada = ahora.toLocaleDateString("es-CO", {\n  weekday: "long",\n  year: "numeric",\n  month: "long",\n  day: "numeric"\n});\n\ndocument.getElementById("salida").innerText = "Hoy es: " + formateada;', 4, 6),

(@j19, 'js-debugging-consola', 'Debugging con console y DevTools', '<p>Además de <code>console.log()</code>, existen <code>console.warn()</code>, <code>console.error()</code>, <code>console.table()</code> (muestra arrays/objetos en tabla) y <code>console.time()</code>/<code>console.timeEnd()</code> para medir cuánto tarda algo. Las DevTools del navegador (F12) son tu mejor aliado para depurar.</p>',
'<p id="salida">Abre la consola del navegador (F12) para ver los resultados</p>',
'console.log("Mensaje normal");\nconsole.warn("Esto es una advertencia");\nconsole.error("Esto es un error");\nconsole.table([{ nombre: "Ana", edad: 25 }, { nombre: "Luis", edad: 30 }]);', 5, 5);

INSERT INTO modulos (curso_id, titulo, orden)
SELECT c.id, 'JS profesional y proyectos', 20 FROM cursos c WHERE c.slug = 'js';
SET @j20 = (SELECT m.id FROM modulos m JOIN cursos c ON c.id = m.curso_id WHERE c.slug = 'js' AND m.titulo = 'JS profesional y proyectos' LIMIT 1);

INSERT INTO lecciones (modulo_id, slug, titulo, contenido, codigo_html, codigo_js, orden, minutos_estimados) VALUES
(@j20, 'js-codigo-limpio', 'Código limpio en JavaScript', '<p>Buenas prácticas: usa nombres de variables descriptivos, funciones pequeñas que hagan una sola cosa, evita anidar demasiados <code>if</code>, prefiere <code>const</code> sobre <code>let</code> cuando el valor no cambia, y comenta solo lo que no es obvio.</p>',
'<p id="salida"></p>',
'// Evitar:\nfunction f(x) { if (x > 0) { if (x < 100) { return true; } } return false; }\n\n// Mejor:\nfunction esPorcentajeValido(valor) {\n  return valor > 0 && valor < 100;\n}\n\ndocument.getElementById("salida").innerText = esPorcentajeValido(50);', 1, 6),

(@j20, 'js-debounce-throttle', 'Debounce y throttle', '<p><strong>Debounce</strong> espera a que el usuario deje de escribir/hacer scroll antes de ejecutar una función (ideal para buscadores). <strong>Throttle</strong> limita cuántas veces se ejecuta una función en un período de tiempo (ideal para eventos de scroll muy frecuentes).</p>',
'<input type="text" id="buscador" placeholder="Escribe para buscar...">\n<p id="resultado"></p>',
'function debounce(fn, espera) {\n  let temporizador;\n  return function (...args) {\n    clearTimeout(temporizador);\n    temporizador = setTimeout(() => fn(...args), espera);\n  };\n}\n\nconst buscar = debounce((valor) => {\n  document.getElementById("resultado").textContent = "Buscando: " + valor;\n}, 500);\n\ndocument.getElementById("buscador").addEventListener("input", (e) => buscar(e.target.value));', 2, 7),

(@j20, 'js-proyecto-galeria-interactiva', 'Proyecto: galería de imágenes interactiva', '<p>Construye una galería donde al hacer clic en una miniatura, la imagen se muestre en grande en un visor principal. Practica selección del DOM, event delegation y modificación de atributos <code>src</code>.</p>',
'<img id="visor" src="https://placekitten.com/400/250" width="400">\n<div id="miniaturas">\n  <img src="https://placekitten.com/100/70" data-full="https://placekitten.com/400/250">\n  <img src="https://placekitten.com/101/70" data-full="https://placekitten.com/401/250">\n</div>',
'document.getElementById("miniaturas").addEventListener("click", (e) => {\n  if (e.target.tagName === "IMG") {\n    document.getElementById("visor").src = e.target.dataset.full;\n  }\n});', 3, 12),

(@j20, 'js-proyecto-buscador-api', 'Proyecto: buscador con fetch a una API pública', '<p>Crea un buscador que consuma una API pública real (como la API de países) usando <code>fetch</code>, mostrando los resultados dinámicamente en el DOM y manejando el estado de carga y errores.</p>',
'<input type="text" id="pais" placeholder="Nombre de un país (ej: colombia)">\n<button id="buscar">Buscar</button>\n<div id="resultado"></div>',
'document.getElementById("buscar").addEventListener("click", async () => {\n  const nombre = document.getElementById("pais").value.trim();\n  const resultado = document.getElementById("resultado");\n  if (!nombre) return;\n\n  resultado.textContent = "Buscando...";\n  try {\n    const respuesta = await fetch(`https://restcountries.com/v3.1/name/${nombre}`);\n    const datos = await respuesta.json();\n    resultado.innerHTML = `<h3>${datos[0].name.common}</h3><p>Capital: ${datos[0].capital?.[0] ?? "N/A"}</p>`;\n  } catch (error) {\n    resultado.textContent = "País no encontrado.";\n  }\n});', 4, 12),

(@j20, 'js-proyecto-piedra-papel-tijera', 'Proyecto: juego de piedra, papel o tijera', '<p>El proyecto final: implementa el clásico juego contra la computadora. Practica generación aleatoria, condicionales anidados, manipulación del DOM y actualización de un marcador de puntos.</p>',
'<button data-opcion="piedra">🪨 Piedra</button>\n<button data-opcion="papel">📄 Papel</button>\n<button data-opcion="tijera">✂️ Tijera</button>\n<p id="resultado"></p>',
'const opciones = ["piedra", "papel", "tijera"];\n\ndocument.querySelectorAll("button").forEach(boton => {\n  boton.addEventListener("click", () => {\n    const jugador = boton.dataset.opcion;\n    const maquina = opciones[Math.floor(Math.random() * 3)];\n    let resultado;\n\n    if (jugador === maquina) resultado = "Empate";\n    else if (\n      (jugador === "piedra" && maquina === "tijera") ||\n      (jugador === "papel" && maquina === "piedra") ||\n      (jugador === "tijera" && maquina === "papel")\n    ) resultado = "¡Ganaste!";\n    else resultado = "Perdiste";\n\n    document.getElementById("resultado").textContent = `Tú: ${jugador} vs Máquina: ${maquina} → ${resultado}`;\n  });\n});', 5, 12);

-- ============================================================
-- Retos de código adicionales (12 nuevos, total 20)
-- ============================================================
INSERT INTO retos (slug, titulo, lenguaje, dificultad, enunciado, html_inicial, css_inicial, js_inicial, comprobacion_js, puntos, orden) VALUES

('reto-tabla-semanal', 'Tabla de horario semanal', 'html', 'facil',
 'Crea una tabla (<code>&lt;table&gt;</code>) con un encabezado de fila (<code>&lt;th&gt;</code>) y al menos 3 filas de datos (<code>&lt;tr&gt;</code> con <code>&lt;td&gt;</code>).',
 '<!-- Escribe tu tabla aquí -->\n', '', '',
 'return doc.querySelectorAll("table tr").length >= 4 && doc.querySelectorAll("th").length >= 1;',
 15, 9),

('reto-formulario-contacto', 'Formulario de contacto básico', 'html', 'medio',
 'Crea un formulario con un input de tipo email marcado como <code>required</code>, un textarea, y un botón de tipo submit.',
 '<!-- Escribe tu formulario aquí -->\n', '', '',
 'var email = doc.querySelector("input[type=email]"); return email && email.required && doc.querySelector("textarea") !== null && doc.querySelector("button, input[type=submit]") !== null;',
 20, 10),

('reto-imagen-figura', 'Imagen con leyenda', 'html', 'facil',
 'Usa <code>&lt;figure&gt;</code> y <code>&lt;figcaption&gt;</code> para mostrar una imagen con su descripción debajo.',
 '<!-- Escribe tu código aquí -->\n', '', '',
 'return doc.querySelector("figure img") !== null && doc.querySelector("figure figcaption") !== null;',
 15, 11),

('reto-grid-3-columnas', 'Grid de 3 columnas', 'css', 'medio',
 'Usa CSS Grid en <code>.contenedor</code> para mostrar los 6 elementos <code>.caja</code> en exactamente 3 columnas iguales.',
 '<div class="contenedor">\n  <div class="caja">1</div><div class="caja">2</div><div class="caja">3</div>\n  <div class="caja">4</div><div class="caja">5</div><div class="caja">6</div>\n</div>',
 '.contenedor {\n  /* agrega grid con 3 columnas aquí */\n}\n.caja { background: #6366f1; color: white; padding: 20px; text-align: center; }', '',
 'var el = doc.querySelector(".contenedor"); if (!el) return false; var s = win.getComputedStyle(el); return s.display === "grid" && s.gridTemplateColumns.split(" ").length === 3;',
 25, 12),

('reto-texto-truncado', 'Truncar texto con puntos suspensivos', 'css', 'dificil',
 'Haz que el párrafo <code>.texto</code> se corte con "..." si no cabe en una sola línea, usando <code>overflow</code>, <code>white-space</code> y <code>text-overflow</code>.',
 '<p class="texto">Este es un texto muy largo que definitivamente no va a caber en una sola línea dentro de un contenedor pequeño.</p>',
 '.texto {\n  width: 200px;\n  /* agrega las 3 propiedades necesarias */\n}', '',
 'var el = doc.querySelector(".texto"); if (!el) return false; var s = win.getComputedStyle(el); return s.overflow === "hidden" && s.textOverflow === "ellipsis" && s.whiteSpace === "nowrap";',
 30, 13),

('reto-boton-degradado', 'Botón con degradado y hover', 'css', 'medio',
 'Dale a <code>.btn</code> un fondo con <code>linear-gradient</code>, y en <code>:hover</code> cambia su <code>transform</code> a alguna escala distinta de 1.',
 '<button class="btn">Botón</button>',
 '.btn {\n  border: none;\n  padding: 12px 24px;\n  color: white;\n  /* agrega background con gradiente */\n}\n.btn:hover {\n  /* agrega transform: scale() */\n}', '',
 'var el = doc.querySelector(".btn"); if (!el) return false; var s = win.getComputedStyle(el); return s.backgroundImage.includes("gradient");',
 20, 14),

('reto-tema-oscuro-variable', 'Variable CSS para tema oscuro', 'css', 'medio',
 'Define una variable <code>--fondo</code> en <code>:root</code> y úsala en <code>background-color</code> de <code>.caja</code> con <code>var(--fondo)</code>.',
 '<div class="caja">Contenido</div>',
 ':root {\n  /* define --fondo aquí */\n}\n.caja {\n  padding: 20px;\n  /* usa var(--fondo) en background-color */\n}', '',
 'var el = doc.querySelector(".caja"); if (!el) return false; var s = win.getComputedStyle(el); return s.backgroundColor !== "rgba(0, 0, 0, 0)" && s.backgroundColor !== "transparent";',
 20, 15),

('reto-arreglo-nombres', 'Ordenar nombres alfabéticamente', 'js', 'facil',
 'Declara una función <code>ordenarNombres(arr)</code> que reciba un array de strings y lo retorne ordenado alfabéticamente (sin modificar el original).',
 '', '',
 'function ordenarNombres(arr) {\n  // usa slice() y sort()\n}',
 'try { var original = ["Carlos", "Ana", "Beto"]; var r = win.ordenarNombres(original); return Array.isArray(r) && r[0] === "Ana" && r[1] === "Beto" && r[2] === "Carlos" && original[0] === "Carlos"; } catch(e) { return false; }',
 25, 16),

('reto-objeto-persona', 'Crear un objeto y acceder a sus datos', 'js', 'facil',
 'Declara una función <code>crearPersona(nombre, edad)</code> que retorne un objeto con las propiedades <code>nombre</code> y <code>edad</code>.',
 '', '',
 'function crearPersona(nombre, edad) {\n  // retorna un objeto\n}',
 'try { var p = win.crearPersona("Ana", 25); return p && p.nombre === "Ana" && p.edad === 25; } catch(e) { return false; }',
 15, 17),

('reto-temporizador-cuenta-regresiva', 'Cuenta regresiva', 'js', 'dificil',
 'Hay un <code>span</code> con id <code>numero</code> que empieza en 5. Usando <code>setInterval</code>, haz que baje 1 cada segundo hasta llegar a 0 (no debe seguir bajando después).',
 '<span id="numero">5</span>', '',
 '// agrega tu código con setInterval aquí\n',
 'return new Promise(function(resolve){ var el = doc.getElementById("numero"); if(!el) return resolve(false); setTimeout(function(){ resolve(el.textContent.trim() === "4"); }, 1300); });',
 35, 18),

('reto-filtrar-productos', 'Filtrar productos por precio', 'js', 'medio',
 'Declara <code>productosCaros(productos, limite)</code> que reciba un array de objetos <code>{nombre, precio}</code> y retorne solo los que tengan <code>precio</code> mayor al <code>limite</code>.',
 '', '',
 'function productosCaros(productos, limite) {\n  // usa filter()\n}',
 'try { var r = win.productosCaros([{nombre:"A",precio:10},{nombre:"B",precio:50}], 20); return Array.isArray(r) && r.length === 1 && r[0].nombre === "B"; } catch(e) { return false; }',
 25, 19),

('reto-clase-rectangulo', 'Clase Rectángulo', 'js', 'dificil',
 'Crea una clase <code>Rectangulo</code> con constructor <code>(ancho, alto)</code> y un método <code>area()</code> que retorne <code>ancho * alto</code>.',
 '', '',
 'class Rectangulo {\n  // completa la clase\n}',
 'try { var r = new win.Rectangulo(4, 5); return r.area() === 20; } catch(e) { return false; }',
 30, 20);
