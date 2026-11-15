<?php

/**
 * Кабинет: список персонажей текущего пользователя.
 *
 * @var array $characters
 */
?>
<section class="panel">
    <div class="panel-head">
        <h1>Мои персонажи</h1>
        <a class="btn" href="<?= url('characters_create') ?>">+ Создать</a>
    </div>

    <?php if ($characters === []): ?>
        <p class="muted">У вас пока нет персонажей. Самое время <a href="<?= url('characters_create') ?>">создать первого героя</a>!</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Имя</th><th>Раса</th><th>Класс</th><th>Уровень</th>
                <th>Мировоззрение</th><th>Создан</th><th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($characters as $character): ?>
                <tr>
                    <td><a href="<?= url('character_view', ['id' => (int)$character['id']]) ?>"><?= e($character['name']) ?></a></td>
                    <td><?= e($character['race_name']) ?></td>
                    <td><?= e($character['class_name']) ?></td>
                    <td><?= (int)$character['level'] ?></td>
                    <td><?= e($character['alignment']) ?></td>
                    <td><?= format_date($character['created_at']) ?></td>
                    <td class="actions">
                        <a href="<?= url('characters_edit', ['id' => (int)$character['id']]) ?>">Изменить</a>
                        <form method="post" action="<?= url('characters_delete') ?>" class="inline"
                              onsubmit="return confirm('Удалить персонажа «<?= e($character['name']) ?>»?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$character['id'] ?>">
                            <button type="submit" class="link-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
