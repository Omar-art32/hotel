# Guía del código fuente

Explicación detallada del código del sistema de reservaciones **Casa Cantera**: qué hace cada archivo, cada clase y cada función, **por qué** está escrita así y **para qué** sirve.

> Complementa a `documentacion.md` (estructura general, instalación y diseño). Este documento se centra en el código.

---

## 1. Visión general

El proyecto es PHP "puro" (sin framework) organizado en capas:

```
Navegador
   │  petición HTTP
   ▼
.htaccess ──► public/*.php        (páginas: reciben la petición y muestran HTML)
                 │
                 ├─► app/config/config.php      (cabeceras, .env, autocarga)
                 ├─► app/nucleo/*               (sesión, CSRF, permisos, bitácora, conexión)
                 ├─► app/controladores/*        (reglas de negocio: login, registro)
                 │        └─► app/repositorios/* (consultas SQL)
                 │                 └─► MariaDB (database/esquema.sql)
                 └─► app/vistas/*               (fragmentos HTML reutilizables)
```

| Capa | Responsabilidad | Por qué separarla |
|------|-----------------|-------------------|
| `public/` | Leer `$_GET`/`$_POST`, llamar al controlador, decidir qué mostrar | Es lo único accesible desde la web; mantenerla delgada reduce lo expuesto |
| `app/nucleo/` | Servicios transversales que usan todas las páginas | Evita repetir la lógica de seguridad en cada página |
| `app/controladores/` | Reglas de negocio (validar, limitar intentos, decidir estados) | Se pueden probar sin navegador ni base de datos |
| `app/repositorios/` | Todo el SQL | Un solo lugar para consultas preparadas; fácil de sustituir en pruebas |
| `app/vistas/` | HTML compartido | No duplicar cabeceras, pies y avisos |

---

## 2. Punto de entrada: `.htaccess`

```apache
Options -Indexes
RewriteEngine On
RewriteRule ^public/ - [L]
RewriteRule ^(.*)$ public/$1 [L]
```

- **Qué hace:** cualquier URL `/hotel/X` se sirve desde `public/X`.
- **Por qué:** así `app/`, `database/`, `storage/`, `tests/` y `.env` **nunca** son accesibles desde el navegador (devuelven 404), aunque el proyecto completo esté dentro de la carpeta del servidor web.
- `Options -Indexes` impide listar el contenido de carpetas.
- La primera regla evita un bucle de reescritura (`public/public/...`).

---

## 3. Configuración: `app/config/config.php`

Todas las páginas empiezan con `require __DIR__ . '/../app/config/config.php';`. Este archivo hace tres cosas:

### 3.1 Cabeceras de seguridad

| Cabecera | Para qué |
|----------|----------|
| `header_remove('X-Powered-By')` | No revelar la versión de PHP |
| `X-Content-Type-Options: nosniff` | El navegador no "adivina" tipos de archivo (evita ejecutar texto como script) |
| `X-Frame-Options: DENY` y `frame-ancestors 'none'` | Nadie puede incrustar el sitio en un `<iframe>` (clickjacking) |
| `Referrer-Policy: same-origin` | Los tokens de la URL (p. ej. restablecer contraseña) no se filtran a Google Fonts |
| `Content-Security-Policy` | Solo se ejecutan scripts propios (`script-src 'self'`): un XSS inyectado no puede cargar código externo ni inline |
| `Strict-Transport-Security` | Solo si la petición llega por HTTPS: obliga al navegador a usar HTTPS en adelante |

`style-src 'unsafe-inline'` existe solo por los `style="background-image: ..."` de `index.php`.

### 3.2 Lectura de `.env`

```php
$entorno = is_readable($rutaEntorno) ? parse_ini_file($rutaEntorno, false, INI_SCANNER_RAW) : false;
```

- Las credenciales de la base de datos **no** están en el código: se leen de `.env` (ignorado por git; la plantilla es `.env.example`).
- `INI_SCANNER_RAW` evita que PHP interprete valores como `true`, `null` o caracteres especiales en contraseñas.
- Si falta `.env`, se registra el error en el log del servidor y se muestra un mensaje genérico con código 500 (no se revelan rutas ni detalles).
- Define las constantes `DB_HOST`, `DB_PUERTO`, `DB_NOMBRE`, `DB_USUARIO`, `DB_CONTRASENA` y luego hace `unset()` del arreglo para no dejar las credenciales en variables globales.

