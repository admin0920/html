# 💻 Dique Programando

Plataforma completa en PHP + MySQL para aprender **HTML, CSS y JavaScript desde 0 hasta avanzado**, lista para subir a **InfinityFree**.

## ✨ Funciones incluidas

- **153 lecciones** (51 de HTML, 51 de CSS, 51 de JavaScript) de 0 a avanzado, repartidas en 39 módulos: desde la primera etiqueta hasta POO, fetch API, closures, Grid avanzado, accesibilidad, proyectos finales, etc.
- **Editor tipo IDE** (CodeMirror: números de línea, resaltado de sintaxis, tema oscuro) en cada lección, reto, laboratorio y en el Sandbox, con vista previa instantánea y una **consola integrada** (como las DevTools del navegador) que muestra `console.log()`, `console.warn()` y errores en tiempo real. Atajo `Ctrl+Enter` para ejecutar.
- **🔑 Inicio de sesión con Google** (OAuth 2.0) además de correo/contraseña — ideal para una plataforma multiusuario.
- **🗺️ Roadmap personalizado**: cada usuario elige un plan de estudio (Relajado / Regular / Intensivo) que va desbloqueando lecciones con el tiempo, o activa el **Modo PRO** para desbloquear todo al instante.
- **🎯 20 retos de código**: mini ejercicios de HTML/CSS/JS con comprobación automática en el navegador (sin backend de ejecución de código).
- **🔬 Laboratorio de práctica**: 5 proyectos reales (tarjeta de perfil, galería con Grid, to-do list, calculadora, landing page) con guardado de progreso y checklist de requisitos.
- **🎮 Arcade de 3 juegos educativos**: "Ordena el código", "Detective de bugs" y "Quiz relámpago", con tabla de mejores puntajes.
- **🏅 Sistema de insignias** (15 logros) por lecciones, racha, retos, laboratorios y cursos completos, con bonus de puntos.
- **Sandbox** libre tipo CodePen para practicar y guardar proyectos propios.
- Registro / inicio de sesión de usuarios con contraseñas cifradas (`password_hash`).
- Seguimiento de progreso por lección y por curso (barras de progreso), puntos y racha de días de estudio.
- Quizzes de repaso al final de las lecciones, con resultados guardados.
- Panel de administración en `/admin` (**sin enlace visible en el sitio** — se accede solo escribiendo la URL) para gestionar cursos, módulos, lecciones, quizzes, retos, laboratorios y usuarios sin tocar código.
- Diseño 100% responsive (menú hamburguesa en móvil, grids adaptables) y botones con estados hover/focus pulidos, sin dependencias de Composer/Node — solo PHP plano compatible con hosting compartido.
- Marca propia integrada: logo e isotipo en la barra de navegación, favicon (todas las resoluciones, incluido apple-touch-icon), login/registro, footer y panel de administración.

## 🎨 Cambiar el logo

El archivo fuente vive en `icono/icono.png` (bloqueado al público por `.htaccess`). A partir de él se generaron automáticamente:

- `assets/img/isotipo.png` — el ícono circular recortado en alta resolución (sin el texto "Dique Programando"), usado como fuente para generar los favicons.
- `assets/img/isotipo-web.png` — versión liviana (260×260, ~65 KB) del isotipo, la que realmente se usa en la navbar, login, footer y hero para no cargar cada página con una imagen pesada.
- `assets/img/favicon-16.png` a `favicon-512.png` — todos los tamaños de favicon / apple-touch-icon.
- `assets/img/logo-completo.png` — el logotipo completo con el texto, por si lo necesitas en algún lugar.

Si más adelante quieres cambiar el logo, reemplaza `icono/icono.png` y pide que se regeneren estos archivos.

## 🚀 Cómo subir esto a InfinityFree

### 1. Crea tu cuenta y sitio en InfinityFree
Crea tu hosting gratuito en https://infinityfree.com y anota el dominio/subdominio asignado.

### 2. Crea la base de datos MySQL
En el **vPanel** de InfinityFree → **MySQL Databases** → crea una base de datos. Anota:
- Host (ej. `sqlXXX.infinityfree.com`)
- Usuario (ej. `if0_XXXXXXXX`)
- Contraseña
- Nombre de la base de datos (ej. `if0_XXXXXXXX_diqueprogramando`)

### 3. Importa el esquema
Entra a **phpMyAdmin** desde el vPanel, selecciona tu base de datos, ve a la pestaña **Importar** y sube el archivo `config/database.sql` (incluye todas las tablas y el contenido: 153 lecciones, 20 retos, 5 laboratorios e insignias, más un usuario administrador).

