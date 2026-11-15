<?php

/**
 * Общий layout приложения. Использует переменные из Core\Controller::render():
 * $content, $flashes, $currentUser, $appName и, опционально, $title.
 *
 * @var string         $content      HTML основного вида
 * @var array          $flashes      Flash-сообщения
 * @var array|null     $currentUser  Текущий пользователь
 * @var string         $appName      Имя приложения
 * @var string|null    $title        Заголовок страницы
 */
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $appName) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <nav class="nav">
        <a class="brand" href="<?= url('home') ?>">⚔ <?= e($appName) ?></a>
        <a href="<?= url('home') ?>">Главная</a>
        <a href="<?= url('characters_search') ?>">Поиск</a>
        <?php if ($currentUser): ?>
            <a href="<?= url('characters_my') ?>">Мои персонажи</a>
            <a href="<?= url('characters_create') ?>">+ Персонаж</a>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin') ?>" class="nav-admin">Админка</a>
            <?php endif; ?>
            <span class="spacer"></span>
            <span class="user-badge"><?= e($currentUser['username']) ?> <em>(<?= e($currentUser['role']) ?>)</em></span>
            <a href="<?= url('logout') ?>">Выход</a>
        <?php else: ?>
            <span class="spacer"></span>
            <a href="<?= url('login') ?>">Вход</a>
            <a href="<?= url('register') ?>">Регистрация</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <?php foreach ($flashes as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="site-footer">
    <p><?= e($appName) ?> · учебный проект на PHP MVC без фреймворков · <?= date('Y') ?></p>
</footer>

<script src="assets/app.js"></script>
</body>
</html>
