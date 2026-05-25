<?php
// pages/frais.php
$page_title = "Frais de formation";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="py-5" style="background:var(--light-bg)">
<div class="container">
  <h1 class="page-title"><span data-lang="fr">Frais de Formation — FaMaKo</span><span data-lang="en">Training Fees — FaMaKo</span></h1>

  <div class="alert-famako alert-info mb-4">
    <i class="fas fa-exchange-alt fa-lg"></i>
    <div><strong><span data-lang="fr">Méthode de paiement :</span><span data-lang="en">Payment method:</span></strong> <span data-lang="fr">Virement bancaire uniquement. Contactez le DAF pour les coordonnées bancaires.</span><span data-lang="en">Bank transfer only. Contact the DAF for banking details.</span></div>
  </div>

  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="famako-card h-100" style="border-top:5px solid var(--accent);">
        <div class="card-body-custom">
          <h4 style="color:var(--navy);margin-bottom:6px;"><i class="fas fa-clipboard-check me-2" style="color:var(--accent)"></i><span data-lang="fr">Frais d'inscription</span><span data-lang="en">Registration fees</span></h4>
          <div style="font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:900;color:var(--navy);margin:14px 0 4px;display:flex;align-items:center;gap:10px;"><i class="fas fa-coins" style="color:var(--accent);font-size:1.8rem;"></i> 45 000 FCFA</div>
          <div style="color:var(--muted);font-style:italic;margin-bottom:12px;font-size:.88rem;">(69 euros) — 1€ = 655,96 FCFA</div>
          <div class="d-flex align-items-center gap-2 mb-2"><i class="fas fa-check-circle" style="color:var(--success)"></i><span style="font-size:.88rem;"><span data-lang="fr">Payable en une seule fois</span><span data-lang="en">Payable once</span></span></div>
          <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle" style="color:var(--success)"></i><span style="font-size:.88rem;"><span data-lang="fr">DSPR et Doctorat</span><span data-lang="en">DSPR and PhD</span></span></div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="famako-card h-100" style="border-top:5px solid var(--navy);">
        <div class="card-body-custom">
          <h4 style="color:var(--navy);margin-bottom:6px;"><i class="fas fa-graduation-cap me-2" style="color:var(--accent)"></i><span data-lang="fr">Frais de scolarité</span><span data-lang="en">Tuition fees</span></h4>
          <div style="font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:900;color:var(--navy);margin:14px 0 4px;display:flex;align-items:center;gap:10px;"><i class="fas fa-university" style="color:var(--accent);font-size:1.8rem;"></i> 540 000 FCFA</div>
          <div style="color:var(--muted);font-style:italic;margin-bottom:12px;font-size:.88rem;">(823 euros) — 1€ = 655,96 FCFA</div>
          <div class="d-flex align-items-center gap-2 mb-2"><i class="fas fa-check-circle" style="color:var(--success)"></i><span style="font-size:.88rem;"><span data-lang="fr">Payable en 1 ou 2 tranches</span><span data-lang="en">Payable in 1 or 2 installments</span></span></div>
          <div class="d-flex align-items-center gap-2"><i class="fas fa-check-circle" style="color:var(--success)"></i><span style="font-size:.88rem;"><span data-lang="fr">DSPR et Doctorat</span><span data-lang="en">DSPR and PhD</span></span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="famako-card p-4 mb-4">
    <h4 style="color:var(--navy);margin-bottom:20px;"><i class="fas fa-list-ol me-2" style="color:var(--accent)"></i><span data-lang="fr">Procédure de paiement</span><span data-lang="en">Payment procedure</span></h4>
    <div class="row g-3">
      <?php foreach([
        ['1','Contact','Contact','Contactez le DAF par email','Contact the DAF by email'],
        ['2','Coordonnées','Bank details','Obtenez les informations bancaires','Get the banking information'],
        ['3','Virement','Transfer','Effectuez le virement avec votre nom en référence','Make the transfer with your name as reference'],
        ['4','Confirmation','Confirmation','Envoyez le reçu au DAF pour validation','Send the receipt to DAF for validation'],
      ] as [$n,$fr,$en,$dfr,$den]): ?>
      <div class="col-6 col-md-3">
        <div style="background:var(--light-bg);border-radius:var(--radius);padding:16px;display:flex;align-items:flex-start;gap:12px;">
          <div style="width:36px;height:36px;flex-shrink:0;background:var(--navy);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;"><?= $n ?></div>
          <div><h6 style="font-weight:700;color:var(--navy);font-size:.88rem;margin-bottom:4px;"><span data-lang="fr"><?= $fr ?></span><span data-lang="en"><?= $en ?></span></h6><p style="font-size:.78rem;margin:0;"><span data-lang="fr"><?= $dfr ?></span><span data-lang="en"><?= $den ?></span></p></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="alert-famako alert-warning mt-4">
      <i class="fas fa-exclamation-triangle"></i>
      <span data-lang="fr">Conservez votre reçu de paiement. Il vous sera demandé pour finaliser votre inscription.</span>
      <span data-lang="en">Keep your payment receipt. It will be required to finalize your registration.</span>
    </div>
  </div>

  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <a href="<?= BASE_URL ?>/pages/contact.php" class="btn-accent"><i class="fas fa-envelope me-2"></i><span data-lang="fr">Contacter le DAF</span><span data-lang="en">Contact DAF</span></a>
    <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-primary-custom"><i class="fas fa-pen me-2"></i><span data-lang="fr">S'inscrire</span><span data-lang="en">Register</span></a>
  </div>
</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
