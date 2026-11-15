<?php

/**
 * Форма запроса ссылки восстановления пароля.
 *
 * @var array<string, string> $errors
 */
?>
<section class="panel form-panel">
    <h1>Восстановление пароля</h1>
    <p class="muted">Введите email — если он зарегистрирован, на него будет отправлена ссылка для смены пароля. Ссылка действует 1 час.</p>

    <form method="post" action="<?= url('password_request') ?>">
        <?= csrf_field() ?>

        <div class="form-row">
            <label for="email">Email *</label>
            <input id="email" type="text" name="email" autofocus>
            <?= field_error($errors, 'email') ?>
        </div>

        <button type="submit" class="btn">Отправить ссылку</button>
    </form>

    <p class="muted">На время разработки ссылка пишется в файл <code>logs/password_reset.log</code>.</p>
</section>
