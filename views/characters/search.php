<?php

/**
 * Публичный поиск персонажей.
 *
 * @var array<string, mixed> $filters    Значения фильтров
 * @var array                $rows       Найденные персонажи
 * @var int                  $total      Всего найдено
 * @var int                  $page       Текущая страница
 * @var int                  $pages      Всего страниц
 * @var array                $races      Расы для списка
 * @var array                $classes    Классы для списка
 * @var list<string>         $alignments Допустимые мировоззрения
 */
?>
<section class="panel">
    <h1>Поиск персонажей</h1>

    <form method="get" action="index.php" class="search-form">
        <input type="hidden" name="r" value="characters_search">
        <div class="form-grid">
            <div class="form-row">
                <label for="name">Имя (частично)</label>
                <input id="name" type="text" name="name" value="<?= e($filters['name']) ?>">
            </div>
            <div class="form-row">
                <label for="race_id">Раса</label>
                <select id="race_id" name="race_id">
                    <option value="">— любая —</option>
                    <?php foreach ($races as $race): ?>
                        <option value="<?= (int)$race['id'] ?>"<?= selected_option($filters['race_id'], $race['id']) ?>>
                            <?= e($race['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="class_id">Класс</label>
                <select id="class_id" name="class_id">
                    <option value="">— любой —</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int)$class['id'] ?>"<?= selected_option($filters['class_id'], $class['id']) ?>>
                            <?= e($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="level_from">Уровень от</label>
                <input id="level_from" type="number" name="level_from" min="1" max="20" value="<?= e($filters['level_from'] ?: '') ?>">
            </div>
            <div class="form-row">
                <label for="level_to">Уровень до</label>
                <input id="level_to" type="number" name="level_to" min="1" max="20" value="<?= e($filters['level_to'] ?: '') ?>">
            </div>
            <div class="form-row">
                <label for="alignment">Мировоззрение</label>
                <select id="alignment" name="alignment">
                    <option value="">— любое —</option>
                    <?php foreach ($alignments as $alignment): ?>
                        <option value="<?= e($alignment) ?>"<?= selected_option($filters['alignment'], $alignment) ?>>
                            <?= e($alignment) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn">Найти</button>
        <a class="btn btn-secondary" href="<?= url('characters_search') ?>">Сбросить</a>
    </form>
</section>

<section class="panel">
    <h2>Результаты: <?= (int)$total ?></h2>

    <?php if ($rows === []): ?>
        <p class="muted">Ничего не найдено. Попробуйте изменить условия поиска.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Имя</th><th>Раса</th><th>Класс</th><th>Уровень</th>
                <th>Мировоззрение</th><th>Игрок</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><a href="<?= url('character_view', ['id' => (int)$row['id']]) ?>"><?= e($row['name']) ?></a></td>
                    <td><?= e($row['race_name']) ?></td>
                    <td><?= e($row['class_name']) ?></td>
                    <td><?= (int)$row['level'] ?></td>
                    <td><?= e($row['alignment']) ?></td>
                    <td><?= e($row['owner_name']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a class="btn btn-secondary" href="<?= url('characters_search', array_merge($filters, ['page' => $page - 1])) ?>">← Назад</a>
            <?php endif; ?>
            <span>Страница <?= (int)$page ?> из <?= (int)$pages ?></span>
            <?php if ($page < $pages): ?>
                <a class="btn btn-secondary" href="<?= url('characters_search', array_merge($filters, ['page' => $page + 1])) ?>">Вперёд →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
