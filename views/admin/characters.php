<?php

/**
 * Список всех персонажей (админ).
 *
 * @var array  $rows
 * @var int    $total
 * @var int    $page
 * @var int    $pages
 * @var string $name Поиск по имени
 */
?>
<section class="panel">
    <h1>Персонажи (<?= (int)$total ?>)</h1>

    <form method="get" action="index.php" class="search-form inline-search">
        <input type="hidden" name="r" value="admin_characters">
        <input type="text" name="name" placeholder="Имя персонажа…" value="<?= e($name) ?>">
        <button type="submit" class="btn btn-secondary">Найти</button>
    </form>

    <?php if ($rows === []): ?>
        <p class="muted">Ничего не найдено.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>ID</th><th>Имя</th><th>Раса</th><th>Класс</th><th>Уровень</th>
                <th>Игрок</th><th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int)$row['id'] ?></td>
                    <td><a href="<?= url('character_view', ['id' => (int)$row['id']]) ?>"><?= e($row['name']) ?></a></td>
                    <td><?= e($row['race_name']) ?></td>
                    <td><?= e($row['class_name']) ?></td>
                    <td><?= (int)$row['level'] ?></td>
                    <td><?= e($row['owner_name']) ?></td>
                    <td class="actions">
                        <a href="<?= url('admin_character_edit', ['id' => (int)$row['id']]) ?>">Изменить</a>
                        <form method="post" action="<?= url('admin_character_delete') ?>" class="inline"
                              onsubmit="return confirm('Удалить персонажа «<?= e($row['name']) ?>»?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                            <button type="submit" class="link-danger">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a class="btn btn-secondary" href="<?= url('admin_characters', ['name' => $name, 'page' => $page - 1]) ?>">← Назад</a>
            <?php endif; ?>
            <span>Страница <?= (int)$page ?> из <?= (int)$pages ?></span>
            <?php if ($page < $pages): ?>
                <a class="btn btn-secondary" href="<?= url('admin_characters', ['name' => $name, 'page' => $page + 1]) ?>">Вперёд →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
