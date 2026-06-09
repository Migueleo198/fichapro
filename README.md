# ⏱ FichaPro

Aplicación web de **control horario y gestión de personal**. Permite a los empleados fichar entrada/salida, registrar descansos y tareas, y solicitar vacaciones o ausencias; y a los administradores gestionar la plantilla, validar fichajes, aprobar solicitudes y generar informes de horas.

Construida desde cero con **PHP (MVC propio) + MariaDB/MySQL**, sin frameworks externos.

## Funcionalidades

- **Fichaje** — entrada/salida con reloj en vivo, descansos por motivo, cálculo automático de horas ordinarias y extra.
- **Empleados** — altas/bajas, roles (administrador / trabajador), activación de cuentas.
- **Jornadas** — horario contratado por empleado (horas/día y semana).
- **Vacaciones y ausencias** — solicitudes con flujo de aprobación; catálogo de tipos de ausencia (bajas, permisos, etc.).
- **Tareas** — registro del trabajo realizado durante la jornada, por tipo.
- **Incidencias** — reclamaciones sobre fichajes con respuesta del administrador.
- **Vehículos** — matrículas registradas por empleado.
- **Informes** — horas por empleado y por día, con exportación a **PDF**.
- **Ajustes** — parámetros de empresa (nombre, hora de inicio, umbral de retraso…).
- **Auditoría** — registro de actividad.
- **Inicio por rol** — panel de administrador (KPIs y pendientes) y panel de empleado con sus fichajes y alta/consulta de incidencias.
- **Estadísticas** — paneles de resumen, fichajes, horas, retrasos y actividad con gráficos (Chart.js).
- **Perfil de usuario** — edición de datos personales y cambio de contraseña desde un modal.
- **Filtros** — búsqueda y filtrado de cada listado en un panel desplegable.
- **Diseño responsive moderno** (blanco + azul, tipografía Inter) adaptado a móvil y escritorio.

## Requisitos

- PHP 8.0+
- MySQL / MariaDB
- Apache con `mod_rewrite`
- Composer (para las librerías de PDF/email)

## Instalación

```bash
# 1. Dependencias
composer install

# 2. Base de datos
mysql -u root < fichapro.sql

# 3. Configuración
#    Edita app/config/config.php con tus credenciales de BD y la URL base.
```

Con el `DocumentRoot` apuntando a `public/`, accede a la app y entra con el usuario por defecto:

| Usuario | Contraseña | Rol |
|---------|-----------|-----|
| `admin` | `admin123` | Administrador |



## Estructura

```
fichapro/
├── app/
│   ├── config/        Configuración y conexión a BD
│   ├── controllers/   Controladores (un módulo cada uno)
│   ├── models/        Acceso a datos
│   ├── views/         Vistas (pages/, inc/, pdf/)
│   ├── lib/           Núcleo MVC (Core router, Controller base)
│   └── helpers/       Funciones de ayuda
├── public/            DocumentRoot (index.php, css, js)
└── fichapro.sql       Esquema + datos de ejemplo
```

## Licencia

Software propietario. Todos los derechos reservados.
