<?php
// admin/includes/admin_layout.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/admin/login.php'); exit;
}
$u = currentUser();
if (!$u || $u['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/admin/login.php'); exit;
}

$cur     = basename($_SERVER['PHP_SELF'], '.php');
$curDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($page_title) ? htmlspecialchars($page_title).' | Admin FaMaKo' : 'Admin FaMaKo' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
<link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet"/>
</head>
<body>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-logo-area">
    <h4>FaMaKo Admin</h4>
    <p>Faculté Maïngo Ködörö</p>
  </div>
  <nav style="flex:1;overflow-y:auto;padding:10px 0;">

    <div class="admin-nav-label">Principal</div>
    <a href="<?= BASE_URL ?>/admin/dashboard.php"
       class="admin-nav-item <?= $cur==='dashboard' ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt"></i> Tableau de bord
    </a>

    <div class="admin-nav-label">Pédagogie</div>
    <a href="<?= BASE_URL ?>/admin/cours/index.php"
       class="admin-nav-item <?= $curDir==='cours' ? 'active' : '' ?>">
      <i class="fas fa-play-circle"></i> Gestion des cours
    </a>
    <a href="<?= BASE_URL ?>/admin/cours/ajouter.php"
       class="admin-nav-item <?= $cur==='ajouter' && $curDir==='cours' ? 'active' : '' ?>">
      <i class="fas fa-plus-circle"></i> Ajouter un cours
    </a>
    <a href="<?= BASE_URL ?>/admin/td/index.php"
       class="admin-nav-item <?= $curDir==='td' ? 'active' : '' ?>"
       style="<?= $curDir==='td' ? '' : '' ?>">
      <i class="fas fa-file-alt"></i> Travaux Dirigés
      <?php
        // Mini compteur TD actifs
        try {
            $nb = getPDO()->query("SELECT COUNT(*) FROM td WHERE actif=1")->fetchColumn();
            if ($nb > 0) echo "<span style='margin-left:auto;background:rgba(201,168,76,.25);color:#c9a84c;font-size:.65rem;padding:.1rem .4rem;border-radius:4px;font-weight:700;'>$nb</span>";
        } catch(Exception $e) {}
      ?>
    </a>

    <div class="admin-nav-label">Inscriptions</div>
    <a href="<?= BASE_URL ?>/admin/inscriptions/index.php"
       class="admin-nav-item <?= $curDir==='inscriptions' ? 'active' : '' ?>">
      <i class="fas fa-user-graduate"></i> Toutes les inscriptions
    </a>

    <div class="admin-nav-label">Comptes</div>
    <a href="<?= BASE_URL ?>/admin/users/index.php"
       class="admin-nav-item <?= $curDir==='users' ? 'active' : '' ?>">
      <i class="fas fa-users-cog"></i> Utilisateurs
    </a>

    <div class="admin-nav-label">Site</div>
    <a href="<?= BASE_URL ?>" target="_blank" class="admin-nav-item">
      <i class="fas fa-external-link-alt"></i> Voir le site
    </a>
    <a href="<?= BASE_URL ?>/pages/td.php" target="_blank" class="admin-nav-item">
      <i class="fas fa-graduation-cap"></i> Espace étudiant TD
    </a>
    <a href="<?= BASE_URL ?>/admin/logout.php" class="admin-nav-item" style="color:rgba(255,100,100,.7)">
      <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>

  </nav>
  <div style="padding:14px 24px;border-top:1px solid rgba(255,255,255,.06);font-size:.75rem;color:rgba(255,255,255,.3);">
    Connecté : <?= htmlspecialchars($u['full_name'] ?? $u['username']) ?><br>
    <span style="color:var(--accent)"><?= ucfirst($u['role']) ?></span>
  </div>
</aside>

<!-- MAIN -->
<div class="admin-content">
  <!-- TOPBAR -->
  <div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
      <button id="sidebarToggle" class="d-lg-none btn btn-sm btn-outline-secondary">
        <i class="fas fa-bars"></i>
      </button>
      <h5 class="mb-0" style="font-family:'Playfair Display',serif;color:var(--navy);font-size:1.1rem;">
        <?= isset($page_title) ? htmlspecialchars($page_title) : '' ?>
      </h5>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span style="font-size:.8rem;color:var(--muted)">
        <i class="fas fa-user-circle me-1" style="color:var(--accent)"></i>
        <?= htmlspecialchars($u['full_name'] ?? $u['username']) ?>
      </span>
      <a href="<?= BASE_URL ?>/admin/logout.php" style="font-size:.8rem;color:var(--danger)">
        <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
      </a>
    </div>
  </div>
  <!-- contenu injecté ici -->