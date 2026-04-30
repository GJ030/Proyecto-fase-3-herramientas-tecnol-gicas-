# CitasSonrisas — Clínica Dental Sonrisas

Proyecto web para la **Clínica Dental Sonrisas**: sitio público (plantilla Bootstrap) y **portal de pacientes** en PHP con gestión de citas, doctores y consultorios.

## Contenido del repositorio

El código desplegable vive en la carpeta del paquete (por ejemplo `1519369-1582898120814_prod-27728`):

| Ruta | Descripción |
|------|-------------|
| `…/site/` | Sitio estático (inicio, servicios, contacto, etc.) y recursos CSS/JS. |
| `…/site/pacientes/` | Aplicación **Portal de Pacientes** (PHP + MySQL). |
| `…/sources/` | Fuentes SCSS de la plantilla (compilación opcional del tema). |

Documentación de la plantilla del sitio público: [Bootstrap Theme — Zemez](http://documentation.zemez.io/html/bootstrap/v1-4/).

## Requisitos

- **PHP** 7 o superior (extensiones: `pdo_mysql`, sesiones).
- **MySQL** 5.7+ / MariaDB con InnoDB y `utf8mb4`.
- Servidor web (**Apache** o **nginx**) con el document root apuntando a `site/` (o a la URL que definas en la configuración).

## Portal de pacientes (`site/pacientes/`)

### Funcionalidad

- **Pacientes**: registro, inicio de sesión, solicitud de citas, consulta de citas propias.
- **Super administrador**: panel en `admin/` para doctores, consultorios, pacientes, citas y horario de la clínica.
- Citas con estados: pendiente, confirmada, cancelada, completada.
- Franjas según horario de clínica, doctores y consultorios; duración por defecto configurable en `config/config.php` (`CITA_DURACION_MINUTOS`).

### Instalación rápida

1. **Base de datos**  
   Crea una base MySQL vacía y ejecuta el script:

   `site/pacientes/database/schema.sql`

2. **Credenciales**  
   Copia el ejemplo y edítalo con tu host, nombre de base, usuario y contraseña:

   ```
   site/pacientes/config/database.local.php.example
   → site/pacientes/config/database.local.php
   ```

   Añade `database.local.php` a `.gitignore` si versionas el proyecto; no subas credenciales reales.

3. **Rutas URL**  
   En `site/pacientes/config/config.php` ajusta:

   - `WEB_BASE`: ruta desde la raíz del dominio hasta la carpeta `pacientes` (sin barra inicial).
   - `SITE_BASE_URL`: ruta del sitio principal para enlazar CSS/JS e imágenes del `index.html` del sitio público.

   Los valores por defecto del archivo asumen un despliegue en subcarpetas tipo `/demos/sonrisas/…`; cámbialos si sirves la app desde la raíz del dominio o otra estructura.

4. **Zona horaria**  
   Definida en `config.php` (`America/Mexico_City`); modifícala si la clínica opera en otra zona.

### Usuario administrador inicial

Tras importar `schema.sql` existe un usuario **super_admin** de ejemplo. **Cámbialo en producción** (email y contraseña). Los datos por defecto están comentados al final del propio `schema.sql` (email y contraseña de demostración).

## Desarrollo

- El portal usa **PDO** con consultas preparadas y sesiones PHP.
- Estilos del portal: `site/pacientes/assets/estilo.css`.
- Lógica compartida de citas: `site/pacientes/includes/citas_helper.php`.

## Licencia y plantilla

El sitio público se basa en una plantilla comercial (Zemez); respeta su licencia al redistribuir o modificar assets del tema.

---

*Proyecto: CitasSonrisas — AneviSoft / tecmi.*
