<?php
/* Cabecera del sitio público. */
// Fuera de la portada, las anclas deben apuntar a index.php.
$rutaInicio = basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? '' : 'index.php';
$enlacesSitio = [
    'habitaciones' => 'Habitaciones',
    'servicios' => 'Servicios',
    'galeria' => 'Galería',
    'opiniones' => 'Opiniones',
    'contacto' => 'Contacto',
];
?>
<header class="sticky top-0 z-20 border-b border-linea/70 bg-ostion/90 backdrop-blur">
  <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-8">
    <a href="index.php" class="flex items-center gap-3 rounded-lg text-tinta focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tinta">
      <svg viewBox="0 0 24 32" class="h-7 w-5 text-naranja" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M1.5 32V12a10.5 10.5 0 0 1 21 0v20" />
        <path d="M6.5 32V12.5a5.5 5.5 0 0 1 11 0V32" />
      </svg>
      <span class="font-titulo text-xl"><?= htmlspecialchars(NOMBRE_HOTEL) ?></span>
    </a>

    <nav aria-label="Principal" class="hidden lg:block">
      <ul class="flex gap-8 text-sm text-musgo">
        <?php foreach ($enlacesSitio as $ancla => $texto): ?>
          <li><a href="<?= $rutaInicio ?>#<?= $ancla ?>" class="rounded-sm transition-colors hover:text-tinta focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-tinta"><?= $texto ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="flex items-center gap-2">
      <a href="login.php" class="inline-flex h-10 items-center rounded-xl border border-linea px-4 text-sm font-medium transition-colors hover:bg-cal focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta">Iniciar sesión</a>
      <a href="<?= $rutaInicio ?>#reservar" class="hidden h-10 items-center rounded-xl bg-naranja px-4 text-sm font-medium text-tinta transition-colors hover:bg-naranja-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tinta sm:inline-flex">Reservar</a>

      <details class="relative lg:hidden" data-menu>
        <summary aria-label="Abrir menú" class="grid size-10 cursor-pointer list-none place-items-center rounded-xl hover:bg-cal focus-visible:outline-2 focus-visible:outline-tinta [&::-webkit-details-marker]:hidden">
          <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </summary>
        <nav aria-label="Principal" class="absolute right-0 top-12 w-60 rounded-2xl border border-linea bg-white p-2 shadow-[0_12px_40px_rgb(30_42_40/0.12)]">
          <ul>
            <?php foreach ($enlacesSitio as $ancla => $texto): ?>
              <li><a href="<?= $rutaInicio ?>#<?= $ancla ?>" class="block rounded-xl px-4 py-3 hover:bg-cal"><?= $texto ?></a></li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </details>
    </div>
  </div>
</header>