### 3.3 Autocarga de clases

```php
spl_autoload_register(function (string $clase): void { ... });
```

- Cuando el código usa `new AuthController()` o `Sesion::iniciar()`, PHP busca `AuthController.php` en `nucleo/`, `controladores/` y `repositorios/`.
- **Por qué:** evita decenas de `require` manuales y garantiza que solo se cargue lo que se usa.

---

## 4. Núcleo (`app/nucleo/`)

### 4.1 `Conexion` — conexión única a MariaDB

```php
public static function obtener(): PDO
```

- Patrón *singleton*: la primera llamada crea el `PDO`, las siguientes reutilizan el mismo. Una petición = una conexión.
- Opciones:
  - `ERRMODE_EXCEPTION`: los errores SQL lanzan `PDOException` en lugar de fallar en silencio.
  - `FETCH_ASSOC`: las filas llegan como arreglos `['columna' => valor]`.
  - `EMULATE_PREPARES => false`: consultas preparadas **reales**; el servidor recibe la consulta y los datos por separado, lo que bloquea la inyección SQL.
  - `charset=utf8mb4`: soporta acentos, ñ y emojis correctamente.

### 4.2 `Sesion` — sesión segura

| Método | Qué hace | Por qué |
|--------|----------|---------|
| `iniciar()` | Configura y arranca la sesión; cierra la sesión si hubo 30 min de inactividad o pasaron 8 h desde el login | Debe llamarse antes de enviar HTML |
| `iniciarUsuario($usuario)` | Guarda al usuario tras un login correcto | Regenera el ID y el token CSRF |
| `actualizarUsuario($usuario)` | Refresca rol/permisos sin reiniciar la sesión | Lo usa `revalidarSesion()` |
| `usuarioActual()` | Devuelve el usuario de la sesión o `null` | Consulta común en todas las páginas |
| `cerrar()` | Vacía `$_SESSION`, borra la cookie y destruye la sesión | Logout completo, no solo "olvidar" al usuario |

Detalles de seguridad en `iniciar()`:

- `session.use_strict_mode = 1`: PHP rechaza IDs de sesión que él no generó (evita que un atacante imponga un ID conocido).
- Nombre propio `sesion_hotel` (en vez de `PHPSESSID`): no revela la tecnología.
- Cookie `httponly` (JavaScript no puede leerla → un XSS no roba la sesión), `samesite=Lax` (no se envía en peticiones POST de otros sitios) y `secure` automático si hay HTTPS.
- `INACTIVIDAD_SEGUNDOS = 1800` y `DURACION_MAXIMA_SEGUNDOS = 28800`: aunque una sesión robada se use constantemente, caduca a las 8 h.

En `iniciarUsuario()`:

- `session_regenerate_id(true)`: el ID de sesión cambia al autenticarse → evita la **fijación de sesión**.
- `unset($_SESSION['csrf'])`: el token CSRF previo al login deja de servir.

### 4.3 `Csrf` — protección contra falsificación de peticiones

| Método | Qué hace |
|--------|----------|
| `token()` | Crea (una vez por sesión) un token aleatorio de 64 caracteres hex con `random_bytes(32)` |
| `campo()` | Devuelve `<input type="hidden" name="csrf" value="...">` para insertar en cada formulario POST |
| `validar($token)` | Compara el token recibido con el de la sesión usando `hash_equals()` |

- **Para qué:** otro sitio web no puede enviar formularios (login, registro, logout) en nombre del usuario, porque no conoce el token.
- **Por qué `hash_equals`:** compara en tiempo constante; un `===` podría filtrar el token midiendo tiempos de respuesta.

### 4.4 `Autorizacion` — protección de páginas internas

```php
public static function requerirPermiso(string $permiso, string $rutaBase = ''): array
```

Flujo:

1. Inicia la sesión.
2. Llama a `AuthController::revalidarSesion()` (relee al usuario de la base de datos).
3. Sin sesión → redirige a `login.php?estado=acceso_requerido`.
4. Sin el permiso pedido → `403 No tienes permiso`.
5. Si la base de datos falla → `503` con mensaje genérico (el detalle va a `error_log`).
6. Si todo está bien, devuelve el usuario para que la página lo use.

