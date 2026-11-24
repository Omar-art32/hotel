<?php
/**
 * Prueba de AuthController, Sesion y Csrf sin base de datos (repositorio y bitácora simulados).
 * Ejecutar: php tests/prueba_auth.php
 */

require __DIR__ . '/dobles_prueba.php';

function nuevoEscenario(): array
{
    $repositorio = new RepositorioFalso([
        'cliente@hotel.mx' => usuarioPrueba(1, 2, 'cliente', 'cliente@hotel.mx'),
        'super@hotel.mx' => usuarioPrueba(2, 5, 'superadministrador', 'super@hotel.mx'),
        'inactivo@hotel.mx' => usuarioPrueba(3, 2, 'cliente', 'inactivo@hotel.mx', activo: false),
        'pendiente@hotel.mx' => usuarioPrueba(4, 2, 'cliente', 'pendiente@hotel.mx', verificado: false),
    ]);
    $bitacora = new BitacoraFalsa();
    $_SESSION = [];
    return [new AuthController($repositorio, $bitacora), $repositorio, $bitacora];
}

prepararSesionDePrueba();

echo "Inicio de sesión correcto\n";
[$auth, $repo, $bitacora] = nuevoEscenario();
$idAnterior = session_id();
comprobar('devuelve exito (correo con mayúsculas y espacios)', $auth->iniciarSesion('  Cliente@Hotel.MX ', 'Correcta#1', '10.0.0.1') === 'exito');
comprobar('guarda el usuario con su destino', (Sesion::usuarioActual()['destino'] ?? '') === 'mis_reservaciones.php');
comprobar('guarda los permisos del rol', (Sesion::usuarioActual()['permisos'] ?? []) === ['consultar_reservaciones']);
comprobar('no guarda el hash en la sesión', !isset(Sesion::usuarioActual()['contrasena_hash']));
comprobar('cambia el identificador de sesión', session_id() !== $idAnterior);
comprobar('registra el intento exitoso', $repo->intentos === [['cliente@hotel.mx', '10.0.0.1', true]]);
comprobar('registra inicio_sesion en la bitácora', $bitacora->eventos === ['inicio_sesion']);
comprobar('actualiza un hash con costo antiguo', isset($repo->hashesActualizados[1]));

echo "Destino por rol\n";
[$auth] = nuevoEscenario();
$auth->iniciarSesion('super@hotel.mx', 'Correcta#1', '10.0.0.1');
comprobar('superadministrador va a admin/usuarios.php', Sesion::usuarioActual()['destino'] === 'admin/usuarios.php');

echo "Errores sin revelar cuentas\n";
[$auth, $repo] = nuevoEscenario();
comprobar('contraseña incorrecta -> credenciales', $auth->iniciarSesion('cliente@hotel.mx', 'mal', '10.0.0.1') === 'credenciales');
comprobar('correo inexistente -> credenciales', $auth->iniciarSesion('nadie@hotel.mx', 'Correcta#1', '10.0.0.1') === 'credenciales');
comprobar('correo con formato inválido -> credenciales', $auth->iniciarSesion('no-es-correo', 'x', '10.0.0.1') === 'credenciales');
comprobar('cuenta inactiva con contraseña incorrecta -> credenciales', $auth->iniciarSesion('inactivo@hotel.mx', 'mal', '10.0.0.1') === 'credenciales');
comprobar('no inicia sesión tras los fallos', Sesion::usuarioActual() === null);
comprobar('registra los 4 fallos', count(array_filter($repo->intentos, fn ($i) => !$i[2])) === 4);
comprobar('campos vacíos -> campos_vacios', $auth->iniciarSesion('', '', '10.0.0.1') === 'campos_vacios');

