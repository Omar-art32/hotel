<?php
require __DIR__ . '/../app/config/config.php';

Sesion::iniciar();
$usuarioActual = Sesion::usuarioActual();
if ($usuarioActual !== null) {
    header('Location: ' . $usuarioActual['destino']);
    exit;
}

$avisos = [
    'errores' => [
        'tono' => 'alerta',
        'titulo' => 'Revisa los datos marcados.',
        'texto' => 'Corrige los campos indicados para crear tu cuenta.',
    ],
    'correo_registrado' => [
        'tono' => 'alerta',
        'titulo' => 'Ya existe una cuenta con este correo.',
        'texto' => 'Inicia sesión con él o usa otro correo.',
    ],
    'formulario_caducado' => [
        'tono' => 'alerta',
        'titulo' => 'La página caducó.',
        'texto' => 'Revisa tus datos y vuelve a enviar el formulario.',
    ],
    'conexion' => [
        'tono' => 'alerta',
        'titulo' => 'El sistema no está disponible en este momento.',
        'texto' => 'Tu cuenta no se creó. Inténtalo de nuevo en unos minutos.',
    ],
];

$estado = '';
$errores = [];
$valores = ['nombre' => '', 'apellidos' => '', 'correo' => '', 'telefono' => '', 'terminos' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $vacio) {
        $valores[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }
    if (!Csrf::validar($_POST['csrf'] ?? null)) {
        $estado = 'formulario_caducado';
    } else {
        try {
            ['estado' => $estado, 'errores' => $errores] = (new UsuarioController())->registrar($_POST);
        } catch (PDOException | RuntimeException $error) {
            error_log('Registro: ' . $error->getMessage());
            $estado = 'conexion';
        }
        if ($estado === 'exito') {
            // El correo viaja en la sesión, no en la URL: no queda en el historial ni en los registros del servidor.
            $_SESSION['correo_registrado'] = strtolower($valores['correo']);
            header('Location: login.php?estado=cuenta_creada');
            exit;
        }
    }
}
$aviso = $avisos[$estado] ?? null;

// Atributos de error de un campo: aria-invalid en el input y mensaje visible.
$invalido = fn (string $campo): string => isset($errores[$campo]) ? ' aria-invalid="true"' : '';
$mensajeError = fn (string $campo): string => isset($errores[$campo])
    ? '>' . htmlspecialchars($errores[$campo])
    : ' hidden>';
$valor = fn (string $campo): string => htmlspecialchars($valores[$campo]);

