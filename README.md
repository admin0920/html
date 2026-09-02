# 💻 Dique Programando

Plataforma completa en PHP + MySQL para aprender **HTML, CSS y JavaScript desde 0 hasta avanzado**, lista para subir a **InfinityFree**.

## ✨ Funciones incluidas

- **37 lecciones** de HTML, CSS y JavaScript (de básico a avanzado: variables CSS, POO en JS, fetch API, accesibilidad, SEO, localStorage, etc.) repartidas en 15 módulos.
- Editor de código en vivo (HTML/CSS/JS) con vista previa instantánea en cada lección.
- **🗺️ Roadmap personalizado**: cada usuario elige un plan de estudio (Relajado / Regular / Intensivo) que va desbloqueando lecciones con el tiempo, o activa el **Modo PRO** para desbloquear todo al instante.
- **🎯 Retos de código**: mini ejercicios de HTML/CSS/JS con comprobación automática en el navegador (sin backend de ejecución de código).
- **🔬 Laboratorio de práctica**: 5 proyectos reales (tarjeta de perfil, galería con Grid, to-do list, calculadora, landing page) con guardado de progreso y checklist de requisitos.
- **🎮 Arcade de 3 juegos educativos**: "Ordena el código", "Detective de bugs" y "Quiz relámpago", con tabla de mejores puntajes.
- **🏅 Sistema de insignias** (15 logros) por lecciones, racha, retos, laboratorios y cursos completos, con bonus de puntos.
- **Sandbox** libre tipo CodePen para practicar y guardar proyectos propios.
- Registro / inicio de sesión de usuarios con contraseñas cifradas (`password_hash`).
- Seguimiento de progreso por lección y por curso (barras de progreso), puntos y racha de días de estudio.
- Quizzes de repaso al final de las lecciones, con resultados guardados.
- Panel de administración (`/admin`) para gestionar cursos, módulos, lecciones, quizzes, **retos, laboratorios** y usuarios sin tocar código.
- Diseño 100% responsive (menú hamburguesa en móvil, grids adaptables), sin dependencias de Composer/Node — solo PHP plano compatible con hosting compartido.

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
Entra a **phpMyAdmin** desde el vPanel, selecciona tu base de datos, ve a la pestaña **Importar** y sube el archivo `config/database.sql` (incluye todas las tablas y el contenido: 37 lecciones, retos, laboratorios e insignias, más un usuario administrador).

> ¿Ya tenías la plataforma instalada antes de esta actualización? No vuelvas a importar `database.sql` completo (fallaría porque las tablas ya existen). En su lugar, importa solo `config/actualizacion_v2.sql`, que agrega las tablas y lecciones nuevas sin tocar lo que ya tienes.

### 4. Configura la conexión
Edita `config/config.php` y reemplaza:

```php
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'TU_PASSWORD_AQUI');
define('DB_NAME', 'if0_XXXXXXXX_diqueprogramando');
define('SITE_URL', 'https://tudominio.infinityfreeapp.com');
```

### 5. Sube los archivos
Sube **todo el contenido** de esta carpeta (no la carpeta en sí) dentro de `htdocs/` usando el **Administrador de archivos** del vPanel o un cliente FTP (FileZilla) con los datos FTP que te da InfinityFree.

### 6. Accede al panel de administración
Ve a `https://tudominio.com/login.php` e inicia sesión con el usuario administrador ya incluido en el seed:

- **Email:** `admin@diqueprogramando.com`
- **Contraseña:** `Admin123!`

⚠️ **Cambia esta contraseña inmediatamente** (puedes registrarte con un nuevo usuario, hacerlo admin desde `/admin/usuarios.php`, y luego eliminar o quitarle el rol admin al usuario por defecto).

### 7. ¡Listo!
Desde `/admin` puedes crear más cursos, módulos, lecciones y quizzes sin tocar código.

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
