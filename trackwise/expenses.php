<?php
require_once 'config/db.php';
requireLogin();

$uid = userId();
$action = $_GET['action'] ?? 'list';
$edit_id = (int)($_GET['id'] ?? 0);
$msg = '';
$error = '';

// ── CATEGORIES for dropdown ──
$cats_q = mysqli_query($conn, "SELECT * FROM categories WHERE user_id=$uid ORDER BY name");
$categories = [];
while ($c = mysqli_fetch_assoc($cats_q)) $categories[] = $c;

// ── DELETE ──
if ($action === 'delete' && $edit_id) {
    mysqli_query($conn, "DELETE FROM expenses WHERE id=$edit_id AND user_id=$uid");
    header("Location: expenses.php?deleted=1");
    exit();
}

// ── SAVE (ADD or EDIT) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']);
    $amount   = (float)$_POST['amount'];
    $cat_id   = (int)$_POST['category_id'];
    $type     = $_POST['type'];
    $date     = $_POST['expense_date'];
    $note     = trim($_POST['note']);

    if (empty($title) || $amount <= 0 || empty($date)) {
        $error = "Please fill in title, amount, and date.";
    } else {
        $esc_title = mysqli_real_escape_string($conn, $title);
        $esc_note  = mysqli_real_escape_string($conn, $note);
        $esc_type  = mysqli_real_escape_string($conn, $type);
        $cat_val   = $cat_id > 0 ? $cat_id : 'NULL';

        if (isset($_POST['expense_id']) && (int)$_POST['expense_id'] > 0) {
            $eid = (int)$_POST['expense_id'];
            mysqli_query($conn, "UPDATE expenses SET title='$esc_title', amount=$amount, category_id=$cat_val, type='$esc_type', expense_date='$date', note='$esc_note' WHERE id=$eid AND user_id=$uid");
            $msg = "Expense updated!";
        } else {
            mysqli_query($conn, "INSERT INTO expenses (user_id, title, amount, category_id, type, expense_date, note) VALUES ($uid,'$esc_title',$amount,$cat_val,'$esc_type','$date','$esc_note')");
            $msg = "Expense added!";
        }
        $action = 'list';
    }
}

// ── LOAD EXPENSE FOR EDIT ──
$edit_exp = null;
if ($action === 'edit' && $edit_id) {
    $r = mysqli_query($conn, "SELECT * FROM expenses WHERE id=$edit_id AND user_id=$uid");
    $edit_exp = mysqli_fetch_assoc($r);
    if (!$edit_exp) { $action = 'list'; }
}

// ── LIST with search/filter ──
$search     = trim($_GET['search'] ?? '');
$filter_cat = (int)($_GET['cat'] ?? 0);
$filter_type = $_GET['type'] ?? '';

$where = "WHERE e.user_id=$uid";
if ($search) {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND e.title LIKE '%$s%'";
}
if ($filter_cat > 0) $where .= " AND e.category_id=$filter_cat";
if ($filter_type)    $where .= " AND e.type='" . mysqli_real_escape_string($conn, $filter_type) . "'";

