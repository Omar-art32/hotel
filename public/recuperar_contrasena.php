<?php
require __DIR__ . '/../app/config/config.php';

// Prototipo visual: el estado llega por ?estado=... Cuando exista AuthController, él decidirá el estado.
// El mensaje de éxito es el mismo aunque el correo no esté registrado, para no revelar qué cuentas existen.
$avisos = [
    'conexion' => [
        'tono' => 'alerta',
        'titulo' => 'No hay conexión con el servidor.',
        'texto' => 'La solicitud no se envió. Comprueba tu conexión a internet e inténtalo de nuevo.',
    ],
    'exito' => [
        'tono' => 'exito',
        'titulo' => 'Revisa tu correo electrónico.',
        'texto' => 'Si hay una cuenta con ese correo, te enviamos un enlace para crear una contraseña nueva. El enlace vence en 60 minutos.',
    ],
];
$estado = $_GET['estado'] ?? '';
$aviso = $avisos[$estado] ?? null;

$tituloPagina = 'Recuperar contraseña';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/acceso_inicio.php';
?>
    <main id="contenido" class="flex flex-1 items-center py-8 lg:py-12">
      <div class="mx-auto w-full max-w-md">
        <h1 id="titulo-recuperar" class="font-titulo text-4xl leading-tight text-balance sm:text-5xl">Recupera tu contraseña</h1>
        <p class="mt-3 max-w-[40ch] text-musgo">
          Escribe el correo de tu cuenta y te enviaremos un enlace para crear una contraseña nueva.
        </p>

        <div class="mt-10">
          <?php require __DIR__ . '/../app/vistas/aviso.php'; ?>

          <?php if ($estado !== 'exito'): ?>
            <form id="formulario-recuperar" action="recuperar_contrasena.php" method="post" novalidate
                  data-validar data-prototipo-destino="recuperar_contrasena.php?estado=exito">
              <label for="correo" class="etiqueta-campo">Correo electrónico</label>
              <input id="correo" name="correo" type="email" required class="campo-texto"
                     autocomplete="email" autocapitalize="none" spellcheck="false" inputmode="email"
                     aria-describedby="error-correo"
                     data-error-vacio="Escribe tu correo electrónico."
                     data-error-formato="Escribe un correo con el formato nombre@dominio.com.">
              <p id="error-correo" class="error-campo" hidden></p>

              <button type="submit" class="boton-principal group mt-8">
                <svg class="hidden size-4 animate-spin group-aria-busy:block motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="2.5" />
                  <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" />
                </svg>
                <span class="group-aria-busy:hidden">Enviar enlace</span>
                <span class="hidden group-aria-busy:inline">Enviando enlace…</span>
              </button>
            </form>
          <?php else: ?>
            <p class="text-sm text-musgo">
              ¿No llegó? Revisa la carpeta de correo no deseado o <a href="recuperar_contrasena.php" class="enlace">solicita otro enlace</a>.
            </p>
          <?php endif; ?>

          <p class="mt-10 border-t border-linea pt-6 text-sm text-musgo">
            ¿Ya la recordaste? <a href="login.php" class="enlace font-medium">Volver a iniciar sesión</a>
          </p>
        </div>
      </div>
    </main>
<?php
require __DIR__ . '/../app/vistas/acceso_fin.php';
require __DIR__ . '/../app/vistas/pie.php';
