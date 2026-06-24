<?php
require_once __DIR__ . '/crm-auth.php';
crmRequireLogin();

$user = crmCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C Beauty CRM</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Tajawal',sans-serif; background:#0E0A0D; height:100vh; display:flex; flex-direction:column; overflow:hidden; }

.top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  height: 52px;
  background: rgba(14,10,13,0.95);
  border-bottom: 1px solid rgba(212,168,67,0.2);
  flex-shrink: 0;
  z-index: 100;
}
.tb-left { display:flex; align-items:center; gap:12px; }
.tb-brand {
  font-size: 1.15rem;
  font-weight: 800;
  background: linear-gradient(135deg,#D4A843,#E8C56A);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 1px;
}
.tb-divider { width:1px; height:22px; background:rgba(255,255,255,0.1); }
.tb-title { color:rgba(255,255,255,0.55); font-size:0.82rem; letter-spacing:2px; text-transform:uppercase; }

.tb-right { display:flex; align-items:center; gap:14px; }
.user-badge {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 5px 14px 5px 8px;
  border-radius: 30px;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.04);
}
.user-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  display: flex; align-items:center; justify-content:center;
  font-size: 0.9rem; font-weight: 800; color:#fff;
}
.user-info { line-height:1.3; }
.user-name { color:#F5EFE8; font-size:0.82rem; font-weight:700; }
.user-role { color:rgba(245,239,232,0.45); font-size:0.72rem; }

.logout-btn {
  display:flex; align-items:center; gap:6px;
  padding: 7px 14px;
  border-radius: 8px;
  border: 1px solid rgba(220,53,69,0.3);
  background: rgba(220,53,69,0.08);
  color: #FF8A95;
  font-family:'Tajawal',sans-serif;
  font-size:0.82rem;
  font-weight:600;
  cursor:pointer;
  text-decoration:none;
  transition: all 0.2s;
}
.logout-btn:hover { background:rgba(220,53,69,0.18); border-color:rgba(220,53,69,0.5); }

.crm-frame {
  flex: 1;
  width: 100%;
  border: none;
  display: block;
}
</style>
</head>
<body>

<div class="top-bar">
  <div class="tb-left">
    <span class="tb-brand">C Beauty</span>
    <div class="tb-divider"></div>
    <span class="tb-title">نظام إدارة العملاء</span>
  </div>
  <div class="tb-right">
    <div class="user-badge">
      <div class="user-avatar" style="background:<?= htmlspecialchars($user['color']) ?>">
        <?= $user['emoji'] ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
        <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
      </div>
    </div>
    <a href="crm-logout.php" class="logout-btn">خروج ←</a>
  </div>
</div>

<iframe src="crm.html" class="crm-frame" id="crmFrame"></iframe>

</body>
</html>
