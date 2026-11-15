<?php

/**
 * Главная страница: счётчики, последние 5 персонажей, топ классов, случайный персонаж.
 *
 * @var array{characters:int, users:int, races:int, classes:int} $counters
 * @var array $latest      Последние 5 персонажей
 * @var array $topClasses  Топ классов
 * @var array|null $random Случайный персонаж
 */
?>
<section class="hero">
    <h1>Персонажи D&D</h1>
    <p>Учебное приложение на чистом PHP (MVC, без фреймворков): создавайте персонажей, ищите героев, управляйте расами и классами.</p>
</section>

<section class="stats-grid">
    <div class="stat-card"><strong><?= (int)$counters['characters'] ?></strong><span>персонажей</span></div>
    <div class="stat-card"><strong><?= (int)$counters['users'] ?></strong><span>игроков</span></div>
    <div class="stat-card"><strong><?= (int)$counters['races'] ?></strong><span>рас</span></div>
    <div class="stat-card"><strong><?= (int)$counters['classes'] ?></strong><span>классов</span></div>
</section>

<div class="columns">
    <section class="panel">
        <h2>Последние персонажи</h2>
        <?php if ($latest === []): ?>
            <p class="muted">Пока нет ни одного персонажа.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr><th>Имя</th><th>Раса</th><th>Класс</th><th>Уровень</th><th>Игрок</th><th>Создан</th></tr>
                </thead>
                <tbody>
                <?php foreach ($latest as $character): ?>
                    <tr>
                        <td><a href="<?= url('character_view', ['id' => (int)$character['id']]) ?>"><?= e($character['name']) ?></a></td>
                        <td><?= e($character['race_name']) ?></td>
                        <td><?= e($character['class_name']) ?></td>
                        <td><?= (int)$character['level'] ?></td>
                        <td><?= e($character['owner_name']) ?></td>
                        <td><?= format_date($character['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Топ классов</h2>
        <?php if ($topClasses === []): ?>
            <p class="muted">Нет данных.</p>
        <?php else: ?>
            <ul class="top-classes">
                <?php foreach ($topClasses as $class): ?>
                    <li>
                        <span><?= e($class['name']) ?></span>
                        <span class="muted"><?= (int)$class['characters_count'] ?> персон. · ср. уровень <?= e($class['avg_level'] ?: '—') ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<?php if ($random !== null): ?>
    <section class="panel random-panel">
        <h2>🎲 Случайный персонаж</h2>
        <p>
            <a class="big-link" href="<?= url('character_view', ['id' => (int)$random['id']]) ?>">
                <?= e($random['name']) ?>
            </a>
            — <?= e($random['race_name']) ?> <?= e($random['class_name']) ?>,
            <?= (int)$random['level'] ?> уровня, мировоззрение «<?= e($random['alignment']) ?>».
        </p>
        <p class="muted">Обновите страницу — герой каждый раз новый.</p>
    </section>
<?php endif; ?>

<?php if (current_user() === null): ?>
    <section class="panel">
        <h2>Попробовать</h2>
        <p>
            <a class="btn" href="<?= url('register') ?>">Регистрация</a>
            <a class="btn btn-secondary" href="<?= url('login') ?>">Вход</a>
            <a class="btn btn-secondary" href="<?= url('characters_search') ?>">Поиск персонажей</a>
        </p>
        <p class="muted">Тестовый админ: <code>admin / admin123</code>, тестовый игрок: <code>aragorn / password123</code>.</p>
    </section>
<?php endif; ?>
