<?php
require_once 'config/db.php';
requireLogin();

$uid = userId();
$msg = '';
$error = '';

// DELETE
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$del_id AND user_id=$uid");
    header("Location: categories.php?deleted=1");
    exit();
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $icon  = trim($_POST['icon']);
    $color = trim($_POST['color']);
    $type  = $_POST['type'];
    $cid   = (int)($_POST['cat_id'] ?? 0);

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        $esc_name = mysqli_real_escape_string($conn, $name);
        $esc_icon = mysqli_real_escape_string($conn, $icon ?: '📌');
        $esc_color = mysqli_real_escape_string($conn, $color ?: '#7C6FFF');
        $esc_type  = mysqli_real_escape_string($conn, $type);

        if ($cid > 0) {
            mysqli_query($conn, "UPDATE categories SET name='$esc_name',icon='$esc_icon',color='$esc_color',type='$esc_type' WHERE id=$cid AND user_id=$uid");
            $msg = "Category updated!";
        } else {
            mysqli_query($conn, "INSERT INTO categories (user_id,name,icon,color,type) VALUES ($uid,'$esc_name','$esc_icon','$esc_color','$esc_type')");
            $msg = "Category added!";
        }
    }
}

$edit_cat = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $r = mysqli_query($conn, "SELECT * FROM categories WHERE id=$eid AND user_id=$uid");
    $edit_cat = mysqli_fetch_assoc($r);
}

