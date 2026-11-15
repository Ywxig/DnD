<?php

/**
 * Редактирование класса.
 *
 * @var array<string, mixed>  $class
 * @var array<string, string> $errors
 */
?>
<section class="panel form-panel">
    <h1>Класс: <?= e($class['name']) ?></h1>

    <form method="post" action="<?= url('admin_class_update', ['id' => (int)$class['id']]) ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="name">Название *</label>
            <input id="name" type="text" name="name" value="<?= e($class['name']) ?>"
                   class="<?= error_class($errors, 'name') ?>">
            <?= field_error($errors, 'name') ?>
        </div>

        <div class="form-row">
            <label for="description">Описание</label>
            <textarea id="description" name="description" rows="3"><?= e($class['description']) ?></textarea>
        </div>

        <button type="submit" class="btn">Сохранить</button>
        <a class="btn btn-secondary" href="<?= url('admin_classes') ?>">Отмена</a>
    </form>
</section>
