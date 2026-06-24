<?php
require_once __DIR__ . '/crm-auth.php';

if (crmIsLoggedIn()) {
    header('Location: crm-access.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(strtolower($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (isset($CRM_TEAM[$username]) && password_verify($password, $CRM_TEAM[$username]['password'])) {
        $member = $CRM_TEAM[$username];
        $_SESSION['crm_user'] = [
            'username' => $username,
            'name'     => $member['name'],
            'fullname' => $member['fullname'],
            'role'     => $member['role'],
            'color'    => $member['color'],
            'emoji'    => $member['emoji'],
            'login_at' => date('c'),
        ];
        header('Location: crm-access.php');
        exit;
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C Beauty CRM — تسجيل الدخول</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

:root {
  --burg: #8B1A4A;
  --burg-dark: #6B1238;
  --gold: #D4A843;
  --gold-light: #E8C56A;
  --bg: #0E0A0D;
  --card: rgba(255,255,255,0.04);
  --border: rgba(212,168,67,0.25);
  --text: #F5EFE8;
  --muted: rgba(245,239,232,0.5);
}

body {
  min-height: 100vh;
  font-family: 'Tajawal', sans-serif;
  background: var(--bg);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

/* Animated background blobs */
.bg-blob {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.15;
  animation: float 8s ease-in-out infinite;
  pointer-events: none;
}
.bg-blob:nth-child(1) { width:500px; height:500px; background:var(--burg); top:-100px; right:-100px; animation-delay:0s; }
.bg-blob:nth-child(2) { width:400px; height:400px; background:var(--gold); bottom:-80px; left:-80px; animation-delay:3s; }
.bg-blob:nth-child(3) { width:300px; height:300px; background:#4A1A8B; top:50%; left:50%; transform:translate(-50%,-50%); animation-delay:1.5s; }

@keyframes float {
  0%,100% { transform: translate(0,0) scale(1); }
  33% { transform: translate(20px,-20px) scale(1.05); }
  66% { transform: translate(-15px,15px) scale(0.95); }
}

.login-wrap {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 460px;
  padding: 20px;
}

.brand {
  text-align: center;
  margin-bottom: 36px;
}
.brand-logo {
  font-size: 2.8rem;
  font-weight: 800;
  background: linear-gradient(135deg, var(--gold), var(--gold-light), var(--gold));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 2px;
  line-height: 1;
}
.brand-sub {
  color: var(--muted);
  font-size: 0.8rem;
  letter-spacing: 4px;
  text-transform: uppercase;
  margin-top: 6px;
}
.brand-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(139,26,74,0.2);
  border: 1px solid rgba(139,26,74,0.4);
  color: #E8A0B8;
  font-size: 0.75rem;
  padding: 4px 14px;
  border-radius: 20px;
  margin-top: 12px;
  letter-spacing: 1px;
}

.card {
  background: rgba(14,10,13,0.8);
  backdrop-filter: blur(30px);
  border: 1px solid var(--border);
  border-radius: 24px;
  padding: 48px 44px 40px;
  box-shadow: 0 40px 80px rgba(0,0,0,0.5), inset 0 1px 0 rgba(255,255,255,0.06);
}

.card-title {
  color: var(--text);
  font-size: 1.3rem;
  font-weight: 700;
  margin-bottom: 6px;
  text-align: center;
}
.card-hint {
  color: var(--muted);
  font-size: 0.88rem;
  text-align: center;
  margin-bottom: 32px;
}

.field { margin-bottom: 18px; }
.field label {
  display: block;
  color: var(--muted);
  font-size: 0.82rem;
  font-weight: 500;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}
.field-wrap {
  position: relative;
}
.field-wrap .icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  pointer-events: none;
}
.field input {
  width: 100%;
  padding: 14px 44px 14px 16px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  color: var(--text);
  font-size: 0.95rem;
  font-family: 'Tajawal', sans-serif;
  outline: none;
  transition: border-color 0.25s, background 0.25s;
  direction: ltr;
  text-align: right;
}
.field input[name="username"] { direction: ltr; text-align: left; }
.field input:focus {
  border-color: var(--gold);
  background: rgba(212,168,67,0.06);
}
.field input::placeholder { color: rgba(255,255,255,0.25); }

.toggle-pass {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--muted);
  font-size: 1rem;
  padding: 4px;
  transition: color 0.2s;
}
.toggle-pass:hover { color: var(--gold); }

.error-msg {
  background: rgba(220,53,69,0.12);
  border: 1px solid rgba(220,53,69,0.35);
  color: #FF8A95;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 0.88rem;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-login {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, var(--burg), #B02060);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  font-weight: 700;
  font-family: 'Tajawal', sans-serif;
  cursor: pointer;
  transition: all 0.25s;
  letter-spacing: 1px;
  margin-top: 8px;
  position: relative;
  overflow: hidden;
}
.btn-login::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
  opacity: 0;
  transition: opacity 0.25s;
}
.btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(139,26,74,0.5); }
.btn-login:hover::after { opacity: 1; }
.btn-login:active { transform: translateY(0); }

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 28px 0 22px;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.08);
}
.divider span { color: var(--muted); font-size: 0.78rem; white-space: nowrap; }

