<?php

/**
 * Форма входа.
 *
 * @var array<string, string> $errors Ошибки по полям
 * @var array<string, mixed>  $data   Введённые значения
 */
?>
<section class="panel form-panel">
    <h1>Вход</h1>

    <form method="post" action="<?= url('login') ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="login">Имя пользователя или email *</label>
            <input id="login" type="text" name="login" value="<?= e($data['login'] ?? '') ?>"
                   class="<?= error_class($errors, 'login') ?>" autofocus>
            <?= field_error($errors, 'login') ?>
        </div>

        <div class="form-row">
            <label for="password">Пароль *</label>
            <input id="password" type="password" name="password" class="<?= error_class($errors, 'password') ?>">
            <?= field_error($errors, 'password') ?>
        </div>

        <button type="submit" class="btn">Войти</button>
    </form>

    <p class="muted">
        Нет аккаунта? <a href="<?= url('register') ?>">Зарегистрируйтесь</a> ·
        <a href="<?= url('password_request') ?>">Забыли пароль?</a>
    </p>
</section>
