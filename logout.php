<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_SESSION['user_id'], 'logout', 'User logged out', $_SERVER['REMOTE_ADDR'] ?? null]);

    if (!empty($_SESSION['login_log_id'])) {
        $closeLog = $pdo->prepare('UPDATE login_logs SET logout_time = NOW() WHERE log_id = ?');
        $closeLog->execute([$_SESSION['login_log_id']]);
    }

    if (!empty($_COOKIE['eventx_remember'])) {
        $selector = explode(':', $_COOKIE['eventx_remember'], 2)[0] ?? '';
        if ($selector !== '') {
            $del = $pdo->prepare('DELETE FROM remember_tokens WHERE selector = ?');
            $del->execute([$selector]);
        }
        setcookie('eventx_remember', '', time() - 3600, '/');
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie('PHPSESSID', '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
session_start();
set_flash('success', 'You have been logged out successfully.');
redirect('login.php');
