<?php
require_once 'config/db.php';
requireLogin();

$uid = userId();

// Totals
$total_q = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses WHERE user_id=$uid");
$total = mysqli_fetch_assoc($total_q)['total'] ?? 0;

$personal_q = mysqli_query($conn, "SELECT SUM(amount) as t FROM expenses WHERE user_id=$uid AND type='personal'");
$personal = mysqli_fetch_assoc($personal_q)['t'] ?? 0;

$business_q = mysqli_query($conn, "SELECT SUM(amount) as t FROM expenses WHERE user_id=$uid AND type='business'");
$business = mysqli_fetch_assoc($business_q)['t'] ?? 0;

// This month
$month_q = mysqli_query($conn, "SELECT SUM(amount) as t FROM expenses WHERE user_id=$uid AND MONTH(expense_date)=MONTH(NOW()) AND YEAR(expense_date)=YEAR(NOW())");
$this_month = mysqli_fetch_assoc($month_q)['t'] ?? 0;

// Recent 5 expenses
$recent_q = mysqli_query($conn, "SELECT e.*, c.name as cat_name, c.icon as cat_icon, c.color as cat_color 
    FROM expenses e 
    LEFT JOIN categories c ON e.category_id = c.id 
    WHERE e.user_id=$uid 
    ORDER BY e.expense_date DESC, e.created_at DESC 
    LIMIT 5");

// Category breakdown for chart
$cat_q = mysqli_query($conn, "SELECT c.name, c.color, SUM(e.amount) as total 
    FROM expenses e 
    JOIN categories c ON e.category_id = c.id 
    WHERE e.user_id=$uid 
    GROUP BY c.id 
    ORDER BY total DESC 
    LIMIT 6");

$chart_labels = [];
$chart_data   = [];
$chart_colors = [];
while ($row = mysqli_fetch_assoc($cat_q)) {
    $chart_labels[] = $row['name'];
    $chart_data[]   = (float)$row['total'];
    $chart_colors[] = $row['color'];
}

// Monthly trend (last 6 months)
$monthly_q = mysqli_query($conn, "SELECT DATE_FORMAT(expense_date,'%b') as month, SUM(amount) as total 
    FROM expenses 
    WHERE user_id=$uid AND expense_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    GROUP BY YEAR(expense_date), MONTH(expense_date) 
    ORDER BY expense_date ASC");

$monthly_labels = [];
$monthly_data   = [];
while ($row = mysqli_fetch_assoc($monthly_q)) {
    $monthly_labels[] = $row['month'];
    $monthly_data[]   = (float)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Trackwise</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0D0F1A;
    --sidebar: #0F1220;
    --card: #13162A;
    --border: #1E2340;
    --accent: #7C6FFF;
    --accent2: #FF6B8A;
    --green: #10B981;
    --yellow: #F59E0B;
    --text: #E8EAFF;
    --muted: #6B7280;
    --sidebar-w: 240px;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    display: flex;
    min-height: 100vh;
  }

  /* ── SIDEBAR ── */
  .sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
  }

  .sidebar-brand {
    padding: 1.5rem 1.4rem;
    border-bottom: 1px solid var(--border);
  }

  .brand-logo {
    font-family: 'Syne', sans-serif;
    font-size: 1.2rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .brand-sub { font-size: 0.7rem; color: var(--muted); margin-top: 0.15rem; }

  .sidebar-nav { padding: 1rem 0.8rem; flex: 1; }

  .nav-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--muted);
    padding: 0 0.6rem;
    margin: 1rem 0 0.4rem;
  }

  .nav-link {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.65rem 0.8rem;
    border-radius: 10px;
    text-decoration: none;
    color: var(--muted);
    font-size: 0.9rem;
    transition: all 0.2s;
    margin-bottom: 0.1rem;
  }

  .nav-link:hover { background: rgba(124,111,255,0.08); color: var(--text); }
  .nav-link.active { background: rgba(124,111,255,0.15); color: var(--accent); font-weight: 600; }
  .nav-link .icon { font-size: 1rem; width: 20px; text-align: center; }

  .sidebar-footer {
    padding: 1rem 0.8rem;
    border-top: 1px solid var(--border);
  }

  .user-info {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.6rem 0.8rem;
    border-radius: 10px;
    margin-bottom: 0.5rem;
    background: rgba(255,255,255,0.03);
  }

  .avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex; align-items: center; justify-content: center;
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.8rem;
    color: white;
    flex-shrink: 0;
  }

  .user-name { font-size: 0.82rem; font-weight: 500; }
  .user-type { font-size: 0.7rem; color: var(--muted); text-transform: capitalize; }

  .logout-btn {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    width: 100%;
    padding: 0.6rem 0.8rem;
    border-radius: 10px;
    background: none;
    border: none;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
  }

  .logout-btn:hover { background: rgba(239,68,68,0.1); color: #FCA5A5; }

  /* ── MAIN ── */
  .main {
    margin-left: var(--sidebar-w);
    flex: 1;
    padding: 2rem;
    min-height: 100vh;
  }

  .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
  }

  .page-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.6rem;
    font-weight: 700;
  }

  .page-title span { color: var(--muted); font-size: 0.9rem; font-family: 'DM Sans', sans-serif; font-weight: 400; display: block; margin-top: 0.2rem; }

  .btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.65rem 1.2rem;
    background: linear-gradient(135deg, var(--accent), #9F7AEA);
    border: none;
    border-radius: 10px;
    color: white;
    font-family: 'Syne', sans-serif;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.2s, transform 0.1s;
  }

  .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

  /* ── STAT CARDS ── */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.3rem 1.4rem;
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 80px; height: 80px;
    border-radius: 50%;
    opacity: 0.08;
  }

  .stat-card.purple::before { background: var(--accent); }
  .stat-card.pink::before   { background: var(--accent2); }
  .stat-card.green::before  { background: var(--green); }
  .stat-card.yellow::before { background: var(--yellow); }

  .stat-icon {
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
    display: block;
  }

  .stat-label { font-size: 0.75rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
  .stat-value { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 700; }
  .stat-value.purple { color: var(--accent); }
  .stat-value.pink   { color: var(--accent2); }
  .stat-value.green  { color: var(--green); }
  .stat-value.yellow { color: var(--yellow); }

  /* ── CHARTS ROW ── */
  .charts-row {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
  }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.4rem;
  }

  .card-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-title a { font-size: 0.78rem; font-family: 'DM Sans', sans-serif; font-weight: 400; color: var(--accent); text-decoration: none; }

  .chart-container { position: relative; height: 200px; }

  /* ── RECENT TABLE ── */
  .recent-table { width: 100%; border-collapse: collapse; }

  .recent-table th {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    text-align: left;
    padding: 0 0 0.8rem;
    border-bottom: 1px solid var(--border);
  }

  .recent-table td {
    padding: 0.85rem 0;
    font-size: 0.88rem;
    border-bottom: 1px solid rgba(30,35,64,0.5);
    vertical-align: middle;
  }

  .recent-table tr:last-child td { border-bottom: none; }

  .cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
  }

  .type-pill {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .type-pill.personal { background: rgba(124,111,255,0.15); color: var(--accent); }
  .type-pill.business { background: rgba(255,107,138,0.15); color: var(--accent2); }

  .amount { font-family: 'Syne', sans-serif; font-weight: 600; }

  .empty-state {
    text-align: center;
    padding: 2.5rem;
    color: var(--muted);
  }

  .empty-state .emoji { font-size: 2.5rem; margin-bottom: 0.7rem; }
  .empty-state p { font-size: 0.9rem; margin-bottom: 1rem; }

  @media (max-width: 900px) {
    .stats-grid { grid-template-columns: repeat(2,1fr); }
    .charts-row { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo">⬡ Trackwise</div>
    <div class="brand-sub">Expense Analytics</div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Main</div>
    <a href="index.php" class="nav-link active"><span class="icon">📊</span> Dashboard</a>
    <a href="expenses.php" class="nav-link"><span class="icon">💸</span> Expenses</a>
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

<!-- MAIN CONTENT -->
<main class="main">
  <div class="page-header">
    <div class="page-title">
      Dashboard
      <span>Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>! Here's your overview.</span>
    </div>
    <a href="expenses.php?action=add" class="btn-primary">+ Add Expense</a>
  </div>

  <!-- STAT CARDS -->
  <div class="stats-grid">
    <div class="stat-card purple">
      <span class="stat-icon">💰</span>
      <div class="stat-label">Total Spending</div>
      <div class="stat-value purple">₹<?= number_format($total, 2) ?></div>
    </div>
    <div class="stat-card pink">
      <span class="stat-icon">🏢</span>
      <div class="stat-label">Business</div>
      <div class="stat-value pink">₹<?= number_format($business, 2) ?></div>
    </div>
    <div class="stat-card green">
      <span class="stat-icon">👤</span>
      <div class="stat-label">Personal</div>
      <div class="stat-value green">₹<?= number_format($personal, 2) ?></div>
    </div>
    <div class="stat-card yellow">
      <span class="stat-icon">📅</span>
      <div class="stat-label">This Month</div>
      <div class="stat-value yellow">₹<?= number_format($this_month, 2) ?></div>
    </div>
  </div>

  <!-- CHARTS -->
  <div class="charts-row">
    <div class="card">
      <div class="card-title">By Category</div>
      <div class="chart-container">
        <?php if (empty($chart_data)): ?>
          <div class="empty-state"><div class="emoji">📂</div><p>No data yet</p></div>
        <?php else: ?>
          <canvas id="doughnutChart"></canvas>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Monthly Trend <a href="expenses.php">View all →</a></div>
      <div class="chart-container">
        <?php if (empty($monthly_data)): ?>
          <div class="empty-state"><div class="emoji">📈</div><p>Add expenses to see trends</p></div>
        <?php else: ?>
          <canvas id="lineChart"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RECENT EXPENSES -->
  <div class="card">
    <div class="card-title">Recent Expenses <a href="expenses.php">See all →</a></div>

    <?php if (mysqli_num_rows($recent_q) === 0): ?>
      <div class="empty-state">
        <div class="emoji">🧾</div>
        <p>No expenses yet. Start by adding one!</p>
        <a href="expenses.php?action=add" class="btn-primary">+ Add First Expense</a>
      </div>
    <?php else: ?>
    <table class="recent-table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Type</th>
          <th>Date</th>
          <th style="text-align:right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($exp = mysqli_fetch_assoc($recent_q)): ?>
        <tr>
          <td><?= htmlspecialchars($exp['title']) ?></td>
          <td>
            <?php if ($exp['cat_name']): ?>
              <span class="cat-badge" style="background:<?= $exp['cat_color'] ?>22; color:<?= $exp['cat_color'] ?>">
                <?= $exp['cat_icon'] ?> <?= htmlspecialchars($exp['cat_name']) ?>
              </span>
            <?php else: ?>
              <span style="color:var(--muted)">—</span>
            <?php endif; ?>
          </td>
          <td><span class="type-pill <?= $exp['type'] ?>"><?= $exp['type'] ?></span></td>
          <td style="color:var(--muted)"><?= date('d M Y', strtotime($exp['expense_date'])) ?></td>
          <td style="text-align:right" class="amount">₹<?= number_format($exp['amount'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</main>

<script>
Chart.defaults.color = '#6B7280';
Chart.defaults.borderColor = '#1E2340';
Chart.defaults.font.family = "'DM Sans', sans-serif";

<?php if (!empty($chart_data)): ?>
new Chart(document.getElementById('doughnutChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($chart_labels) ?>,
    datasets: [{
      data: <?= json_encode($chart_data) ?>,
      backgroundColor: <?= json_encode(array_map(fn($c) => $c . 'CC', $chart_colors)) ?>,
      borderColor: '#13162A',
      borderWidth: 3,
      hoverOffset: 6
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } },
    cutout: '65%'
  }
});
<?php endif; ?>

<?php if (!empty($monthly_data)): ?>
new Chart(document.getElementById('lineChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($monthly_labels) ?>,
    datasets: [{
      label: 'Spending',
      data: <?= json_encode($monthly_data) ?>,
      borderColor: '#7C6FFF',
      backgroundColor: 'rgba(124,111,255,0.1)',
      tension: 0.4,
      fill: true,
      pointBackgroundColor: '#7C6FFF',
      pointRadius: 5
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: '#1E2340' } },
      y: { grid: { color: '#1E2340' }, beginAtZero: true }
    }
  }
});
<?php endif; ?>
</script>
</body>
</html>