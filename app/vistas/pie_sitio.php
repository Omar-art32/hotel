<?php /* Pie del sitio público. Usa $enlacesSitio de cabecera_sitio.php. */ ?>
<footer class="bg-tinta text-white/70">
  <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-8 md:grid-cols-[2fr_1fr_1fr]">
    <div>
      <p class="font-titulo text-2xl text-white"><?= htmlspecialchars(NOMBRE_HOTEL) ?></p>
      <p class="mt-3 max-w-[40ch]">Hotel boutique de 18 habitaciones en el centro histórico de Querétaro.</p>
    </div>
    <nav aria-label="Secciones del sitio">
      <ul class="space-y-2">
        <?php foreach ($enlacesSitio as $ancla => $texto): ?>
          <li><a href="<?= $rutaInicio ?>#<?= $ancla ?>" class="rounded-sm hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"><?= $texto ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
    <nav aria-label="Cuenta e información legal">
      <ul class="space-y-2">
        <li><a href="login.php" class="rounded-sm hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Iniciar sesión</a></li>
        <li><a href="registro.php" class="rounded-sm hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Crear una cuenta</a></li>
        <li><a href="aviso_privacidad.php" class="rounded-sm hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Aviso de privacidad</a></li>
        <li><a href="terminos.php" class="rounded-sm hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">Términos y condiciones</a></li>
      </ul>
    </nav>
  </div>
  <p class="border-t border-white/10 px-4 py-6 text-center text-sm">© <?= date('Y') ?> <?= htmlspecialchars(NOMBRE_HOTEL) ?>. Todos los derechos reservados.</p>
</footer>
