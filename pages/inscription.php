<?php
/**
 * pages/inscription.php
 * Formulaire d'inscription public — matricule format [AA][MM][AN][NNNN]
 *   AA   = 2 derniers chiffres de l'ANNÉE D'INSCRIPTION
 *   MM   = mois de naissance (2 chiffres)
 *   AN   = 2 derniers chiffres de l'ANNÉE DE NAISSANCE
 *   NNNN = séquentiel incrémental 4 chiffres (0001-9999)
 * Exemple : 2601810050
 */
$page_title = "Inscription";
require_once __DIR__ . '/../config/database.php';
$pdo = getPDO();
$disciplines = $pdo->query("SELECT id, nom_fr FROM disciplines WHERE actif=1 ORDER BY ordre")->fetchAll();

// ----------------------------------------------------------------
//  Génération atomique du matricule via la table matricule_sequences
// ----------------------------------------------------------------
function genererMatricule(PDO $pdo, string $date_naissance): string
{
    $aa     = date('y');                               // 2 chiffres année inscription (ex: 26)
    $mm     = date('m', strtotime($date_naissance));   // mois naissance (ex: 01)
    $an     = substr(date('y', strtotime($date_naissance)), 0, 2); // 2 chiffres année naissance (ex: 81)
    $prefix = $aa . $mm . $an;                         // ex: 260181

    // Incrémentation atomique dans la table dédiée
    $pdo->prepare("
        INSERT INTO matricule_sequences (prefix, last_seq)
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE last_seq = last_seq + 1
    ")->execute([$prefix]);

    $seq = (int)$pdo->prepare("SELECT last_seq FROM matricule_sequences WHERE prefix = ?")
                    ->execute([$prefix]) ? 0 : 0; // init
    $stmt = $pdo->prepare("SELECT last_seq FROM matricule_sequences WHERE prefix = ?");
    $stmt->execute([$prefix]);
    $seq = (int)$stmt->fetchColumn();

    if ($seq > 9999) {
        throw new RuntimeException("Séquentiel matricule dépassé pour le préfixe $prefix.");
    }

    return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT); // ex: 2601810050
}

