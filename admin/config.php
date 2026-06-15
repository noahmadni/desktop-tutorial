<?php
define('ADMIN_PASSWORD', password_hash('cbeauty2025', PASSWORD_DEFAULT));
define('SESSION_NAME', 'cbeauty_admin');
define('DATA_FILE', __DIR__ . '/../data/content.json');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');

session_name(SESSION_NAME);
session_start();

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

function getContent() {
    $file = DATA_FILE;
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true);
}

function saveContent($data) {
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function uploadImage($file, $name = '') {
    $dir = UPLOADS_DIR;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp','gif'];
    if (!in_array($ext, $allowed)) return false;
    $filename = ($name ?: uniqid()) . '.' . $ext;
    $dest = $dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $filename;
    }
    return false;
}
?>
