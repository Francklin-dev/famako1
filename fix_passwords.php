<?php
// fix_passwords.php — Regénère les hashes bcrypt depuis PHP
// Accès unique : http://localhost/famako/fix_passwords.php
// SUPPRIMER après utilisation !
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'])) { http_response_code(403); die('Accès refusé.'); }

require_once 'config/config.php';
$pdo = getPDO();

$users = [
    ['admin@famako.edu',      'admin123'],
    ['gestion@famako.edu',    'admin123'],
    ['biblio@famako.edu',     'admin123'],
];

$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$ok = 0; $fail = 0;
?><!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Fix Passwords — FaMaKo</title>
<style>body{font-family:Arial,sans-serif;max-width:580px;margin:40px auto;padding:20px;background:#f0ebe0}.card{background:#fff;border-radius:14px;padding:30px;box-shadow:0 4px 20px rgba(0,0,0,.1)}.ok{color:#1a7f5a}.err{color:#a8292f}.warn{background:#fff3cd;border:1px solid #ffc107;padding:14px;border-radius:8px;margin-top:18px}a.btn{display:inline-block;margin-top:14px;padding:10px 22px;background:#0a2342;color:#c9a84c;border-radius:8px;text-decoration:none;font-weight:bold}</style></head>
<body><div class="card">
<h2>🔧 Correction des mots de passe — FaMaKo</h2><ul>
<?php foreach ($users as [$email, $pwd]):
    $hash = password_hash($pwd, PASSWORD_DEFAULT);
    $r = $stmt->execute([$hash, $email]);
    if ($r && $stmt->rowCount() > 0) { echo "<li class='ok'>✅ $email — OK</li>"; $ok++; }
    else { echo "<li class='err'>⚠️ $email — non trouvé</li>"; $fail++; }
endforeach; ?>
</ul>
<p><strong><?= $ok ?> succès, <?= $fail ?> non trouvés.</strong></p>
<div class="warn">⚠️ <strong>Supprimez ce fichier maintenant !</strong><br><code>sudo rm /var/www/html/famako/fix_passwords.php</code></div>
<a class="btn" href="admin/login.php">→ Aller à l'administration</a>
</div></body></html>