**Por qué por permiso y no por rol:** las páginas piden `gestionar_habitaciones`, no "ser administrador". Si mañana otro rol necesita ese acceso, basta con añadir el permiso en la tabla `roles_permisos`, sin tocar código.

`$rutaBase` existe porque las páginas en subcarpetas (`admin/`, `recepcion/`) necesitan `../login.php`.

### 4.5 `Bitacora` — registro de eventos

| Método | Qué hace |
|--------|----------|
| `registrar($evento, $idUsuario, $detalle)` | Inserta un evento en la tabla `bitacora` con la IP |
| `contarPorIp($eventos, $ip, $minutos)` | Cuenta eventos de una IP en una ventana de tiempo (se usa para limitar registros) |
| `ipCliente()` | Devuelve `REMOTE_ADDR` |

- Eventos que se registran hoy: `inicio_sesion`, `cierre_sesion`, `sesion_revocada`, `acceso_cuenta_inactiva`, `registro_usuario`, `registro_correo_existente`.
- **Por qué solo `REMOTE_ADDR`:** la cabecera `X-Forwarded-For` la puede inventar el cliente; si se usara, un atacante evadiría el límite de intentos cambiando la cabecera.
- El constructor acepta un `PDO` opcional para poder inyectar uno falso en pruebas.

---

## 5. Repositorio: `app/repositorios/UsuarioRepositorio.php`

Contiene **todo** el SQL relacionado con usuarios. Todas las consultas son preparadas (`?` + `execute([...])`).

| Método | Consulta | Lo usa |
|--------|----------|--------|
| `buscarPorCorreo($correo)` | Usuario + nombre de rol por correo | Login, registro |
| `buscarPorId($id)` | Usuario + rol por ID | `revalidarSesion()` |
| `buscarUno($condicion, $valor)` *(privado)* | `SELECT ... JOIN roles WHERE $condicion LIMIT 1` | Los dos anteriores |
| `permisosDeRol($idRol)` | Lista de códigos de permiso del rol | Se guarda en la sesión |
| `crearCliente(...)` | `INSERT ... SELECT id_rol FROM roles WHERE nombre='cliente'` | Registro |
| `actualizarContrasenaHash($id, $hash)` | Actualiza el hash | Rehash automático en login |
| `contarFallidosPorCorreo($correo, $min)` | Fallos del correo en la ventana **desde su último acceso correcto** | Límite de intentos |
| `contarFallidosPorIp($ip, $min)` | Fallos de la IP en la ventana | Límite de intentos |
| `registrarIntento($correo, $ip, $ok)` | Inserta en `intentos_login` | Login |

Decisiones importantes:

- **Conexión perezosa** (`pdo()` con `??=`): la conexión se abre en la primera consulta. Si el formulario tiene errores de validación, nunca se toca la base de datos.
- **`buscarUno` interpola `$condicion`**: es seguro porque `$condicion` siempre es texto fijo escrito en el código (`'u.correo = ?'`); el valor del usuario va siempre como parámetro.
- **`crearCliente` usa `INSERT ... SELECT`**: busca el `id_rol` por nombre en lugar de escribir un número fijo, así no depende del orden en que se insertaron los roles. Si el rol no existe, lanza `RuntimeException` con una pista clara.
- **`contarFallidosPorCorreo` reinicia el conteo tras un login correcto**: si la persona se equivoca 4 veces, entra, sale y se equivoca otra vez, no queda bloqueada injustamente.
- **`sprintf('%d')` para los minutos**: `INTERVAL` no admite parámetros `?` en todas las versiones; `%d` fuerza un entero, por lo que no hay riesgo de inyección.
- `substr($correo, 0, 254)`: respeta el tamaño de la columna aunque llegue un texto enorme.

---

## 6. Controladores

### 6.1 `AuthController` — inicio y cierre de sesión

Constantes:

| Constante | Valor | Para qué |
|-----------|-------|----------|
| `MAX_FALLIDOS_CORREO` | 5 | Bloqueo por cuenta (fuerza bruta sobre un correo) |
| `MAX_FALLIDOS_IP` | 20 | Bloqueo por IP (prueba de muchos correos desde una máquina) |
| `VENTANA_MINUTOS` | 15 | Duración de la ventana de conteo |
| `DESTINOS` | rol → página | A dónde ir tras el login según el rol |
| `HASH_FALSO` | hash bcrypt | Ver abajo |

