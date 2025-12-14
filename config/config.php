<?php
// Общие настройки
session_start();

// Пути
// Для встроенного PHP сервера (php -S localhost:8000):
define('BASE_URL', 'http://localhost:8000');
// Для Apache/Nginx используйте: 'http://localhost/kursovaya'
define('ROOT_PATH', __DIR__ . '/..');

// Подключение к БД
require_once __DIR__ . '/database.php';

// Функции для работы с сессией
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}
?>

