<?php
require __DIR__ . '/../app/config/config.php';

// Prototipo visual: el estado llega por ?estado=... Cuando exista UsuarioController, él decidirá el estado.
// El mensaje de éxito es el mismo aunque el correo ya esté registrado, para no revelar qué cuentas existen.
$avisos = [
    'conexion' => [
        'tono' => 'alerta',
        'titulo' => 'No hay conexión con el servidor.',
        'texto' => 'Tu cuenta no se creó. Comprueba tu conexión a internet e inténtalo de nuevo.',
    ],
    'exito' => [
        'tono' => 'exito',
        'titulo' => 'Revisa tu correo electrónico.',
        'texto' => 'Te enviamos un enlace para confirmar tu cuenta. Ábrelo para poder iniciar sesión.',
    ],
];
$estado = $_GET['estado'] ?? '';
$aviso = $avisos[$estado] ?? null;

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

          <?php if ($estado !== 'exito'): ?>
            <form id="formulario-registro" action="registro.php" method="post" novalidate
                  data-validar data-prototipo-destino="registro.php?estado=exito">
              <div class="grid gap-6 sm:grid-cols-2">
                <div>
                  <label for="nombre" class="etiqueta-campo">Nombre</label>
                  <input id="nombre" name="nombre" type="text" required maxlength="80" class="campo-texto"
                         autocomplete="given-name" aria-describedby="error-nombre"
                         data-error-vacio="Escribe tu nombre.">
                  <p id="error-nombre" class="error-campo" hidden></p>
                </div>
                <div>
                  <label for="apellidos" class="etiqueta-campo">Apellidos</label>
                  <input id="apellidos" name="apellidos" type="text" required maxlength="80" class="campo-texto"
                         autocomplete="family-name" aria-describedby="error-apellidos"
                         data-error-vacio="Escribe tus apellidos.">
                  <p id="error-apellidos" class="error-campo" hidden></p>
                </div>
              </div>

              <div class="mt-6">
                <label for="correo" class="etiqueta-campo">Correo electrónico</label>
                <input id="correo" name="correo" type="email" required class="campo-texto"
                       autocomplete="email" autocapitalize="none" spellcheck="false" inputmode="email"
                       aria-describedby="error-correo"
                       data-error-vacio="Escribe tu correo electrónico."
                       data-error-formato="Escribe un correo con el formato nombre@dominio.com.">
                <p id="error-correo" class="error-campo" hidden></p>
              </div>

              <div class="mt-6">
                <label for="telefono" class="etiqueta-campo">Teléfono <span class="font-normal text-musgo">(opcional)</span></label>
                <input id="telefono" name="telefono" type="tel" class="campo-texto" autocomplete="tel" inputmode="tel"
                       aria-describedby="ayuda-telefono">
                <p id="ayuda-telefono" class="mt-2 text-sm text-musgo">Lo usamos solo para avisos sobre tu reservación.</p>
              </div>

              <div class="mt-6">
                <label for="contrasena" class="etiqueta-campo">Contraseña</label>
                <div class="relative">
                  <input id="contrasena" name="contrasena" type="password" required minlength="8" class="campo-texto pr-24"
                         autocomplete="new-password" aria-describedby="ayuda-contrasena error-contrasena"
                         data-error-vacio="Escribe una contraseña."
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
                <label for="confirmar-contrasena" class="etiqueta-campo">Confirma la contraseña</label>
                <input id="confirmar-contrasena" name="confirmar_contrasena" type="password" required class="campo-texto"
                       autocomplete="new-password" aria-describedby="error-confirmar-contrasena"
                       data-error-vacio="Escribe de nuevo la contraseña."
                       data-igual-a="contrasena" data-error-coincidencia="Las contraseñas no coinciden.">
                <p id="error-confirmar-contrasena" class="error-campo" hidden></p>
              </div>

              <div class="mt-6">
                <label class="flex cursor-pointer items-start gap-3 text-sm">
                  <input id="terminos" type="checkbox" name="terminos" value="1" required
                         class="mt-0.5 size-4 shrink-0 rounded accent-naranja focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta"
                         aria-describedby="error-terminos"
                         data-error-vacio="Acepta los términos para crear tu cuenta.">
                  <span>Acepto los <a href="terminos.php" class="enlace">términos y condiciones</a> y el <a href="aviso_privacidad.php" class="enlace">aviso de privacidad</a>.</span>
                </label>
                <p id="error-terminos" class="error-campo" hidden></p>
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
          <?php else: ?>
            <a href="login.php" class="boton-principal">Ir a iniciar sesión</a>
          <?php endif; ?>
        </div>
      </div>
    </main>
<?php
require __DIR__ . '/../app/vistas/acceso_fin.php';
require __DIR__ . '/../app/vistas/pie.php';
