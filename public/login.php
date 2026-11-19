<?php
require __DIR__ . '/../app/config/config.php';

// Prototipo visual: el estado llega por ?estado=... Cuando exista AuthController, él decidirá el estado.
// Los mensajes nunca indican si el correo está registrado.
$avisos = [
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
        'titulo' => 'Falta confirmar tu correo electrónico.',
        'texto' => 'Abre el enlace que te enviamos al crear tu cuenta. Revisa también la carpeta de correo no deseado.',
    ],
    'conexion' => [
        'tono' => 'alerta',
        'titulo' => 'No hay conexión con el servidor.',
        'texto' => 'Tus datos no se enviaron. Comprueba tu conexión a internet e inténtalo de nuevo.',
    ],
    'exito' => [
        'tono' => 'exito',
        'titulo' => 'Sesión iniciada.',
        'texto' => 'Te llevamos a tu cuenta en un momento.',
    ],
];
$aviso = $avisos[$_GET['estado'] ?? ''] ?? null;

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

            <form id="formulario-login" action="login.php" method="post" novalidate
                  data-validar data-prototipo-destino="login.php?estado=exito">
              <div>
                <label for="correo" class="etiqueta-campo">Correo electrónico</label>
                <input id="correo" name="correo" type="email" required class="campo-texto"
                       autocomplete="username" autocapitalize="none" spellcheck="false" inputmode="email"
                       aria-describedby="error-correo"
                       data-error-vacio="Escribe tu correo electrónico."
                       data-error-formato="Escribe un correo con el formato nombre@dominio.com.">
                <p id="error-correo" class="error-campo" hidden></p>
              </div>

              <div class="mt-6">
                <div class="mb-2 flex items-baseline justify-between">
                  <label for="contrasena" class="etiqueta-campo mb-0">Contraseña</label>
                  <a href="recuperar_contrasena.php" class="enlace text-sm">¿Olvidaste tu contraseña?</a>
                </div>
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

              <label class="mt-6 flex cursor-pointer items-center gap-3 text-sm">
                <input type="checkbox" name="recordar" value="1"
                       class="size-4 rounded accent-naranja focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
                Mantener la sesión iniciada en este dispositivo
              </label>

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
