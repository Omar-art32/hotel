<?php
require __DIR__ . '/../app/config/config.php';

// Prototipo visual del doble factor para recepción y administración. Aún no valida códigos reales.
$avisos = [
    'codigo_incorrecto' => [
        'tono' => 'alerta',
        'titulo' => 'El código no es válido o ya expiró.',
        'texto' => 'Escribe el código que aparece ahora en tu aplicación de autenticación.',
    ],
    'exito' => [
        'tono' => 'exito',
        'titulo' => 'Identidad verificada.',
        'texto' => 'Te llevamos a tu panel en un momento.',
    ],
];
$aviso = $avisos[$_GET['estado'] ?? ''] ?? null;

$tituloPagina = 'Verificación en dos pasos';
require __DIR__ . '/../app/vistas/encabezado.php';
require __DIR__ . '/../app/vistas/acceso_inicio.php';
?>
    <main id="contenido" class="flex flex-1 items-center py-8 lg:py-12">
      <div class="mx-auto w-full max-w-md">
          <h1 id="titulo-verificacion" class="font-titulo text-4xl leading-tight text-balance sm:text-5xl">Confirma que eres tú</h1>
          <p class="mt-4 max-w-[36ch] text-musgo">
            Escribe el código de 6 dígitos que muestra tu aplicación de autenticación. El código cambia cada 30 segundos.
          </p>

          <div class="mt-10">
            <?php require __DIR__ . '/../app/vistas/aviso.php'; ?>

            <form id="formulario-verificacion" action="verificacion_2fa.php" method="post" novalidate
                  data-validar data-prototipo-destino="verificacion_2fa.php?estado=exito">
              <label for="codigo" class="etiqueta-campo">Código de verificación</label>
              <input id="codigo" name="codigo" type="text" required pattern="[0-9]{6}" maxlength="6"
                     inputmode="numeric" autocomplete="one-time-code" placeholder="000000"
                     class="campo-texto h-16 text-center font-titulo text-3xl tracking-[0.5em] indent-[0.5em] tabular-nums placeholder:text-piedra"
                     aria-describedby="ayuda-codigo error-codigo" data-solo-digitos
                     data-error-vacio="Escribe el código de verificación."
                     data-error-formato="El código tiene 6 dígitos, sin espacios ni letras.">
              <p id="ayuda-codigo" class="mt-2 text-sm text-musgo">Solo números, sin espacios.</p>
              <p id="error-codigo" class="error-campo" hidden></p>

              <button type="submit" class="boton-principal group mt-8">
                <svg class="hidden size-4 animate-spin group-aria-busy:block motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="2.5" />
                  <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" />
                </svg>
                <span class="group-aria-busy:hidden">Verificar código</span>
                <span class="hidden group-aria-busy:inline">Verificando…</span>
              </button>
            </form>

            <div class="mt-10 space-y-3 border-t border-linea pt-6 text-sm text-musgo">
              <p>¿Perdiste acceso a tu aplicación? Pide a un administrador que restablezca tu verificación.</p>
              <p><a href="login.php" class="enlace">Volver al inicio de sesión</a></p>
            </div>
          </div>
      </div>
    </main>
<?php
require __DIR__ . '/../app/vistas/acceso_fin.php';
require __DIR__ . '/../app/vistas/pie.php';
