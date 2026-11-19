<?php
require __DIR__ . '/../app/config/config.php';

Sesion::iniciar();
$usuarioActual = Sesion::usuarioActual();
if ($usuarioActual !== null) {
    header('Location: ' . $usuarioActual['destino']);
    exit;
}

// Los mensajes nunca indican si el correo está registrado.
$avisos = [
    'campos_vacios' => [
        'tono' => 'alerta',
        'titulo' => 'Faltan datos para iniciar sesión.',
        'texto' => 'Escribe tu correo electrónico y tu contraseña.',
    ],
    'credenciales' => [
        'tono' => 'alerta',
        'titulo' => 'El correo o la contraseña no son correctos.',
        'texto' => 'Revisa ambos datos e inténtalo de nuevo. Si no recuerdas tu contraseña, puedes restablecerla.',
    ],
    'bloqueada' => [
        'tono' => 'alerta',
        'titulo' => 'Esta cuenta no tiene acceso en este momento.',
        'texto' => 'Comunícate con recepción para revisar el estado de tu cuenta.',
    ],
    'sin_verificar' => [
        'tono' => 'alerta',
        'titulo' => 'Esta cuenta aún no está habilitada.',
        'texto' => 'Comunícate con recepción para activarla.',
    ],
    'bloqueo_temporal' => [
        'tono' => 'alerta',
        'titulo' => 'Demasiados intentos fallidos.',
        'texto' => 'Por seguridad, espera ' . AuthController::VENTANA_MINUTOS . ' minutos antes de volver a intentarlo.',
    ],
    'formulario_caducado' => [
        'tono' => 'alerta',
        'titulo' => 'La página caducó.',
        'texto' => 'Escribe de nuevo tus datos e inicia sesión.',
    ],
    'conexion' => [
        'tono' => 'alerta',
        'titulo' => 'El sistema no está disponible en este momento.',
        'texto' => 'Inténtalo de nuevo en unos minutos.',
    ],
    'cuenta_creada' => [
        'tono' => 'exito',
        'titulo' => 'Tu cuenta está lista.',
        'texto' => 'Inicia sesión con tu correo y tu contraseña.',
    ],
    'sesion_cerrada' => [
        'tono' => 'exito',
        'titulo' => 'Cerraste sesión.',
        'texto' => 'Vuelve cuando quieras.',
    ],
    'acceso_requerido' => [
        'tono' => 'alerta',
        'titulo' => 'Inicia sesión para continuar.',
        'texto' => 'La página que intentaste abrir requiere una cuenta, o tu sesión terminó por inactividad.',
    ],
];

$estado = '';
$correo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim((string) ($_POST['correo'] ?? ''));
    if (!Csrf::validar($_POST['csrf'] ?? null)) {
        $estado = 'formulario_caducado';
    } else {
        try {
            $estado = (new AuthController())->iniciarSesion($correo, (string) ($_POST['contrasena'] ?? ''), Bitacora::ipCliente());
        } catch (PDOException $error) {
            error_log('Login: ' . $error->getMessage());
            $estado = 'conexion';
        }
        if ($estado === 'exito') {
            header('Location: ' . Sesion::usuarioActual()['destino']);
            exit;
        }
    }
} elseif (in_array($_GET['estado'] ?? '', ['cuenta_creada', 'sesion_cerrada', 'acceso_requerido'], true)) {
    // Solo los mensajes que llegan por redirección; los errores de acceso nunca vienen por la URL.
    $estado = $_GET['estado'];
    if ($estado === 'cuenta_creada') {
        $correo = $_SESSION['correo_registrado'] ?? '';
        unset($_SESSION['correo_registrado']);
    }
}
$aviso = $avisos[$estado] ?? null;

$tituloPagina = 'Iniciar sesión';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/acceso_inicio.php';
?>
    <main id="contenido" class="flex flex-1 items-center py-8 lg:py-12">
      <div class="mx-auto w-full max-w-md">
          <h1 id="titulo-login" class="font-titulo text-4xl leading-tight text-balance sm:text-5xl">Te damos la bienvenida</h1>
          <p class="mt-3 text-musgo">
            ¿Aún no tienes cuenta? <a href="registro.php" class="enlace font-medium">Crear una cuenta</a>
          </p>

          <div class="mt-10">
            <?php require __DIR__ . '/../app/vistas/aviso.php'; ?>

            <form id="formulario-login" action="login.php" method="post" novalidate data-validar>
              <?= Csrf::campo() ?>
              <div>
                <label for="correo" class="etiqueta-campo">Correo electrónico</label>
                <input id="correo" name="correo" type="email" required class="campo-texto"
                       value="<?= htmlspecialchars($correo) ?>"
                       autocomplete="username" autocapitalize="none" spellcheck="false" inputmode="email"
                       aria-describedby="error-correo"
                       data-error-vacio="Escribe tu correo electrónico."
                       data-error-formato="Escribe un correo con el formato nombre@dominio.com.">
                <p id="error-correo" class="error-campo" hidden></p>
              </div>

              <div class="mt-6">
                <label for="contrasena" class="etiqueta-campo">Contraseña</label>
                <div class="relative">
                  <input id="contrasena" name="contrasena" type="password" required class="campo-texto pr-24"
                         autocomplete="current-password" aria-describedby="error-contrasena"
                         data-error-vacio="Escribe tu contraseña.">
                  <button type="button" data-alternar-contrasena="contrasena" aria-controls="contrasena" aria-pressed="false"
                          class="absolute inset-y-1.5 right-1.5 rounded-lg px-3 text-sm font-medium text-tinta hover:bg-white focus-visible:outline-2 focus-visible:outline-tinta">
                    Mostrar
                  </button>
                </div>
                <p id="error-contrasena" class="error-campo" hidden></p>
              </div>

              <button type="submit" class="boton-principal group mt-8">
                <svg class="hidden size-4 animate-spin group-aria-busy:block motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="2.5" />
                  <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" />
                </svg>
                <span class="group-aria-busy:hidden">Iniciar sesión</span>
                <span class="hidden group-aria-busy:inline">Iniciando sesión…</span>
              </button>
            </form>
          </div>
      </div>
    </main>
<?php
require __DIR__ . '/../app/vistas/acceso_fin.php';
require __DIR__ . '/../app/vistas/pie.php';
