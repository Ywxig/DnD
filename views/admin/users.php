<?php

/**
 * Список пользователей с поиском и действиями.
 *
 * @var array  $users
 * @var string $query Поисковый запрос
 * @var int    $currentUserId
 */
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
?>
<section class="panel">
    <div class="panel-head">
        <h1>Пользователи</h1>
        <a class="btn" href="<?= url('admin_user_create') ?>">+ Новый пользователь</a>
    </div>

    <form method="get" action="index.php" class="search-form inline-search">
        <input type="hidden" name="r" value="admin_users">
        <input type="text" name="q" placeholder="Имя или email…" value="<?= e($query) ?>">
        <button type="submit" class="btn btn-secondary">Найти</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>ID</th><th>Имя</th><th>Email</th><th>Роль</th>
            <th>Регистрация</th><th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <?php $self = (int)$user['id'] === $currentUserId; ?>
            <tr>
                <td><?= (int)$user['id'] ?></td>
                <td><?= e($user['username']) ?><?= $self ? ' <span class="muted">(вы)</span>' : '' ?></td>
                <td><?= e($user['email']) ?></td>
                <td><span class="badge badge-<?= e($user['role']) ?>"><?= e($user['role']) ?></span></td>
                <td><?= format_date($user['created_at']) ?></td>
                <td class="actions">
                    <?php if (!$self): ?>
                        <form method="post" action="<?= url('admin_user_role') ?>" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <select name="role">
                                <option value="user"<?= selected_option($user['role'], 'user') ?>>user</option>
                                <option value="admin"<?= selected_option($user['role'], 'admin') ?>>admin</option>
                            </select>
                            <button type="submit" class="btn-mini">Сменить роль</button>
                        </form>
                        <form method="post" action="<?= url('admin_user_delete') ?>" class="inline"
                              onsubmit="return confirm('Удалить пользователя «<?= e($user['username']) ?>» вместе с его персонажами?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <button type="submit" class="link-danger">Удалить</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">свой аккаунт</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
