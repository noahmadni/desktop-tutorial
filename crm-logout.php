<?php
require_once __DIR__ . '/crm-auth.php';
session_unset();
session_destroy();
header('Location: crm-login.php');
exit;
