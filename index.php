<?php
$page_title = "Accueil";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';
$pdo = getPDO();
// Nouvelle ligne (sans ordre, tri par nom)
$disciplines = $pdo->query("SELECT id, nom_fr FROM disciplines WHERE actif=1 ORDER BY nom_fr")->fetchAll();
$stats = [
  'cours'       => $pdo->query("SELECT COUNT(*) FROM cours WHERE actif=1")->fetchColumn(),
  'inscriptions'=> $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn(),
  'biblio'      => $pdo->query("SELECT COUNT(*) FROM bibliotheque WHERE statut='publie'")->fetchColumn(),
];
?>
<!-- ANNOUNCE TICKER -->
<div class="announce-band">
  <div class="ticker-wrap container-fluid px-0">
    <div class="ticker">
      <span data-lang="fr"><i class="fas fa-bullhorn me-2"></i>Inscriptions Doctorat 2026 ouvertes — DSPR requis</span>
      <span data-lang="en"><i class="fas fa-bullhorn me-2"></i>PhD 2026 Applications Open — DSPR required</span>
      <span data-lang="fr"><i class="fas fa-graduation-cap me-2"></i>13 disciplines en Sciences de l'Éducation</span>
      <span data-lang="en"><i class="fas fa-graduation-cap me-2"></i>13 Educational Sciences disciplines</span>
      <span data-lang="fr"><i class="fas fa-university me-2"></i>Paiement par virement bancaire — Contactez le DAF</span>
      <span data-lang="en"><i class="fas fa-university me-2"></i>Payment by bank transfer — Contact DAF</span>
      <span data-lang="fr"><i class="fas fa-award me-2"></i>Faculté d'inspiration Baha'ie · Bangui, RCA</span>
      <span data-lang="en"><i class="fas fa-award me-2"></i>Baha'i-inspired Faculty · Bangui, CAR</span>
      <!-- duplicate for seamless loop -->
      <span data-lang="fr"><i class="fas fa-bullhorn me-2"></i>Inscriptions Doctorat 2026 ouvertes — DSPR requis</span>
      <span data-lang="en"><i class="fas fa-bullhorn me-2"></i>PhD 2026 Applications Open — DSPR required</span>
      <span data-lang="fr"><i class="fas fa-graduation-cap me-2"></i>13 disciplines en Sciences de l'Éducation</span>
      <span data-lang="en"><i class="fas fa-graduation-cap me-2"></i>13 Educational Sciences disciplines</span>
      <span data-lang="fr"><i class="fas fa-university me-2"></i>Paiement par virement bancaire — Contactez le DAF</span>
      <span data-lang="en"><i class="fas fa-university me-2"></i>Payment by bank transfer — Contact DAF</span>
      <span data-lang="fr"><i class="fas fa-award me-2"></i>Faculté d'inspiration Baha'ie · Bangui, RCA</span>
      <span data-lang="en"><i class="fas fa-award me-2"></i>Baha'i-inspired Faculty · Bangui, CAR</span>
    </div>
  </div>
</div>

