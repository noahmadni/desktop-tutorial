<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    if (password_verify($pass, ADMIN_PASSWORD)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Incorrect password. Please try again.';
    }
}
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C Beauty Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
  min-height:100vh;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d1f1f 50%, #1a1a1a 100%);
  display:flex; align-items:center; justify-content:center;
  font-family:'Inter',sans-serif;
}
.login-card {
  background: rgba(255,255,255,0.05);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(201,169,110,0.3);
  border-radius: 24px;
  padding: 60px 50px;
  width: 100%;
  max-width: 420px;
  text-align: center;
}
.logo {
  font-family:'Playfair Display',serif;
  font-size: 2.5rem;
  background: linear-gradient(135deg, #C9A96E, #D4AF37);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 8px;
}
.logo-sub {
  color: rgba(255,255,255,0.5);
  font-size: 0.85rem;
  letter-spacing: 3px;
  text-transform: uppercase;
  margin-bottom: 40px;
}
h2 {
  color: #fff;
  font-size: 1.4rem;
  font-weight: 500;
  margin-bottom: 30px;
}
.field {
  position: relative;
  margin-bottom: 20px;
}
.field input {
  width: 100%;
  padding: 16px 20px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(201,169,110,0.3);
  border-radius: 12px;
  color: #fff;
  font-size: 1rem;
  font-family: 'Inter',sans-serif;
  outline: none;
  transition: border-color 0.3s;
}
.field input:focus { border-color: #C9A96E; }
.field input::placeholder { color: rgba(255,255,255,0.4); }
.btn-login {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #C9A96E, #D4AF37);
  border: none;
  border-radius: 12px;
  color: #1a1a1a;
  font-size: 1rem;
  font-weight: 700;
  font-family: 'Inter',sans-serif;
  cursor: pointer;
  transition: opacity 0.3s, transform 0.2s;
  letter-spacing: 1px;
}
.btn-login:hover { opacity: 0.9; transform: translateY(-1px); }
.error {
  background: rgba(220,53,69,0.15);
  border: 1px solid rgba(220,53,69,0.4);
  color: #ff6b7a;
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 0.9rem;
}
.back-link {
  display:inline-block;
  margin-top:20px;
  color: rgba(255,255,255,0.4);
  text-decoration:none;
  font-size:0.85rem;
  transition: color 0.3s;
}
.back-link:hover { color: #C9A96E; }
</style>
</head>
<body>
<div class="login-card">
  <div class="logo">C Beauty</div>
  <div class="logo-sub">Admin Panel</div>
  <h2>Welcome Back 👋</h2>
  <?php if($error): ?>
    <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="field">
      <input type="password" name="password" placeholder="Enter admin password" required autofocus>
    </div>
    <button type="submit" class="btn-login">LOGIN TO DASHBOARD</button>
  </form>
  <a href="../index.html" class="back-link">← Back to Website</a>
</div>
</body>
</html>