> ¿Ya tenías la plataforma instalada antes de esta actualización? No vuelvas a importar `database.sql` completo (fallaría porque las tablas ya existen). En su lugar importa, **en este orden**, los archivos de actualización que te falten:
> 1. `config/actualizacion_v2.sql` (si venías de la primera versión: agrega roadmap, retos, laboratorios e insignias)
> 2. `config/actualizacion_v3.sql` (agrega el login con Google y las 116 lecciones y retos nuevos que llevan el total a 153 lecciones y 20 retos)

### 4. Configura la conexión
Edita `config/config.php` y reemplaza:

```php
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'TU_PASSWORD_AQUI');
define('DB_NAME', 'if0_XXXXXXXX_diqueprogramando');
define('SITE_URL', 'https://tudominio.infinityfreeapp.com');
```

### 5. (Opcional) Activa el inicio de sesión con Google
1. Ve a https://console.cloud.google.com/apis/credentials y crea un proyecto.
2. Configura la pantalla de consentimiento OAuth (tipo "Externo") con el nombre de tu sitio.
3. Crea credenciales → **ID de cliente de OAuth** → tipo "Aplicación web".
4. En "URIs de redireccionamiento autorizados" agrega exactamente tu `SITE_URL` + `/auth_google_callback.php` (ej. `https://tudominio.infinityfreeapp.com/auth_google_callback.php`).
5. Copia el **Client ID** y **Client Secret** en `config/config.php`:

```php
define('GOOGLE_CLIENT_ID', 'tu-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'tu-secreto');
```

Si dejas estos dos valores vacíos (como vienen por defecto), el botón "Continuar con Google" simplemente no aparece y el sitio funciona normal solo con correo/contraseña.

> ⚠️ **Importante sobre InfinityFree**: el login con Google necesita que el servidor haga peticiones salientes (cURL) a los servidores de Google. El plan **gratuito** de InfinityFree a veces bloquea este tipo de conexiones salientes para prevenir abusos. Si configuras todo correctamente y el botón de Google sigue sin funcionar, ese es probablemente el motivo — no es un error del código. El resto de la plataforma no depende de esto y sigue funcionando perfecto.

### 6. Sube los archivos
Sube **todo el contenido** de esta carpeta (no la carpeta en sí) dentro de `htdocs/` usando el **Administrador de archivos** del vPanel o un cliente FTP (FileZilla) con los datos FTP que te da InfinityFree.

### 7. Accede al panel de administración
El panel de administración **no tiene ningún enlace visible** en el sitio público (es intencional, por seguridad). Para entrar, ve directamente a `https://tudominio.com/admin/` e inicia sesión con el usuario administrador ya incluido en el seed:

- **Email:** `admin@diqueprogramando.com`
- **Contraseña:** `Admin123!`

⚠️ **Cambia esta contraseña inmediatamente** (puedes registrarte con un nuevo usuario, hacerlo admin desde `/admin/usuarios.php`, y luego eliminar o quitarle el rol admin al usuario por defecto).

### 8. ¡Listo!
Desde `/admin` puedes crear más cursos, módulos, lecciones, retos, laboratorios y quizzes sin tocar código.

## 📁 Estructura del proyecto

```
config/          Configuración y esquema SQL
includes/        Conexión a BD, autenticación, funciones, header/footer
assets/          CSS y JS
admin/           Panel de administración
index.php        Página de inicio
cursos.php       Listado de cursos
curso.php        Detalle de un curso (módulos y lecciones)
leccion.php      Lección con editor de código en vivo
quiz.php         Cuestionario de repaso
sandbox.php      Editor libre HTML/CSS/JS con guardado de proyectos
perfil.php       Perfil del usuario y progreso
register.php / login.php / logout.php   Autenticación
```

## 🔒 Seguridad

- Todas las consultas usan sentencias preparadas (`mysqli` con `bind_param`) para prevenir inyección SQL.
- Contraseñas guardadas con `password_hash()` / `password_verify()`.
- Formularios protegidos con token CSRF.
- Carpeta `config/` bloqueada por `.htaccess` para que nadie pueda descargar `config.php` ni `database.sql`.

## 🛠 Requisitos

- PHP 7.4+ con extensión `mysqli` (InfinityFree lo trae por defecto).
- MySQL / MariaDB.
- No requiere Composer, Node ni acceso SSH — 100% compatible con hosting compartido gratuito.
