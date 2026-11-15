<?php

/**
 * Управление классами: список + форма создания.
 *
 * @var array $classes Список классов с characters_count
 */
?>
<section class="panel">
    <h1>Классы</h1>

    <table>
        <thead>
        <tr><th>ID</th><th>Название</th><th>Описание</th><th>Персонажей</th><th>Действия</th></tr>
        </thead>
        <tbody>
        <?php foreach ($classes as $class): ?>
            <tr>
                <td><?= (int)$class['id'] ?></td>
                <td><?= e($class['name']) ?></td>
                <td><?= e(mb_strimwidth((string)$class['description'], 0, 60, '…')) ?></td>
                <td><?= (int)$class['characters_count'] ?></td>
                <td class="actions">
                    <a href="<?= url('admin_class_edit', ['id' => (int)$class['id']]) ?>">Изменить</a>
                    <?php if ((int)$class['characters_count'] === 0): ?>
                        <form method="post" action="<?= url('admin_class_delete') ?>" class="inline"
                              onsubmit="return confirm('Удалить класс «<?= e($class['name']) ?>»?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$class['id'] ?>">
                            <button type="submit" class="link-danger">Удалить</button>
                        </form>
                    <?php else: ?>
                        <span class="muted" title="Нельзя удалить: есть персонажи этого класса">используется</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="muted">Удалить можно только класс, который не использует ни один персонаж.</p>
</section>

<section class="panel form-panel">
    <h2>Новый класс</h2>
    <form method="post" action="<?= url('admin_class_create') ?>">
        <?= csrf_field() ?>
        <div class="form-row">
            <label for="class-name">Название *</label>
            <input id="class-name" type="text" name="name" required>
        </div>
        <div class="form-row">
            <label for="class-description">Описание</label>
            <textarea id="class-description" name="description" rows="2"></textarea>
        </div>
        <button type="submit" class="btn">Создать класс</button>
    </form>
</section>
