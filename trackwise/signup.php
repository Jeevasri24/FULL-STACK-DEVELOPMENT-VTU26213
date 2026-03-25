<?php
require_once 'config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];
    $type     = $_POST['account_type'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already registered. <a href='login.php'>Login instead?</a>";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $esc_name  = mysqli_real_escape_string($conn, $name);
            $esc_email = mysqli_real_escape_string($conn, $email);
            $esc_type  = mysqli_real_escape_string($conn, $type);

            $sql = "INSERT INTO users (full_name, email, password, account_type) VALUES ('$esc_name','$esc_email','$hashed','$esc_type')";
            if (mysqli_query($conn, $sql)) {
                $user_id = mysqli_insert_id($conn);

                // Insert default categories
                $defaults = [
                    ['Food & Dining','🍔','#FF6B6B','personal'],
                    ['Transport','🚗','#4ECDC4','personal'],
                    ['Shopping','🛍️','#A78BFA','personal'],
                    ['Health','❤️','#F97316','personal'],
                    ['Entertainment','🎬','#3B82F6','personal'],
                    ['Office Supplies','📎','#6366F1','business'],
                    ['Travel','✈️','#10B981','business'],
                    ['Marketing','📊','#F59E0B','business'],
                    ['Utilities','💡','#64748B','business'],
                    ['Other','📌','#94A3B8','personal'],
                ];
                foreach ($defaults as $cat) {
                    $cn = mysqli_real_escape_string($conn, $cat[0]);
                    $ci = mysqli_real_escape_string($conn, $cat[1]);
                    $cc = mysqli_real_escape_string($conn, $cat[2]);
                    $ct = mysqli_real_escape_string($conn, $cat[3]);
                    mysqli_query($conn, "INSERT INTO categories (user_id,name,icon,color,type) VALUES ('$user_id','$cn','$ci','$cc','$ct')");
                }

                $success = "Account created! <a href='login.php'>Login now →</a>";
            } else {
                $error = "Something went wrong. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up — Trackwise</title>
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
    --success: #10B981;
    --error: #EF4444;
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

  .auth-wrapper {
    width: 100%;
    max-width: 460px;
  }

  .brand {
    text-align: center;
    margin-bottom: 2rem;
  }

  .brand-logo {
    font-family: 'Syne', sans-serif;
    font-size: 1.8rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.5px;
  }

  .brand p {
    color: var(--muted);
    font-size: 0.85rem;
    margin-top: 0.3rem;
  }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 2.2rem;
  }

  .card h2 {
    font-family: 'Syne', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
  }

  .card .subtitle {
    color: var(--muted);
    font-size: 0.85rem;
    margin-bottom: 1.8rem;
  }

  .alert {
    padding: 0.8rem 1rem;
    border-radius: 10px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
  }

  .alert.error { background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3); color: #FCA5A5; }
  .alert.success { background: rgba(16,185,129,0.12); border: 1px solid rgba(16,185,129,0.3); color: #6EE7B7; }
  .alert a { color: inherit; font-weight: 600; }

  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

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

  input, select {
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

  input:focus, select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(124,111,255,0.15);
  }

  select option { background: #1a1d30; }

  .type-toggle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.6rem;
  }

  .type-toggle input[type="radio"] { display: none; }

  .type-toggle label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.7rem;
    border: 1px solid var(--border);
    border-radius: 10px;
    cursor: pointer;
    font-size: 0.85rem;
    text-transform: none;
    letter-spacing: 0;
    transition: all 0.2s;
    color: var(--muted);
  }

  .type-toggle input[type="radio"]:checked + label {
    border-color: var(--accent);
    background: rgba(124,111,255,0.12);
    color: var(--text);
  }

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
    margin-top: 0.5rem;
  }

  .btn:hover { opacity: 0.9; transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }

  .login-link {
    text-align: center;
    margin-top: 1.4rem;
    font-size: 0.88rem;
    color: var(--muted);
  }

  .login-link a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 600;
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
    <h2>Create your account</h2>
    <p class="subtitle">Start tracking smarter today</p>

    <?php if ($error): ?>
      <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" placeholder="John Doe" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min 6 chars" required>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Repeat" required>
        </div>
      </div>

      <div class="form-group">
        <label>Account Type</label>
        <div class="type-toggle">
          <div>
            <input type="radio" name="account_type" id="personal" value="personal" checked>
            <label for="personal">👤 Personal</label>
          </div>
          <div>
            <input type="radio" name="account_type" id="business" value="business">
            <label for="business">🏢 Business</label>
          </div>
        </div>
      </div>

      <button type="submit" class="btn">Create Account →</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>
</body>
</html>