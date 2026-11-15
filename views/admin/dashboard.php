<?php

/**
 * Дашборд админа.
 *
 * @var array{characters:int, users:int, races:int, classes:int} $counters
 * @var array $users  Последние пользователи
 * @var array $latest Последние персонажи
 */
?>
<section class="panel">
    <h1>Админка</h1>
    <div class="stats-grid">
        <div class="stat-card"><strong><?= (int)$counters['characters'] ?></strong><span>персонажей</span></div>
        <div class="stat-card"><strong><?= (int)$counters['users'] ?></strong><span>пользователей</span></div>
        <div class="stat-card"><strong><?= (int)$counters['races'] ?></strong><span>рас</span></div>
        <div class="stat-card"><strong><?= (int)$counters['classes'] ?></strong><span>классов</span></div>
    </div>
    <div class="quick-links">
        <a class="btn" href="<?= url('admin_users') ?>">Пользователи</a>
        <a class="btn" href="<?= url('admin_characters') ?>">Персонажи</a>
        <a class="btn" href="<?= url('admin_races') ?>">Расы</a>
        <a class="btn" href="<?= url('admin_classes') ?>">Классы</a>
    </div>
</section>

<div class="columns">
    <section class="panel">
        <h2>Последние пользователи</h2>
        <table>
            <thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Регистрация</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['username']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="badge badge-<?= e($user['role']) ?>"><?= e($user['role']) ?></span></td>
                    <td><?= format_date($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel">
        <h2>Последние персонажи</h2>
        <table>
            <thead><tr><th>Имя</th><th>Класс</th><th>Игрок</th></tr></thead>
            <tbody>
            <?php foreach ($latest as $character): ?>
                <tr>
                    <td><a href="<?= url('character_view', ['id' => (int)$character['id']]) ?>"><?= e($character['name']) ?></a></td>
                    <td><?= e($character['class_name']) ?></td>
                    <td><?= e($character['owner_name']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
