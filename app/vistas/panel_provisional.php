<?php
/*
 * Página interna provisional: muestra quién inició sesión y permite cerrarla.
 * Espera $usuario (de Autorizacion::requerirPermiso) y $tituloPagina. Usa $rutaBase de encabezado.php.
 */
$nombresRoles = [
    'cliente' => 'Cliente',
    'recepcionista' => 'Recepción',
    'administrador' => 'Administración',
    'superadministrador' => 'Superadministración',
];
?>
<header class="border-b border-linea bg-ostion">
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-8">
    <a href="<?= htmlspecialchars($rutaBase) ?>index.php" class="flex items-center gap-3 rounded-lg focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tinta">
      <svg viewBox="0 0 24 32" class="h-7 w-5 text-naranja" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M1.5 32V12a10.5 10.5 0 0 1 21 0v20" />
        <path d="M6.5 32V12.5a5.5 5.5 0 0 1 11 0V32" />
      </svg>
      <span class="font-titulo text-xl"><?= htmlspecialchars(NOMBRE_HOTEL) ?></span>
    </a>

    <div class="flex items-center gap-4">
      <p class="hidden text-right text-sm sm:block">
        <span class="block font-medium"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></span>
        <span class="block text-musgo"><?= $nombresRoles[$usuario['rol']] ?? htmlspecialchars($usuario['rol']) ?></span>
      </p>
      <form action="<?= htmlspecialchars($rutaBase) ?>logout.php" method="post">
        <?= Csrf::campo() ?>
        <button type="submit" class="inline-flex h-10 items-center rounded-xl border border-linea px-4 text-sm font-medium transition-colors hover:bg-cal focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">
          Cerrar sesión
        </button>
      </form>
    </div>
  </div>
</header>

<main id="contenido" class="mx-auto max-w-7xl px-4 py-16 sm:px-8">
  <h1 class="font-titulo text-4xl sm:text-5xl"><?= htmlspecialchars($tituloPagina) ?></h1>
  <p class="mt-4 text-lg text-musgo">Hola, <?= htmlspecialchars($usuario['nombre']) ?>. Iniciaste sesión correctamente.</p>
  <p class="mt-8 max-w-[52ch] rounded-2xl bg-cal px-5 py-4 text-musgo">Esta sección está en construcción.</p>
</main>
