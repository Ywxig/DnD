<?php

/**
 * Редактирование персонажа (админ) — та же форма, что и у игрока.
 *
 * @var string                $action
 * @var array<string, mixed>  $data
 * @var array<string, string> $errors
 * @var array                 $races
 * @var array                 $classes
 * @var array<string, mixed>  $character
 */
?>
<section class="panel form-panel">
    <h1>Персонаж: <?= e($character['name']) ?></h1>
    <p class="muted">Владелец: <?= e($character['owner_name'] ?? '(неизвестен)') ?></p>
    <?php require __DIR__ . '/../characters/_form.php'; ?>
    <p><a class="btn btn-secondary" href="<?= url('admin_characters') ?>">← К списку персонажей</a></p>
</section>