#### `iniciarSesion($correo, $contrasena, $ip): string`

Devuelve un **estado** (texto) que `login.php` traduce a un mensaje. Pasos:

1. Normaliza el correo (`strtolower(trim())`) → `Ana@Hotel.mx` y `ana@hotel.mx` son la misma cuenta.
2. Campos vacíos → `campos_vacios`.
3. Si se superó el límite por correo **o** por IP → `bloqueo_temporal` (antes de comprobar la contraseña, para que el atacante no pueda seguir probando).
4. Busca al usuario (solo si el correo tiene formato válido).
5. `password_verify()` **siempre** se ejecuta: si el usuario no existe, se verifica contra `HASH_FALSO`.
   - **Por qué:** bcrypt tarda ~100 ms. Si solo se verificara cuando el correo existe, un atacante sabría qué correos están registrados midiendo el tiempo de respuesta.
6. Usuario inexistente o contraseña incorrecta → registra intento fallido → `credenciales` (mismo mensaje en ambos casos).
7. Cuenta inactiva → `bloqueada`; sin verificar → `sin_verificar`. Estos estados solo se revelan **con la contraseña correcta**, para no filtrar qué cuentas existen.
8. `password_needs_rehash()`: si PHP cambia el algoritmo o el costo por defecto, el hash se actualiza de forma transparente en el siguiente login.
9. Registra intento exitoso, abre la sesión → `exito`.

#### `abrirSesion($usuario)` *(privado)*

Guarda los datos en la sesión (`Sesion::iniciarUsuario`) y registra `inicio_sesion` en la bitácora.

#### `revalidarSesion(): ?array`

Se ejecuta en **cada** petición a una página protegida (desde `Autorizacion`). Relee al usuario de la base de datos:

- Si fue eliminado, desactivado o desverificado → registra `sesion_revocada`, cierra la sesión y devuelve `null`.
- Si sigue activo → actualiza rol y permisos en la sesión.

**Para qué:** si un administrador desactiva una cuenta o le quita un permiso, el cambio surte efecto **en la siguiente petición**, no hasta que la persona vuelva a iniciar sesión.

#### `datosDeSesion($usuario)` *(privado)*

Arma el arreglo que se guarda en `$_SESSION['usuario']`: id, nombre, apellidos, correo, rol, permisos y destino. **No** guarda el hash de la contraseña.

#### `cerrarSesion()`

Registra `cierre_sesion` y cierra la sesión. El `try/finally` garantiza que la sesión se cierre **aunque falle la bitácora**.

### 6.2 `UsuarioController` — registro de clientes

#### `registrar(array $datos, string $ip): array`

Devuelve `['estado' => ..., 'errores' => [campo => mensaje]]`. Estados: `exito`, `errores`, `correo_registrado`, `bloqueo_temporal`.

1. **Validación en el servidor** (aunque exista validación en JavaScript, el navegador se puede saltar):

| Campo | Regla | Por qué |
|-------|-------|---------|
| nombre | obligatorio, ≤ 60, `PATRON_NOMBRE` | Admite acentos, ñ, `O'Connor`, `Pérez-Núñez`, `Ma. José` |
| apellidos | obligatorio, ≤ 80, `PATRON_NOMBRE` | Igual |
| correo | obligatorio, ≤ 254, `FILTER_VALIDATE_EMAIL` | 254 es el máximo del estándar |
| teléfono | opcional; 7–20 de `0-9 + ( ) - espacio` | Formatos nacionales e internacionales |
| contraseña | ≥ 8 con mayúscula, minúscula, número y signo (`PATRON_CONTRASENA`); ≤ 72 bytes | bcrypt ignora lo que pasa de 72 bytes: se rechaza en lugar de recortar en silencio |
| confirmación | igual a la contraseña | |
| términos | debe valer `'1'` | Consentimiento explícito |

   Las expresiones usan `\p{L}` / `\p{Lu}` / `\p{Ll}` y el modificador `u` para que funcionen con letras Unicode (no solo A–Z).