.team-members {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}
.member-chip {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 5px;
  padding: 10px 6px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.06);
  background: rgba(255,255,255,0.03);
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}
.member-chip:hover {
  background: rgba(212,168,67,0.08);
  border-color: rgba(212,168,67,0.3);
  transform: translateY(-2px);
}
.member-chip .avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  font-weight: 800;
  color: #fff;
}
.member-chip .m-name { color: var(--text); font-size: 0.8rem; font-weight: 600; }
.member-chip .m-role { color: var(--muted); font-size: 0.7rem; }

.footer-links {
  text-align: center;
  margin-top: 28px;
}
.footer-links a {
  color: var(--muted);
  text-decoration: none;
  font-size: 0.82rem;
  transition: color 0.2s;
  margin: 0 10px;
}
.footer-links a:hover { color: var(--gold); }

/* Shimmer on card border */
@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}
</style>
</head>
<body>
<div class="bg-blob"></div>
<div class="bg-blob"></div>
<div class="bg-blob"></div>

<div class="login-wrap">
  <div class="brand">
    <div class="brand-logo">C Beauty</div>
    <div class="brand-sub">Beauty & Skin Care</div>
    <div class="brand-badge">🔒 نظام إدارة العملاء — للفريق فقط</div>
  </div>

  <div class="card">
    <div class="card-title">مرحباً بك في الـ CRM 👋</div>
    <div class="card-hint">سجّلي دخولك باسم المستخدم وكلمة المرور الخاصة بك</div>

    <?php if ($error): ?>
    <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
      <div class="field">
        <label>اسم المستخدم</label>
        <div class="field-wrap">
          <span class="icon">👤</span>
          <input type="text" name="username" id="username"
                 placeholder="أدخلي اسم المستخدم"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 autocomplete="username" required autofocus>
        </div>
      </div>
      <div class="field">
        <label>كلمة المرور</label>
        <div class="field-wrap">
          <span class="icon">🔑</span>
          <input type="password" name="password" id="password"
                 placeholder="أدخلي كلمة المرور"
                 autocomplete="current-password" required>
          <button type="button" class="toggle-pass" onclick="togglePass()" id="toggleBtn">👁</button>
        </div>
      </div>
      <button type="submit" class="btn-login">دخول للنظام ←</button>
    </form>

    <div class="divider"><span>أعضاء الفريق</span></div>

    <div class="team-members">
      <?php
      $chips = [
        ['sara',  '#8B1A4A', '👑', 'سارة',  'مديرة'],
        ['nada',  '#C8722A', '💄', 'ندى',   'مستشارة'],
        ['hind',  '#6B3FA0', '🌸', 'هند',   'استقبال'],
        ['lina',  '#1A6B4A', '✨', 'لينا',  'علاجات'],
        ['admin', '#2C3E50', '⚙️', 'Admin', 'مسؤول'],
      ];
      foreach ($chips as [$u, $color, $emoji, $name, $role]): ?>
      <div class="member-chip" onclick="fillUser('<?= $u ?>')">
        <div class="avatar" style="background:<?= $color ?>"><?= $emoji ?></div>
        <div class="m-name"><?= $name ?></div>
        <div class="m-role"><?= $role ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="footer-links">
    <a href="index.html">← الموقع الرئيسي</a>
    <a href="admin/">لوحة الإدارة</a>
  </div>
</div>

<script>
function fillUser(u) {
  document.getElementById('username').value = u;
  document.getElementById('password').focus();
}

function togglePass() {
  const p = document.getElementById('password');
  const b = document.getElementById('toggleBtn');
  if (p.type === 'password') { p.type = 'text'; b.textContent = '🙈'; }
  else { p.type = 'password'; b.textContent = '👁'; }
}
</script>
</body>
</html>
