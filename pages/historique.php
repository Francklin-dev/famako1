<?php
$page_title = "Histoire de la Faculté";
require_once __DIR__ . '/../config/database.php';
$pdo = getPDO();
$events = $pdo->query("SELECT * FROM historique ORDER BY annee ASC")->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<main>
<!-- HERO SECTION -->
<section style="background:linear-gradient(135deg,var(--navy),var(--navy-lt));padding:70px 0;color:#fff;position:relative;overflow:hidden;">
  <div style="position:absolute;top:-60px;right:-60px;width:400px;height:400px;border-radius:50%;background:rgba(201,168,76,.07);"></div>
  <div class="container" style="position:relative;z-index:1;">
    <div class="section-label" style="color:var(--accent)"><span data-lang="fr">Notre parcours</span><span data-lang="en">Our journey</span></div>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,3rem);font-weight:900;color:#fff;margin-bottom:16px;">
      <span data-lang="fr">Histoire & Identité de la Faculté</span>
      <span data-lang="en">Faculty History & Identity</span>
    </h1>
    <p style="color:rgba(255,255,255,.75);max-width:560px;font-size:.98rem;line-height:1.7;">
      <span data-lang="fr">Découvrez le parcours remarquable de la Faculté Maïngo Ködörö, depuis sa fondation jusqu'à son rayonnement actuel en République Centrafricaine.</span>
      <span data-lang="en">Discover the remarkable journey of Maïngo Ködörö Faculty, from its foundation to its current influence in the Central African Republic.</span>
    </p>
  </div>
</section>

<!-- PRÉSENTATION -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <div class="section-label">Mission</div>
        <h2 class="section-title"><span data-lang="fr">Qui sommes-nous ?</span><span data-lang="en">Who are we?</span></h2>
        <div class="section-divider"></div>
        <p><span data-lang="fr">La Faculté Maïngo Ködörö est un centre d'excellence créé en partenariat avec l'Université de Bangui. D'inspiration Baha'ie, elle vise à former des cadres spécialisés en Sciences de l'Éducation capables de contribuer au développement durable de la République Centrafricaine.</span>
        <span data-lang="en">Maïngo Ködörö Faculty is a center of excellence created in partnership with the University of Bangui. Baha'i-inspired, it aims to train specialized executives in Educational Sciences capable of contributing to the sustainable development of the Central African Republic.</span></p>
        <div class="row g-3 mt-3">
          <div class="col-6"><div style="background:var(--white);border-radius:var(--radius);padding:16px;text-align:center;box-shadow:var(--shadow-sm);"><div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--accent);">13</div><div style="font-size:.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;"><span data-lang="fr">Disciplines</span><span data-lang="en">Disciplines</span></div></div></div>
          <div class="col-6"><div style="background:var(--white);border-radius:var(--radius);padding:16px;text-align:center;box-shadow:var(--shadow-sm);"><div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--accent);">2018</div><div style="font-size:.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;"><span data-lang="fr">Fondée</span><span data-lang="en">Founded</span></div></div></div>
        </div>
      </div>
      <div class="col-lg-6">
        <!-- VIDÉO DOYEN -->
        <div style="border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow-lg);">
          <div style="position:relative;padding-top:56.25%;">
            <iframe src="https://youtu.be/4kPMSTrp6s4"
              title="Présentation du Doyen"
              style="position:absolute;top:0;left:0;width:100%;height:100%;"
              frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen loading="lazy"></iframe>
          </div>
        </div>
        <p class="text-center mt-2" style="font-size:.78rem;color:var(--muted);"><i class="fab fa-youtube me-1 text-danger"></i><span data-lang="fr">Message du Doyen · Faculté Maïngo Ködörö</span><span data-lang="en">Dean's message · Maïngo Ködörö Faculty</span></p>
      </div>
    </div>
  </div>
</section>

<!-- TIMELINE -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label"><span data-lang="fr">Chronologie</span><span data-lang="en">Timeline</span></div>
      <h2 class="section-title"><span data-lang="fr">Les grandes étapes</span><span data-lang="en">Key milestones</span></h2>
      <div class="section-divider mx-auto"></div>
    </div>

    <?php if (empty($events)): ?>
    <p class="text-center text-muted"><span data-lang="fr">Aucun événement disponible.</span></p>
    <?php else: ?>
    <div class="timeline">
      <?php foreach ($events as $i => $e): ?>
      <div class="timeline-item">
        <div class="tl-content">
          <div class="tl-card">
            <div class="tl-year"><?= $e['annee'] ?></div>
            <div class="tl-title"><span data-lang="fr"><?= htmlspecialchars($e['titre_fr']) ?></span><?php if ($e['titre_en']): ?><span data-lang="en"><?= htmlspecialchars($e['titre_en']) ?></span><?php endif; ?></div>
            <?php if ($e['contenu_fr']): ?>
            <p class="tl-text"><span data-lang="fr"><?= nl2br(htmlspecialchars($e['contenu_fr'])) ?></span><?php if ($e['contenu_en']): ?><span data-lang="en"><?= nl2br(htmlspecialchars($e['contenu_en'])) ?></span><?php endif; ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="tl-dot"><div class="dot"><?= $e['annee'] ?></div></div>
        <div class="tl-empty"></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- VALEURS -->
<section class="py-5" style="background:var(--navy)">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label" style="color:var(--accent)"><span data-lang="fr">Nos valeurs</span><span data-lang="en">Our values</span></div>
      <h2 class="section-title" style="color:#fff;"><span data-lang="fr">Ce qui nous guide</span><span data-lang="en">What guides us</span></h2>
    </div>
    <div class="row g-4">
      <?php foreach([
        ['fa-star','Excellence','Excellence','Nous visons le plus haut niveau de qualité dans la formation.','We aim for the highest level of quality in training.'],
        ['fa-hands-helping','Service','Service','Former des acteurs engagés au service de leur communauté.','Train committed actors serving their community.'],
        ['fa-globe-africa','Impact','Impact','Contribuer au développement durable de la RCA et de l\'Afrique.','Contribute to the sustainable development of CAR and Africa.'],
        ['fa-heart','Humanisme','Humanism','L\'épanouissement humain au cœur de notre pédagogie.','Human development at the heart of our pedagogy.'],
      ] as [$ico,$fr,$en,$dfr,$den]): ?>
      <div class="col-6 col-md-3">
        <div class="text-center" style="padding:20px;">
          <div style="width:60px;height:60px;background:rgba(201,168,76,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;border:1px solid rgba(201,168,76,.3);">
            <i class="fas <?= $ico ?>" style="color:var(--accent);font-size:1.4rem;"></i>
          </div>
          <h5 style="color:#fff;font-weight:700;margin-bottom:8px;"><span data-lang="fr"><?= $fr ?></span><span data-lang="en"><?= $en ?></span></h5>
          <p style="color:rgba(255,255,255,.55);font-size:.83rem;"><span data-lang="fr"><?= $dfr ?></span><span data-lang="en"><?= $den ?></span></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
