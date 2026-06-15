<?php
require_once 'config.php';
requireLogin();

$content = getContent();
$success = '';
$activeTab = $_GET['tab'] ?? 'overview';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_hero') {
        $content['hero']['headline'] = $_POST['headline'];
        $content['hero']['subheadline'] = $_POST['subheadline'];
        $content['hero']['btn1'] = $_POST['btn1'];
        $content['hero']['btn2'] = $_POST['btn2'];
        saveContent($content);
        $success = 'Hero section updated!';
    }

    if ($action === 'save_about') {
        $content['about']['title'] = $_POST['title'];
        $content['about']['story'] = $_POST['story'];
        $content['about']['mission'] = $_POST['mission'];
        $content['about']['stats']['customers'] = $_POST['customers'];
        $content['about']['stats']['products'] = $_POST['products'];
        $content['about']['stats']['consultations'] = $_POST['consultations'];
        $content['about']['stats']['satisfaction'] = $_POST['satisfaction'];
        saveContent($content);
        $success = 'About section updated!';
    }

    if ($action === 'save_contact') {
        $content['contact']['whatsapp'] = $_POST['whatsapp'];
        $content['contact']['email'] = $_POST['email'];
        $content['contact']['phone'] = $_POST['phone'];
        $content['contact']['instagram'] = $_POST['instagram'];
        $content['contact']['tiktok'] = $_POST['tiktok'];
        $content['contact']['snapchat'] = $_POST['snapchat'];
        $content['contact']['facebook'] = $_POST['facebook'];
        saveContent($content);
        $success = 'Contact info updated!';
    }

    if ($action === 'add_product') {
        $imagePath = '';
        if (!empty($_FILES['image']['name'])) {
            $imagePath = uploadImage($_FILES['image'], 'product_' . time());
        }
        $newProduct = [
            'id' => time(),
            'name' => $_POST['name'],
            'category' => $_POST['category'],
            'price' => $_POST['price'],
            'rating' => (int)$_POST['rating'],
            'description' => $_POST['description'],
            'image' => $imagePath
        ];
        $content['products'][] = $newProduct;
        saveContent($content);
        $success = 'Product added!';
    }

    if ($action === 'delete_product') {
        $id = (int)$_POST['product_id'];
        $content['products'] = array_values(array_filter($content['products'], fn($p) => $p['id'] != $id));
        saveContent($content);
        $success = 'Product deleted!';
    }

    if ($action === 'edit_product') {
        $id = (int)$_POST['product_id'];
        foreach ($content['products'] as &$p) {
            if ($p['id'] == $id) {
                $p['name'] = $_POST['name'];
                $p['category'] = $_POST['category'];
                $p['price'] = $_POST['price'];
                $p['rating'] = (int)$_POST['rating'];
                $p['description'] = $_POST['description'];
                if (!empty($_FILES['image']['name'])) {
                    $img = uploadImage($_FILES['image'], 'product_' . $id);
                    if ($img) $p['image'] = $img;
                }
                break;
            }
        }
        saveContent($content);
        $success = 'Product updated!';
    }

    if ($action === 'add_testimonial') {
        $content['testimonials'][] = [
            'name' => $_POST['name'],
            'rating' => (int)$_POST['rating'],
            'text' => $_POST['text'],
            'date' => $_POST['date']
        ];
        saveContent($content);
        $success = 'Testimonial added!';
    }

    if ($action === 'delete_testimonial') {
        $idx = (int)$_POST['idx'];
        array_splice($content['testimonials'], $idx, 1);
        saveContent($content);
        $success = 'Testimonial deleted!';
    }

    if ($action === 'change_password') {
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if ($new === $confirm && strlen($new) >= 6) {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            file_put_contents(__DIR__ . '/config.php',
                str_replace(
                    "define('ADMIN_PASSWORD', password_hash('cbeauty2025', PASSWORD_DEFAULT));",
                    "define('ADMIN_PASSWORD', '$hash');",
                    file_get_contents(__DIR__ . '/config.php')
                )
            );
            // Actually store hash in a separate file for safety
            file_put_contents(__DIR__ . '/.password', $hash);
            $success = 'Password changed!';
        } else {
            $success = '❌ Passwords do not match or too short (min 6 chars)';
        }
    }

    header("Location: dashboard.php?tab=$activeTab&msg=" . urlencode($success));
    exit;
}

