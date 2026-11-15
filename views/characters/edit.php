<?php

/**
 * Страница редактирования персонажа.
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
    <h1>Редактирование: <?= e($character['name']) ?></h1>
    <?php require __DIR__ . '/_form.php'; ?>
</section>
