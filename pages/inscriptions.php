<?php
// pages/inscriptions.php
$page_title = "Inscriptions & Paiement";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
?>
<main>

<!-- HERO -->
<section style="background:linear-gradient(135deg,var(--navy),var(--navy-lt));padding:70px 0;color:#fff;">
  <div class="container">
    <div class="section-label" style="color:var(--accent)">
      <span data-lang="fr">Rejoindre la Faculté</span>
      <span data-lang="en">Join the Faculty</span>
    </div>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.8rem);font-weight:900;color:#fff;margin-bottom:14px;">
      <span data-lang="fr">Inscriptions &amp; Paiement</span>
      <span data-lang="en">Enrollment &amp; Payment</span>
    </h1>
    <p style="color:rgba(255,255,255,.75);max-width:580px;font-size:.98rem;line-height:1.7;">
      <span data-lang="fr">Tout ce que vous devez savoir avant de vous inscrire : ce que vous payez, quand vous le payez, et ce que vous obtenez en retour.</span>
      <span data-lang="en">Everything you need to know before enrolling: what you pay, when you pay it, and what you get in return.</span>
    </p>
  </div>
</section>

<!-- SECTION 1 : PARCOURS D'INSCRIPTION -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label"><span data-lang="fr">Étape par étape</span><span data-lang="en">Step by step</span></div>
      <h2 class="section-title"><span data-lang="fr">Comment s'inscrire ?</span><span data-lang="en">How to enroll?</span></h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-4">
      <?php foreach([
        ['1','fa-file-alt','Remplir le dossier','Fill out the form',
         'Complétez le formulaire d\'inscription en ligne avec vos informations personnelles, votre diplôme le plus récent et votre lettre de motivation.',
         'Complete the online registration form with your personal information, your most recent diploma and your motivation letter.'],
        ['2','fa-check-circle','Validation du dossier','File validation',
         'L\'administration examine votre dossier et vous confirme votre admission par email dans un délai de 5 à 10 jours ouvrés.',
         'The administration reviews your file and confirms your admission by email within 5 to 10 business days.'],
        ['3','fa-university','Effectuer le paiement','Make the payment',
         'Une fois admis·e, vous recevez les coordonnées bancaires pour effectuer votre virement. Vous pouvez payer en 1 ou 2 tranches.',
         'Once admitted, you receive the bank details to make your transfer. You can pay in 1 or 2 installments.'],
        ['4','fa-graduation-cap','Accès aux cours','Access to courses',
         'Dès réception de votre justificatif de paiement, vous recevez vos codes d\'accès personnels et pouvez commencer les cours.',
         'Upon receipt of your proof of payment, you receive your personal access codes and can start the courses.'],
      ] as [$n,$ico,$fr,$en,$dfr,$den]): ?>
      <div class="col-12 col-md-6 col-lg-3">
        <div class="famako-card p-4 h-100 text-center">
          <div style="width:56px;height:56px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:900;color:var(--navy);"><?= $n ?></div>
          <div style="width:40px;height:40px;background:var(--light-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <i class="fas <?= $ico ?>" style="color:var(--navy);font-size:1.1rem;"></i>
          </div>
          <h6 style="font-weight:700;color:var(--navy);margin-bottom:10px;"><span data-lang="fr"><?= $fr ?></span><span data-lang="en"><?= $en ?></span></h6>
          <p style="font-size:.83rem;color:var(--muted);margin:0;line-height:1.6;"><span data-lang="fr"><?= $dfr ?></span><span data-lang="en"><?= $den ?></span></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SECTION 2 : CE QUE VOUS PAYEZ -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label"><span data-lang="fr">Transparence totale</span><span data-lang="en">Full transparency</span></div>
      <h2 class="section-title"><span data-lang="fr">Ce que vous payez &amp; ce que vous obtenez</span><span data-lang="en">What you pay &amp; what you get</span></h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-4 mb-5">
      <!-- Frais d'inscription -->
      <div class="col-md-6">
        <div class="famako-card h-100" style="border-top:5px solid var(--accent);">
          <div class="p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <i class="fas fa-clipboard-check fa-2x" style="color:var(--accent)"></i>
              <div>
                <h4 style="color:var(--navy);margin:0;font-weight:700;"><span data-lang="fr">Frais d'inscription</span><span data-lang="en">Registration fees</span></h4>
                <div style="font-size:.8rem;color:var(--muted);"><span data-lang="fr">À payer une seule fois</span><span data-lang="en">Paid once</span></div>
              </div>
            </div>
            <div style="font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:900;color:var(--navy);margin:0 0 4px;">
              <i class="fas fa-coins" style="color:var(--accent);font-size:1.6rem;"></i> 45 000 FCFA
            </div>
            <div style="color:var(--muted);font-style:italic;margin-bottom:16px;font-size:.85rem;">(≈ 69 euros)</div>
            <hr style="border-color:var(--light-bg);margin:16px 0;">
            <p style="font-weight:700;font-size:.88rem;color:var(--navy);margin-bottom:10px;"><span data-lang="fr">En échange, vous obtenez :</span><span data-lang="en">In exchange, you get:</span></p>
            <?php foreach([
              ['fr'=>'Ouverture et traitement de votre dossier','en'=>'Opening and processing of your file'],
              ['fr'=>'Examen de vos diplômes et documents','en'=>'Review of your diplomas and documents'],
              ['fr'=>'Numéro de matricule officiel FaMaKo','en'=>'Official FaMaKo student ID number'],
              ['fr'=>'Confirmation d\'admission écrite','en'=>'Written admission confirmation'],
            ] as $item): ?>
            <div class="d-flex align-items-start gap-2 mb-2">
              <i class="fas fa-check-circle mt-1" style="color:var(--success);font-size:.85rem;flex-shrink:0;"></i>
              <span style="font-size:.85rem;"><span data-lang="fr"><?= $item['fr'] ?></span><span data-lang="en"><?= $item['en'] ?></span></span>
            </div>
            <?php endforeach; ?>
            <div class="alert-famako alert-info mt-3" style="font-size:.82rem;">
              <i class="fas fa-calendar-alt"></i>
              <span data-lang="fr"><strong>Quand ?</strong> À régler après confirmation de l'admission par l'administration.</span>
              <span data-lang="en"><strong>When?</strong> To be paid after confirmation of admission by the administration.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Frais de scolarité -->
      <div class="col-md-6">
        <div class="famako-card h-100" style="border-top:5px solid var(--navy);">
          <div class="p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
              <i class="fas fa-graduation-cap fa-2x" style="color:var(--navy)"></i>
              <div>
                <h4 style="color:var(--navy);margin:0;font-weight:700;"><span data-lang="fr">Frais de scolarité</span><span data-lang="en">Tuition fees</span></h4>
                <div style="font-size:.8rem;color:var(--muted);"><span data-lang="fr">Payable en 1 ou 2 tranches</span><span data-lang="en">Payable in 1 or 2 installments</span></div>
              </div>
            </div>
            <div style="font-family:'Playfair Display',serif;font-size:2.4rem;font-weight:900;color:var(--navy);margin:0 0 4px;">
              <i class="fas fa-university" style="color:var(--accent);font-size:1.6rem;"></i> 540 000 FCFA
            </div>
            <div style="color:var(--muted);font-style:italic;margin-bottom:16px;font-size:.85rem;">(≈ 823 euros)</div>
            <hr style="border-color:var(--light-bg);margin:16px 0;">
            <p style="font-weight:700;font-size:.88rem;color:var(--navy);margin-bottom:10px;"><span data-lang="fr">En échange, vous obtenez :</span><span data-lang="en">In exchange, you get:</span></p>
            <?php foreach([
              ['fr'=>'Accès à tous les cours DSPR de l\'année','en'=>'Access to all DSPR courses for the year'],
              ['fr'=>'Accès aux travaux dirigés (TD)','en'=>'Access to practical exercises (TD)'],
              ['fr'=>'Codes d\'accès personnels à la plateforme','en'=>'Personal access codes to the platform'],
              ['fr'=>'Suivi pédagogique par les enseignants','en'=>'Educational follow-up by teachers'],
              ['fr'=>'Accès aux cours Zoom (lundi, mercredi, samedi)','en'=>'Access to Zoom classes (Mon, Wed, Sat)'],
            ] as $item): ?>
            <div class="d-flex align-items-start gap-2 mb-2">
              <i class="fas fa-check-circle mt-1" style="color:var(--success);font-size:.85rem;flex-shrink:0;"></i>
              <span style="font-size:.85rem;"><span data-lang="fr"><?= $item['fr'] ?></span><span data-lang="en"><?= $item['en'] ?></span></span>
            </div>
            <?php endforeach; ?>
            <div class="alert-famako alert-info mt-3" style="font-size:.82rem;">
              <i class="fas fa-calendar-alt"></i>
              <span data-lang="fr"><strong>Quand ?</strong> 1ère tranche à la rentrée, 2ème tranche en janvier (si paiement en 2 fois).</span>
              <span data-lang="en"><strong>When?</strong> 1st installment at the start, 2nd installment in January (if paying in 2 installments).</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Récapitulatif total -->
    <div class="famako-card p-4" style="background:var(--navy);color:#fff;">
      <div class="row align-items-center g-3">
        <div class="col-md-6">
          <h5 style="color:var(--accent);margin-bottom:8px;"><i class="fas fa-calculator me-2"></i><span data-lang="fr">Total pour une année DSPR</span><span data-lang="en">Total for one DSPR year</span></h5>
          <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:#fff;">585 000 FCFA <span style="font-size:1rem;color:rgba(255,255,255,.55);">(≈ 892 €)</span></div>
          <p style="color:rgba(255,255,255,.65);font-size:.85rem;margin-top:8px;margin-bottom:0;"><span data-lang="fr">Frais d'inscription (45 000) + Frais de scolarité (540 000)</span><span data-lang="en">Registration fees (45,000) + Tuition fees (540,000)</span></p>
        </div>
        <div class="col-md-6 d-flex flex-column gap-2">
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-shield-alt" style="color:var(--accent);width:20px;"></i>
            <span style="font-size:.85rem;color:rgba(255,255,255,.8);"><span data-lang="fr">Paiement sécurisé par virement bancaire</span><span data-lang="en">Secure payment by bank transfer</span></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-receipt" style="color:var(--accent);width:20px;"></i>
            <span style="font-size:.85rem;color:rgba(255,255,255,.8);"><span data-lang="fr">Reçu officiel fourni pour chaque paiement</span><span data-lang="en">Official receipt provided for each payment</span></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="fas fa-headset" style="color:var(--accent);width:20px;"></i>
            <span style="font-size:.85rem;color:rgba(255,255,255,.8);"><span data-lang="fr">Accompagnement personnalisé du DAF</span><span data-lang="en">Personalized support from the DAF</span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 3 : APPLICATION MOBILE -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <div class="section-label"><span data-lang="fr">Application mobile</span><span data-lang="en">Mobile app</span></div>
        <h2 class="section-title"><span data-lang="fr">Téléchargez l'application FaMaKo</span><span data-lang="en">Download the FaMaKo app</span></h2>
        <div class="section-divider"></div>
        <p style="line-height:1.7;"><span data-lang="fr">Accédez à vos cours, travaux dirigés et informations d'inscription directement depuis votre smartphone. L'application FaMaKo est disponible en téléchargement gratuit pour Android.</span>
        <span data-lang="en">Access your courses, practical exercises and enrollment information directly from your smartphone. The FaMaKo app is available for free download on Android.</span></p>
        <ul class="list-unstyled mt-3 mb-4">
          <?php foreach([
            ['fa-mobile-alt','Optimisée pour Android','Optimized for Android'],
            ['fa-wifi','Disponible hors connexion (cours téléchargés)','Available offline (downloaded courses)'],
            ['fa-bell','Notifications de cours et de TD','Course and TD notifications'],
            ['fa-lock','Accès sécurisé avec vos codes personnels','Secure access with your personal codes'],
          ] as [$ico,$fr,$en]): ?>
          <li class="d-flex align-items-start gap-3 mb-2">
            <i class="fas <?= $ico ?>" style="color:var(--accent);margin-top:3px;width:18px;text-align:center;"></i>
            <span style="font-size:.9rem;"><span data-lang="fr"><?= $fr ?></span><span data-lang="en"><?= $en ?></span></span>
          </li>
          <?php endforeach; ?>
        </ul>
        <div class="d-flex gap-3 flex-wrap">
          <a href="<?= BASE_URL ?>/assets/uploads/famako.apk" class="btn-accent" download>
            <i class="fab fa-android me-2"></i>
            <span data-lang="fr">Télécharger l'APK Android</span>
            <span data-lang="en">Download Android APK</span>
          </a>
          <a href="<?= BASE_URL ?>/pages/contact.php" class="btn-outline-navy">
            <i class="fas fa-question-circle me-2"></i>
            <span data-lang="fr">Besoin d'aide ?</span>
            <span data-lang="en">Need help?</span>
          </a>
        </div>
        <p style="font-size:.75rem;color:var(--muted);margin-top:10px;"><i class="fas fa-info-circle me-1"></i><span data-lang="fr">Pour installer un APK sur Android, autorisez les sources inconnues dans les paramètres de sécurité.</span><span data-lang="en">To install an APK on Android, allow unknown sources in your security settings.</span></p>
      </div>
      <div class="col-lg-5 offset-lg-1">
        <div class="famako-card p-4 text-center">
          <div style="width:80px;height:80px;background:linear-gradient(135deg,#3DDC84,#2ca86a);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(61,220,132,.3);">
            <i class="fab fa-android" style="font-size:2.5rem;color:#fff;"></i>
          </div>
          <h5 style="color:var(--navy);font-weight:700;margin-bottom:6px;">FaMaKo App</h5>
          <p style="color:var(--muted);font-size:.85rem;margin-bottom:20px;"><span data-lang="fr">Application officielle de la Faculté Maïngo Ködörö</span><span data-lang="en">Official app of Maïngo Ködörö Faculty</span></p>
          <div style="background:var(--light-bg);border-radius:var(--radius);padding:16px;margin-bottom:16px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span style="font-size:.8rem;color:var(--muted);"><span data-lang="fr">Format</span><span data-lang="en">Format</span></span>
              <span style="font-size:.8rem;font-weight:600;color:var(--navy);">APK (Android)</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span style="font-size:.8rem;color:var(--muted);"><span data-lang="fr">Compatibilité</span><span data-lang="en">Compatibility</span></span>
              <span style="font-size:.8rem;font-weight:600;color:var(--navy);">Android 7.0+</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span style="font-size:.8rem;color:var(--muted);"><span data-lang="fr">Prix</span><span data-lang="en">Price</span></span>
              <span style="font-size:.8rem;font-weight:600;color:var(--success);"><span data-lang="fr">Gratuit</span><span data-lang="en">Free</span></span>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/assets/uploads/famako.apk" class="btn-accent w-100 justify-content-center" download>
            <i class="fas fa-download me-2"></i>
            <span data-lang="fr">Télécharger maintenant</span>
            <span data-lang="en">Download now</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SECTION 4 : PROCÉDURE DE PAIEMENT -->
