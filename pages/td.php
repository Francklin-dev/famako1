<?php
// pages/td.php
$page_title = "Travaux Dirigés";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="py-5" style="background:var(--light-bg)">
<div class="container">

  <!-- ACCÈS RESTREINT -->
  <div class="text-center" style="max-width:640px;margin:0 auto;padding:60px 20px;">
    <div style="width:80px;height:80px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
      <i class="fas fa-lock" style="color:var(--accent);font-size:2rem;"></i>
    </div>
    <h1 style="font-family:'Playfair Display',serif;color:var(--navy);font-size:clamp(1.6rem,3vw,2.2rem);font-weight:900;margin-bottom:16px;">
      <span data-lang="fr">Travaux Dirigés</span>
      <span data-lang="en">Practical Exercises</span>
    </h1>
    <div class="famako-card p-4 mb-4" style="border-top:4px solid var(--accent);">
      <p style="font-size:1.05rem;color:var(--navy);font-weight:600;margin-bottom:12px;">
        <i class="fas fa-book-open me-2" style="color:var(--accent)"></i>
        <span data-lang="fr">Accès réservé aux étudiants inscrits</span>
        <span data-lang="en">Access restricted to enrolled students</span>
      </p>
      <p style="color:var(--muted);font-size:.92rem;line-height:1.7;margin-bottom:0;">
        <span data-lang="fr">Les travaux dirigés de la Faculté Maïngo Ködörö sont accessibles uniquement aux étudiant·e·s régulièrement inscrit·e·s. Pour accéder à ces contenus, vous devez être inscrit·e et avoir reçu vos codes d'accès de l'administration.</span>
        <span data-lang="en">Practical exercises of Maïngo Ködörö Faculty are accessible only to regularly enrolled students. To access this content, you must be enrolled and have received your access codes from the administration.</span>
      </p>
    </div>
    <div class="alert-famako alert-info mb-4" style="text-align:left;">
      <i class="fas fa-info-circle fa-lg"></i>
      <div>
        <strong><span data-lang="fr">Comment accéder aux TD ?</span><span data-lang="en">How to access exercises?</span></strong><br>
        <span data-lang="fr">Une fois votre inscription validée, l'administration vous communiquera vos codes d'accès personnels par email. Ces codes vous permettront de télécharger vos travaux dirigés.</span>
        <span data-lang="en">Once your registration is validated, the administration will send you your personal access codes by email. These codes will allow you to download your practical exercises.</span>
      </div>
    </div>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-accent">
        <i class="fas fa-pen me-2"></i>
        <span data-lang="fr">S'inscrire maintenant</span>
        <span data-lang="en">Register now</span>
      </a>
      <a href="<?= BASE_URL ?>/pages/contact.php" class="btn-outline-navy">
        <i class="fas fa-envelope me-2"></i>
        <span data-lang="fr">Contacter l'administration</span>
        <span data-lang="en">Contact administration</span>
      </a>
    </div>
  </div>

</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
