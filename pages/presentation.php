<?php
// pages/presentation.php
$page_title = "Présentation";
require_once __DIR__ . '/../config/database.php';
$pdo = getPDO();
$disciplines = $pdo->query("SELECT * FROM disciplines WHERE actif=1 ORDER BY ordre")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main>
<section style="background:linear-gradient(135deg,var(--navy),var(--navy-lt));padding:70px 0;color:#fff;">
  <div class="container">
    <div class="section-label" style="color:var(--accent)"><span data-lang="fr">À propos</span><span data-lang="en">About</span></div>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.8rem);font-weight:900;color:#fff;margin-bottom:14px;">
      <span data-lang="fr">Présentation de la Faculté Maïngo Ködörö</span>
      <span data-lang="en">Presentation of Maïngo Ködörö Faculty</span>
    </h1>
    <p style="color:rgba(255,255,255,.75);max-width:560px;"><span data-lang="fr">Plateforme officielle de formation doctorale en Sciences de l'Éducation en République Centrafricaine.</span><span data-lang="en">Official platform for doctoral training in Educational Sciences in the Central African Republic.</span></p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-5 align-items-start">
      <div class="col-lg-8">
        <div class="famako-card p-4 mb-4">
          <h3 style="color:var(--navy);margin-bottom:14px;"><i class="fas fa-university me-2" style="color:var(--accent)"></i><span data-lang="fr">Centre d'excellence</span><span data-lang="en">Center of excellence</span></h3>
          <p><span data-lang="fr">La Faculté Maïngo Ködörö est un centre d'excellence en Sciences de l'éducation créé à Bangui. D'inspiration Baha'ie, la Faculté vise à former des cadres spécialisés en Sciences de l'Éducation capables de contribuer aux activités éducatives et de développement humain durable au bénéfice des territoires de la République Centrafricaine et d'autres pays.</span>
          <span data-lang="en">Maïngo Ködörö Faculty is a center of excellence in Educational Sciences created in Bangui. Baha'i-inspired, the Faculty aims to train specialized executives in Educational Sciences capable of contributing to educational activities and sustainable human development for the benefit of the territories of the Central African Republic and other countries.</span></p>
          <div class="alert-famako alert-warning mt-3">
            <i class="fas fa-exclamation-triangle"></i>
            <div><strong><span data-lang="fr">Important :</span><span data-lang="en">Important:</span></strong> <span data-lang="fr">Tous les étudiants doivent suivre et valider le DSPR avant d'accéder au Doctorat.</span><span data-lang="en">All students must complete and validate the DSPR before accessing the PhD.</span></div>
          </div>
        </div>


      </div>
      <div class="col-lg-4">
        <div class="famako-card p-0">
          <div class="p-4" style="background:var(--navy);border-radius:var(--radius) var(--radius) 0 0;">
            <h4 style="color:var(--accent);font-family:'Playfair Display',serif;margin-bottom:14px;"><i class="fas fa-info-circle me-2"></i><span data-lang="fr">Informations clés</span><span data-lang="en">Key information</span></h4>
            <ul class="list-unstyled mb-0">
              <?php foreach([
                ['fa-award','Diplôme','DSPR + Doctorat'],
                ['fa-calendar-check','Durée','4–5 ans'],
                ['fa-book-open','Disciplines','13 spécialités'],
                ['fa-money-bill','Paiement','Virement bancaire'],
                ['fa-map-marker-alt','Lieu','Bangui, RCA'],
              ] as [$ic,$fr,$val]): ?>
              <li class="mb-3 d-flex align-items-start gap-3">
                <i class="fas <?= $ic ?>" style="color:var(--accent);margin-top:2px;width:16px;text-align:center;"></i>
                <div><div style="color:rgba(255,255,255,.5);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;"><?= $fr ?></div><div style="color:#fff;font-weight:600;font-size:.88rem;"><?= $val ?></div></div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
          <div class="p-4">
            <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-accent w-100 justify-content-center mb-2">
              <i class="fas fa-pen me-2"></i><span data-lang="fr">S'inscrire maintenant</span><span data-lang="en">Register now</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/inscriptions.php" class="btn-outline-navy w-100 justify-content-center">
              <i class="fas fa-coins me-2"></i><span data-lang="fr">Voir les frais</span><span data-lang="en">See fees</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
