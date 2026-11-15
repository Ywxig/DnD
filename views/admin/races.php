<?php

/**
 * Управление расами: список + форма создания.
 *
 * @var array $races Список рас с characters_count
 */
?>
<section class="panel">
    <h1>Расы</h1>

    <table>
        <thead>
        <tr><th>ID</th><th>Название</th><th>Описание</th><th>Персонажей</th><th>Действия</th></tr>
        </thead>
        <tbody>
        <?php foreach ($races as $race): ?>
            <tr>
                <td><?= (int)$race['id'] ?></td>
                <td><?= e($race['name']) ?></td>
                <td><?= e(mb_strimwidth((string)$race['description'], 0, 60, '…')) ?></td>
                <td><?= (int)$race['characters_count'] ?></td>
                <td class="actions">
                    <a href="<?= url('admin_race_edit', ['id' => (int)$race['id']]) ?>">Изменить</a>
                    <?php if ((int)$race['characters_count'] === 0): ?>
                        <form method="post" action="<?= url('admin_race_delete') ?>" class="inline"
                              onsubmit="return confirm('Удалить расу «<?= e($race['name']) ?>»?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$race['id'] ?>">
                            <button type="submit" class="link-danger">Удалить</button>
                        </form>
                    <?php else: ?>
                        <span class="muted" title="Нельзя удалить: есть персонажи этой расы">используется</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="muted">Удалить можно только расу, которую не использует ни один персонаж.</p>
</section>

<section class="panel form-panel">
    <h2>Новая раса</h2>
    <form method="post" action="<?= url('admin_race_create') ?>">
        <?= csrf_field() ?>
        <div class="form-row">
            <label for="race-name">Название *</label>
            <input id="race-name" type="text" name="name" required>
        </div>
        <div class="form-row">
            <label for="race-description">Описание</label>
            <textarea id="race-description" name="description" rows="2"></textarea>
        </div>
        <button type="submit" class="btn">Создать расу</button>
    </form>
</section>