$msg = $_GET['msg'] ?? '';
$categories = ['cleansers'=>'Cleansers','moisturizers'=>'Moisturizers','sunscreens'=>'Sunscreens','serums'=>'Serums','acne'=>'Acne','whitening'=>'Whitening','hair'=>'Hair Care','body'=>'Body Care','natural'=>'Natural'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C Beauty Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --gold:#C9A96E;--gold2:#D4AF37;--dark:#1a1a1a;--dark2:#2a2a2a;--dark3:#333;
  --text:#e0e0e0;--text2:rgba(255,255,255,0.6);--border:rgba(201,169,110,0.2);
  --success:#4CAF50;--danger:#e74c3c;--card:rgba(255,255,255,0.05);
}
body{font-family:'Inter',sans-serif;background:var(--dark);color:var(--text);min-height:100vh;display:flex}

/* SIDEBAR */
.sidebar{
  width:260px;min-height:100vh;background:var(--dark2);
  border-right:1px solid var(--border);display:flex;flex-direction:column;
  position:fixed;left:0;top:0;z-index:100;
}
.sidebar-logo{padding:30px 25px;border-bottom:1px solid var(--border)}
.sidebar-logo .brand{font-family:'Playfair Display',serif;font-size:1.8rem;background:linear-gradient(135deg,#C9A96E,#D4AF37);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.sidebar-logo .sub{color:var(--text2);font-size:0.7rem;letter-spacing:3px;text-transform:uppercase;margin-top:4px}
.sidebar-nav{flex:1;padding:20px 0}
.nav-section{padding:8px 25px;color:var(--text2);font-size:0.7rem;letter-spacing:2px;text-transform:uppercase;margin-top:15px}
.nav-item{display:flex;align-items:center;gap:12px;padding:13px 25px;color:var(--text2);text-decoration:none;transition:all 0.2s;border-left:3px solid transparent}
.nav-item:hover{color:var(--gold);background:rgba(201,169,110,0.08);border-left-color:var(--gold)}
.nav-item.active{color:var(--gold);background:rgba(201,169,110,0.12);border-left-color:var(--gold)}
.nav-item .icon{font-size:1.1rem;width:20px;text-align:center}
.sidebar-footer{padding:20px 25px;border-top:1px solid var(--border)}
.logout-btn{display:block;text-align:center;padding:11px;background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.3);border-radius:10px;color:#e74c3c;text-decoration:none;font-size:0.9rem;transition:all 0.2s}
.logout-btn:hover{background:rgba(231,76,60,0.25)}

/* MAIN */
.main{margin-left:260px;flex:1;padding:30px}
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:30px}
.page-title{font-size:1.6rem;font-weight:600;color:#fff}
.page-title span{color:var(--gold)}
.topbar-actions{display:flex;gap:12px;align-items:center}
.view-site{padding:9px 20px;background:linear-gradient(135deg,var(--gold),var(--gold2));border-radius:10px;color:#1a1a1a;text-decoration:none;font-weight:600;font-size:0.9rem;transition:opacity 0.2s}
.view-site:hover{opacity:0.85}

/* STATS */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:30px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;text-align:center}
.stat-icon{font-size:2rem;margin-bottom:10px}
.stat-num{font-size:2rem;font-weight:700;color:var(--gold)}
.stat-label{color:var(--text2);font-size:0.85rem;margin-top:4px}

/* CARDS */
.section-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:24px}
.card-title{font-size:1.1rem;font-weight:600;color:#fff;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.card-title .icon{color:var(--gold)}

/* FORMS */
.form-group{margin-bottom:18px}
.form-group label{display:block;color:var(--text2);font-size:0.85rem;margin-bottom:7px;text-transform:uppercase;letter-spacing:1px}
.form-group input,.form-group textarea,.form-group select{
  width:100%;padding:13px 16px;background:rgba(255,255,255,0.06);
  border:1px solid var(--border);border-radius:10px;color:#fff;
  font-size:0.95rem;font-family:'Inter',sans-serif;outline:none;transition:border-color 0.3s;
}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{border-color:var(--gold)}
.form-group textarea{resize:vertical;min-height:100px}
.form-group select option{background:#2a2a2a}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}

/* BUTTONS */
.btn{padding:12px 24px;border:none;border-radius:10px;font-size:0.9rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:8px}
.btn-primary{background:linear-gradient(135deg,var(--gold),var(--gold2));color:#1a1a1a}
.btn-primary:hover{opacity:0.85;transform:translateY(-1px)}
.btn-danger{background:rgba(231,76,60,0.2);border:1px solid rgba(231,76,60,0.4);color:#e74c3c}
.btn-danger:hover{background:rgba(231,76,60,0.35)}
.btn-sm{padding:7px 14px;font-size:0.8rem}
.btn-outline{background:transparent;border:1px solid var(--border);color:var(--text)}
.btn-outline:hover{border-color:var(--gold);color:var(--gold)}

/* SUCCESS */
.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.alert-success{background:rgba(76,175,80,0.15);border:1px solid rgba(76,175,80,0.4);color:#81c784}

/* PRODUCTS TABLE */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{text-align:left;padding:12px 16px;color:var(--text2);font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border)}
td{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,0.05);font-size:0.9rem;vertical-align:middle}
tr:hover td{background:rgba(255,255,255,0.02)}
.badge{padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;text-transform:capitalize}
.badge-gold{background:rgba(201,169,110,0.2);color:var(--gold)}
.stars{color:var(--gold2)}
.prod-img{width:45px;height:45px;border-radius:8px;object-fit:cover;background:linear-gradient(135deg,#C9A96E22,#D4AF3722);display:flex;align-items:center;justify-content:center;font-size:1.2rem}

/* MODAL */
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;align-items:center;justify-content:center;padding:20px}
.modal.open{display:flex}
.modal-box{background:#242424;border:1px solid var(--border);border-radius:20px;padding:32px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
.modal-title{font-size:1.2rem;font-weight:600;color:#fff}
.modal-close{background:none;border:none;color:var(--text2);font-size:1.5rem;cursor:pointer;line-height:1}
.modal-close:hover{color:#fff}

/* TABS */
.tab-content{display:none}.tab-content.active{display:block}

/* TESTIMONIALS */
.testi-card{background:rgba(255,255,255,0.03);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:start;gap:12px}
.testi-info .name{font-weight:600;color:#fff;margin-bottom:4px}
.testi-info .text{color:var(--text2);font-size:0.9rem;margin-top:6px}
.testi-info .date{color:var(--text2);font-size:0.8rem;margin-top:4px}

/* FILE INPUT */
.file-input-wrap{position:relative}
.file-input-wrap input[type=file]{opacity:0;position:absolute;inset:0;cursor:pointer}
.file-label{display:flex;align-items:center;gap:10px;padding:13px 16px;background:rgba(255,255,255,0.06);border:1px dashed var(--border);border-radius:10px;color:var(--text2);font-size:0.9rem;cursor:pointer;transition:all 0.2s}
.file-label:hover{border-color:var(--gold);color:var(--gold)}

/* OVERVIEW QUICK ACTIONS */
.quick-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px}
.quick-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;text-decoration:none;color:var(--text);transition:all 0.2s}
.quick-card:hover{border-color:var(--gold);transform:translateY(-2px)}
.quick-card .qi{font-size:2rem;margin-bottom:10px}
.quick-card .qt{font-size:0.95rem;font-weight:500;color:#fff}
.quick-card .qs{font-size:0.8rem;color:var(--text2);margin-top:4px}

@media(max-width:900px){
  .sidebar{width:220px}.main{margin-left:220px}
  .stats-grid{grid-template-columns:1fr 1fr}
  .form-row,.form-row-3{grid-template-columns:1fr}
  .quick-grid{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand">C Beauty</div>
    <div class="sub">Admin Panel</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <a href="?tab=overview" class="nav-item <?= $activeTab==='overview'?'active':'' ?>"><span class="icon">🏠</span> Overview</a>
    <div class="nav-section">Content</div>
    <a href="?tab=hero" class="nav-item <?= $activeTab==='hero'?'active':'' ?>"><span class="icon">🖼️</span> Hero Section</a>
    <a href="?tab=about" class="nav-item <?= $activeTab==='about'?'active':'' ?>"><span class="icon">✨</span> About & Stats</a>
    <a href="?tab=products" class="nav-item <?= $activeTab==='products'?'active':'' ?>"><span class="icon">🛍️</span> Products</a>
    <a href="?tab=testimonials" class="nav-item <?= $activeTab==='testimonials'?'active':'' ?>"><span class="icon">💬</span> Testimonials</a>
    <div class="nav-section">Settings</div>
    <a href="?tab=contact" class="nav-item <?= $activeTab==='contact'?'active':'' ?>"><span class="icon">📞</span> Contact & Social</a>
    <a href="?tab=password" class="nav-item <?= $activeTab==='password'?'active':'' ?>"><span class="icon">🔐</span> Change Password</a>
  </nav>
  <div class="sidebar-footer">
    <a href="logout.php" class="logout-btn">🚪 Logout</a>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div>
      <div class="page-title">Welcome back, <span>Admin</span> 👑</div>
      <div style="color:var(--text2);font-size:0.85rem;margin-top:4px"><?= date('l, F j, Y') ?></div>
    </div>
    <div class="topbar-actions">
      <a href="../index.html" target="_blank" class="view-site">🌐 View Website</a>
    </div>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <!-- OVERVIEW -->
  <?php if($activeTab === 'overview'): ?>
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">🛍️</div><div class="stat-num"><?= count($content['products']) ?></div><div class="stat-label">Products</div></div>
    <div class="stat-card"><div class="stat-icon">💬</div><div class="stat-num"><?= count($content['testimonials']) ?></div><div class="stat-label">Testimonials</div></div>
    <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-num"><?= number_format($content['about']['stats']['customers']) ?>+</div><div class="stat-label">Customers</div></div>
    <div class="stat-card"><div class="stat-icon">⭐</div><div class="stat-num"><?= $content['about']['stats']['satisfaction'] ?>%</div><div class="stat-label">Satisfaction</div></div>
  </div>
  <div class="quick-grid">
    <a href="?tab=hero" class="quick-card"><div class="qi">🖼️</div><div class="qt">Edit Hero</div><div class="qs">Headline & CTA buttons</div></a>
    <a href="?tab=products" class="quick-card"><div class="qi">🛍️</div><div class="qt">Manage Products</div><div class="qs">Add, edit or remove products</div></a>
    <a href="?tab=about" class="quick-card"><div class="qi">📊</div><div class="qt">Update Stats</div><div class="qs">Counters & brand story</div></a>
    <a href="?tab=testimonials" class="quick-card"><div class="qi">💬</div><div class="qt">Testimonials</div><div class="qs">Customer reviews</div></a>
    <a href="?tab=contact" class="quick-card"><div class="qi">📞</div><div class="qt">Contact Info</div><div class="qs">WhatsApp, email, social</div></a>
    <a href="?tab=password" class="quick-card"><div class="qi">🔐</div><div class="qt">Security</div><div class="qs">Change admin password</div></a>
  </div>
  <?php endif; ?>

  <!-- HERO -->
  <?php if($activeTab === 'hero'): ?>
  <div class="section-card">
    <div class="card-title"><span class="icon">🖼️</span> Hero Section</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_hero">
      <div class="form-group"><label>Main Headline</label><input type="text" name="headline" value="<?= htmlspecialchars($content['hero']['headline']) ?>" required></div>
      <div class="form-group"><label>Sub Headline</label><textarea name="subheadline"><?= htmlspecialchars($content['hero']['subheadline']) ?></textarea></div>
      <div class="form-row">
        <div class="form-group"><label>Button 1 Text</label><input type="text" name="btn1" value="<?= htmlspecialchars($content['hero']['btn1']) ?>"></div>
        <div class="form-group"><label>Button 2 Text</label><input type="text" name="btn2" value="<?= htmlspecialchars($content['hero']['btn2']) ?>"></div>
      </div>
      <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- ABOUT -->
  <?php if($activeTab === 'about'): ?>
  <div class="section-card">
    <div class="card-title"><span class="icon">✨</span> About & Statistics</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_about">
      <div class="form-group"><label>Section Title</label><input type="text" name="title" value="<?= htmlspecialchars($content['about']['title']) ?>"></div>
      <div class="form-group"><label>Brand Story</label><textarea name="story" rows="4"><?= htmlspecialchars($content['about']['story']) ?></textarea></div>
      <div class="form-group"><label>Mission Statement</label><textarea name="mission" rows="3"><?= htmlspecialchars($content['about']['mission']) ?></textarea></div>
      <div class="card-title" style="margin-top:20px"><span class="icon">📊</span> Statistics</div>
      <div class="form-row">
        <div class="form-group"><label>Customers Served</label><input type="number" name="customers" value="<?= $content['about']['stats']['customers'] ?>"></div>
        <div class="form-group"><label>Products</label><input type="number" name="products" value="<?= $content['about']['stats']['products'] ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Consultations</label><input type="number" name="consultations" value="<?= $content['about']['stats']['consultations'] ?>"></div>
        <div class="form-group"><label>Satisfaction Rate (%)</label><input type="number" name="satisfaction" value="<?= $content['about']['stats']['satisfaction'] ?>" max="100"></div>
      </div>
      <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- PRODUCTS -->
  <?php if($activeTab === 'products'): ?>
  <div class="section-card">
    <div class="card-title" style="justify-content:space-between">
      <span><span class="icon">🛍️</span> Products (<?= count($content['products']) ?>)</span>
      <button class="btn btn-primary btn-sm" onclick="document.getElementById('addModal').classList.add('open')">+ Add Product</button>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Rating</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($content['products'] as $p): ?>
          <tr>
            <td>
              <?php if(!empty($p['image'])): ?>
                <img src="../<?= htmlspecialchars($p['image']) ?>" alt="" style="width:45px;height:45px;border-radius:8px;object-fit:cover">
              <?php else: ?>
                <div class="prod-img">🌸</div>
              <?php endif; ?>
            </td>
            <td style="font-weight:500;color:#fff"><?= htmlspecialchars($p['name']) ?></td>
            <td><span class="badge badge-gold"><?= $categories[$p['category']] ?? $p['category'] ?></span></td>
            <td style="color:var(--gold);font-weight:600">$<?= htmlspecialchars($p['price']) ?></td>
            <td class="stars"><?= str_repeat('★', $p['rating']) . str_repeat('☆', 5-$p['rating']) ?></td>
            <td>
              <button class="btn btn-outline btn-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($p)) ?>)">✏️ Edit</button>
              <form method="POST" style="display:inline" onsubmit="return confirm('Delete this product?')">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ADD MODAL -->
  <div class="modal" id="addModal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">➕ Add New Product</div>
        <button class="modal-close" onclick="document.getElementById('addModal').classList.remove('open')">×</button>
      </div>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_product">
        <div class="form-group"><label>Product Name</label><input type="text" name="name" required></div>
        <div class="form-row">
          <div class="form-group"><label>Category</label>
            <select name="category">
              <?php foreach($categories as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Price ($)</label><input type="text" name="price" required></div>
        </div>
        <div class="form-group"><label>Rating (1-5)</label><select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select></div>
        <div class="form-group"><label>Description</label><textarea name="description" rows="3"></textarea></div>
        <div class="form-group"><label>Product Image</label>
          <div class="file-input-wrap"><div class="file-label">📸 Click to upload image (JPG, PNG, WEBP)</div><input type="file" name="image" accept="image/*"></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">➕ Add Product</button>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div class="modal" id="editModal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">✏️ Edit Product</div>
        <button class="modal-close" onclick="document.getElementById('editModal').classList.remove('open')">×</button>
      </div>
      <form method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="action" value="edit_product">
        <input type="hidden" name="product_id" id="edit_id">
        <div class="form-group"><label>Product Name</label><input type="text" name="name" id="edit_name" required></div>
        <div class="form-row">
          <div class="form-group"><label>Category</label>
            <select name="category" id="edit_category">
              <?php foreach($categories as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-group"><label>Price ($)</label><input type="text" name="price" id="edit_price"></div>
        </div>
        <div class="form-group"><label>Rating</label><select name="rating" id="edit_rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select></div>
        <div class="form-group"><label>Description</label><textarea name="description" id="edit_desc" rows="3"></textarea></div>
        <div class="form-group"><label>New Image (optional)</label>
          <div class="file-input-wrap"><div class="file-label">📸 Upload new image</div><input type="file" name="image" accept="image/*"></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">💾 Save Changes</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- TESTIMONIALS -->
  <?php if($activeTab === 'testimonials'): ?>
  <div class="section-card">
    <div class="card-title" style="justify-content:space-between">
      <span><span class="icon">💬</span> Testimonials</span>
      <button class="btn btn-primary btn-sm" onclick="document.getElementById('testiModal').classList.add('open')">+ Add Review</button>
    </div>
    <?php foreach($content['testimonials'] as $i => $t): ?>
    <div class="testi-card">
      <div class="testi-info">
        <div class="name"><?= htmlspecialchars($t['name']) ?> <span class="stars"><?= str_repeat('★',$t['rating']) ?></span></div>
        <div class="text">"<?= htmlspecialchars($t['text']) ?>"</div>
        <div class="date"><?= htmlspecialchars($t['date']) ?></div>
      </div>
      <form method="POST" onsubmit="return confirm('Delete?')">
        <input type="hidden" name="action" value="delete_testimonial">
        <input type="hidden" name="idx" value="<?= $i ?>">
        <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="modal" id="testiModal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">➕ Add Testimonial</div>
        <button class="modal-close" onclick="document.getElementById('testiModal').classList.remove('open')">×</button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="add_testimonial">
        <div class="form-group"><label>Customer Name</label><input type="text" name="name" required></div>
        <div class="form-row">
          <div class="form-group"><label>Rating</label><select name="rating"><option>5</option><option>4</option><option>3</option></select></div>
          <div class="form-group"><label>Date</label><input type="text" name="date" placeholder="e.g. June 2025"></div>
        </div>
        <div class="form-group"><label>Review Text</label><textarea name="text" rows="4" required></textarea></div>
        <button type="submit" class="btn btn-primary" style="width:100%">➕ Add Review</button>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- CONTACT -->
  <?php if($activeTab === 'contact'): ?>
  <div class="section-card">
    <div class="card-title"><span class="icon">📞</span> Contact & Social Media</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_contact">
      <div class="form-row">
        <div class="form-group"><label>WhatsApp Number</label><input type="text" name="whatsapp" value="<?= htmlspecialchars($content['contact']['whatsapp']) ?>"></div>
        <div class="form-group"><label>Phone Number</label><input type="text" name="phone" value="<?= htmlspecialchars($content['contact']['phone']) ?>"></div>
      </div>
      <div class="form-group"><label>Email Address</label><input type="email" name="email" value="<?= htmlspecialchars($content['contact']['email']) ?>"></div>
      <div class="card-title" style="margin-top:10px"><span class="icon">📱</span> Social Media Links</div>
      <div class="form-row">
        <div class="form-group"><label>Instagram URL</label><input type="url" name="instagram" value="<?= htmlspecialchars($content['contact']['instagram']) ?>"></div>
        <div class="form-group"><label>TikTok URL</label><input type="url" name="tiktok" value="<?= htmlspecialchars($content['contact']['tiktok']) ?>"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Snapchat URL</label><input type="url" name="snapchat" value="<?= htmlspecialchars($content['contact']['snapchat']) ?>"></div>
        <div class="form-group"><label>Facebook URL</label><input type="url" name="facebook" value="<?= htmlspecialchars($content['contact']['facebook']) ?>"></div>
      </div>
      <button type="submit" class="btn btn-primary">💾 Save Changes</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- PASSWORD -->
  <?php if($activeTab === 'password'): ?>
  <div class="section-card" style="max-width:500px">
    <div class="card-title"><span class="icon">🔐</span> Change Admin Password</div>
    <form method="POST">
      <input type="hidden" name="action" value="change_password">
      <div class="form-group"><label>New Password</label><input type="password" name="new_password" minlength="6" required></div>
      <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" minlength="6" required></div>
      <button type="submit" class="btn btn-primary">🔐 Update Password</button>
    </form>
  </div>
  <?php endif; ?>

</main>

<script>
function openEdit(p) {
  document.getElementById('edit_id').value = p.id;
  document.getElementById('edit_name').value = p.name;
  document.getElementById('edit_price').value = p.price;
  document.getElementById('edit_desc').value = p.description;
  document.getElementById('edit_rating').value = p.rating;
  document.getElementById('edit_category').value = p.category;
  document.getElementById('editModal').classList.add('open');
}
// Close modal on outside click
document.querySelectorAll('.modal').forEach(m => {
  m.addEventListener('click', e => { if(e.target === m) m.classList.remove('open'); });
});
</script>
</body>
</html>
