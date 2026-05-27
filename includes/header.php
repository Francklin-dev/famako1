<?php
// includes/header.php
require_once __DIR__ . '/../config/database.php';
$page_actuelle = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr" id="htmlRoot">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= isset($page_title) ? sanitize($page_title) . ' | FaMaKo' : 'Faculté Maïngo Ködörö' ?></title>
  <meta name="description" content="Faculté Maïngo Ködörö — Sciences de l'Éducation, Doctorat, Bangui RCA"/>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
  <link href="<?= BASE_URL ?>/assets/css/main.css" rel="stylesheet"/>
  <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="d-flex gap-3 flex-wrap">
        <span><i class="fas fa-map-marker-alt fa-xs me-1"></i> Bangui, République Centrafricaine</span>
        <span class="d-none d-md-inline"><i class="fas fa-clock fa-xs me-1"></i>
          <span data-lang="fr">Lun–Ven : 8h–17h</span>
          <span data-lang="en">Mon–Fri: 8am–5pm</span>
        </span>
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex gap-1">
          <button class="lang-btn active" id="langFr">FR</button>
          <button class="lang-btn" id="langEn">EN</button>
        </div>
        <div class="d-flex gap-2 align-items-center">
  <a href="#"><i class="fab fa-facebook-f fa-xs"></i></a>
  <a href="https://www.youtube.com/@famako" target="_blank"><i class="fab fa-youtube fa-xs"></i></a>
  <a href="https://us06web.zoom.us/j/87948815783?pwd=p2fjRPConAr2gkbrJFFPzBb2BmaxAz.1"
     target="_blank"
     style="background:#2D8CFF;color:#fff;padding:2px 10px;border-radius:4px;font-size:.75rem;text-decoration:none;font-weight:600;">
    <i class="fas fa-video me-1"></i>Zoom
  </a>
</div>
      </div>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg main-navbar">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">
      <div class="brand-logo">
        <img src="<?= BASE_URL ?>/assets/img/logo_famako.png" alt="FaMaKo" onerror="this.style.display='none';this.parentElement.innerHTML='<span>FM</span>'">
      </div>
      <div class="brand-text">
        <div class="brand-title"><span data-lang="fr">Faculté Maïngo Ködörö</span><span data-lang="en">Maïngo Ködörö Faculty</span></div>
        <div class="brand-sub"><span data-lang="fr">Sciences de l'Éducation · Bangui</span><span data-lang="en">Educational Sciences · Bangui</span></div>
      </div>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navMenu">
      <ul class="navbar-nav align-items-lg-center gap-1 me-2">
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='index'?'active':'' ?>" href="<?= BASE_URL ?>/index.php"><span data-lang="fr">Accueil</span><span data-lang="en">Home</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='presentation'?'active':'' ?>" href="<?= BASE_URL ?>/pages/presentation.php"><span data-lang="fr">Présentation</span><span data-lang="en">About</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='historique'?'active':'' ?>" href="<?= BASE_URL ?>/pages/historique.php"><span data-lang="fr">Histoire</span><span data-lang="en">History</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='cours'?'active':'' ?>" href="<?= BASE_URL ?>/pages/cours.php"><span data-lang="fr">Cours</span><span data-lang="en">Courses</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='td'?'active':'' ?>" href="<?= BASE_URL ?>/pages/td.php"><span data-lang="fr">Travaux Dirigés</span><span data-lang="en">Practical Exercises</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='frais'?'active':'' ?>" href="<?= BASE_URL ?>/pages/frais.php"><span data-lang="fr">Frais</span><span data-lang="en">Fees</span></a></li>
        <li class="nav-item"><a class="nav-link <?= $page_actuelle==='contact'?'active':'' ?>" href="<?= BASE_URL ?>/pages/contact.php"><span data-lang="fr">Contact</span><span data-lang="en">Contact</span></a></li>
      </ul>
      <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-inscription">
        <i class="fas fa-pen me-1"></i>
        <span data-lang="fr">S'inscrire</span><span data-lang="en">Register</span>
      </a>
    </div>
  </div>
</nav>
