<?php

/**
 * Форма создания пользователя (админ).
 *
 * @var array<string, string> $errors
 * @var array<string, mixed>  $data
 */
?>
<section class="panel form-panel">
    <h1>Новый пользователь</h1>

    <form method="post" action="<?= url('admin_user_create') ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="username">Имя пользователя *</label>
            <input id="username" type="text" name="username" value="<?= e($data['username'] ?? '') ?>"
                   class="<?= error_class($errors, 'username') ?>">
            <?= field_error($errors, 'username') ?>
        </div>

        <div class="form-row">
            <label for="email">Email *</label>
            <input id="email" type="text" name="email" value="<?= e($data['email'] ?? '') ?>"
                   class="<?= error_class($errors, 'email') ?>">
            <?= field_error($errors, 'email') ?>
        </div>

        <div class="form-row">
            <label for="password">Пароль * (мин. 6 символов)</label>
            <input id="password" type="password" name="password" class="<?= error_class($errors, 'password') ?>">
            <?= field_error($errors, 'password') ?>
        </div>

        <div class="form-row">
            <label for="role">Роль *</label>
            <select id="role" name="role">
                <option value="user"<?= selected_option($data['role'] ?? '', 'user') ?>>Обычный пользователь</option>
                <option value="admin"<?= selected_option($data['role'] ?? '', 'admin') ?>>Администратор</option>
            </select>
        </div>

        <button type="submit" class="btn">Создать</button>
        <a class="btn btn-secondary" href="<?= url('admin_users') ?>">Отмена</a>
    </form>
</section>