$list_q = mysqli_query($conn, "SELECT e.*, c.name as cat_name, c.icon as cat_icon, c.color as cat_color 
    FROM expenses e LEFT JOIN categories c ON e.category_id=c.id 
    $where ORDER BY e.expense_date DESC, e.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Expenses — Trackwise</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0D0F1A; --sidebar: #0F1220; --card: #13162A; --border: #1E2340;
    --accent: #7C6FFF; --accent2: #FF6B8A; --green: #10B981;
    --text: #E8EAFF; --muted: #6B7280; --sidebar-w: 240px;
  }

  body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text); display:flex; min-height:100vh; }

  /* SIDEBAR */
  .sidebar { width:var(--sidebar-w); background:var(--sidebar); border-right:1px solid var(--border); display:flex; flex-direction:column; position:fixed; top:0;left:0;bottom:0; z-index:100; }
  .sidebar-brand { padding:1.5rem 1.4rem; border-bottom:1px solid var(--border); }
  .brand-logo { font-family:'Syne',sans-serif; font-size:1.2rem; font-weight:800; background:linear-gradient(135deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .brand-sub { font-size:0.7rem; color:var(--muted); margin-top:0.15rem; }
  .sidebar-nav { padding:1rem 0.8rem; flex:1; }
  .nav-label { font-size:0.65rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--muted); padding:0 0.6rem; margin:1rem 0 0.4rem; }
  .nav-link { display:flex; align-items:center; gap:0.7rem; padding:0.65rem 0.8rem; border-radius:10px; text-decoration:none; color:var(--muted); font-size:0.9rem; transition:all 0.2s; margin-bottom:0.1rem; }
  .nav-link:hover { background:rgba(124,111,255,0.08); color:var(--text); }
  .nav-link.active { background:rgba(124,111,255,0.15); color:var(--accent); font-weight:600; }
  .nav-link .icon { font-size:1rem; width:20px; text-align:center; }
  .sidebar-footer { padding:1rem 0.8rem; border-top:1px solid var(--border); }
  .user-info { display:flex; align-items:center; gap:0.7rem; padding:0.6rem 0.8rem; border-radius:10px; margin-bottom:0.5rem; background:rgba(255,255,255,0.03); }
  .avatar { width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:0.8rem;color:white;flex-shrink:0; }
  .user-name { font-size:0.82rem; font-weight:500; }
  .user-type { font-size:0.7rem; color:var(--muted); text-transform:capitalize; }
  .logout-btn { display:flex; align-items:center; gap:0.6rem; width:100%; padding:0.6rem 0.8rem; border-radius:10px; background:none; border:none; color:var(--muted); font-family:'DM Sans',sans-serif; font-size:0.88rem; cursor:pointer; text-decoration:none; transition:all 0.2s; }
  .logout-btn:hover { background:rgba(239,68,68,0.1); color:#FCA5A5; }

  /* MAIN */
  .main { margin-left:var(--sidebar-w); flex:1; padding:2rem; }
  .page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:2rem; }
  .page-title { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:700; }
  .page-title span { color:var(--muted); font-size:0.9rem; font-family:'DM Sans',sans-serif; font-weight:400; display:block; margin-top:0.2rem; }

  .btn-primary { display:inline-flex; align-items:center; gap:0.4rem; padding:0.65rem 1.2rem; background:linear-gradient(135deg,var(--accent),#9F7AEA); border:none; border-radius:10px; color:white; font-family:'Syne',sans-serif; font-size:0.88rem; font-weight:600; cursor:pointer; text-decoration:none; transition:opacity 0.2s,transform 0.1s; }
  .btn-primary:hover { opacity:0.9; transform:translateY(-1px); }

  .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:1.4rem; margin-bottom:1.2rem; }
  .card-title { font-family:'Syne',sans-serif; font-size:1rem; font-weight:700; margin-bottom:1.2rem; }

  /* ALERTS */
  .alert { padding:0.8rem 1rem; border-radius:10px; font-size:0.88rem; margin-bottom:1rem; }
  .alert.success { background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:#6EE7B7; }
  .alert.error   { background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#FCA5A5; }

  /* FORM */
  .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  .form-full { grid-column:1/-1; }
  .form-group { margin-bottom:0; }

  label { display:block; font-size:0.78rem; font-weight:500; color:var(--muted); margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.05em; }

  input, select, textarea { width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border); border-radius:10px; padding:0.7rem 0.9rem; color:var(--text); font-family:'DM Sans',sans-serif; font-size:0.9rem; transition:border-color 0.2s,box-shadow 0.2s; outline:none; }
  input:focus, select:focus, textarea:focus { border-color:var(--accent); box-shadow:0 0 0 3px rgba(124,111,255,0.15); }
  select option { background:#1a1d30; }
  textarea { resize:vertical; min-height:80px; }

  .type-toggle { display:grid; grid-template-columns:1fr 1fr; gap:0.5rem; }
  .type-toggle input[type="radio"] { display:none; }
  .type-toggle label { display:flex; align-items:center; justify-content:center; gap:0.4rem; padding:0.65rem; border:1px solid var(--border); border-radius:10px; cursor:pointer; font-size:0.85rem; text-transform:none; letter-spacing:0; transition:all 0.2s; color:var(--muted); }
  .type-toggle input[type="radio"]:checked + label { border-color:var(--accent); background:rgba(124,111,255,0.12); color:var(--text); }

  .form-actions { display:flex; gap:0.8rem; margin-top:1.2rem; }
  .btn-save { padding:0.7rem 1.6rem; background:linear-gradient(135deg,var(--accent),#9F7AEA); border:none; border-radius:10px; color:white; font-family:'Syne',sans-serif; font-size:0.9rem; font-weight:700; cursor:pointer; transition:opacity 0.2s; }
  .btn-save:hover { opacity:0.9; }
  .btn-cancel { padding:0.7rem 1.2rem; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:10px; color:var(--muted); font-family:'DM Sans',sans-serif; font-size:0.9rem; cursor:pointer; text-decoration:none; display:inline-block; }

  /* FILTERS */
  .filters { display:flex; gap:0.7rem; margin-bottom:1rem; flex-wrap:wrap; }
  .filters input, .filters select { flex:1; min-width:150px; max-width:220px; margin:0; }

  /* TABLE */
  .exp-table { width:100%; border-collapse:collapse; }
  .exp-table th { font-size:0.72rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); text-align:left; padding:0 0 0.8rem; border-bottom:1px solid var(--border); }
  .exp-table td { padding:0.9rem 0; font-size:0.88rem; border-bottom:1px solid rgba(30,35,64,0.5); vertical-align:middle; }
  .exp-table tr:last-child td { border-bottom:none; }
  .exp-table tr:hover td { background:rgba(255,255,255,0.01); }

  .cat-badge { display:inline-flex; align-items:center; gap:0.3rem; padding:0.2rem 0.6rem; border-radius:6px; font-size:0.75rem; font-weight:500; }
  .type-pill { display:inline-block; padding:0.15rem 0.5rem; border-radius:20px; font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; }
  .type-pill.personal { background:rgba(124,111,255,0.15); color:var(--accent); }
  .type-pill.business { background:rgba(255,107,138,0.15); color:var(--accent2); }
  .amount { font-family:'Syne',sans-serif; font-weight:600; }

  .action-btns { display:flex; gap:0.4rem; }
  .btn-edit { padding:0.3rem 0.7rem; background:rgba(124,111,255,0.12); border:none; border-radius:6px; color:var(--accent); font-size:0.78rem; cursor:pointer; text-decoration:none; transition:background 0.2s; }
  .btn-edit:hover { background:rgba(124,111,255,0.25); }
  .btn-del { padding:0.3rem 0.7rem; background:rgba(239,68,68,0.1); border:none; border-radius:6px; color:#FCA5A5; font-size:0.78rem; cursor:pointer; transition:background 0.2s; }
  .btn-del:hover { background:rgba(239,68,68,0.2); }

  .empty-state { text-align:center; padding:3rem; color:var(--muted); }
  .empty-state .emoji { font-size:2.5rem; margin-bottom:0.7rem; }
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
    <a href="expenses.php" class="nav-link active"><span class="icon">💸</span> Expenses</a>
    <a href="categories.php" class="nav-link"><span class="icon">🏷️</span> Categories</a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
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
    <div class="page-title">
      Expenses
      <span>Manage all your spending records</span>
    </div>
    <?php if ($action === 'list'): ?>
      <a href="expenses.php?action=add" class="btn-primary">+ Add Expense</a>
    <?php endif; ?>
  </div>

  <?php if ($msg): ?><div class="alert success">✓ <?= $msg ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert error">✗ <?= $error ?></div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert success">✓ Expense deleted.</div><?php endif; ?>

  <!-- ADD / EDIT FORM -->
  <?php if ($action === 'add' || $action === 'edit'): ?>
  <div class="card">
    <div class="card-title"><?= $action === 'edit' ? '✏️ Edit Expense' : '➕ New Expense' ?></div>
    <form method="POST">
      <?php if ($edit_exp): ?>
        <input type="hidden" name="expense_id" value="<?= $edit_exp['id'] ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group form-full">
          <label>Title / Description</label>
          <input type="text" name="title" placeholder="e.g. Lunch at Cafe, Office WiFi..." required value="<?= htmlspecialchars($edit_exp['title'] ?? $_POST['title'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Amount (₹)</label>
          <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required value="<?= $edit_exp['amount'] ?? $_POST['amount'] ?? '' ?>">
        </div>

        <div class="form-group">
          <label>Date</label>
          <input type="date" name="expense_date" required value="<?= $edit_exp['expense_date'] ?? $_POST['expense_date'] ?? date('Y-m-d') ?>">
        </div>

        <div class="form-group">
          <label>Category</label>
          <select name="category_id">
            <option value="0">— No category —</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($edit_exp['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Type</label>
          <div class="type-toggle">
            <div>
              <input type="radio" name="type" id="t_personal" value="personal" <?= ($edit_exp['type'] ?? 'personal') === 'personal' ? 'checked' : '' ?>>
              <label for="t_personal">👤 Personal</label>
            </div>
            <div>
              <input type="radio" name="type" id="t_business" value="business" <?= ($edit_exp['type'] ?? '') === 'business' ? 'checked' : '' ?>>
              <label for="t_business">🏢 Business</label>
            </div>
          </div>
        </div>

        <div class="form-group form-full">
          <label>Note (optional)</label>
          <textarea name="note" placeholder="Add any extra details..."><?= htmlspecialchars($edit_exp['note'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-save"><?= $action === 'edit' ? 'Update Expense' : 'Save Expense' ?></button>
        <a href="expenses.php" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <!-- LIST -->
  <?php if ($action === 'list'): ?>
  <div class="card">
    <!-- Filters -->
    <form method="GET" class="filters">
      <input type="hidden" name="action" value="list">
      <input type="text" name="search" placeholder="🔍 Search expenses..." value="<?= htmlspecialchars($search) ?>">
      <select name="cat">
        <option value="0">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $filter_cat == $cat['id'] ? 'selected' : '' ?>>
            <?= $cat['icon'] ?> <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <select name="type">
        <option value="">All Types</option>
        <option value="personal" <?= $filter_type === 'personal' ? 'selected' : '' ?>>Personal</option>
        <option value="business" <?= $filter_type === 'business' ? 'selected' : '' ?>>Business</option>
      </select>
      <button type="submit" class="btn-primary" style="flex-shrink:0">Filter</button>
      <?php if ($search || $filter_cat || $filter_type): ?>
        <a href="expenses.php" style="color:var(--muted);align-self:center;font-size:0.85rem;text-decoration:none">✕ Clear</a>
      <?php endif; ?>
    </form>

    <?php if (mysqli_num_rows($list_q) === 0): ?>
      <div class="empty-state">
        <div class="emoji">🧾</div>
        <p><?= ($search || $filter_cat || $filter_type) ? 'No expenses match your filters.' : 'No expenses yet. Add your first one!' ?></p>
      </div>
    <?php else: ?>
    <table class="exp-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Type</th>
          <th>Date</th>
          <th>Note</th>
          <th style="text-align:right">Amount</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($exp = mysqli_fetch_assoc($list_q)): ?>
        <tr>
          <td><strong><?= htmlspecialchars($exp['title']) ?></strong></td>
          <td>
            <?php if ($exp['cat_name']): ?>
              <span class="cat-badge" style="background:<?= $exp['cat_color'] ?>22;color:<?= $exp['cat_color'] ?>">
                <?= $exp['cat_icon'] ?> <?= htmlspecialchars($exp['cat_name']) ?>
              </span>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td><span class="type-pill <?= $exp['type'] ?>"><?= $exp['type'] ?></span></td>
          <td style="color:var(--muted)"><?= date('d M Y', strtotime($exp['expense_date'])) ?></td>
          <td style="color:var(--muted);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= $exp['note'] ? htmlspecialchars(substr($exp['note'],0,50)) : '—' ?>
          </td>
          <td style="text-align:right" class="amount">₹<?= number_format($exp['amount'],2) ?></td>
          <td style="text-align:right">
            <div class="action-btns" style="justify-content:flex-end">
              <a href="expenses.php?action=edit&id=<?= $exp['id'] ?>" class="btn-edit">Edit</a>
              <button class="btn-del" onclick="if(confirm('Delete this expense?')) window.location='expenses.php?action=delete&id=<?= $exp['id'] ?>'">Delete</button>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</main>
</body>
</html>