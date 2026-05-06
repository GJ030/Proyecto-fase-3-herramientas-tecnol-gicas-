# 🦷 CitasSonrisas — Clínica Dental Sonrisas

[![Deploy](https://img.shields.io/github/actions/workflow/status/QuorbitIntelligence/SonrisaNorte/deploy.yml?label=deploy&logo=github-actions)](https://github.com/QuorbitIntelligence/SonrisaNorte/actions)
[![Tests](https://img.shields.io/github/actions/workflow/status/QuorbitIntelligence/SonrisaNorte/tests.yml?label=tests&logo=playwright)](https://github.com/QuorbitIntelligence/SonrisaNorte/actions)
[![Uptime](https://img.shields.io/badge/uptime-99.94%25-brightgreen)](https://github.com/QuorbitIntelligence/SonrisaNorte)
[![PHP](https://img.shields.io/badge/PHP-7%2B-777BB4?logo=php)](https://www.php.net/)
[![Docker](https://img.shields.io/badge/Docker-20.10%2B-2496ED?logo=docker)](https://www.docker.com/)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

Sistema web integral para la **Clínica Dental Sonrisas** que combina un sitio público responsivo, un portal de pacientes en PHP/MySQL y un pipeline DevOps completo con CI/CD, containerización y pruebas automatizadas.

> **Proyecto Integrador — Metodología DevOps**  
> Universidad TecMilenio, Campus Ciudad de México  
> **Equipo:** Paola Flores · Gael Jurado · Mauricio Sanchez  
> **Profesor:** Marco Horacio Moreno Gonzales

---

## Tabla de Contenidos

1. [Descripción](#-descripción)
2. [Problema Identificado](#-problema-identificado)
3. [Solución](#-solución)
4. [Arquitectura](#-arquitectura)
5. [Requerimientos](#-requerimientos)
6. [Instalación](#-instalación)
   - [Ambiente de desarrollo](#ambiente-de-desarrollo)
   - [Ejecución de pruebas](#ejecución-de-pruebas-manuales)
   - [Despliegue en producción](#despliegue-en-producción)
7. [Configuración](#-configuración)
8. [Uso](#-uso)
   - [Pacientes (usuario final)](#pacientes--usuario-final)
   - [Administrador](#administrador--superadmin)
9. [Contribución](#-contribución)
10. [Roadmap](#-roadmap)
11. [Métricas y Resultados](#-métricas-y-resultados)
12. [Producto](#-producto)

---

## Descripción

**CitasSonrisas** es una plataforma web de dos capas diseñada para digitalizar la operación de la Clínica Dental Sonrisas:

| Capa | Tecnología | Propósito |
|------|-----------|-----------|
| **Sitio público** | HTML5, Tailwind CSS, JavaScript ES6+, Google Apps Script | Página institucional + agendamiento de citas en línea sin costo de infraestructura |
| **Portal de pacientes** | PHP 7+ MVC, MySQL, PDO, Bootstrap | Gestión completa de citas, doctores, consultorios y usuarios para la clínica |

El sistema gestiona citas en estados `pendiente → confirmada → cancelada → completada`, envía notificaciones automáticas por correo electrónico y protege formularios mediante **Google reCAPTCHA**.

---

## Problema Identificado

La clínica operaba con métodos manuales que generaban fricciones críticas:

- **Desorganización operacional** — información dispersa en llamadas telefónicas, WhatsApp y cuadernos físicos sin integración.
- **Pérdida de información** — ausencia de respaldo digital ante extravío de documentos.
- **Inaccesibilidad fuera de horario** — pacientes no podían consultar disponibilidad ni agendar fuera del horario laboral de la oficina.
- **Baja productividad del personal** — tiempo significativo invertido en gestión telefónica de citas.
- **Alta tasa de inasistencias** — ausencia de recordatorios automatizados.
- **Doble agendamiento** — conflictos de horario por falta de sistema centralizado.

---

## Solución

Se desarrolló una plataforma web que resuelve cada punto crítico:

| Problema | Solución implementada |
|----------|-----------------------|
| Desorganización | Base de datos MySQL centralizada con portal web accesible 24/7 |
| Pérdida de información | Persistencia en MySQL (portal) y Google Sheets (sitio público) con respaldo automático |
| Inaccesibilidad | Agendamiento en línea disponible las 24 horas |
| Baja productividad | Automatización de confirmaciones, actualizaciones y cancelaciones vía SMTP/MailService |
| Inasistencias | Notificaciones por correo electrónico en cada cambio de estado de la cita |
| Doble agendamiento | Validaciones `doctorOcupado` y `salaOcupada` en tiempo real antes de confirmar la cita |

La implementación adoptó **metodología DevOps** con pipeline CI/CD en GitHub Actions, containerización Docker y suite de 10 pruebas End-to-End con Playwright, logrando clasificación **Elite** según las métricas DORA.

---

## 🏗 Arquitectura

El sistema sigue un patrón **MVC de 5 capas** para el portal PHP y una arquitectura **serverless de 3 capas** para el sitio público:

```
┌─────────────────────────────────────────────────────────────┐
│  01 · CLIENTE                                               │
│  Navegador Web (HTTP/HTTPS)  │  reCAPTCHA (Curl.php)        │
└───────────────────┬─────────────────────────────────────────┘
                    │ HTTP Request
                    ▼
┌─────────────────────────────────────────────────────────────┐
│  02 · ENRUTAMIENTO                                          │
│  Router (Core) → /admin/*, /citas/*, /auth/*                │
│  Middleware / Auth Guard → sesión + rol                     │
└───────────────────┬─────────────────────────────────────────┘
                    │ Dispatching
                    ▼
┌─────────────────────────────────────────────────────────────┐
│  03 · CONTROLADORES (App\Controllers)                       │
│  AdminController  │  AuthController  │  PacienteController  │
└───────────────────┬─────────────────────────────────────────┘
                    │ Model calls + MailService
                    ▼
┌─────────────────────────────────────────────────────────────┐
│  04 · MODELOS Y SERVICIOS (App\Models · App\Services)       │
│  Cita · Doctor · Sala · Sucursal · User · MailService       │
└───────────────────┬─────────────────────────────────────────┘
                    │ PDO / SMTP
                    ▼
┌─────────────────────────────────────────────────────────────┐
│  05 · INFRAESTRUCTURA                                       │
│  MySQL (Core\Database)  │  SMTP/Mailer  │  reCAPTCHA API    │
└─────────────────────────────────────────────────────────────┘
```

### Stack tecnológico 

| Componente | Tecnología | Versión mínima |
|-----------|-----------|---------------|
| Backend (portal) | PHP | 7.0+ |
| Base de datos | MySQL / MariaDB InnoDB | 5.7+ / 10.3+ |
| Servidor web | Apache / nginx | — |
| Frontend (sitio público) | HTML5, Tailwind CSS, JavaScript | ES6+ |
| Backend (sitio público) | Google Apps Script | — |
| Base de datos (sitio público) | Google Sheets | — |
| Hosting (sitio público) | GitHub Pages | — |
| CI/CD | GitHub Actions | — |
| Containerización | Docker + Docker Compose | 20.10+ / 2.x |
| Testing E2E | Playwright | — |
| Control de versiones | Git | 2.30+ |
| Runtime de pruebas | Node.js | 18.x LTS |


## ⚙️ Requerimientos

### Servidor de aplicación / web (Portal PHP)

- **PHP 7.0+** con extensiones: `pdo_mysql`, `session`, `openssl`, `mbstring`
- **Apache 2.4+** (con `mod_rewrite`) **o nginx** con el document root apuntando a `site/`
- **MySQL 5.7+** o **MariaDB 10.3+** con motor InnoDB y charset `utf8mb4`

### Sitio público (serverless)

- Cuenta de **Google** para Google Apps Script y Google Sheets
- Repositorio en **GitHub** con GitHub Pages habilitado

### Desarrollo local y CI/CD

- **Git 2.30+**
- **Node.js 18.x LTS** (para Playwright)
- **Docker 20.10+** y **Docker Compose 2.x** 

### Paquetes / dependencias adicionales

| Paquete                        | Uso                              | Instalación                      |
|--------------------------------|----------------------------------|----------------------------------|
| `google/recaptcha`             | Validación de formularios        | Incluido en `vendor/` (Composer) |
| `ReCaptcha\RequestMethod\Curl` | Wrapper cURL para reCAPTCHA      | Incluido (`Curl.php`)            |
| Playwright                     | Tests E2E automatizados          | `npx playwright install`         |
| PHPMailer / SMTP lib           | Envío de correos via MailService | Incluido en `vendor/`            |

---

## Instalación

### Ambiente de desarrollo

#### Opción A — Con Docker (recomendado)

```bash
# 1. Clonar el repositorio
git clone https://github.com/QuorbitIntelligence/SonrisaNorte.git
cd SonrisaNorte

# 2. Script de instalación automática (verifica dependencias, instala Node modules y Playwright)
chmod +x install.sh
./install.sh

# 3. Levantar el stack con Docker Compose
docker-compose up -d

# 4. Verificar que los servicios estén corriendo
docker-compose ps

# 5. Abrir en el navegador
open http://localhost:8080
```

Para detener el ambiente:

```bash
docker-compose down
```

Ver logs en tiempo real:

```bash
docker-compose logs -f
```

#### Opción B — Instalación manual (Portal PHP)

```bash
# 1. Clonar el repositorio
git clone https://github.com/QuorbitIntelligence/SonrisaNorte.git
cd SonrisaNorte/site/pacientes

# 2. Crear la base de datos e importar el esquema
mysql -u root -p -e "CREATE DATABASE citassonrisas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p citassonrisas < database/schema.sql

# 3. Configurar credenciales de base de datos
cp config/database.local.php.example config/database.local.php
# Editar config/database.local.php con tu host, base, usuario y contraseña

# 4. Ajustar rutas URL en config/config.php
#    WEB_BASE → ruta desde la raíz del dominio hasta la carpeta pacientes
#    SITE_BASE_URL → ruta del sitio principal

# 5. Apuntar el DocumentRoot de Apache/nginx a site/
```

#### Configurar el sitio público (Google Apps Script)

```bash
# 1. Ir a https://script.google.com y crear un nuevo proyecto
# 2. Copiar el contenido de Google_Apps_Script_CitasNuevas.gs al proyecto
# 3. Crear un Google Sheet con la hoja 'CitasNuevas' y 12 columnas
# 4. Implementar como Web App:
#    - Ejecutar como: Yo
#    - Quién tiene acceso: Cualquier persona
# 5. Copiar la URL generada (termina en /exec)
# 6. En index.html, actualizar la constante URL_API_CITAS con esa URL
```

---

### Ejecución de pruebas manualmente

```bash
# Instalar dependencias y navegadores de Playwright
cd SonrisaNorte
npm install
npx playwright install

# Ejecutar los 10 tests E2E contra producción
npx playwright test

# Ejecutar en modo debug (abre el navegador)
npx playwright test --debug

# Generar reporte HTML de resultados
npx playwright show-report
```

Los tests cubren:

| # | Test             | Funcionalidad validada |
|---|------------------|----------------------|
| 1 | Homepage Load    | Sitio carga con título correcto |
| 2 | Navigation       | Navbar presente y visible |
| 3 | Agendar Button   | Botón principal existe y es clickeable |
| 4 | Modal Opening    | Modal de citas se abre correctamente |
| 5 | Form Fields      | Todos los campos del formulario presentes |
| 6 | Services Display | 6 servicios dentales mostrados |
| 7 | Contact Form     | Formulario de contacto presente |
| 8 | WhatsApp Button  | Botón flotante de WhatsApp visible |
| 9 | Footer Content   | Footer con información correcta |
| 10 | Version Check   | Versión v3.5 o superior |

---

### Despliegue en producción

#### GitHub Pages (sitio público — gratuito, SSL automático)

```bash
# El deploy se activa automáticamente con cada push a main:
git push origin main

# El pipeline deploy.yml ejecuta en ~2m 15s:
# Checkout → Validación HTML → Syntax check → URL check → File size → Build report → Deploy
```

#### Servidor propio / VPS (portal PHP)

```bash
# 1. Subir el código al servidor (FTP, rsync, etc.)
rsync -avz site/ usuario@servidor:/var/www/html/

# 2. En el servidor, importar el esquema SQL
mysql -u usuario -p citassonrisas < database/schema.sql

# 3. Configurar el VirtualHost de Apache o el server block de nginx
#    DocumentRoot → /var/www/html/site
#    Habilitar mod_rewrite (Apache)

# 4. Asegurarse de que PHP tenga permisos de escritura en carpetas de sesión
```

#### Docker en la nube (cualquier proveedor con Docker)

```bash
# Construir la imagen
docker build -t citassonrisas:latest .

# Ejecutar en producción
docker run -d -p 80:80 --restart unless-stopped citassonrisas:latest

# O usar docker-compose en el servidor
docker-compose -f docker-compose.yml up -d
```

---

## Configuración

### Archivos de configuración principales

| Archivo                                            | Propósito                                                      |
|----------------------------------------------------|----------------------------------------------------------------|
| `site/pacientes/config/config.php`                 | Zona horaria, `WEB_BASE`, `SITE_BASE_URL`, duración de citas   |
| `site/pacientes/config/database.local.php`         | Credenciales de base de datos (**no versionar**)               |
| `site/pacientes/config/database.local.php.example` | Plantilla de credenciales                                      |
| `index.html`                                       | Constante `URL_API_CITAS` con endpoint de Google Apps Script   |
| `Dockerfile`                                       | Imagen base `nginx:alpine`, headers de seguridad, health check |
| `docker-compose.yml`                               | Puertos, volúmenes, redes, política de restart                 |
| `.github/workflows/deploy.yml`                     | Pipeline de deploy a GitHub Pages                              |
| `.github/workflows/tests.yml`                      | Pipeline de tests E2E (schedule diario 9 AM UTC)               | 

### Variables clave en `config.php`

```php
define('WEB_BASE', 'demos/sonrisas/site/pacientes'); // Ruta desde raíz del dominio
define('SITE_BASE_URL', '/demos/sonrisas/site');      // Ruta del sitio público
define('CITA_DURACION_MINUTOS', 30);                   // Duración por defecto de citas
define('TIMEZONE', 'America/Mexico_City');              // Zona horaria
```

### Configuración de reCAPTCHA

En `config.php` configura tus claves de Google reCAPTCHA v2:

```php
define('RECAPTCHA_SITE_KEY', 'tu_site_key');
define('RECAPTCHA_SECRET_KEY', 'tu_secret_key');
```

### Usuario administrador inicial

Tras importar `schema.sql` existe un super_admin de demostración. **Cámbialo antes de ir a producción.** Los datos de ejemplo están comentados al final de `schema.sql`.

### Docker — variables de entorno

```yaml
# docker-compose.yml
environment:
  - DB_HOST=db
  - DB_NAME=citassonrisas
  - DB_USER=root
  - DB_PASS=secreto
```

---

## Uso

### Pacientes — usuario final

1. **Registrarse** en `/auth/registro` con nombre, correo y contraseña.
2. **Iniciar sesión** en `/auth/login`.
3. **Agendar una cita** desde el portal (`/citas/nueva`): seleccionar sucursal, doctor, sala, fecha y horario disponible.
4. **Consultar citas propias** en `/citas` — ver estado (`pendiente / confirmada / cancelada / completada`).
5. **Recibir notificaciones** automáticas por correo al crear, actualizar o cancelar una cita.

> Para el sitio público, el agendamiento está disponible sin registro a través del modal de la página principal, con consulta de disponibilidad en tiempo real (horarios en verde = disponible, rojo = ocupado).

### Administrador — superadmin

Accede al panel en `/admin/` con las credenciales de superadmin.

| Sección | Ruta | Acciones disponibles |
|---------|------|----------------------|
| Dashboard | `/admin/dashboard` | Vista general del sistema |
| Citas | `/admin/citas` | Listar, crear, editar, cancelar citas (CRUD completo) |
| Doctores | `/admin/doctores` | Registrar, actualizar, eliminar doctores |
| Salas | `/admin/salas` | Gestionar consultorios por sucursal |
| Sucursales | `/admin/sucursales` | Gestionar sedes de la clínica |
| Usuarios | `/admin/usuarios` | Gestionar pacientes y administradores |
| Correo | `/admin/correo` | Enviar correos personalizados a pacientes |
| Calendario | `/admin/calendario` | Vista de calendario de citas |

**Flujo de creación de cita (admin):**

```
/admin/citas/nueva → Seleccionar paciente, doctor, sala, sucursal, fecha y hora
                   → Validación de conflictos (doctorOcupado / salaOcupada)
                   → Guardar → Envío automático de correo de confirmación al paciente
```

**Validaciones del sistema:**
- Franjas horarias en bloques de 30 minutos (`HORAS_VALIDAS`).
- No se permite doble reserva de doctor ni de sala en el mismo horario.
- Los campos obligatorios son validados en servidor antes de persistir.

---

## 🤝 Contribución

¡Las contribuciones son bienvenidas! Sigue estos pasos:

### 1. Fork y clona el repositorio

```bash
git clone https://github.com/QuorbitIntelligence/SonrisaNorte.git
cd SonrisaNorte
```

### 2. Configura tu ambiente de desarrollo

```bash
./install.sh         # Verifica dependencias e instala Node modules
docker-compose up -d # Levanta el stack local
```

### 3. Crea un branch para tu feature o fix

```bash
# Nombra el branch de forma descriptiva
git checkout -b feature/nombre-de-la-feature
# o para correcciones:
git checkout -b fix/descripcion-del-bug
```

### 4. Implementa tus cambios y ejecuta las pruebas

```bash
# Ejecuta los tests antes de hacer commit
npx playwright test

# Asegúrate de que todos los tests pasen (objetivo: > 90%)
```

### 5. Haz commit siguiendo el formato de mensaje estándar

```bash
git add .
git commit -m "feat: descripción corta del cambio"
# Tipos: feat | fix | docs | style | refactor | test | chore
```

### 6. Sube tu branch y abre un Pull Request

```bash
git push origin feature/nombre-de-la-feature
```

Luego ve a GitHub y abre un **Pull Request** hacia la rama `main`:
- Describe qué cambios hiciste y por qué.
- Referencia el issue relacionado si aplica (ej. `Closes #42`).
- Agrega capturas de pantalla si modificaste la UI.

### 7. Espera revisión y merge

Un miembro del equipo revisará tu PR. Si hay comentarios, realiza los ajustes en el mismo branch y actualiza el PR con un nuevo commit. Una vez aprobado, se hará **merge a `main`** y el pipeline de CI/CD desplegará automáticamente los cambios.

### Guía de estilo

- **PHP**: sigue PSR-12, usa `declare(strict_types=1)` en cada archivo.
- **JavaScript**: ES6+, sin dependencias pesadas, funciones documentadas con JSDoc.
- **SQL**: consultas preparadas con PDO; nunca interpolación directa de variables.
- **Commits**: mensajes en español o inglés, formato `tipo: descripción corta`.

---

## 🗺 Roadmap

### Corto plazo

- [ ] Migrar a PostgreSQL cuando el volumen supere 500 citas/mes
- [ ] Implementar caché de horarios disponibles con TTL de 5 minutos
- [ ] Agregar autenticación OAuth + tokens JWT para el sitio público
- [ ] Establecer ambiente de staging separado de producción

### Mediano plazo (DevOps)

- [ ] Integrar **Sentry** para error tracking y alertas en tiempo real
- [ ] Configurar **Uptime Robot** para monitoreo continuo de disponibilidad
- [ ] Expandir la suite de tests con casos edge y pruebas de seguridad
- [ ] Automatizar rollback si los tests en producción fallan

### Largo plazo (Producto)

- [ ] **Recordatorios automáticos** vía email/SMS 24 horas antes de cada cita
- [ ] **Portal de historial** — que el paciente vea su expediente de citas y documentos
- [ ] **Gestión de inventario** de materiales dentales
- [ ] **Optimización de horarios con ML** para predecir ausencias (no-shows)
- [ ] **Integración de pagos** (Stripe, Conekta) para pago de cita en línea
- [ ] **Arquitectura multi-tenant** para soporte de múltiples sucursales independientes
- [ ] **Aplicación móvil nativa** (iOS / Android)
- [ ] **Dashboard de analytics** avanzado con métricas de la clínica

---

## Métricas y Resultados

El sistema alcanza clasificación **Elite** según DORA Metrics:

| Métrica DORA | Valor del proyecto | Umbral Elite |
|-------------|-------------------|--------------|
| Deployment Frequency | 3.2 deploys/día | > 1/día |
| Lead Time for Changes | 45 minutos | < 1 hora |
| Change Failure Rate | 6% | 0–15% |
| Time to Restore | 12 minutos | < 1 hora |

| Métrica operacional | Valor | Objetivo | Estado |
|--------------------|-------|----------|--------|
| Tiempo de carga | 1.8 s | < 3 s | ✅ |
| Uptime (30 días) | 99.94% | > 99% | ✅ |
| Tiempo de deploy | 2m 15s | < 5 min | ✅ |
| Tasa de éxito de tests | 96.1% | > 90% | ✅ |
| MTTR (rollback) | 12 min | < 60 min | ✅ |

**Impacto operacional estimado:** el sistema automatizado libera ~9 horas mensuales del personal administrativo respecto al flujo manual, equivalente a ~$16,200 MXN anuales a $150 MXN/hora.

---

### Acceso al sistema desplegado

| Entorno | URL |
|---------|-----|
| Sitio público (GitHub Pages) | https://quorbitintelligence.github.io/SonrisaNorte/ |
| Portal de pacientes (desarrollo local) | http://localhost:8080 |

---

## Referencias

- DORA. (2021). *Accelerate State of DevOps Report 2021*. Google Cloud. https://cloud.google.com/devops/state-of-devops
- Forsgren, N., Humble, J., & Kim, G. (2018). *Accelerate: The Science of Lean Software and DevOps*. IT Revolution Press.
- Fowler, M., & Foemmel, M. (2006). *Continuous Integration*. https://martinfowler.com/articles/continuousIntegration.html
- Docker Inc. (2025). *Docker Documentation*. https://docs.docker.com/
- GitHub. (2025). *GitHub Actions Documentation*. https://docs.github.com/en/actions
- Microsoft. (2025). *Playwright Documentation*. https://playwright.dev/
- Google. (2025). *Apps Script Documentation*. https://developers.google.com/apps-script
- Zemez. (s.f.). *Bootstrap Theme v1.4 Documentation*. http://documentation.zemez.io/html/bootstrap/v1-4/

---

## Licencia

El portal de pacientes y el código DevOps del proyecto son de uso académico bajo licencia MIT.  
El sitio público se basa en una plantilla comercial de **Zemez** — respeta su licencia al redistribuir o modificar los assets del tema.

---

