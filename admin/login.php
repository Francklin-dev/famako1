<?php
// admin/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';  // Correction : ajout du point avant le slash

// Rediriger si déjà connecté
if (isLoggedIn()) { 
    header('Location: ' . BASE_URL . '/admin/dashboard.php'); 
    exit; 
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    $pdo   = getPDO();
    $stmt  = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user  = $stmt->fetch();
    
    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']    = $user;
        $_SESSION['user_role'] = $user['role'];
        
        $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
        
        header('Location: ' . BASE_URL . '/admin/dashboard.php'); 
        exit;
    }
    $error = 'Email ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Connexion Admin — FaMaKo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'DM Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#0a2342 0%,#163a6b 100%);padding:20px}
.login-card{background:#fff;border-radius:20px;padding:44px 40px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.3);border:1px solid rgba(201,168,76,.2)}
.login-logo{width:70px;height:70px;background:linear-gradient(135deg,#0a2342,#163a6b);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;border:2px solid #c9a84c;box-shadow:0 0 0 5px rgba(201,168,76,.12)}
h1{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:900;color:#0a2342;text-align:center;margin-bottom:4px}
.sub{text-align:center;color:#8896a9;font-size:.83rem;margin-bottom:28px}
label{display:block;margin-bottom:6px;font-size:.84rem;font-weight:600;color:#0a2342}
input{width:100%;padding:11px 14px;border:1.5px solid rgba(10,35,66,.12);border-radius:10px;font-size:.9rem;font-family:'DM Sans',sans-serif;outline:none;transition:.25s;background:#faf7f0}
input:focus{border-color:#c9a84c;box-shadow:0 0 0 3px rgba(201,168,76,.15);background:#fff}
.mb{margin-bottom:18px}
.btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#0f2240,#0a1829);color:#d4b46a;border:none;border-radius:10px;font-weight:700;font-size:.95rem;cursor:pointer;transition:.25s;font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:8px}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(10,24,41,.35)}
.error{background:rgba(168,41,47,.08);color:#a8292f;border:1px solid rgba(168,41,47,.2);padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.86rem;display:flex;gap:10px;align-items:center}
.back{text-align:center;margin-top:18px;font-size:.82rem;color:#8896a9}
.back a{color:#c9a84c;font-weight:600}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-logo"><i class="fas fa-lock" style="color:#c9a84c;font-size:1.6rem"></i></div>
  <h1>Espace Administration</h1>
  <p class="sub">Faculté Maïngo Ködörö</p>
  <?php if ($error): ?>
  <div class="error"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb"><label for="email"><i class="fas fa-envelope me-1"></i> Email</label><input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="admin@famako.edu"></div>
    <div class="mb"><label for="pass"><i class="fas fa-lock me-1"></i> Mot de passe</label><input type="password" id="pass" name="password" required placeholder="••••••••"></div>
    <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
  </form>
  <p class="back"><a href="<?= BASE_URL ?>"><i class="fas fa-arrow-left me-1"></i>Retour au site</a></p>
</div>
</body></html>
