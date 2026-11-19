# Sistema de reservaciones de hotel

Sitio web y sistema de reservaciones para un hotel boutique (nombre de ejemplo: **Casa Cantera**).
Los huéspedes consultan habitaciones y reservan; recepción registra entradas y salidas; la administración
gestiona el hotel.

**Tecnologías:** PHP 8 · MariaDB 10.11 · HTML5 · JavaScript · Tailwind CSS v4 · XAMPP

## Estado

| Parte | Estado |
|---|---|
| Interfaz: portada, login, verificación 2FA, registro, recuperar y restablecer contraseña, aviso de privacidad, términos | ✅ Prototipo visual |
| Base de datos: tablas, roles, permisos y validaciones | ✅ `database/esquema.sql` |
| Conexión a la base de datos | ✅ `app/nucleo/Conexion.php` |
| Lógica: autenticación, reservaciones, paneles de recepción y administración | ⏳ Pendiente |

Mientras no exista la lógica del servidor, las pantallas simulan sus estados con `?estado=...`
(por ejemplo, `login.php?estado=credenciales`).

## Roles

| Rol | Verificación en dos pasos |
|---|---|
| Invitado | — |
| Cliente | No |
| Recepcionista | Sí |
| Administrador | Sí |
| Superadministrador | Sí |

## Instalación

### En XAMPP

1. Clona el repositorio dentro de `htdocs`:
   ```bash
   cd /opt/lampp/htdocs        # En Windows: C:\xampp\htdocs
   git clone https://github.com/Omar-art32/hotel.git
   ```
2. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
3. En phpMyAdmin, con el usuario `root`, importa `database/esquema.sql`. El script crea la base `equipo_2`.
4. Crea el usuario de la aplicación con permisos limitados:
   ```sql
   CREATE USER 'usuario_equipo_2'@'localhost' IDENTIFIED BY 'tu_contraseña';
   GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON equipo_2.* TO 'usuario_equipo_2'@'localhost';
   ```
5. Copia `.env.example` como `.env` y completa los datos de conexión.
6. Abre `http://localhost/hotel`.

El CSS ya viene compilado, así que el servidor no necesita Node.js.

### En desarrollo (sin XAMPP)

Requiere PHP 8 y Node.js.

```bash
npm install
npm run css:vigilar              # recompila el CSS al guardar cambios
php -S localhost:8000 -t public  # en otra terminal
```

Abre `http://localhost:8000`.

## Estructura

```
public/       Páginas y recursos visibles desde el navegador
app/          Configuración, núcleo, controladores, repositorios y vistas (no accesible desde el navegador)
database/     Esquema y datos de prueba
storage/      Logs y respaldos
docs/         Documentación
```

El `.htaccess` de la raíz envía todas las peticiones a `public/`, así que `app/`, `database/` y `.env`
nunca quedan expuestos.

## Documentación

Flujo de archivos, relación entre vistas, base de datos, convenciones y pendientes:
**[docs/documentacion.md](docs/documentacion.md)**

## Convenciones

- Identificadores en español, sin acentos ni ñ.
- Archivos y carpetas en `snake_case`; clases PHP en `PascalCase`; variables y funciones en `camelCase`;
  clases CSS e `id` de HTML en `kebab-case`.
- Textos visibles en español correcto, con acentos.
- Los estilos se escriben con clases de Tailwind. Después de cambiarlas: `npm run css:compilar`.

## Seguridad

- `.env` contiene credenciales y **no se sube a Git**.
- Las contraseñas se guardarán con `password_hash` y los tokens solo como hash SHA-256.
- Todas las consultas deben ser preparadas (`Conexion::obtener()` ya desactiva la emulación).