<main>
<!-- HERO -->
<section class="hero">
  <div class="container py-4">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge">
          <span data-lang="fr"><i class="fas fa-star me-1"></i>Inscriptions Doctorat 2026 Ouvertes</span>
          <span data-lang="en"><i class="fas fa-star me-1"></i>PhD Applications 2026 Open</span>
        </div>
        <h1>
          <span data-lang="fr">Bienvenue à la Faculté <span>Maïngo Ködörö</span></span>
          <span data-lang="en">Welcome to <span>Maïngo Ködörö</span> Faculty</span>
        </h1>
        <p class="hero-subtitle mt-3">
          <span data-lang="fr">Plateforme officielle de formation et de recherche pédagogique en RCA. Formons ensemble les leaders éducatifs de demain.</span>
          <span data-lang="en">Official platform for educational training and research in CAR. Let's train tomorrow's educational leaders together.</span>
        </p>
        <div class="d-flex gap-3 flex-wrap mt-4">
          <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-accent">
            <span data-lang="fr">S'inscrire</span><span data-lang="en">Register</span>
            <i class="fas fa-arrow-right fa-xs"></i>
          </a>
          <a href="<?= BASE_URL ?>/pages/presentation.php" class="btn-outline-navy" style="color:#fff;border-color:rgba(255,255,255,.4)">
            <span data-lang="fr">En savoir plus</span><span data-lang="en">Learn more</span>
          </a>
          <a href="<?= BASE_URL ?>/pages/cours.php" class="btn-outline-navy" style="color:#fff;border-color:rgba(255,255,255,.4)">
            <span data-lang="fr">Nos cours</span><span data-lang="en">Our courses</span>
          </a>
        </div>
        <div class="d-flex gap-4 flex-wrap mt-4">
          <div class="hero-stat"><div class="num">13</div><div class="lbl"><span data-lang="fr">Disciplines</span><span data-lang="en">Disciplines</span></div></div>
          <div class="hero-stat"><div class="num">DSPR</div><div class="lbl"><span data-lang="fr">Requis</span><span data-lang="en">Required</span></div></div>
          <div class="hero-stat"><div class="num">4–5</div><div class="lbl"><span data-lang="fr">Ans de formation</span><span data-lang="en">Years program</span></div></div>
        </div>
      </div>
      <div class="col-lg-5 offset-lg-1">
        <div class="hero-photo-grid">
          <figure class="hero-photo"><img src="<?= BASE_URL ?>/assets/img/Students.jpg" alt="Étudiants" onerror="this.src='https://placehold.co/280x210/163a6b/ffffff?text=Étudiants'"><figcaption data-lang="fr">Étudiants</figcaption></figure>
          <figure class="hero-photo"><img src="<?= BASE_URL ?>/assets/img/Enseignants_pour_Ecole_Communautaire.jpg" alt="École" onerror="this.src='https://placehold.co/280x210/0a2342/ffffff?text=École'"><figcaption data-lang="fr">École</figcaption></figure>
          <figure class="hero-photo"><img src="<?= BASE_URL ?>/assets/img/doyen_picture.jpg" alt="Doyen" onerror="this.src='https://placehold.co/280x210/1a4a80/ffffff?text=Doyen'"><figcaption data-lang="fr">Doyen</figcaption></figure>
          <figure class="hero-photo"><img src="<?= BASE_URL ?>/assets/img/Enseignants.jpg" alt="Enseignants" onerror="this.src='https://placehold.co/280x210/0a2342/ffffff?text=Enseignants'"><figcaption data-lang="fr">Enseignants</figcaption></figure>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PARCOURS -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="text-center mb-4">
      <div class="section-label"><span data-lang="fr">Parcours</span><span data-lang="en">Pathway</span></div>
      <h2 class="section-title"><span data-lang="fr">Votre chemin vers le Doctorat</span><span data-lang="en">Your path to PhD</span></h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-3">
      <?php foreach([
        ['1','S\'inscrire au DSPR','Register for DSPR','Inscription au programme de préparation à la recherche.','Research preparation program registration.','inscription'],
        ['2','Valider le DSPR','Validate DSPR','Cours, examens et validation de l\'année DSPR.','Courses, exams and DSPR year validation.','cours'],
        ['3','Accéder au Doctorat','Access PhD','Après réussite du DSPR, accès au cycle doctoral.','After passing DSPR, access to the doctoral cycle.','presentation'],
        ['4','Choisir sa discipline','Choose discipline','13 spécialités en Sciences de l\'Éducation disponibles.','13 specialties in Educational Sciences available.','cours'],
      ] as [$n,$fr,$en,$dfr,$den,$target]): ?>
      <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>/pages/<?= $target ?>.php" class="text-decoration-none">
          <div class="famako-card p-4 h-100 text-center">
            <div style="width:44px;height:44px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--navy);font-size:1.1rem;margin:0 auto 14px;"><?= $n ?></div>
            <h6 style="font-weight:700;color:var(--navy);margin-bottom:8px;"><span data-lang="fr"><?= $fr ?></span><span data-lang="en"><?= $en ?></span></h6>
            <p style="font-size:.82rem;margin:0;"><span data-lang="fr"><?= $dfr ?></span><span data-lang="en"><?= $den ?></span></p>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- DISCIPLINES -->