$tituloPagina = 'Crear una cuenta';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/acceso_inicio.php';
?>
    <main id="contenido" class="flex flex-1 items-center py-8 lg:py-12">
      <div class="mx-auto w-full max-w-md">
        <h1 id="titulo-registro" class="font-titulo text-4xl leading-tight text-balance sm:text-5xl">Crea tu cuenta</h1>
        <p class="mt-3 text-musgo">
          ¿Ya tienes cuenta? <a href="login.php" class="enlace font-medium">Iniciar sesión</a>
        </p>

        <div class="mt-10">
          <?php require __DIR__ . '/../app/vistas/aviso.php'; ?>

            <form id="formulario-registro" action="registro.php" method="post" novalidate data-validar>
              <?= Csrf::campo() ?>
              <div class="grid gap-6 sm:grid-cols-2">
                <div>
                  <label for="nombre" class="etiqueta-campo">Nombre</label>
                  <input id="nombre" name="nombre" type="text" required maxlength="60" class="campo-texto"
                         value="<?= $valor('nombre') ?>"<?= $invalido('nombre') ?>
                         autocomplete="given-name" aria-describedby="error-nombre"
                         data-error-vacio="Escribe tu nombre.">
                  <p id="error-nombre" class="error-campo"<?= $mensajeError('nombre') ?></p>
                </div>
                <div>
                  <label for="apellidos" class="etiqueta-campo">Apellidos</label>
                  <input id="apellidos" name="apellidos" type="text" required maxlength="80" class="campo-texto"
                         value="<?= $valor('apellidos') ?>"<?= $invalido('apellidos') ?>
                         autocomplete="family-name" aria-describedby="error-apellidos"
                         data-error-vacio="Escribe tus apellidos.">
                  <p id="error-apellidos" class="error-campo"<?= $mensajeError('apellidos') ?></p>
                </div>
              </div>

              <div class="mt-6">
                <label for="correo" class="etiqueta-campo">Correo electrónico</label>
                <input id="correo" name="correo" type="email" required maxlength="254" class="campo-texto"
                       value="<?= $valor('correo') ?>"<?= $invalido('correo') ?>
                       autocomplete="email" autocapitalize="none" spellcheck="false" inputmode="email"
                       aria-describedby="error-correo"
                       data-error-vacio="Escribe tu correo electrónico."
                       data-error-formato="Escribe un correo con el formato nombre@dominio.com.">
                <p id="error-correo" class="error-campo"<?= $mensajeError('correo') ?></p>
              </div>

              <div class="mt-6">
                <label for="telefono" class="etiqueta-campo">Teléfono <span class="font-normal text-musgo">(opcional)</span></label>
                <input id="telefono" name="telefono" type="tel" maxlength="20" class="campo-texto" autocomplete="tel" inputmode="tel"
                       value="<?= $valor('telefono') ?>"<?= $invalido('telefono') ?>
                       aria-describedby="ayuda-telefono error-telefono">
                <p id="ayuda-telefono" class="mt-2 text-sm text-musgo">Lo usamos solo para avisos sobre tu reservación.</p>
                <p id="error-telefono" class="error-campo"<?= $mensajeError('telefono') ?></p>
              </div>

              <div class="mt-6">
                <label for="contrasena" class="etiqueta-campo">Contraseña</label>
                <div class="relative">
                  <input id="contrasena" name="contrasena" type="password" required minlength="8" maxlength="72" class="campo-texto pr-24"<?= $invalido('contrasena') ?>
                         autocomplete="new-password" aria-describedby="ayuda-contrasena error-contrasena"
                         data-error-vacio="Escribe una contraseña."
                         data-error-formato="La contraseña necesita al menos 8 caracteres.">
                  <button type="button" data-alternar-contrasena="contrasena" aria-controls="contrasena" aria-pressed="false"
                          class="absolute inset-y-1.5 right-1.5 rounded-lg px-3 text-sm font-medium text-tinta hover:bg-white focus-visible:outline-2 focus-visible:outline-tinta">
                    Mostrar
                  </button>
                </div>
                <p id="ayuda-contrasena" class="mt-2 text-sm text-musgo">Mínimo 8 caracteres. Una frase fácil de recordar funciona bien.</p>
                <p id="error-contrasena" class="error-campo"<?= $mensajeError('contrasena') ?></p>
              </div>

              <div class="mt-6">
                <label for="confirmar-contrasena" class="etiqueta-campo">Confirma la contraseña</label>
                <input id="confirmar-contrasena" name="confirmar_contrasena" type="password" required class="campo-texto"<?= $invalido('confirmar_contrasena') ?>
                       autocomplete="new-password" aria-describedby="error-confirmar-contrasena"
                       data-error-vacio="Escribe de nuevo la contraseña."
                       data-igual-a="contrasena" data-error-coincidencia="Las contraseñas no coinciden.">
                <p id="error-confirmar-contrasena" class="error-campo"<?= $mensajeError('confirmar_contrasena') ?></p>
              </div>

              <div class="mt-6">
                <label class="flex cursor-pointer items-start gap-3 text-sm">
                  <input id="terminos" type="checkbox" name="terminos" value="1" required<?= $valores['terminos'] === '1' ? ' checked' : '' ?><?= $invalido('terminos') ?>
                         class="mt-0.5 size-4 shrink-0 rounded accent-naranja focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta"
                         aria-describedby="error-terminos"
                         data-error-vacio="Acepta los términos para crear tu cuenta.">
                  <span>Acepto los <a href="terminos.php" class="enlace">términos y condiciones</a> y el <a href="aviso_privacidad.php" class="enlace">aviso de privacidad</a>.</span>
                </label>
                <p id="error-terminos" class="error-campo"<?= $mensajeError('terminos') ?></p>
              </div>

              <button type="submit" class="boton-principal group mt-8">
                <svg class="hidden size-4 animate-spin group-aria-busy:block motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="2.5" />
                  <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" />
                </svg>
                <span class="group-aria-busy:hidden">Crear cuenta</span>
                <span class="hidden group-aria-busy:inline">Creando cuenta…</span>
              </button>
            </form>
        </div>
      </div>
    </main>
<?php
require __DIR__ . '/../app/vistas/acceso_fin.php';
require __DIR__ . '/../app/vistas/pie.php';
