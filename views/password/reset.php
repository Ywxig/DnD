<?php

/**
 * Форма установки нового пароля по одноразовому токену.
 *
 * @var string                $token  Токен из ссылки
 * @var array<string, string> $errors
 */
?>
<section class="panel form-panel">
    <h1>Новый пароль</h1>

    <form method="post" action="<?= url('password_reset') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="form-row">
            <label for="password">Новый пароль *</label>
            <input id="password" type="password" name="password" autofocus
                   class="<?= error_class($errors, 'password') ?>">
            <?= field_error($errors, 'password') ?>
            <p class="hint">Минимум 6 символов.</p>
        </div>

        <div class="form-row">
            <label for="password_confirm">Повторите пароль *</label>
            <input id="password_confirm" type="password" name="password_confirm"
                   class="<?= error_class($errors, 'password_confirm') ?>">
            <?= field_error($errors, 'password_confirm') ?>
        </div>

        <button type="submit" class="btn">Сменить пароль</button>
    </form>
</section>
