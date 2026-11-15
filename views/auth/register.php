<?php

/**
 * Форма регистрации.
 *
 * @var array<string, string> $errors Ошибки по полям
 * @var array<string, mixed>  $data   Введённые значения
 */
?>
<section class="panel form-panel">
    <h1>Регистрация</h1>

    <form method="post" action="<?= url('register') ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="username">Имя пользователя *</label>
            <input id="username" type="text" name="username" value="<?= e($data['username'] ?? '') ?>"
                   class="<?= error_class($errors, 'username') ?>" autofocus>
            <?= field_error($errors, 'username') ?>
            <p class="hint">3–30 символов: латиница, цифры, подчёркивание.</p>
        </div>

        <div class="form-row">
            <label for="email">Email *</label>
            <input id="email" type="text" name="email" value="<?= e($data['email'] ?? '') ?>"
                   class="<?= error_class($errors, 'email') ?>">
            <?= field_error($errors, 'email') ?>
        </div>

        <div class="form-row">
            <label for="password">Пароль *</label>
            <input id="password" type="password" name="password" class="<?= error_class($errors, 'password') ?>">
            <?= field_error($errors, 'password') ?>
            <p class="hint">Минимум 6 символов.</p>
        </div>

        <div class="form-row">
            <label for="password_confirm">Повторите пароль *</label>
            <input id="password_confirm" type="password" name="password_confirm"
                   class="<?= error_class($errors, 'password_confirm') ?>">
            <?= field_error($errors, 'password_confirm') ?>
        </div>

        <button type="submit" class="btn">Зарегистрироваться</button>
    </form>

    <p class="muted">Уже есть аккаунт? <a href="<?= url('login') ?>">Войти</a></p>
</section>
