<?php /* Espera $aviso = ['tono' => 'alerta'|'exito', 'titulo' => ..., 'texto' => ...] o null. */ ?>
<?php if ($aviso): ?>
  <div role="<?= $aviso['tono'] === 'exito' ? 'status' : 'alert' ?>"
       class="mb-8 rounded-2xl px-5 py-4 <?= $aviso['tono'] === 'exito' ? 'bg-exito/8' : 'bg-alerta/8' ?>">
    <p class="font-medium <?= $aviso['tono'] === 'exito' ? 'text-exito' : 'text-alerta' ?>"><?= htmlspecialchars($aviso['titulo']) ?></p>
    <p class="mt-1 text-sm text-musgo"><?= htmlspecialchars($aviso['texto']) ?></p>
  </div>
<?php endif; ?>
