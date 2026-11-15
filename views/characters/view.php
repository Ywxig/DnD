<?php

/**
 * Карточка персонажа с модификаторами характеристик.
 *
 * @var array<string, mixed> $character   Данные персонажа (+ имена расы/класса/владельца)
 * @var array<string, string> $statLabels Подписи характеристик
 */
use App\Models\Character;
?>
<section class="panel character-card">
    <div class="panel-head">
        <h1><?= e($character['name']) ?></h1>
        <?php if ((int)$character['user_id'] === (int)($_SESSION['user_id'] ?? 0) || is_admin()): ?>
            <a class="btn btn-secondary" href="<?= url('characters_edit', ['id' => (int)$character['id']]) ?>">Изменить</a>
        <?php endif; ?>
    </div>

    <dl class="character-meta">
        <div><dt>Раса</dt><dd><?= e($character['race_name']) ?></dd></div>
        <div><dt>Класс</dt><dd><?= e($character['class_name']) ?></dd></div>
        <div><dt>Уровень</dt><dd><?= (int)$character['level'] ?></dd></div>
        <div><dt>Мировоззрение</dt><dd><?= e($character['alignment']) ?></dd></div>
        <div><dt>Игрок</dt><dd><?= e($character['owner_name']) ?></dd></div>
        <div><dt>Создан</dt><dd><?= format_date($character['created_at']) ?></dd></div>
    </dl>

    <h2>Характеристики</h2>
    <table class="stats-table">
        <thead>
        <tr><th>Характеристика</th><th>Значение</th><th>Модификатор</th></tr>
        </thead>
        <tbody>
        <?php foreach (Character::STATS as $stat): ?>
            <tr>
                <td><?= e($statLabels[$stat]) ?></td>
                <td><?= (int)$character[$stat] ?></td>
                <td><span class="badge"><?= signed_modifier((int)$character[$stat]) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="muted">Модификатор = floor((значение − 10) / 2).</p>

    <?php if (($character['backstory'] ?? '') !== ''): ?>
        <h2>Предыстория</h2>
        <p class="backstory"><?= nl2br(e($character['backstory'])) ?></p>
    <?php endif; ?>
</section>