2. **Límite por IP**: máximo 10 registros (exitosos o con correo repetido) por hora. Frena la creación masiva de cuentas y el sondeo de correos registrados.
3. **Correo repetido**: se consulta antes de insertar → `correo_registrado`.
4. **Condición de carrera**: si dos peticiones registran el mismo correo al mismo tiempo, el índice único de la base de datos lanza `SQLSTATE 23000`; se captura y se responde igual que un correo repetido. Cualquier otro error se relanza.
5. Contraseña guardada con `password_hash(PASSWORD_DEFAULT)` (bcrypt); nunca en texto plano.
6. Registra `registro_usuario` en la bitácora.

---

## 7. Páginas (`public/`)

### 7.1 Patrón común

Todas las páginas con formulario siguen la misma estructura:

```php
require config.php;
Sesion::iniciar();
if (ya hay sesión) → redirigir a su destino;

$avisos = [estado => [tono, titulo, texto]];   // mensajes posibles
if (POST) {
    if (!Csrf::validar(...)) $estado = 'formulario_caducado';
    else try { $estado = controlador->accion(...); }
         catch (PDOException) { error_log(...); $estado = 'conexion'; }
    if ($estado === 'exito') → redirigir;
}
// HTML: muestra $avisos[$estado] y repinta los valores escritos
```

**Por qué:** la página no decide reglas, solo traduce estados a mensajes. Los errores de base de datos nunca se muestran al usuario (solo van a `error_log`).

### 7.2 Página por página

| Archivo | Qué hace | Estado |
|---------|----------|--------|
| `index.php` | Portada: habitaciones, servicios, galería, opiniones, preguntas frecuentes y formulario de búsqueda. El contenido está en arreglos PHP al inicio del archivo y se recorre con `foreach` | Contenido estático; el buscador aún no consulta disponibilidad |
| `login.php` | Formulario de acceso → `AuthController::iniciarSesion()`; al tener éxito redirige al `destino` del rol | Funcional |
| `registro.php` | Formulario de alta → `UsuarioController::registrar()`; al tener éxito redirige a `login.php?estado=cuenta_creada`. Repinta los valores escritos (nunca la contraseña) y muestra errores por campo | Funcional |
| `logout.php` | Solo acepta **POST con token CSRF** (un enlace o `<img>` de otro sitio no puede cerrar la sesión). Si la BD falla, la sesión se cierra igual | Funcional |
| `mis_reservaciones.php` | Protegida con `consultar_reservaciones` | Provisional |
| `recepcion/panel.php` | Protegida (recepción) | Provisional |
| `admin/habitaciones.php` | Protegida con `gestionar_habitaciones` | Provisional |
| `admin/usuarios.php` | Protegida (superadministración) | Provisional |
| `verificacion_2fa.php`, `recuperar_contrasena.php`, `restablecer_contrasena.php` | Solo interfaz; muestran estados leídos de `?estado=` | Prototipo, sin backend ni correos |
| `aviso_privacidad.php`, `terminos.php` | Textos legales con la vista `documento_legal.php` | Estático |
| `admin/bitacora.php`, `recepcion/check_in.php`, `recepcion/check_out.php` | — | **Vacíos** (responden 200 con página en blanco) |

Las páginas protegidas tienen solo 10 líneas:

```php
$rutaBase = '../';
require config.php;
$usuario = Autorizacion::requerirPermiso('gestionar_habitaciones', $rutaBase);
$tituloPagina = 'Habitaciones';
require encabezado.php; require panel_provisional.php; require pie.php;
```

---

## 8. Vistas (`app/vistas/`)

| Vista | Para qué | Espera |
|-------|----------|--------|
| `encabezado.php` | `<!doctype>`, `<head>`, fuentes, CSS y enlace "Saltar al contenido" | `$tituloPagina`, `$rutaBase` |
| `pie.php` | Carga `validation.js` y cierra `</body></html>` | — |
| `cabecera_sitio.php` | Menú del sitio público; muestra "Iniciar sesión" o "Mi cuenta" según la sesión | `Sesion::iniciar()` previo |
| `pie_sitio.php` | Pie del sitio público (contacto, enlaces legales) | — |
| `acceso_inicio.php` / `acceso_fin.php` | Marco de dos columnas de las pantallas de acceso (login, registro, 2FA...) | — |
| `aviso.php` | Caja de mensaje con `role="alert"` (error) o `role="status"` (éxito) para lectores de pantalla | `$aviso` o `null` |
| `panel_provisional.php` | Página interna "en construcción" con el nombre, el rol y el botón de cerrar sesión (formulario POST con `Csrf::campo()`) | `$usuario`, `$tituloPagina` |
| `documento_legal.php` | Plantilla de los textos legales | — |

