<?php
require_once 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter your email and password.";
    } else {
        $esc = mysqli_real_escape_string($conn, $email);
        $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$esc' LIMIT 1");

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['account_type'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Trackwise</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #0D0F1A;
    --card: #13162A;
    --border: #1E2340;
    --accent: #7C6FFF;
    --accent2: #FF6B8A;
    --text: #E8EAFF;
    --muted: #6B7280;
  }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background-image: radial-gradient(ellipse at 20% 50%, rgba(124,111,255,0.08) 0%, transparent 60%),
                      radial-gradient(ellipse at 80% 20%, rgba(255,107,138,0.06) 0%, transparent 50%);
  }

  .auth-wrapper { width: 100%; max-width: 420px; }

  .brand { text-align: center; margin-bottom: 2rem; }

  .brand-logo {
    font-family: 'Syne', sans-serif;
    font-size: 1.8rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .brand p { color: var(--muted); font-size: 0.85rem; margin-top: 0.3rem; }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 2.2rem;
  }

  .card h2 { font-family: 'Syne', sans-serif; font-size: 1.4rem; font-weight: 700; margin-bottom: 0.3rem; }
  .card .subtitle { color: var(--muted); font-size: 0.85rem; margin-bottom: 1.8rem; }

  .alert {
    padding: 0.8rem 1rem;
    border-radius: 10px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
    background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.3);
    color: #FCA5A5;
  }

  .form-group { margin-bottom: 1.1rem; }

  label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--muted);
    margin-bottom: 0.4rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  input {
    width: 100%;
    background: rgba(255,255,255,0.04);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
  }

  input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(124,111,255,0.15);
  }

  .forgot { text-align: right; margin-top: -0.6rem; margin-bottom: 1rem; }
  .forgot a { color: var(--accent); font-size: 0.8rem; text-decoration: none; }

  .btn {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, var(--accent), #9F7AEA);
    border: none;
    border-radius: 12px;
    color: white;
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
  }

  .btn:hover { opacity: 0.9; transform: translateY(-1px); }

  .signup-link {
    text-align: center;
    margin-top: 1.4rem;
    font-size: 0.88rem;
    color: var(--muted);
  }

  .signup-link a { color: var(--accent); text-decoration: none; font-weight: 600; }

  .divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.4rem 0;
    color: var(--muted);
    font-size: 0.8rem;
  }

  .divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }
</style>
</head>
<body>
<div class="auth-wrapper">
  <div class="brand">
    <div class="brand-logo">⬡ Trackwise</div>
    <p>Relational Expense Analytics</p>
  </div>

  <div class="card">
    <h2>Welcome back 👋</h2>
    <p class="subtitle">Sign in to your account</p>

    <?php if ($error): ?>
      <div class="alert"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Your password" required>
      </div>

      <button type="submit" class="btn">Sign In →</button>
    </form>

    <div class="signup-link">
      Don't have an account? <a href="signup.php">Sign up free</a>
    </div>
  </div>
</div>
</body>
</html>