$success = $error = '';
$matricule_genere = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom            = trim($_POST['nom']            ?? '');
    $prenom         = trim($_POST['prenom']         ?? '');
    $email          = trim($_POST['email']          ?? '');
    $contact        = trim($_POST['contact']        ?? '');
    $adresse        = trim($_POST['adresse']        ?? '');
    $pays           = trim($_POST['pays']           ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $disc_id        = (int)($_POST['discipline_id'] ?? 0);

    // ---- Validations ----
    if (!$nom || !$prenom || !$email || !$disc_id || !$date_naissance || !$pays) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strtotime($date_naissance) === false || strtotime($date_naissance) > time()) {
        $error = 'Date de naissance invalide.';
    } else {
        $check = $pdo->prepare("SELECT id FROM inscriptions WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'Cette adresse email est déjà utilisée pour une inscription.';
        } else {
            try {
                $uploadDir = UPLOAD_DIR . 'inscriptions/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                function uploadFile(string $key, string $dir, int $maxSize): string
                {
                    if (empty($_FILES[$key]['name'])) return '';
                    $file = $_FILES[$key];
                    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $maxSize) return '';
                    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                    if (!in_array($ext, $allowed)) return '';
                    $newName = uniqid($key . '_') . '.' . $ext;
                    move_uploaded_file($file['tmp_name'], $dir . $newName);
                    return $newName;
                }

                $photo   = uploadFile('photo',   $uploadDir, MAX_PHOTO_SIZE);
                $cv      = uploadFile('cv',      $uploadDir, 50 * 1024 * 1024);
                $diplome = uploadFile('diplome', $uploadDir, 50 * 1024 * 1024);
                $lettre  = uploadFile('lettre',  $uploadDir, 20 * 1024 * 1024);

                // Génération du matricule
                $pdo->beginTransaction();
                $matricule = genererMatricule($pdo, $date_naissance);

                $stmt = $pdo->prepare("
                    INSERT INTO inscriptions
                      (nom, prenom, email, contact, adresse, pays, date_naissance,
                       discipline_id, photo_path, cv_path, diplome_path, lettre_path, matricule)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
                ");
                $stmt->execute([
                    $nom, $prenom, $email, $contact, $adresse, $pays, $date_naissance,
                    $disc_id, $photo, $cv, $diplome, $lettre, $matricule
                ]);
                $pdo->commit();

                $matricule_genere = $matricule;
                $success = "Votre inscription a été enregistrée ! Votre matricule est : <strong>$matricule</strong>. Nous vous contacterons sous 48h.";

            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $error = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
                error_log($e->getMessage());
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';

$pays_liste = [
    'Afghanistan', 'Afrique du Sud', 'Albanie', 'Algérie', 'Allemagne', 'Andorre', 'Angola',
    'Antigua-et-Barbuda', 'Arabie saoudite', 'Argentine', 'Arménie', 'Australie', 'Autriche',
    'Azerbaïdjan', 'Bahamas', 'Bahreïn', 'Bangladesh', 'Barbade', 'Belgique', 'Belize',
    'Bénin', 'Bhoutan', 'Biélorussie', 'Birmanie', 'Bolivie', 'Bosnie-Herzégovine', 'Botswana',
    'Brésil', 'Brunei', 'Bulgarie', 'Burkina Faso', 'Burundi', 'Cambodge', 'Cameroun', 'Canada',
    'Cap-Vert', 'République Centrafricaine', 'Chili', 'Chine', 'Chypre', 'Colombie', 'Comores',
    'Congo', 'République Démocratique du Congo', 'Corée du Nord', 'Corée du Sud', 'Costa Rica',
    'Côte d\'Ivoire', 'Croatie', 'Cuba', 'Danemark', 'Djibouti', 'Dominique', 'Égypte',
    'Émirats arabes unis', 'Équateur', 'Érythrée', 'Espagne', 'Estonie', 'Eswatini', 'États-Unis',
    'Éthiopie', 'Fidji', 'Finlande', 'France', 'Gabon', 'Gambie', 'Géorgie', 'Ghana', 'Grèce',
    'Grenade', 'Guatemala', 'Guinée', 'Guinée-Bissau', 'Guinée équatoriale', 'Guyana', 'Haïti',
    'Honduras', 'Hongrie', 'Inde', 'Indonésie', 'Irak', 'Iran', 'Irlande', 'Islande', 'Israël',
    'Italie', 'Jamaïque', 'Japon', 'Jordanie', 'Kazakhstan', 'Kenya', 'Kirghizistan', 'Kiribati',
    'Koweït', 'Laos', 'Lesotho', 'Lettonie', 'Liban', 'Libéria', 'Libye', 'Liechtenstein',
    'Lituanie', 'Luxembourg', 'Macédoine du Nord', 'Madagascar', 'Malaisie', 'Malawi', 'Maldives',
    'Mali', 'Malte', 'Maroc', 'Marshall (Îles)', 'Maurice', 'Mauritanie', 'Mexique', 'Micronésie',
    'Moldavie', 'Monaco', 'Mongolie', 'Monténégro', 'Mozambique', 'Namibie', 'Nauru', 'Népal',
    'Nicaragua', 'Niger', 'Nigeria', 'Norvège', 'Nouvelle-Zélande', 'Oman', 'Ouganda',
    'Ouzbékistan', 'Pakistan', 'Palaos', 'Palestine', 'Panama', 'Papouasie-Nouvelle-Guinée',
    'Paraguay', 'Pays-Bas', 'Pérou', 'Philippines', 'Pologne', 'Portugal', 'Qatar',
    'République centrafricaine', 'République dominicaine', 'République tchèque', 'Roumanie',
    'Royaume-Uni', 'Russie', 'Rwanda', 'Saint-Christophe-et-Niévès', 'Sainte-Lucie',
    'Saint-Marin', 'Saint-Vincent-et-les-Grenadines', 'Salomon (Îles)', 'Salvador', 'Samoa',
    'Sao Tomé-et-Principe', 'Sénégal', 'Serbie', 'Seychelles', 'Sierra Leone', 'Singapour',
    'Slovaquie', 'Slovénie', 'Somalie', 'Soudan', 'Soudan du Sud', 'Sri Lanka', 'Suède',
    'Suisse', 'Suriname', 'Syrie', 'Tadjikistan', 'Tanzanie', 'Tchad', 'Thaïlande', 'Timor oriental',
    'Togo', 'Tonga', 'Trinité-et-Tobago', 'Tunisie', 'Turkménistan', 'Turquie', 'Tuvalu',
    'Ukraine', 'Uruguay', 'Vanuatu', 'Vatican', 'Venezuela', 'Viêt Nam', 'Yémen', 'Zambie',
    'Zimbabwe', 'Autre'
];
?>
<main class="py-5" style="background:var(--light-bg)">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

      <div class="text-center mb-5">
        <div class="section-label">Candidature</div>
        <h1 class="section-title">Formulaire d'inscription</h1>
        <div class="section-divider mx-auto"></div>
        <p>Remplissez ce formulaire pour vous inscrire au programme DSPR ou Doctorat.
           Un matricule unique vous sera automatiquement attribué.</p>
      </div>

      <?php if ($success): ?>
      <div class="alert-famako alert-success mb-4" style="flex-direction:column;align-items:flex-start;gap:8px;">
        <div class="d-flex gap-3 align-items-center">
          <i class="fas fa-check-circle fa-lg"></i>
          <div><?= $success ?></div>
        </div>
        <?php if ($matricule_genere): ?>
        <!-- Affichage visuel du matricule avec décomposition -->
        <div style="background:var(--navy);border-radius:var(--radius);padding:18px 24px;margin-top:8px;width:100%;">
          <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:var(--accent);letter-spacing:.15em;text-align:center;">
            🎓 <?= htmlspecialchars($matricule_genere) ?>
          </div>
          <?php
            $m = $matricule_genere;
            $aa_disp = $m[0].$m[1];
            $mm_disp = $m[2].$m[3];
            $an_disp = $m[4].$m[5];
            $nn_disp = substr($m, 6, 4);
            $mois_noms = ['01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Jun',
                          '07'=>'Jul','08'=>'Aoû','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'];
          ?>
          <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
            <?php foreach ([
              ['20'.$aa_disp, "Année d'inscr."],
              [$mois_noms[$mm_disp] ?? $mm_disp, 'Mois naissance'],
              ["'".$an_disp, 'Année naissance'],
              ['#'.$nn_disp, 'N° séquentiel'],
            ] as [$val, $lbl]): ?>
            <div style="text-align:center;background:rgba(255,255,255,.08);padding:8px 14px;border-radius:8px;">
              <div style="color:var(--accent);font-weight:700;font-size:1rem;"><?= $val ?></div>
              <div style="color:rgba(255,255,255,.5);font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;margin-top:2px;"><?= $lbl ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <p style="font-size:.82rem;color:var(--muted);margin:4px 0 0;">
          <i class="fas fa-exclamation-triangle me-1" style="color:var(--warning)"></i>
          Conservez ce matricule — il vous sera demandé pour toutes vos démarches à la faculté.
        </p>
        <?php endif; ?>
      </div>
      <?php elseif ($error): ?>
      <div class="alert-famako alert-danger mb-4">
        <i class="fas fa-exclamation-circle fa-lg"></i>
        <div><?= htmlspecialchars($error) ?></div>
      </div>
      <?php endif; ?>

     

      <div class="famako-card">
        <div class="card-header-custom">
          <span style="font-weight:700;color:var(--navy);font-family:'Playfair Display',serif;">
            <i class="fas fa-graduation-cap me-2" style="color:var(--accent)"></i>Dossier de candidature
          </span>
          <span style="font-size:.75rem;color:var(--muted);"><span class="text-danger">*</span> Champs obligatoires</span>
        </div>
        <div class="card-body-custom">
        <form method="POST" enctype="multipart/form-data">

          <!-- IDENTITÉ -->
          <h6 class="mb-3 mt-1" style="color:var(--navy);border-bottom:2px solid var(--accent);padding-bottom:6px;">
            <i class="fas fa-user me-2" style="color:var(--accent)"></i>Informations personnelles
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Nom <span class="req">*</span></label>
                <input type="text" name="nom" class="form-control-custom"
                  value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                  required placeholder="DUPONT" style="text-transform:uppercase"
                  oninput="this.value=this.value.toUpperCase();">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Prénom <span class="req">*</span></label>
                <input type="text" name="prenom" class="form-control-custom"
                  value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                  required placeholder="Jean">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Date de naissance <span class="req">*</span></label>
                <input type="date" name="date_naissance" id="dateNaissance" class="form-control-custom"
                  value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>"
                  required max="<?= date('Y-m-d', strtotime('-16 years')) ?>"
                  onchange="updateMatriculePreview()">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Contact (téléphone)</label>
                <input type="text" name="contact" class="form-control-custom"
                  value="<?= htmlspecialchars($_POST['contact'] ?? '') ?>"
                  placeholder="+236 70 00 00 00">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Pays <span class="req">*</span></label>
                <select name="pays" class="form-control-custom" required>
                  <option value="">— Sélectionnez votre pays —</option>
                  <?php foreach ($pays_liste as $p): ?>
                  <option value="<?= htmlspecialchars($p) ?>"
                    <?= (($_POST['pays'] ?? '') === $p) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Email <span class="req">*</span></label>
                <input type="email" name="email" class="form-control-custom"
                  value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                  required placeholder="vous@email.com">
              </div>
            </div>
            <div class="col-12">
              <div class="form-field-custom">
                <label>Adresse postale</label>
                <textarea name="adresse" class="form-control-custom" rows="2"
                  placeholder="Bangui, RCA"><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
              </div>
            </div>
          </div>

          <!-- APERÇU MATRICULE (live preview) -->
          <div id="matriculePreview" style="display:none;background:linear-gradient(135deg,var(--navy),var(--navy-lt));border-radius:var(--radius);padding:18px 22px;margin-bottom:24px;">
            <div style="font-size:.72rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;">Aperçu de votre matricule</div>
            <div id="matriculeVal" style="font-family:'Playfair Display',serif;font-size:1.9rem;font-weight:900;color:var(--accent);letter-spacing:.14em;">—</div>
            <div id="matriculeLegend" class="d-flex gap-3 mt-2 flex-wrap" style="font-size:.68rem;"></div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.35);margin-top:8px;">Le numéro séquentiel définitif sera attribué à la validation.</div>
          </div>

          <!-- DISCIPLINE -->
          <h6 class="mb-3" style="color:var(--navy);border-bottom:2px solid var(--accent);padding-bottom:6px;">
            <i class="fas fa-graduation-cap me-2" style="color:var(--accent)"></i>Choix de la discipline
          </h6>
          <div class="form-field-custom mb-4">
            <label>Discipline souhaitée <span class="req">*</span></label>
            <select name="discipline_id" class="form-control-custom" required>
              <option value="">— Sélectionnez une discipline —</option>
              <?php foreach ($disciplines as $d): ?>
              <option value="<?= $d['id'] ?>"
                <?= (($_POST['discipline_id'] ?? '') == $d['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($d['nom_fr']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- DOCUMENTS -->
          <h6 class="mb-3" style="color:var(--navy);border-bottom:2px solid var(--accent);padding-bottom:6px;">
            <i class="fas fa-paperclip me-2" style="color:var(--accent)"></i>Documents à joindre
          </h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Photo d'identité <small style="color:var(--muted)">(max 15 Mo · JPG/PNG)</small></label>
                <label class="file-upload-zone">
                  <input type="file" name="photo" accept=".jpg,.jpeg,.png">
                  <i class="fas fa-camera d-block"></i>
                  <p class="upload-filename mt-1">Cliquez ou glissez votre photo</p>
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>CV <small style="color:var(--muted)">(PDF/Word · max 50 Mo)</small></label>
                <label class="file-upload-zone">
                  <input type="file" name="cv" accept=".pdf,.doc,.docx">
                  <i class="fas fa-file-alt d-block"></i>
                  <p class="upload-filename mt-1">Cliquez ou glissez votre CV</p>
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Diplôme (Licence/BAC+3) <small style="color:var(--muted)">(PDF)</small></label>
                <label class="file-upload-zone">
                  <input type="file" name="diplome" accept=".pdf,.jpg,.jpeg,.png">
                  <i class="fas fa-certificate d-block"></i>
                  <p class="upload-filename mt-1">Cliquez ou glissez votre diplôme</p>
                </label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-field-custom">
                <label>Lettre de motivation <small style="color:var(--muted)">(PDF/Word)</small></label>
                <label class="file-upload-zone">
                  <input type="file" name="lettre" accept=".pdf,.doc,.docx">
                  <i class="fas fa-pen d-block"></i>
                  <p class="upload-filename mt-1">Cliquez ou glissez votre lettre</p>
                </label>
              </div>
            </div>
          </div>

          <div class="alert-famako alert-info mb-4">
            <i class="fas fa-info-circle"></i>
            <span>Après validation de votre dossier, nous vous contacterons par email. Votre matricule définitif vous sera communiqué avec votre lettre d'admission.</span>
          </div>

          <button type="submit" class="btn-accent w-100 py-3"
            style="font-size:1rem;font-family:'DM Sans',sans-serif;border-radius:var(--radius);">
            <i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature
          </button>
        </form>
        </div>
      </div>
    </div>
  </div>
</div>
</main>

<script>
// Noms des mois pour la légende
const MOIS = {
  '01':'Janv','02':'Févr','03':'Mars','04':'Avr','05':'Mai','06':'Juin',
  '07':'Juil','08':'Août','09':'Sept','10':'Oct','11':'Nov','12':'Déc'
};

function updateMatriculePreview() {
  const date  = document.getElementById('dateNaissance')?.value || '';
  const prev  = document.getElementById('matriculePreview');
  const val   = document.getElementById('matriculeVal');
  const leg   = document.getElementById('matriculeLegend');

  if (!date) { if(prev) prev.style.display='none'; return; }

  // Calculs côté JS (identiques au PHP)
  const now   = new Date();
  const aa    = String(now.getFullYear()).slice(2);          // ex: "26"
  const parts = date.split('-');                             // ["1981","01","15"]
  const mm    = parts[1];                                    // "01"
  const an    = parts[0].slice(2);                           // "81"
  const prefix = aa + mm + an;                               // "260181"

  if(prev) prev.style.display = 'block';
  if(val)  val.textContent = prefix + '????';

  // Légende décomposée
  if(leg) leg.innerHTML = [
    [`20${aa}`, "Année inscr."],
    [MOIS[mm] || mm, "Mois naiss."],
    [`'${an}`, "Année naiss."],
    ['#????', "Séquentiel"]
  ].map(([v,l])=>`
    <span style="background:rgba(255,255,255,.08);padding:4px 10px;border-radius:6px;color:rgba(255,255,255,.9);">
      <strong style="color:var(--accent);">${v}</strong>
      <span style="color:rgba(255,255,255,.45);font-size:.62rem;margin-left:4px;">${l}</span>
    </span>`).join('');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
