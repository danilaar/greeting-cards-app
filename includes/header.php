<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Открытки и Приглашения'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>/index.php" class="logo">🎴 Открытки</a>
            <ul class="nav-menu">
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Главная</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/my_cards.php">Мои открытки</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo BASE_URL; ?>/admin/templates.php">Управление шаблонами</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>/logout.php">Выход (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/login.php">Вход</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/register.php">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="container">