<section class="py-5">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-7">
        <div class="section-label"><span data-lang="fr">Mode opératoire</span><span data-lang="en">How it works</span></div>
        <h2 class="section-title"><span data-lang="fr">Procédure de paiement</span><span data-lang="en">Payment procedure</span></h2>
        <div class="section-divider"></div>
        <div class="row g-3 mt-2">
          <?php foreach([
            ['1','fa-envelope','Contacter le DAF','Contact the DAF','Envoyez un email au Directeur Administratif et Financier pour obtenir les coordonnées bancaires.','Send an email to the Administrative and Financial Director to obtain the bank details.'],
            ['2','fa-university','Effectuer le virement','Make the transfer','Effectuez le virement avec votre nom complet et votre numéro de matricule en référence.','Make the transfer with your full name and student ID number as reference.'],
            ['3','fa-file-upload','Envoyer le reçu','Send the receipt','Transmettez le justificatif de paiement au DAF par email pour validation.','Send the proof of payment to the DAF by email for validation.'],
            ['4','fa-key','Recevoir vos codes','Receive your codes','Une fois le paiement validé, vous recevez vos codes d\'accès personnels sous 48h.','Once the payment is validated, you receive your personal access codes within 48 hours.'],
          ] as [$n,$ico,$fr,$en,$dfr,$den]): ?>
          <div class="col-12">
            <div style="background:var(--light-bg);border-radius:var(--radius);padding:16px 20px;display:flex;align-items:flex-start;gap:16px;">
              <div style="width:44px;height:44px;flex-shrink:0;background:var(--navy);color:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-family:'Playfair Display',serif;font-size:1.1rem;"><?= $n ?></div>
              <div>
                <h6 style="font-weight:700;color:var(--navy);font-size:.92rem;margin-bottom:4px;">
                  <i class="fas <?= $ico ?> me-2" style="color:var(--accent);"></i>
                  <span data-lang="fr"><?= $fr ?></span><span data-lang="en"><?= $en ?></span>
                </h6>
                <p style="font-size:.82rem;color:var(--muted);margin:0;line-height:1.5;"><span data-lang="fr"><?= $dfr ?></span><span data-lang="en"><?= $den ?></span></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="alert-famako alert-warning mt-4">
          <i class="fas fa-exclamation-triangle"></i>
          <span data-lang="fr">Conservez précieusement votre reçu de paiement. Il vous sera demandé à chaque étape de votre parcours académique.</span>
          <span data-lang="en">Keep your payment receipt carefully. It will be requested at each step of your academic journey.</span>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="famako-card p-0">
          <div class="p-4" style="background:var(--navy);border-radius:var(--radius) var(--radius) 0 0;">
            <h4 style="color:var(--accent);font-family:'Playfair Display',serif;margin-bottom:16px;">
              <i class="fas fa-question-circle me-2"></i>
              <span data-lang="fr">Questions fréquentes</span>
              <span data-lang="en">Frequently asked questions</span>
            </h4>
            <?php foreach([
              ['Peut-on payer en plusieurs fois ?','Can I pay in installments?',
               'Oui. Les frais de scolarité (540 000 FCFA) peuvent être réglés en 2 tranches. Les frais d\'inscription (45 000 FCFA) sont à régler en une seule fois.',
               'Yes. Tuition fees (540,000 FCFA) can be paid in 2 installments. Registration fees (45,000 FCFA) must be paid in one installment.'],
              ['Puis-je m\'inscrire depuis l\'étranger ?','Can I enroll from abroad?',
               'Oui. Les inscriptions sont ouvertes à tous les ressortissants de la RCA et des pays voisins. Le virement bancaire international est accepté.',
               'Yes. Enrollment is open to all nationals from CAR and neighboring countries. International bank transfers are accepted.'],
              ['Quels documents sont nécessaires ?','What documents are required?',
               'Copie de la pièce d\'identité, dernier diplôme obtenu (au moins Bac+4/Master), lettre de motivation, CV et photo d\'identité.',
               'Copy of ID, most recent diploma (at least Bachelor+4/Master), motivation letter, CV and ID photo.'],
            ] as [$qfr,$qen,$afr,$aen]): ?>
            <div style="margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid rgba(255,255,255,.1);">
              <p style="color:var(--accent);font-weight:600;font-size:.85rem;margin-bottom:6px;">
                <i class="fas fa-chevron-right me-1" style="font-size:.7rem;"></i>
                <span data-lang="fr"><?= $qfr ?></span><span data-lang="en"><?= $qen ?></span>
              </p>
              <p style="color:rgba(255,255,255,.7);font-size:.8rem;margin:0;line-height:1.5;">
                <span data-lang="fr"><?= $afr ?></span><span data-lang="en"><?= $aen ?></span>
              </p>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="p-4">
            <a href="<?= BASE_URL ?>/pages/inscription.php" class="btn-accent w-100 justify-content-center mb-2">
              <i class="fas fa-pen me-2"></i>
              <span data-lang="fr">Déposer ma candidature</span>
              <span data-lang="en">Submit my application</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/contact.php" class="btn-outline-navy w-100 justify-content-center">
              <i class="fas fa-envelope me-2"></i>
              <span data-lang="fr">Contacter le DAF</span>
              <span data-lang="en">Contact the DAF</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