Todo dato dinámico se imprime con `htmlspecialchars()` para evitar **XSS**.

---

## 9. JavaScript: `public/assets/js/validation.js`

Un único script, sin dependencias, que se activa con atributos `data-*` (no hay JS inline, por la CSP).

| Función / bloque | Qué hace |
|------------------|----------|
| `obtenerMensajeError(campo)` | Usa la API nativa `validity` del navegador (`valueMissing`, `typeMismatch`, `patternMismatch`, `tooShort`) y devuelve el mensaje de `data-error-vacio` o `data-error-formato`. Además: `data-igual-a` (confirmar contraseña) y `data-despues-de` (fecha de salida posterior a la de llegada) |
| `mostrarError(campo, mensaje)` | Escribe el mensaje en `#error-<id>` y pone/quita `aria-invalid` (accesibilidad) |
| `validarFormulario(evento)` | Al enviar: valida todos los campos, enfoca el primero inválido y cancela el envío; si todo está bien, deshabilita el botón (`aria-busy`) para evitar doble envío. `data-prototipo-destino` simula una respuesta en las páginas aún sin backend |
| Listener `input` | El error desaparece en cuanto el campo se corrige |
| `data-solo-digitos` | Elimina lo que no sea dígito (código 2FA) |
| `data-alternar-contrasena` | Botón "Mostrar/Ocultar" contraseña con `aria-pressed` |
| `details[data-menu]` | Cierra el menú móvil al elegir una sección |

**Importante:** esta validación es solo para comodidad del usuario. La validación que protege los datos es la del servidor (`UsuarioController`, `AuthController`).

---

## 10. Base de datos: `database/esquema.sql`

### 10.1 Tablas

| Tabla | Para qué |
|-------|----------|
| `roles` | invitado, cliente, recepcionista, administrador, superadministrador (`requiere_2fa` para el personal) |
| `permisos` | 24 códigos de acción (`realizar_check_in`, `gestionar_usuarios`, ...) |
| `roles_permisos` | Qué permisos tiene cada rol (relación muchos a muchos) |
| `usuarios` | Cuentas; `contrasena_hash`, `esta_activo`, `esta_verificado`; correo único |
| `tipos_habitacion`, `habitaciones` | Catálogo (capacidad por tipo, habitación activa/inactiva) |
| `reservaciones` | Reservas con fechas, personas y estado |
| `pagos` | Pagos de cada reservación |
| `servicios`, `reservaciones_servicios` | Servicios extra y su relación con reservaciones |
| `bitacora` | Eventos del sistema (si se borra el usuario, `id_usuario` queda en `NULL` y el evento se conserva) |
| `intentos_login` | Cada intento de acceso; índices por `(correo, fecha)` e `(ip, fecha)` para que los conteos sean rápidos |
| `tokens_usuario` | Preparada para recuperación de contraseña y verificación de correo; guarda el **hash** del token, no el token |

Los permisos por rol se insertan buscando por **nombre** (`WHERE r.nombre = 'cliente'`), no por id fijo.

### 10.2 Procedimiento y triggers

`validar_reservacion(...)` se ejecuta automáticamente desde los triggers `validar_reservacion_insert` y `validar_reservacion_update` (`BEFORE INSERT/UPDATE`):

- Comprueba que la habitación exista y esté activa.
- Comprueba que el número de personas no supere la capacidad del tipo.
- Si el estado es `confirmada` o `check_in`, comprueba que **no haya traslape** de fechas con otra reservación activa de la misma habitación (`entrada < salida_otra AND salida > entrada_otra`). Usa `FOR UPDATE` para bloquear esas filas y evitar que dos reservas simultáneas pasen la validación.
- Si algo falla lanza `SIGNAL SQLSTATE '45000'` con un mensaje.

**Por qué en la base de datos:** la regla "no reservar dos veces la misma habitación" se cumple aunque en el futuro otro código (o una consulta manual) inserte reservaciones.

En `UPDATE` solo se revalida la capacidad si cambian la habitación o el número de personas, para no bloquear cambios de estado de reservas antiguas.

---

## 11. Pruebas (`tests/`)

