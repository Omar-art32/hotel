<?php
/**
 * Prueba de UsuarioController::registrar() sin base de datos.
 * Ejecutar: php tests/prueba_registro.php
 */

require __DIR__ . '/dobles_prueba.php';

function escenarioRegistro(): array
{
    $repositorio = new RepositorioFalso(['existente@hotel.mx' => usuarioPrueba(1, 2, 'cliente', 'existente@hotel.mx')]);
    $bitacora = new BitacoraFalsa();
    $_SESSION = [];
    return [new UsuarioController($repositorio, $bitacora), $repositorio, $bitacora];
}

function datosValidos(array $cambios = []): array
{
    return array_merge([
        'nombre' => ' Ana ', 'apellidos' => 'Pérez Núñez', 'correo' => ' Ana@Hotel.MX ', 'telefono' => '',
        'contrasena' => 'Frase segura 1!', 'confirmar_contrasena' => 'Frase segura 1!', 'terminos' => '1',
    ], $cambios);
}

prepararSesionDePrueba();

echo "Registro correcto\n";
[$controlador, $repo, $bitacora] = escenarioRegistro();
$resultado = $controlador->registrar(datosValidos());
$creado = $repo->buscarPorCorreo('ana@hotel.mx');
comprobar('devuelve exito', $resultado['estado'] === 'exito');
comprobar('guarda el correo en minúsculas y sin espacios', $creado !== null);
comprobar('guarda el nombre sin espacios sobrantes', ($creado['nombre'] ?? '') === 'Ana');
comprobar('guarda la contraseña cifrada, no en texto', password_verify('Frase segura 1!', $creado['contrasena_hash'] ?? '') && ($creado['contrasena_hash'] ?? '') !== 'Frase segura 1!');
comprobar('teléfono vacío se guarda como NULL', array_key_exists('telefono', $creado) && $creado['telefono'] === null);
comprobar('se crea con rol cliente, activa y habilitada', ($creado['rol'] ?? '') === 'cliente' && $creado['esta_activo'] && $creado['esta_verificado']);
comprobar('no inicia sesión: la persona entra después por el login', Sesion::usuarioActual() === null);
comprobar('registra registro_usuario en la bitácora', $bitacora->eventos === ['registro_usuario']);

echo "Validación en el servidor\n";
$casos = [
    'nombre vacío' => [['nombre' => '  '], 'nombre'],
    'nombre de más de 60 caracteres' => [['nombre' => str_repeat('á', 61)], 'nombre'],
    'nombre con números' => [['nombre' => 'Ana2'], 'nombre'],
    'nombre que empieza con guion' => [['nombre' => '-Ana'], 'nombre'],
    'apellidos vacíos' => [['apellidos' => ''], 'apellidos'],
    'apellidos con símbolos' => [['apellidos' => 'Pérez <b>'], 'apellidos'],
    'correo vacío' => [['correo' => ''], 'correo'],
    'correo sin formato' => [['correo' => 'ana@'], 'correo'],
    'teléfono con letras' => [['telefono' => '442-ABC-1234'], 'telefono'],
    'contraseña de 7 caracteres' => [['contrasena' => '1234567', 'confirmar_contrasena' => '1234567'], 'contrasena'],
    'contraseña sin mayúscula' => [['contrasena' => 'frase segura 1!', 'confirmar_contrasena' => 'frase segura 1!'], 'contrasena'],
    'contraseña sin minúscula' => [['contrasena' => 'FRASE SEGURA 1!', 'confirmar_contrasena' => 'FRASE SEGURA 1!'], 'contrasena'],
    'contraseña sin número' => [['contrasena' => 'Frase segura!', 'confirmar_contrasena' => 'Frase segura!'], 'contrasena'],
    'contraseña sin signo especial' => [['contrasena' => 'Frase segura 1', 'confirmar_contrasena' => 'Frase segura 1'], 'contrasena'],
    'contraseña de más de 72 bytes' => [['contrasena' => 'Aa1!' . str_repeat('a', 69), 'confirmar_contrasena' => 'Aa1!' . str_repeat('a', 69)], 'contrasena'],
    'confirmación distinta' => [['confirmar_contrasena' => 'otra frase'], 'confirmar_contrasena'],
    'términos sin aceptar' => [['terminos' => ''], 'terminos'],
];
foreach ($casos as $descripcion => [$cambios, $campo]) {
    [$controlador, $repo] = escenarioRegistro();
    $resultado = $controlador->registrar(datosValidos($cambios));
    comprobar("$descripcion -> error en $campo, sin crear cuenta", $resultado['estado'] === 'errores' && isset($resultado['errores'][$campo]) && $repo->buscarPorCorreo('ana@hotel.mx') === null);
}
[$controlador] = escenarioRegistro();
comprobar('nombre y apellidos con acentos, apóstrofo, punto y guion', $controlador->registrar(datosValidos(['nombre' => 'Ma. José', 'apellidos' => "O'Connor Pérez-Núñez"]))['estado'] === 'exito');
[$controlador] = escenarioRegistro();
comprobar('teléfono válido con lada y espacios', $controlador->registrar(datosValidos(['telefono' => '+52 (442) 000-0000']))['estado'] === 'exito');
[$controlador] = escenarioRegistro();
comprobar('formulario vacío marca los 6 campos obligatorios', count($controlador->registrar([])['errores']) === 6);

echo "Correo ya registrado\n";
[$controlador, , $bitacora] = escenarioRegistro();
$resultado = $controlador->registrar(datosValidos(['correo' => 'EXISTENTE@hotel.mx']));
comprobar('devuelve correo_registrado', $resultado['estado'] === 'correo_registrado' && isset($resultado['errores']['correo']));
comprobar('no inicia sesión', Sesion::usuarioActual() === null);
comprobar('no registra nada en la bitácora', $bitacora->eventos === []);
[$controlador, $repo] = escenarioRegistro();
$repo->simularCorreoDuplicado = true;
comprobar('registro simultáneo del mismo correo (error 23000) -> correo_registrado', $controlador->registrar(datosValidos())['estado'] === 'correo_registrado');

terminarPruebas();
