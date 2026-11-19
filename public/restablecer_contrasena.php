<?php
require __DIR__ . '/../app/config/config.php';

// Prototipo visual: se llega desde el enlace del correo (?token=...). El estado llega por ?estado=...
// Cuando exista AuthController, él validará el token y decidirá el estado.
$avisos = [
    'token_invalido' => [
        'tono' => 'alerta',
        'titulo' => 'Este enlace ya no es válido.',
        'texto' => 'Los enlaces vencen en 60 minutos y solo se pueden usar una vez. Solicita uno nuevo para continuar.',
    ],
    'conexion' => [
        'tono' => 'alerta',
        'titulo' => 'No hay conexión con el servidor.',
        'texto' => 'Tu contraseña no cambió. Comprueba tu conexión a internet e inténtalo de nuevo.',
    ],
    'exito' => [
        'tono' => 'exito',
        'titulo' => 'Contraseña actualizada.',
        'texto' => 'Ya puedes iniciar sesión con tu contraseña nueva. Cerramos las sesiones abiertas en otros dispositivos.',
    ],
];
$estado = $_GET['estado'] ?? '';
$aviso = $avisos[$estado] ?? null;
$token = $_GET['token'] ?? '';

$tituloPagina = 'Crear contraseña nueva';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/acceso_inicio.php';
?>
    <main id="contenido" class="flex flex-1 items-center py-8 lg:py-12">
      <div class="mx-auto w-full max-w-md">
        <h1 id="titulo-restablecer" class="font-titulo text-4xl leading-tight text-balance sm:text-5xl">Crea una contraseña nueva</h1>
        <p class="mt-3 max-w-[40ch] text-musgo">
          Elige una contraseña que no uses en otros sitios.
        </p>

        <div class="mt-10">
          <?php require __DIR__ . '/../app/vistas/aviso.php'; ?>

          <?php if ($estado === 'exito'): ?>
            <a href="login.php" class="boton-principal">Iniciar sesión</a>
          <?php elseif ($estado === 'token_invalido'): ?>
            <a href="recuperar_contrasena.php" class="boton-principal">Solicitar un enlace nuevo</a>
          <?php else: ?>
            <form id="formulario-restablecer" action="restablecer_contrasena.php" method="post" novalidate
                  data-validar data-prototipo-destino="restablecer_contrasena.php?estado=exito">
              <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

              <div>
                <label for="contrasena" class="etiqueta-campo">Contraseña nueva</label>
                <div class="relative">
                  <input id="contrasena" name="contrasena" type="password" required minlength="8" class="campo-texto pr-24"
                         autocomplete="new-password" aria-describedby="ayuda-contrasena error-contrasena"
                         data-error-vacio="Escribe una contraseña nueva."
                         data-error-formato="La contraseña necesita al menos 8 caracteres.">
                  <button type="button" data-alternar-contrasena="contrasena" aria-controls="contrasena" aria-pressed="false"
                          class="absolute inset-y-1.5 right-1.5 rounded-lg px-3 text-sm font-medium text-tinta hover:bg-white focus-visible:outline-2 focus-visible:outline-tinta">
                    Mostrar
                  </button>
                </div>
                <p id="ayuda-contrasena" class="mt-2 text-sm text-musgo">Mínimo 8 caracteres. Una frase fácil de recordar funciona bien.</p>
                <p id="error-contrasena" class="error-campo" hidden></p>
              </div>

              <div class="mt-6">
                <label for="confirmar-contrasena" class="etiqueta-campo">Confirma la contraseña nueva</label>
                <input id="confirmar-contrasena" name="confirmar_contrasena" type="password" required class="campo-texto"
                       autocomplete="new-password" aria-describedby="error-confirmar-contrasena"
                       data-error-vacio="Escribe de nuevo la contraseña."
                       data-igual-a="contrasena" data-error-coincidencia="Las contraseñas no coinciden.">
                <p id="error-confirmar-contrasena" class="error-campo" hidden></p>
              </div>

              <button type="submit" class="boton-principal group mt-8">
                <svg class="hidden size-4 animate-spin group-aria-busy:block motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="2.5" />
                  <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" />
                </svg>
                <span class="group-aria-busy:hidden">Guardar contraseña</span>
                <span class="hidden group-aria-busy:inline">Guardando contraseña…</span>
              </button>
            </form>
          <?php endif; ?>

          <p class="mt-10 border-t border-linea pt-6 text-sm text-musgo">
            <a href="login.php" class="enlace font-medium">Volver a iniciar sesión</a>
          </p>
        </div>
      </div>
    </main>
<?php
require __DIR__ . '/../app/vistas/acceso_fin.php';
require __DIR__ . '/../app/vistas/pie.php';