| Archivo | Qué prueba |
|---------|------------|
| `dobles_prueba.php` | Repositorio y bitácora **en memoria** (`RepositorioFalso`, etc.) que sustituyen a la BD; `ErrorCorreoDuplicado` simula el `SQLSTATE 23000` |
| `prueba_auth.php` | Login: campos vacíos, credenciales, bloqueos por correo/IP, cuentas inactivas, rehash, revalidación, logout, CSRF |
| `prueba_registro.php` | Registro: validaciones por campo, correo repetido, carrera con índice único, límite por IP |

Por eso los controladores reciben el repositorio y la bitácora en el constructor (`?UsuarioRepositorio $usuarios = null`): en producción se crean los reales, en pruebas se pasan los falsos.

Ejecutar desde la raíz del proyecto:

```bash
php tests/prueba_auth.php
php tests/prueba_registro.php
```

Las consultas SQL reales de `UsuarioRepositorio` no están cubiertas: requieren MariaDB.

---

## 12. Flujos completos

### 12.1 Inicio de sesión

```
login.php (POST)
 ├─ Csrf::validar ──✗──► "La página caducó"
 └─ AuthController::iniciarSesion
     ├─ ¿bloqueo por correo/IP? ──► bloqueo_temporal
     ├─ buscarPorCorreo + password_verify (o HASH_FALSO)
     │    └─ ✗ ──► registrarIntento(false) ──► credenciales
     ├─ ¿inactiva / sin verificar? ──► bloqueada / sin_verificar
     ├─ rehash si hace falta
     ├─ registrarIntento(true)
     └─ Sesion::iniciarUsuario (nuevo ID, nuevo CSRF) + bitácora
 └─ redirige a DESTINOS[rol]
```

### 12.2 Acceso a una página protegida

```
admin/habitaciones.php
 └─ Autorizacion::requerirPermiso('gestionar_habitaciones')
     ├─ Sesion::iniciar (caduca por inactividad / 8 h)
     ├─ revalidarSesion (relee usuario y permisos de la BD)
     │    └─ sin sesión o cuenta desactivada ──► login.php?estado=acceso_requerido
     ├─ sin permiso ──► 403
     └─ ✓ ──► muestra la página
```

### 12.3 Registro

```
registro.php (POST) ─► Csrf ─► UsuarioController::registrar
   validar campos ─► límite por IP ─► ¿correo existe? ─► crearCliente (bcrypt)
   ─► bitácora ─► login.php?estado=cuenta_creada
```

---

## 13. Resumen de medidas de seguridad

| Amenaza | Defensa | Dónde |
|---------|---------|-------|
| Inyección SQL | Consultas preparadas reales | `Conexion`, `UsuarioRepositorio`, `Bitacora` |
| XSS | `htmlspecialchars` + CSP sin scripts inline | Vistas, `config.php` |
| CSRF | Token por sesión en todo POST, `SameSite=Lax` | `Csrf`, `Sesion` |
| Robo de sesión | Cookie `HttpOnly`, caducidad 30 min / 8 h | `Sesion` |
| Fijación de sesión | `use_strict_mode` + `session_regenerate_id` | `Sesion` |
| Fuerza bruta | 5 fallos por correo / 20 por IP en 15 min | `AuthController` |
| Enumeración de correos | Mismo mensaje, mismo tiempo (`HASH_FALSO`), límite de registros | `AuthController`, `UsuarioController` |
| Contraseñas filtradas | bcrypt + rehash automático | `AuthController`, `UsuarioController` |
| Permisos obsoletos | Revalidación en cada petición | `revalidarSesion` |
| Archivos sensibles expuestos | Solo `public/` es accesible | `.htaccess` |
| Clickjacking | `X-Frame-Options`, `frame-ancestors` | `config.php` |
| Fugas de errores | `error_log` + mensajes genéricos | Páginas, `Autorizacion` |

---

## 14. Pendiente

- `ReservacionController.php`, `ReservacionRepositorio.php`, `admin/bitacora.php`, `recepcion/check_in.php` y `recepcion/check_out.php` están vacíos.
- 2FA, recuperación y restablecimiento de contraseña: solo interfaz (la tabla `tokens_usuario` y `roles.requiere_2fa` ya existen).
- El buscador de `index.php` no consulta disponibilidad todavía.
- El sitio se sirve por HTTP; con HTTPS se activan solos la cookie `secure` y `HSTS`.
