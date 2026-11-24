<?php
/*
 * Documento legal con índice. Espera:
 * $tituloDocumento, $fechaActualizacion, $introduccion y
 * $secciones = [['id' => ..., 'titulo' => ..., 'contenido' => HTML redactado en el código, nunca datos del usuario], ...].
 */
?>
<main id="contenido" class="mx-auto max-w-7xl px-4 pb-24 pt-12 sm:px-8 lg:pt-16">
  <header class="max-w-3xl">
    <h1 class="font-titulo text-4xl leading-tight text-balance sm:text-5xl"><?= htmlspecialchars($tituloDocumento) ?></h1>
    <p class="mt-3 text-sm text-musgo">Última actualización: <?= htmlspecialchars($fechaActualizacion) ?></p>
    <p class="mt-6 text-lg text-musgo"><?= htmlspecialchars($introduccion) ?></p>
  </header>

  <div class="mt-12 grid gap-12 lg:grid-cols-[16rem_1fr] lg:gap-16">
    <nav aria-label="Contenido del documento" class="lg:sticky lg:top-24 lg:self-start">
      <p class="text-sm font-medium">En esta página</p>
      <ol class="mt-3 space-y-1 border-l border-linea text-sm text-musgo">
        <?php foreach ($secciones as $seccion): ?>
          <li>
            <a href="#<?= htmlspecialchars($seccion['id']) ?>" class="-ml-px block border-l-2 border-transparent py-1 pl-4 transition-colors hover:border-naranja hover:text-tinta focus-visible:outline-2 focus-visible:outline-tinta">
              <?= htmlspecialchars($seccion['titulo']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ol>
    </nav>

    <article class="max-w-[68ch] text-musgo [&_a]:text-tinta [&_a]:underline [&_a]:decoration-naranja [&_a]:decoration-2 [&_a]:underline-offset-4 [&_li]:mt-2 [&_mark]:rounded [&_mark]:bg-naranja/20 [&_mark]:px-1 [&_mark]:text-tinta [&_p]:mt-4 [&_p]:leading-relaxed [&_strong]:font-medium [&_strong]:text-tinta [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:marker:text-naranja">
      <?php foreach ($secciones as $seccion): ?>
        <section id="<?= htmlspecialchars($seccion['id']) ?>" class="scroll-mt-24 border-t border-linea py-10 first:border-t-0 first:pt-0" aria-labelledby="titulo-<?= htmlspecialchars($seccion['id']) ?>">
          <h2 id="titulo-<?= htmlspecialchars($seccion['id']) ?>" class="font-titulo text-2xl text-tinta"><?= htmlspecialchars($seccion['titulo']) ?></h2>
          <?= $seccion['contenido'] ?>
        </section>
      <?php endforeach; ?>
    </article>
  </div>
</main>
