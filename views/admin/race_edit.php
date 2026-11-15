<?php

/**
 * Редактирование расы.
 *
 * @var array<string, mixed> $race
 * @var array<string, string> $errors
 */
?>
<section class="panel form-panel">
    <h1>Раса: <?= e($race['name']) ?></h1>

    <form method="post" action="<?= url('admin_race_update', ['id' => (int)$race['id']]) ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="name">Название *</label>
            <input id="name" type="text" name="name" value="<?= e($race['name']) ?>"
                   class="<?= error_class($errors, 'name') ?>">
            <?= field_error($errors, 'name') ?>
        </div>

        <div class="form-row">
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="3"><?= e($race['description']) ?></textarea>
        </div>

        <button type="submit" class="btn">Сохранить</button>
        <a class="btn btn-secondary" href="<?= url('admin_races') ?>">Отмена</a>
    </form>
</section>