<section class="py-5">
  <div class="container">
    <div class="row align-items-center mb-4">
      <div class="col">
        <div class="section-label">Disciplines</div>
        <h2 class="section-title"><span data-lang="fr">Nos 13 spécialités</span><span data-lang="en">Our 13 specialties</span></h2>
        <div class="section-divider"></div>
      </div>
      <div class="col-auto"><a href="<?= BASE_URL ?>/pages/cours.php" class="btn-accent"><span data-lang="fr">Voir les cours</span><span data-lang="en">See courses</span></a></div>
    </div>
    <div class="disciplines-grid">
      <?php foreach ($disciplines as $d): ?>
      <div class="disc-card">
        <?= htmlspecialchars($variable ?? '') ?>
        <span><?= htmlspecialchars($d['nom_fr']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- VIDEO DOYEN -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <div class="section-label"><span data-lang="fr">Mot du Doyen</span><span data-lang="en">Dean's Message</span></div>
        <h2 class="section-title"><span data-lang="fr">Présentation de la Faculté</span><span data-lang="en">Faculty Presentation</span></h2>
        <div class="section-divider"></div>
        <p><span data-lang="fr">Le Doyen de la Faculté Maïngo Ködörö vous présente la vision, les valeurs et les ambitions de notre institution d'inspiration Baha'ie au service de l'éducation en République Centrafricaine.</span><span data-lang="en">The Dean of Maïngo Ködörö Faculty presents the vision, values and ambitions of our Baha'i-inspired institution at the service of education in the Central African Republic.</span></p>
        <a href="<?= BASE_URL ?>/pages/presentation.php" class="btn-primary-custom mt-3">
          <span data-lang="fr">En savoir plus</span><span data-lang="en">Learn more</span>
          <i class="fas fa-arrow-right fa-xs"></i>
        </a>
      </div>
      <div class="col-lg-7">
        <div style="position:relative;padding-top:56.25%;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-lg);">
          <iframe
    src="https://www.youtube.com/embed/4kPMSTrp6s4"
    title="Présentation du Doyen — Faculté Maïngo Ködörö"
    style="position:absolute;top:0;left:0;width:100%;height:100%;"
    frameborder="0"
    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
    allowfullscreen
    loading="lazy">
</iframe>
        <p class="text-center mt-2" style="font-size:.78rem;color:var(--muted);"><i class="fab fa-youtube me-1 text-danger"></i><span data-lang="fr">Présentation officielle du Doyen · Faculté Maïngo Ködörö</span><span data-lang="en">Official Dean Presentation · Maïngo Ködörö Faculty</span></p>
      </div>
    </div>
  </div>
</section>

<!-- STATS -->
<section class="stats-bar">
  <div class="container">
    <div class="row g-4 text-center">
      <div class="col-6 col-md-3"><div class="stat-item"><div class="stat-num" data-count="13">0</div><div class="stat-lbl"><span data-lang="fr">Disciplines</span><span data-lang="en">Disciplines</span></div></div></div>
      <div class="col-6 col-md-3"><div class="stat-item"><div class="stat-num" data-count="<?= (int)$stats['cours'] ?>">0</div><div class="stat-lbl"><span data-lang="fr">Cours disponibles</span><span data-lang="en">Available courses</span></div></div></div>
      <div class="col-6 col-md-3"><div class="stat-item"><div class="stat-num" data-count="<?= (int)$stats['inscriptions'] ?>">0</div><div class="stat-lbl"><span data-lang="fr">Inscriptions</span><span data-lang="en">Registrations</span></div></div></div>
      <div class="col-6 col-md-3"><div class="stat-item"><div class="stat-num" data-count="<?= (int)$stats['biblio'] ?>">0</div><div class="stat-lbl"><span data-lang="fr">Ressources bibliothèque</span><span data-lang="en">Library resources</span></div></div></div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h2><span data-lang="fr">Prêt à rejoindre la Faculté Maïngo Ködörö ?</span><span data-lang="en">Ready to join Maïngo Ködörö Faculty?</span></h2>
        <p class="mt-2"><span data-lang="fr">Les inscriptions 2026 sont ouvertes. Commencez par le DSPR.</span><span data-lang="en">2026 registrations are open. Start with the DSPR.</span></p>
      </div>
      <div class="col-lg-5 d-flex gap-3 flex-wrap justify-content-lg-end">
        <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-primary-custom"><span data-lang="fr">S'inscrire maintenant</span><span data-lang="en">Register now</span> <i class="fas fa-arrow-right ms-1"></i></a>
        <a href="<?= BASE_URL ?>/pages/frais.php" class="btn-outline-navy"><span data-lang="fr">Voir les frais</span><span data-lang="en">See fees</span></a>
      </div>
    </div>
  </div>
</section>
</main>

<!-- CHAT WIDGET -->
<div class="chat-widget">
  <button class="chat-button" id="chatButton"><i class="fas fa-comments"></i></button>
  <div class="chat-box" id="chatBox">
    <div class="chat-header">
      <span><i class="fas fa-robot me-2"></i>Assistant FaMaKo</span>
      <button id="closeChat"><i class="fas fa-times"></i></button>
    </div>
    <div class="chat-messages" id="chatMessages"></div>
    <div class="chat-input-row">
      <input type="text" id="chatInput" placeholder="Votre message…">
      <button id="sendChat"><i class="fas fa-paper-plane"></i></button>
    </div>
  </div>
</div>
<button class="scroll-top-btn" id="scrollTopBtn"><i class="fas fa-chevron-up"></i></button>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