echo "Estados de la cuenta (solo con contraseña correcta)\n";
[$auth, , $bitacora] = nuevoEscenario();
comprobar('cuenta inactiva -> bloqueada', $auth->iniciarSesion('inactivo@hotel.mx', 'Correcta#1', '10.0.0.1') === 'bloqueada');
comprobar('registra el intento en la bitácora', $bitacora->eventos === ['acceso_cuenta_inactiva']);
comprobar('cuenta sin verificar -> sin_verificar', $auth->iniciarSesion('pendiente@hotel.mx', 'Correcta#1', '10.0.0.1') === 'sin_verificar');
comprobar('ninguna de las dos inicia sesión', Sesion::usuarioActual() === null);

echo "Límite de intentos\n";
[$auth] = nuevoEscenario();
for ($i = 0; $i < AuthController::MAX_FALLIDOS_CORREO; $i++) {
    $auth->iniciarSesion('cliente@hotel.mx', 'mal', '10.0.0.' . $i);
}
comprobar('tras 5 fallos del correo bloquea aunque la contraseña sea correcta', $auth->iniciarSesion('cliente@hotel.mx', 'Correcta#1', '10.0.0.9') === 'bloqueo_temporal');
[$auth] = nuevoEscenario();
for ($i = 0; $i < AuthController::MAX_FALLIDOS_IP; $i++) {
    $auth->iniciarSesion("otro$i@hotel.mx", 'mal', '10.0.0.50');
}
comprobar('tras 20 fallos desde una IP la bloquea', $auth->iniciarSesion('cliente@hotel.mx', 'Correcta#1', '10.0.0.50') === 'bloqueo_temporal');
comprobar('otra IP sigue pudiendo entrar', $auth->iniciarSesion('cliente@hotel.mx', 'Correcta#1', '10.0.0.51') === 'exito');

echo "Cierre de sesión\n";
[$auth, , $bitacora] = nuevoEscenario();
$auth->iniciarSesion('cliente@hotel.mx', 'Correcta#1', '10.0.0.1');
$auth->cerrarSesion();
comprobar('borra el usuario de la sesión', Sesion::usuarioActual() === null);
comprobar('registra cierre_sesion', $bitacora->eventos === ['inicio_sesion', 'cierre_sesion']);

echo "Revalidación de la sesión en cada página\n";
Sesion::iniciar();
[$auth, $repo] = nuevoEscenario();
$auth->iniciarSesion('cliente@hotel.mx', 'Correcta#1', '10.0.0.1');
comprobar('cuenta sin cambios sigue con sesión', ($auth->revalidarSesion()['rol'] ?? '') === 'cliente');
$repo->modificar('cliente@hotel.mx', ['id_rol' => 5, 'rol' => 'superadministrador']);
$revalidado = $auth->revalidarSesion();
comprobar('un cambio de rol actualiza permisos y destino', $revalidado['permisos'] === ['gestionar_usuarios'] && $revalidado['destino'] === 'admin/usuarios.php');
$repo->modificar('cliente@hotel.mx', ['esta_activo' => 0]);
comprobar('cuenta desactivada -> pierde la sesión', $auth->revalidarSesion() === null && Sesion::usuarioActual() === null);
Sesion::iniciar();
comprobar('sin sesión devuelve null', $auth->revalidarSesion() === null);

echo "CSRF\n";
Sesion::iniciar();
$tokenAnterior = Csrf::token();
[$auth] = nuevoEscenario();
$_SESSION['csrf'] = $tokenAnterior;
$auth->iniciarSesion('cliente@hotel.mx', 'Correcta#1', '10.0.0.1');
comprobar('iniciar sesión cambia el token', Csrf::token() !== $tokenAnterior);

Sesion::iniciar();
$token = Csrf::token();
comprobar('acepta el token correcto', Csrf::validar($token));
comprobar('rechaza un token distinto', !Csrf::validar(str_repeat('a', 64)));
comprobar('rechaza la ausencia de token', !Csrf::validar(null));
comprobar('el campo oculto contiene el token', str_contains(Csrf::campo(), $token));

terminarPruebas();
