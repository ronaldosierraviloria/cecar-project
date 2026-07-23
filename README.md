<div align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  <h1 align="center">🎓 Sistema de Gestión de Trabajos de Grado</h1>
  <p align="center">
    Plataforma integral para la administración, evaluación y seguimiento de trabajos de grado académicos
  </p>
</div>

---

## 📋 Tabla de Contenidos

- [Descripción General](#-descripción-general)
- [Stack Tecnológico](#-stack-tecnológico)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación y Configuración](#-instalación-y-configuración)
- [Roles del Sistema](#-roles-del-sistema)
- [Base de Datos](#-base-de-datos)
- [Modelos Principales](#-modelos-principales)
- [Controladores](#-controladores)
- [Rutas del Sistema](#-rutas-del-sistema)
- [Sistema de Notificaciones](#-sistema-de-notificaciones)
- [Correos Electrónicos](#-correos-electrónicos)
- [Vistas (Frontend)](#-vistas-frontend)
- [Pruebas](#-pruebas)
- [Comandos Útiles](#-comandos-útiles)
- [Migraciones Base de Datos](#-migraciones-base-de-datos)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Licencia](#-licencia)

---

## 📝 Descripción General

**Sistema de Gestión de Trabajos de Grado** es una aplicación web construida con **Laravel 12** que permite gestionar el ciclo de vida completo de trabajos de grado académicos. La plataforma soporta múltiples roles (Administrador, Gestor y Evaluador) y facilita:

- 📤 **Subida y versionado** de documentos PDF (tesis, propuestas, informes)
- 📋 **Asignación de rúbricas** personalizadas por tipo de trabajo
- 👨‍🏫 **Asignación de evaluadores** con plazos de revisión
- ⭐ **Evaluación** mediante rúbricas con criterios configurables
- 📬 **Notificaciones** en tiempo real y por correo electrónico
- 📊 **Seguimiento** de estados y trazabilidad histórica
- ⏰ **Alertas** de vencimiento de plazos
- 🎓 **Gestión de directores** de tesis
- 🏛️ **Organización por facultades y áreas** de especialidad

---

## 🛠️ Stack Tecnológico

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **PHP** | ^8.2 | Lenguaje de programación backend |
| **Laravel** | ^12.0 | Framework principal |
| **PostgreSQL / MySQL / SQLite** | - | Base de datos (multiplataforma) |
| **Tailwind CSS** | ^3.4 | Framework CSS utilitario |
| **Alpine.js** | ^3.15 | Interactividad frontend ligera |
| **Flowbite** | ^4.0 | Componentes UI basados en Tailwind |
| **ApexCharts** | ^5.15 | Gráficos y estadísticas |
| **Vite** | ^7.0 | Bundler de assets frontend |
| **Mailtrap** | - | Pruebas de correo electrónico |
| **PHPUnit** | ^11.5 | Testing automatizado |
| **Laravel Sail** | ^1.41 | Entorno Docker para desarrollo |
| **Laravel Pint** | ^1.24 | Formateador de código PHP |

---

## 📌 Requisitos del Sistema

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 20.x
- **NPM** >= 9.x
- **Base de datos**: PostgreSQL, MySQL, MariaDB o SQLite
- **Extensiones PHP**: `pdo_mysql`, `pdo_pgsql`, `mbstring`, `xml`, `bcmath`, `curl`, `gd`, `zip`

---

## 🚀 Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd Sistema_Grado
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
```

Edita el archivo `.env` con tus datos:

```env
APP_NAME="Sistema de Grados"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Base de datos (ejemplo con PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=sistema_grados
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Mailtrap (para pruebas de correo)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_contraseña_mailtrap
MAIL_FROM_ADDRESS="no-reply@sistemagrados.com"
MAIL_FROM_NAME="Sistema de Grados"
```

### 3. Instalar dependencias

```bash
# Usando el script de setup incluido
composer setup

# O paso a paso:
composer install
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

### 4. Poblar datos iniciales

```bash
php artisan db:seed --class=AdminUserSeeder
```

Esto crea el usuario administrador por defecto:
- **Correo:** `administrador@sistema.com`
- **Contraseña:** `Cecar2026`

### 5. Iniciar el servidor de desarrollo

```bash
composer dev
```

Este comando inicia simultáneamente:
- **Servidor PHP** en `http://localhost:8000`
- **Queue worker** para procesar notificaciones
- **Log viewer** (Pail)
- **Vite** para HMR de assets

> O individualmente: `php artisan serve` y `npm run dev`

### 6. Acceder al sistema

Abre `http://localhost:8000` en tu navegador e inicia sesión con las credenciales del administrador.

---

## 👥 Roles del Sistema

### 👑 Administrador
- Acceso completo al panel de administración
- Gestión de usuarios (crear, editar, activar/desactivar)
- Gestión de facultades, áreas y tipos de trabajo
- Asignación de evaluadores a trabajos
- Visualización de todos los trabajos del sistema
- Aprobación y retiro de trabajos
- Seguimiento de evaluaciones

### 📋 Gestor
- Creación de nuevos trabajos de grado
- Subida de versiones de documentos PDF
- Asignación de rúbricas a trabajos
- Subida de informes finales
- Visualización del estado de sus trabajos
- Listado de evaluadores disponibles

### ⭐ Evaluador
- Dashboard con trabajos asignados
- Evaluación mediante rúbricas (con criterios configurables)
- Subida de retroalimentación por versión de documento
- Visualización de plazos de revisión
- Aceptación de términos y condiciones
- Generación de rúbricas en PDF

---

## 🗄️ Base de Datos

### Diagrama de Clases
El proyecto incluye un diagrama UML en `docs/diagrama_clases.puml`.

### Esquema General

```
usuario
  ├── administrador (rol = 'Administrador')
  ├── gestor (1:1 con tabla gestor)
  └── evaluador (1:1 con tabla profesor)
        └── area (N:1)
              └── facultad (N:1)

trabajo
  ├── tipo_trabajo (N:1)
  ├── estudiante (1:N)
  ├── trabajo_profesor (N:N con profesor)
  ├── trabajo_rubrica (N:N con rubrica)
  ├── director (N:N via director_trabajo)
  ├── evaluaciones (1:N)
  ├── retroalimentaciones (1:N)
  └── historial_estados (1:N)
```

---

## 🧩 Modelos Principales

| Modelo | Tabla | Propósito |
|--------|-------|-----------|
| `Usuario` | `usuario` | Usuarios del sistema con autenticación y roles |
| `Trabajo` | `trabajo` | Trabajos de grado con versionado de documentos |
| `Estudiante` | `estudiante` | Estudiantes asociados a trabajos |
| `Profesor` | `profesor` | Evaluadores (vinculados a usuarios) |
| `Gestor` | `gestor` | Gestores (vinculados a usuarios) |
| `Area` | `area` | Áreas de especialidad académica |
| `Facultad` | `facultad` | Facultades universitarias |
| `TipoTrabajo` | `tipo_trabajo` | Tipos de trabajo (tesis, propuesta, pasantía) |
| `Rubrica` | `rubrica` | Plantillas de rúbrica por tipo de trabajo |
| `Calificacion` | `calificacion` | Calificaciones asociadas a rúbricas |
| `Evaluacion` | `evaluaciones` | Evaluaciones completas con criterios JSON |
| `Retroalimentacion` | `retroalimentaciones` | Retroalimentación por versión de documento |
| `Director` | `directors` | Directores/asesores de tesis |
| `Alerta` | `alerta` | Alertas del sistema |
| `Seguimiento` | `seguimiento` | Seguimiento de revisiones por administradores |
| `HistorialEstado` | `historial_estados` | Trazabilidad de cambios de estado |
| `TrabajoEstudiante` | `trabajo_estudiante` | Pivote trabajo-estudiante |
| `TrabajoProfesor` | `trabajo_profesor` | Pivote trabajo-evaluador (con plazos) |

### Campos destacados en `TrabajoProfesor` (pivote):
- `fecha_asignacion` — Fecha en que se asignó el evaluador
- `fecha_limite_revision` — Plazo para completar la revisión
- `estado_revision` — Pendiente, En revisión, Completado
- `retroalimentacion_finalizada` — Indica si la retroalimentación está completa
- Accessor `esta_vencida` — Calcula si el plazo se venció
- Accessor `dias_restantes` — Calcula días restantes hasta el vencimiento

### Campos destacados en `Evaluacion`:
- `criterios` — Almacenado como JSON con los criterios evaluados
- `nota_final` — Calificación decimal (precisión 2)
- `resultado` — Aprobado/Reprobado (nullable)
- `tipo_plantilla` — Tipo de plantilla usada
- `firma` — Firma digital del evaluador
- `criterios` → casteado como `array` automáticamente

---

## 🎮 Controladores

### Autenticación
| Controlador | Métodos principales |
|-------------|-------------------|
| `AuthController` | `showLoginForm()`, `login()`, `logout()`, `redirectByRole()` |

### Administración
| Controlador | Propósito |
|-------------|-----------|
| `Admin/AdminController` | Dashboard, listado de trabajos, asignar evaluadores, detalles, aprobar/retirar trabajos |
| `Admin/AdminAreaController` | CRUD de áreas de especialidad |
| `Admin/AdminFacultadController` | CRUD de facultades |
| `Admin/AdminTipoTrabajoController` | CRUD de tipos de trabajo (con activar/desactivar) |
| `Admin/AdminGestorController` | Gestión de gestores |
| `Admin/UsuarioController` | CRUD de usuarios con activación/desactivación |

### Gestor
| Controlador | Propósito |
|-------------|-----------|
| `Gestor/GestorController` | Dashboard, lista de evaluadores, crear proyecto |
| `Gestor/TrabajoController` | CRUD de trabajos, subida de archivos, versionado, informe final |
| `Gestor/AsignarRubricaController` | Asignación de rúbricas a trabajos |

### Evaluador
| Controlador | Propósito |
|-------------|-----------|
| `Evaluador/ControllerEvaluador` | Dashboard, evaluación con rúbricas, guardar progreso, detalles, PDF |
| `Evaluador/EvaluadorController` | Guardar asignación de evaluadores |
| `Evaluador/RetroalimentacionController` | Gestión de retroalimentación |

### Generales
| Controlador | Propósito |
|-------------|-----------|
| `NotificacionController` | Visualización y gestión de notificaciones |
| `UserController` | Perfil de usuario y gestión de información |

---

## 🛣️ Rutas del Sistema

### Autenticación
| Ruta | Método | Descripción |
|------|--------|-------------|
| `/login` | GET/POST | Inicio de sesión |
| `/logout` | POST | Cierre de sesión |

### Administrador (`/admin/*`)
| Ruta | Método | Descripción |
|------|--------|-------------|
| `/admin` | GET | Dashboard principal |
| `/admin/trabajos` | GET | Listado de todos los trabajos |
| `/admin/asignar-evaluador/{id}` | GET | Formulario de asignación |
| `/admin/guardar-evaluadores/{id}` | POST | Guardar asignación |
| `/admin/usuarios` | GET/POST | CRUD de usuarios |
| `/admin/usuarios/{id}/toggle` | POST | Activar/desactivar usuario |
| `/admin/facultades-areas` | GET | Gestión de facultades y áreas |
| `/admin/lista-tipo-trabajo` | GET | Tipos de trabajo |
| `/admin/proyecto/{id}` | GET | Detalles del trabajo |
| `/admin/trabajo/{id}/aprobar` | POST | Aprobar trabajo |
| `/admin/trabajo/{id}/retirar` | POST | Retirar trabajo |
| `/admin/trabajo/{id}/eliminar` | DELETE | Eliminar trabajo |

### Gestor (`/gestor/*`)
| Ruta | Método | Descripción |
|------|--------|-------------|
| `/gestor` | GET | Dashboard |
| `/gestor/crear-trabajo` | GET/POST | Crear nuevo trabajo |
| `/gestor/lista-evaluadores` | GET | Lista de evaluadores |
| `/gestor/trabajo/{id}` | GET | Detalles del trabajo |
| `/gestor/trabajo/{id}/rubrica` | GET/POST | Asignar rúbrica |
| `/gestor/trabajo/{id}/subir-nueva-version` | POST | Subir nueva versión |
| `/gestor/trabajo/{id}/subir-informe-final` | GET/POST | Informe final |
| `/gestor/trabajo/{id}/retirar` | POST | Retirar trabajo |
| `/gestor/trabajo/eliminar/{id}` | DELETE | Eliminar trabajo |

### Evaluador (`/evaluador/*`)
| Ruta | Método | Descripción |
|------|--------|-------------|
| `/evaluador` | GET | Dashboard |
| `/evaluador/evaluacion/{id}` | GET | Evaluar trabajo |
| `/evaluador/evaluacion/{id}/detalles` | GET | Detalles de evaluación |
| `/evaluador/evaluacion/{id}/rubrica-pdf` | GET | Descargar rúbrica PDF |
| `/trabajos/{id}/guardar-evaluacion` | POST | Guardar evaluación |
| `/trabajos/{id}/guardar-progreso` | POST | Guardar progreso |
| `/evaluador/aceptar-terminos` | POST | Aceptar términos |

### Notificaciones
| Ruta | Método | Descripción |
|------|--------|-------------|
| `/notificaciones` | GET | Listar notificaciones |
| `/notificaciones/{id}/leida` | POST | Marcar como leída |
| `/notificaciones/todas-leidas` | POST | Marcar todas leídas |
| `/notificaciones/todas` | DELETE | Eliminar todas |

---

## 🔔 Sistema de Notificaciones

El sistema incluye notificaciones vía base de datos para mantener informados a todos los actores:

| Notificación | Disparador |
|--------------|------------|
| `EvaluadorAsignado` | Cuando se asigna un evaluador a un trabajo |
| `NuevoTrabajoSubido` | Cuando se sube un nuevo trabajo |
| `NuevaVersionDisponible` | Cuando hay una nueva versión del documento |
| `InformeFinalSubido` | Cuando se sube el informe final |
| `RetroalimentacionEmitida` | Cuando un evaluador emite retroalimentación |
| `RetroalimentacionFinalizada` | Cuando se finaliza la retroalimentación |
| `TrabajoAprobado` | Cuando un administrador aprueba un trabajo |
| `TrabajoEliminado` | Cuando se elimina un trabajo |
| `TrabajoReactivado` | Cuando se reactiva un trabajo |
| `TrabajoRetirado` | Cuando se retira un trabajo |
| `PlazoExtendido` | Cuando se extiende un plazo de revisión |
| `TrabajoEliminadoEvaluador` | Notificación al evaluador de eliminación |
| `TrabajoRetiradoEvaluador` | Notificación al evaluador de retiro |
| `PropuestaEvaluada` | Cuando se evalúa una propuesta |

### Evento
- `NuevoComentarioPublicado` — Se dispara al publicar un comentario

---

## 📧 Correos Electrónicos (Mailtrap)

Integración con **Mailtrap** para pruebas de envío de correos:

| Mailable | Descripción |
|----------|-------------|
| `EjemploMailtrap` | Correo de prueba para verificar integración |
| `TrabajoSubidoEstudiante` | Notificación al estudiante al subir su trabajo |

Vista de prueba: `/test-mailtrap` (requiere autenticación)

---

## 🎨 Vistas (Frontend)

### Layouts
| Archivo | Propósito |
|---------|-----------|
| `layouts/app.blade.php` | Layout principal |
| `layouts/baseAdmin.blade.php` | Layout base para administradores |
| `layouts/baseGestor.blade.php` | Layout base para gestores |
| `layouts/baseEvaluador.blade.php` | Layout base para evaluadores |
| `layouts/baseEvaluadorFullscreen.blade.php` | Layout a pantalla completa para evaluaciones |
| `layouts/partials/admin*.blade.php` | Header y sidebar del administrador |
| `layouts/partials/gestor*.blade.php` | Header y sidebar del gestor |
| `layouts/partials/evaluador*.blade.php` | Header y sidebar del evaluador |

### Vistas de Administración
| Archivo | Propósito |
|---------|-----------|
| `admin/dashboard.blade.php` | Dashboard con KPIs y gráficos |
| `admin/trabajos.blade.php` | Listado de todos los trabajos |
| `admin/detallesTrabajo.blade.php` | Detalles de un trabajo específico |
| `admin/asignarEvaluador.blade.php` | Asignación de evaluadores |
| `admin/listaAreas.blade.php` | Gestión de facultades y áreas |
| `admin/listaEstudiantes.blade.php` | Listado de estudiantes |
| `admin/listaTipoTrabajo.blade.php` | Tipos de trabajo |
| `admin/usuarios/index.blade.php` | CRUD de usuarios |
| `admin/perfil.blade.php` | Perfil del administrador |

### Vistas de Gestor
| Archivo | Propósito |
|---------|-----------|
| `gestor/dashboard.blade.php` | Dashboard del gestor |
| `gestor/creartrabajo.blade.php` | Creación de nuevo trabajo |
| `gestor/detallesTrabajo.blade.php` | Detalles del trabajo |
| `gestor/listaEvaluadores.blade.php` | Lista de evaluadores disponibles |
| `gestor/asignar_rubrica.blade.php` | Asignación de rúbrica |
| `gestor/subirInformeFinal.blade.php` | Subida de informe final |
| `gestor/perfil.blade.php` | Perfil del gestor |

### Vistas de Evaluador
| Archivo | Propósito |
|---------|-----------|
| `evaluador/dashboard.blade.php` | Dashboard del evaluador |
| `evaluador/evaluacion.blade.php` | Evaluación de trabajo |
| `evaluador/detallesEvaluacion.blade.php` | Detalles de evaluación |
| `evaluador/rubrica_pdf.blade.php` | Vista PDF de rúbrica |
| `evaluador/rubricas/pasantia.blade.php` | Rúbrica de pasantía |
| `evaluador/rubricas/propuesta_de_grado.blade.php` | Rúbrica de propuesta |
| `evaluador/rubricas/trabajo_de_grado.blade.php` | Rúbrica de trabajo de grado |
| `evaluador/perfil.blade.php` | Perfil del evaluador |

### Componentes Compartidos
| Archivo | Propósito |
|---------|-----------|
| `components/notificaciones-dropdown.blade.php` | Dropdown de notificaciones |
| `components/notification.blade.php` | Template de notificación individual |
| `components/loading-overlay.blade.php` | Overlay de carga |

---

## 🧪 Pruebas

El proyecto incluye **33 archivos de prueba** entre unitarias y de integración:

### Pruebas Unitarias (Modelos)
```bash
# Ejecutar todas las pruebas
php artisan test

# Pruebas de modelos específicos
php artisan test tests/Unit/Models/UsuarioTest.php
php artisan test tests/Unit/Models/TrabajoTest.php
php artisan test tests/Unit/Models/ProfesorTest.php
```

### Pruebas de Funcionalidad
```bash
# Pruebas de autenticación
php artisan test tests/Feature/Auth/AuthTest.php

# Pruebas del panel de administración
php artisan test tests/Feature/Admin/AdminDashboardTest.php

# Pruebas de gestor
php artisan test tests/Feature/Gestor/
```

### Pruebas de Notificaciones
```bash
php artisan test tests/Unit/Notifications/AllNotificationsTest.php
```

### Comando de prueba completo
```bash
composer test
```

---

## ⌨️ Comandos Útiles

```bash
# Instalación completa
composer setup

# Desarrollo (todo en uno)
composer dev

# Ejecutar pruebas
composer test

# Migraciones y seeds
php artisan migrate:fresh --seed
php artisan db:seed

# Limpiar caché
php artisan optimize:clear

# Cola de trabajos (notificaciones)
php artisan queue:listen --tries=1

# Ver logs en tiempo real
php artisan pail
```

---

## 📦 Migraciones Base de Datos

| Migración | Tabla | Descripción |
|-----------|-------|-------------|
| `000000` | - | Creación de enums de PostgreSQL |
| `000001` | `usuario` | Usuarios del sistema |
| `000002` | `area` | Áreas de especialidad |
| `000003` | `gestor` | Gestores |
| `000004` | `tipo_trabajo` | Tipos de trabajo |
| `000005` | `trabajo` | Trabajos de grado |
| `000006` | `estudiante` | Estudiantes |
| `000007` | `profesor` | Profesores/evaluadores |
| `000008` | `trabajo_estudiante` | Pivote trabajo-estudiante |
| `000009` | `rubrica` | Rúbricas de evaluación |
| `000010` | `trabajo_profesor` | Pivote trabajo-evaluador |
| `000011` | `trabajo_rubrica` | Pivote trabajo-rúbrica |
| `000012` | `calificacion` | Calificaciones |
| `000013` | `alerta` | Alertas del sistema |
| `000014` | `seguimiento` | Seguimiento admin |
| `000015` | `sessions` | Sesiones PHP |
| `210410` | `facultad` | Facultades |
| `210441` | `notifications` | Notificaciones BD |
| `151000` | `retroalimentaciones` | Retroalimentación |
| `160000` | `historial_estados` | Historial de estados |
| `195725` | `directors` | Directores de tesis |
| `195749` | `director_trabajo` | Pivote director-trabajo |
| `200002` | `evaluaciones` | Evaluaciones detalladas |

---

## 📁 Estructura del Proyecto

```
Sistema_Grado/
├── app/
│   ├── Console/            # Comandos Artisan
│   ├── Events/             # Eventos del sistema
│   ├── Exceptions/         # Manejo de excepciones
│   ├── Http/
│   │   ├── Controllers/    # Controladores (Admin, Auth, Gestor, Evaluador)
│   │   ├── Middleware/      # Middleware (auth, roles, check.activo)
│   │   └── Kernel.php      # Registro de middleware
│   ├── Mail/               # Clases de correo (Mailtrap, TrabajoSubido)
│   ├── Models/             # Modelos Eloquent
│   ├── Notifications/      # Notificaciones (14 clases)
│   └── Services/           # Servicios (AlertService, BusinessDaysService)
├── bootstrap/              # Archivos de bootstrap
├── config/                 # Configuración (app, auth, database, mail, session)
├── database/
│   ├── factories/          # Factory para pruebas
│   ├── migrations/         # Migraciones (40 archivos)
│   └── seeders/            # Seeders (AdminUserSeeder, DatabaseSeeder)
├── docs/
│   └── diagrama_clases.puml # Diagrama UML de clases
├── public/                 # Punto de entrada público
├── resources/
│   ├── css/                # Estilos (Tailwind)
│   ├── js/                 # JavaScript (Alpine.js, notificaciones, sesión)
│   └── views/              # Vistas Blade (44 archivos)
├── routes/
│   ├── console.php         # Rutas de consola
│   └── web.php             # Rutas web
├── storage/                # Almacenamiento (logs, sesiones, caché)
├── tests/                  # Pruebas (33 archivos)
├── composer.json           # Dependencias PHP
├── package.json            # Dependencias NPM
├── tailwind.config.js      # Configuración de Tailwind CSS
├── vite.config.js          # Configuración de Vite
├── postcss.config.js       # Configuración de PostCSS
└── phpunit.xml             # Configuración de PHPUnit
```

---

## 🔐 Funcionalidades Clave

### Autenticación y Seguridad
- Login con correo y contraseña
- Redirección por rol automática
- Middleware de verificación de usuario activo (`check.activo`)
- Timeout de sesión configurable
- Protección CSRF

### Gestión de Trabajos
- Creación de trabajos con título, tipo y archivo PDF
- Versionado de documentos (múltiples versiones)
- Subida de informe final
- Estados: Pendiente, En revisión, Aprobado, Retirado
- Retiro lógico (soft delete) de trabajos

### Evaluación
- Rúbricas personalizadas por tipo de trabajo (tesis, propuesta, pasantía)
- Evaluación con criterios almacenados en JSON
- Cálculo de nota final
- Guardado de progreso (borrador)
- Firma digital del evaluador
- Generación de PDF de rúbrica completada
- Aceptación de términos por parte del evaluador

### Plazos y Alertas
- Asignación automática de plazos de revisión
- Cálculo de días restantes y vencimiento
- Prórroga de plazos por administrador

### Notificaciones en Tiempo Real
- Notificaciones persistentes en base de datos
- Marcado individual y masivo como leídas
- Dropdown de notificaciones en tiempo real (vía Alpine.js)

---

## 📄 Licencia

Este proyecto es software de código abierto licenciado bajo [MIT license](https://opensource.org/licenses/MIT). Está construido sobre [Laravel](https://laravel.com), que también utiliza la licencia MIT.

---

<div align="center">
  <sub>Construido con ❤️ usando Laravel 12, Tailwind CSS, Alpine.js y Flowbite</sub>
</div>