$cats_q = mysqli_query($conn, "SELECT c.*, COUNT(e.id) as exp_count FROM categories c LEFT JOIN expenses e ON e.category_id=c.id WHERE c.user_id=$uid GROUP BY c.id ORDER BY c.name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories — Trackwise</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  :root{--bg:#0D0F1A;--sidebar:#0F1220;--card:#13162A;--border:#1E2340;--accent:#7C6FFF;--accent2:#FF6B8A;--text:#E8EAFF;--muted:#6B7280;--sidebar-w:240px}
  body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh}
  .sidebar{width:var(--sidebar-w);background:var(--sidebar);border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
  .sidebar-brand{padding:1.5rem 1.4rem;border-bottom:1px solid var(--border)}
  .brand-logo{font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;background:linear-gradient(135deg,var(--accent),var(--accent2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  .brand-sub{font-size:0.7rem;color:var(--muted);margin-top:0.15rem}
  .sidebar-nav{padding:1rem 0.8rem;flex:1}
  .nav-label{font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);padding:0 0.6rem;margin:1rem 0 0.4rem}
  .nav-link{display:flex;align-items:center;gap:0.7rem;padding:0.65rem 0.8rem;border-radius:10px;text-decoration:none;color:var(--muted);font-size:0.9rem;transition:all 0.2s;margin-bottom:0.1rem}
  .nav-link:hover{background:rgba(124,111,255,0.08);color:var(--text)}
  .nav-link.active{background:rgba(124,111,255,0.15);color:var(--accent);font-weight:600}
  .nav-link .icon{font-size:1rem;width:20px;text-align:center}
  .sidebar-footer{padding:1rem 0.8rem;border-top:1px solid var(--border)}
  .user-info{display:flex;align-items:center;gap:0.7rem;padding:0.6rem 0.8rem;border-radius:10px;margin-bottom:0.5rem;background:rgba(255,255,255,0.03)}
  .avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:0.8rem;color:white;flex-shrink:0}
  .user-name{font-size:0.82rem;font-weight:500}
  .user-type{font-size:0.7rem;color:var(--muted);text-transform:capitalize}
  .logout-btn{display:flex;align-items:center;gap:0.6rem;width:100%;padding:0.6rem 0.8rem;border-radius:10px;background:none;border:none;color:var(--muted);font-family:'DM Sans',sans-serif;font-size:0.88rem;cursor:pointer;text-decoration:none;transition:all 0.2s}
  .logout-btn:hover{background:rgba(239,68,68,0.1);color:#FCA5A5}
  .main{margin-left:var(--sidebar-w);flex:1;padding:2rem}
  .page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem}
  .page-title{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:700}
  .page-title span{color:var(--muted);font-size:0.9rem;font-family:'DM Sans',sans-serif;font-weight:400;display:block;margin-top:0.2rem}
  .layout{display:grid;grid-template-columns:1fr 1.5fr;gap:1.2rem;align-items:start}
  .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:1.4rem}
  .card-title{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;margin-bottom:1.2rem}
  .alert{padding:0.8rem 1rem;border-radius:10px;font-size:0.88rem;margin-bottom:1rem}
  .alert.success{background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);color:#6EE7B7}
  .alert.error{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);color:#FCA5A5}
  .form-group{margin-bottom:1rem}
  label{display:block;font-size:0.78rem;font-weight:500;color:var(--muted);margin-bottom:0.4rem;text-transform:uppercase;letter-spacing:0.05em}
  input,select{width:100%;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;padding:0.7rem 0.9rem;color:var(--text);font-family:'DM Sans',sans-serif;font-size:0.9rem;transition:border-color 0.2s,box-shadow 0.2s;outline:none}
  input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(124,111,255,0.15)}
  select option{background:#1a1d30}
  .color-row{display:flex;align-items:center;gap:0.8rem}
  .color-preview{width:36px;height:36px;border-radius:8px;flex-shrink:0;border:2px solid var(--border)}
  input[type="color"]{padding:0.2rem;height:42px;cursor:pointer}
  .type-toggle{display:grid;grid-template-columns:1fr 1fr;gap:0.5rem}
  .type-toggle input[type="radio"]{display:none}
  .type-toggle label{display:flex;align-items:center;justify-content:center;gap:0.4rem;padding:0.6rem;border:1px solid var(--border);border-radius:10px;cursor:pointer;font-size:0.82rem;text-transform:none;letter-spacing:0;transition:all 0.2s;color:var(--muted)}
  .type-toggle input[type="radio"]:checked+label{border-color:var(--accent);background:rgba(124,111,255,0.12);color:var(--text)}
  .btn-save{width:100%;padding:0.75rem;background:linear-gradient(135deg,var(--accent),#9F7AEA);border:none;border-radius:10px;color:white;font-family:'Syne',sans-serif;font-size:0.92rem;font-weight:700;cursor:pointer;transition:opacity 0.2s;margin-top:0.5rem}
  .btn-save:hover{opacity:0.9}
  .btn-reset{width:100%;padding:0.6rem;background:rgba(255,255,255,0.04);border:1px solid var(--border);border-radius:10px;color:var(--muted);font-family:'DM Sans',sans-serif;font-size:0.85rem;cursor:pointer;text-decoration:none;display:block;text-align:center;margin-top:0.5rem}
  .cat-list{display:flex;flex-direction:column;gap:0.6rem}
  .cat-item{display:flex;align-items:center;gap:0.8rem;padding:0.85rem 1rem;background:rgba(255,255,255,0.02);border:1px solid var(--border);border-radius:12px;transition:background 0.2s}
  .cat-item:hover{background:rgba(255,255,255,0.04)}
  .cat-icon-box{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
  .cat-info{flex:1}
  .cat-name{font-weight:600;font-size:0.9rem}
  .cat-meta{font-size:0.75rem;color:var(--muted);margin-top:0.1rem}
  .type-pill{display:inline-block;padding:0.1rem 0.45rem;border-radius:20px;font-size:0.65rem;font-weight:600;text-transform:uppercase;letter-spacing:0.04em}
  .type-pill.personal{background:rgba(124,111,255,0.15);color:var(--accent)}
  .type-pill.business{background:rgba(255,107,138,0.15);color:var(--accent2)}
  .cat-actions{display:flex;gap:0.4rem}
  .btn-edit{padding:0.3rem 0.7rem;background:rgba(124,111,255,0.12);border:none;border-radius:6px;color:var(--accent);font-size:0.78rem;cursor:pointer;text-decoration:none;transition:background 0.2s}
  .btn-edit:hover{background:rgba(124,111,255,0.25)}
  .btn-del{padding:0.3rem 0.7rem;background:rgba(239,68,68,0.1);border:none;border-radius:6px;color:#FCA5A5;font-size:0.78rem;cursor:pointer;transition:background 0.2s}
  .btn-del:hover{background:rgba(239,68,68,0.2)}
  .empty-state{text-align:center;padding:2rem;color:var(--muted);font-size:0.9rem}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">⬡ Trackwise</div>
    <div class="brand-sub">Expense Analytics</div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Main</div>
    <a href="index.php" class="nav-link"><span class="icon">📊</span> Dashboard</a>
    <a href="expenses.php" class="nav-link"><span class="icon">💸</span> Expenses</a>
    <a href="categories.php" class="nav-link active"><span class="icon">🏷️</span> Categories</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <div class="user-type"><?= $_SESSION['user_type'] ?></div>
      </div>
    </div>
    <a href="logout.php" class="logout-btn">🚪 Sign Out</a>
  </div>
</aside>

<main class="main">
  <div class="page-header">
    <div class="page-title">Categories <span>Organise your expenses</span></div>
  </div>

  <?php if ($msg): ?><div class="alert success">✓ <?= $msg ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert error">✗ <?= $error ?></div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert success">✓ Category deleted.</div><?php endif; ?>

  <div class="layout">
    <!-- FORM -->
    <div class="card">
      <div class="card-title"><?= $edit_cat ? '✏️ Edit Category' : '➕ New Category' ?></div>
      <form method="POST">
        <?php if ($edit_cat): ?>
          <input type="hidden" name="cat_id" value="<?= $edit_cat['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" placeholder="e.g. Food & Dining" required value="<?= htmlspecialchars($edit_cat['name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Icon (emoji)</label>
          <input type="text" name="icon" placeholder="🍔" maxlength="4" value="<?= htmlspecialchars($edit_cat['icon'] ?? '📌') ?>">
        </div>

        <div class="form-group">
          <label>Color</label>
          <div class="color-row">
            <div class="color-preview" id="colorPreview" style="background:<?= $edit_cat['color'] ?? '#7C6FFF' ?>"></div>
            <input type="color" name="color" id="colorPicker" value="<?= $edit_cat['color'] ?? '#7C6FFF' ?>" style="flex:1">
          </div>
        </div>

        <div class="form-group">
          <label>Type</label>
          <div class="type-toggle">
            <div>
              <input type="radio" name="type" id="cp" value="personal" <?= ($edit_cat['type'] ?? 'personal') === 'personal' ? 'checked' : '' ?>>
              <label for="cp">👤 Personal</label>
            </div>
            <div>
              <input type="radio" name="type" id="cb" value="business" <?= ($edit_cat['type'] ?? '') === 'business' ? 'checked' : '' ?>>
              <label for="cb">🏢 Business</label>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-save"><?= $edit_cat ? 'Update Category' : 'Add Category' ?></button>
        <?php if ($edit_cat): ?>
          <a href="categories.php" class="btn-reset">Cancel</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- LIST -->
    <div class="card">
      <div class="card-title">Your Categories</div>
      <?php if (mysqli_num_rows($cats_q) === 0): ?>
        <div class="empty-state">No categories yet. Add one!</div>
      <?php else: ?>
      <div class="cat-list">
        <?php while ($cat = mysqli_fetch_assoc($cats_q)): ?>
        <div class="cat-item">
          <div class="cat-icon-box" style="background:<?= $cat['color'] ?>22"><?= $cat['icon'] ?></div>
          <div class="cat-info">
            <div class="cat-name"><?= htmlspecialchars($cat['name']) ?></div>
            <div class="cat-meta">
              <span class="type-pill <?= $cat['type'] ?>"><?= $cat['type'] ?></span>
              · <?= $cat['exp_count'] ?> expense<?= $cat['exp_count'] != 1 ? 's' : '' ?>
            </div>
          </div>
          <div class="cat-actions">
            <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn-edit">Edit</a>
            <button class="btn-del" onclick="if(confirm('Delete category?')) window.location='categories.php?delete=<?= $cat['id'] ?>'">Delete</button>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<script>
const picker = document.getElementById('colorPicker');
const preview = document.getElementById('colorPreview');
picker.addEventListener('input', () => { preview.style.background = picker.value; });
</script>
</body>
</html>