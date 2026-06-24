<?php
/**
 * C Beauty CRM — Authentication config
 * Team members with username + hashed password + role + avatar color
 */

define('CRM_SESSION', 'cbeauty_crm');

$CRM_TEAM = [
    'sara' => [
        'name'     => 'سارة',
        'fullname' => 'سارة المدير',
        'password' => password_hash('sara@crm2025', PASSWORD_DEFAULT),
        'role'     => 'مديرة',
        'color'    => '#8B1A4A',
        'emoji'    => '👑',
    ],
    'nada' => [
        'name'     => 'ندى',
        'fullname' => 'ندى الاستشارية',
        'password' => password_hash('nada@crm2025', PASSWORD_DEFAULT),
        'role'     => 'مستشارة جمال',
        'color'    => '#C8722A',
        'emoji'    => '💄',
    ],
    'hind' => [
        'name'     => 'هند',
        'fullname' => 'هند الاستقبال',
        'password' => password_hash('hind@crm2025', PASSWORD_DEFAULT),
        'role'     => 'موظفة استقبال',
        'color'    => '#6B3FA0',
        'emoji'    => '🌸',
    ],
    'lina' => [
        'name'     => 'لينا',
        'fullname' => 'لينا العلاجات',
        'password' => password_hash('lina@crm2025', PASSWORD_DEFAULT),
        'role'     => 'أخصائية علاجات',
        'color'    => '#1A6B4A',
        'emoji'    => '✨',
    ],
    'admin' => [
        'name'     => 'Admin',
        'fullname' => 'مسؤول النظام',
        'password' => password_hash('cbeauty@admin2025', PASSWORD_DEFAULT),
        'role'     => 'مسؤول',
        'color'    => '#2C3E50',
        'emoji'    => '⚙️',
    ],
];

session_name(CRM_SESSION);
session_start();

function crmIsLoggedIn(): bool {
    return isset($_SESSION['crm_user']) && !empty($_SESSION['crm_user']);
}

function crmRequireLogin(): void {
    if (!crmIsLoggedIn()) {
        header('Location: crm-login.php');
        exit;
    }
}

function crmCurrentUser(): array {
    return $_SESSION['crm_user'] ?? [];
}
