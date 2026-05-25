<?php
// admin/cours/ajouter.php  (modifier.php inclut ce même fichier avec $editId)
$editId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page_title = $editId ? "Modifier le cours" : "Ajouter un cours";
require_once __DIR__ . '/../includes/admin_layout.php';
$pdo = getPDO();
// Nouvelle ligne (sans ordre, tri par nom)
$disciplines = $pdo->query("SELECT id, nom_fr FROM disciplines WHERE actif=1 ORDER BY nom_fr")->fetchAll();

$cours = [
    'titre'         => '',
    'description'   => '',
    'discipline_id' => '',
    'type_fichier'  => 'pdf',
    'url_video'     => '',
    'date_cours'    => '',
    'mois_cours'    => '',
    'annee_cours'   => '',
    'actif'         => 1,
    'fichier_path'  => '',
    'fichier_nom'   => '',
    'fichier_taille'=> 0,
    'code_acces'    => '',   // hash bcrypt du code étudiant
    'code_acces_hint' => '', // indice affiché à l'étudiant
];

if ($editId) {
    $row = $pdo->prepare("SELECT * FROM cours WHERE id = ?");
    $row->execute([$editId]); 
    $fetched = $row->fetch();
    if ($fetched) {
        $cours = array_merge($cours, $fetched);
    }
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Code d'accès étudiant ──────────────────────────────────────
    $newCode    = trim($_POST['new_code_acces'] ?? '');
    $removeCode = !empty($_POST['remove_code_acces']);
    $codeHash   = $cours['code_acces'] ?: null;   // conserve l'ancien
    if ($removeCode)         $codeHash = null;
    elseif ($newCode !== '') $codeHash = password_hash($newCode, PASSWORD_DEFAULT);

    $data = [
        'titre'           => trim($_POST['titre'] ?? ''),
        'description'     => trim($_POST['description'] ?? ''),
        'discipline_id'   => (int)($_POST['discipline_id'] ?? 0) ?: null,
        'type_fichier'    => $_POST['type_fichier'] ?? 'pdf',
        'url_video'       => trim($_POST['url_video'] ?? ''),
        'annee_cours'     => (int)($_POST['annee_cours'] ?? 0) ?: null,
        'mois_cours'      => (int)($_POST['mois_cours'] ?? 0) ?: null,
        'actif'           => isset($_POST['actif']) ? 1 : 0,
        'user_id'         => $_SESSION['user_id'] ?? 0,
        'code_acces'      => $codeHash,
        'code_acces_hint' => trim($_POST['code_acces_hint'] ?? '') ?: null,
    ];
    
    if (!$data['titre']) { 
        $error = 'Le titre est obligatoire.'; 
    } else {
        // Fichier - initialisation des valeurs par défaut
        $fichierPath = $cours['fichier_path'] ?? '';
        $fichierNom  = $cours['fichier_nom'] ?? '';
        $fichierSize = $cours['fichier_taille'] ?? 0;
        
        // Gestion du téléchargement de fichier
        if (isset($_FILES['fichier']) && !empty($_FILES['fichier']['name']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_DIR . 'cours/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
            $newName = uniqid('cours_') . '.' . $ext;
            
            if (move_uploaded_file($_FILES['fichier']['tmp_name'], $uploadDir . $newName)) {
                $fichierPath = $newName;
                $fichierNom  = $_FILES['fichier']['name'];
                $fichierSize = $_FILES['fichier']['size'];
            } else {
                $error = "Erreur lors du téléchargement du fichier.";
            }
        }
        
        $data['fichier_path']   = $fichierPath;
        $data['fichier_nom']    = $fichierNom;
        $data['fichier_taille'] = $fichierSize;

        if (!$error) {
            try {
                if ($editId) {
                    $stmt = $pdo->prepare("UPDATE cours SET titre=?, description=?, discipline_id=?, type_fichier=?, url_video=?, annee_cours=?, mois_cours=?, actif=?, user_id=?, fichier_path=?, fichier_nom=?, fichier_taille=?, code_acces=?, code_acces_hint=?, updated_at=NOW() WHERE id=?");
                    $stmt->execute([
                        $data['titre'], $data['description'], $data['discipline_id'],
                        $data['type_fichier'], $data['url_video'], $data['annee_cours'],
                        $data['mois_cours'], $data['actif'], $data['user_id'],
                        $data['fichier_path'], $data['fichier_nom'], $data['fichier_taille'],
                        $data['code_acces'], $data['code_acces_hint'],
                        $editId
                    ]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO cours (titre, description, discipline_id, type_fichier, url_video, annee_cours, mois_cours, actif, user_id, fichier_path, fichier_nom, fichier_taille, code_acces, code_acces_hint) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $data['titre'], $data['description'], $data['discipline_id'],
                        $data['type_fichier'], $data['url_video'], $data['annee_cours'],
                        $data['mois_cours'], $data['actif'], $data['user_id'],
                        $data['fichier_path'], $data['fichier_nom'], $data['fichier_taille'],
                        $data['code_acces'], $data['code_acces_hint']
                    ]);
                    $editId = $pdo->lastInsertId();
                }
                
                header('Location: index.php?saved=1'); 
                exit;
            } catch (PDOException $e) {
                $error = "Erreur base de données : " . $e->getMessage();
            }
        }
    }
}

$moisNoms = [1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'];
?>

<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= BASE_URL ?>/admin/cours/index.php" class="btn-outline-navy" style="padding:8px 14px;font-size:.82rem;"><i class="fas fa-arrow-left me-1"></i>Retour</a>
  <h2 style="font-family:'Playfair Display',serif;color:var(--navy);font-size:1.3rem;margin:0;"><?= htmlspecialchars($page_title) ?></h2>
</div>

<?php if ($error): ?>
  <div class="alert-famako alert-danger mb-4"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="famako-card p-4" style="max-width:760px;">
<form method="POST" enctype="multipart/form-data">
  <div class="form-field-custom">
    <label>Titre du cours <span class="req">*</span></label>
    <input type="text" name="titre" class="form-control-custom" value="<?= htmlspecialchars($cours['titre']) ?>" required placeholder="Ex: Introduction à la philosophie de l'éducation">
  </div>

  <div class="form-field-custom">
    <label>Description</label>
    <textarea name="description" class="form-control-custom" rows="3" placeholder="Décrivez le contenu du cours…"><?= htmlspecialchars($cours['description']) ?></textarea>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="form-field-custom">
        <label>Discipline</label>
        <select name="discipline_id" class="form-control-custom">
          <option value="">— Sélectionner —</option>
          <?php foreach ($disciplines as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $cours['discipline_id']==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['nom_fr']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-field-custom">
        <label>Type de fichier <span class="req">*</span></label>
        <select name="type_fichier" class="form-control-custom" id="typeSelect">
          <?php foreach (['pdf'=>'PDF','word'=>'Word','video'=>'Vidéo','diapo'=>'Diaporama','autre'=>'Autre'] as $k=>$v): ?>
          <option value="<?= $k ?>" <?= $cours['type_fichier']===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-field-custom">
        <label>Année</label>
        <input type="number" name="annee_cours" class="form-control-custom" value="<?= htmlspecialchars($cours['annee_cours']) ?>" min="2000" max="2099" placeholder="2026">
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-field-custom">
        <label>Mois</label>
        <select name="mois_cours" class="form-control-custom">
          <option value="">—</option>
          <?php foreach ($moisNoms as $k=>$v): ?>
          <option value="<?=$k?>" <?=$cours['mois_cours']==$k?'selected':''?>><?=$v?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-field-custom">
        <label>Actif</label>
        <div style="padding:11px 0;"><input type="checkbox" name="actif" value="1" <?= !empty($cours['actif'])?'checked':'' ?> style="width:18px;height:18px;accent-color:var(--accent);"> <span style="font-size:.88rem;color:var(--muted);">Visible publiquement</span></div>
      </div>
    </div>
  </div>

  <!-- ── Protection par code d'accès étudiant ─────────────────── -->
  <div class="form-field-custom">
    <label>
      <i class="fas fa-lock me-1" style="color:var(--accent)"></i>
      Code d'accès étudiant
      <small style="color:var(--muted);font-weight:400;"> (optionnel — laissez vide pour un cours public)</small>
    </label>
    <?php if (!empty($cours['code_acces'])): ?>
    <div class="alert-famako alert-info mb-2" style="padding:8px 14px;font-size:.82rem;">
      <i class="fas fa-lock me-1"></i> Ce cours est <strong>protégé</strong>. Entrez un nouveau code pour le changer.
      <label style="display:inline-flex;align-items:center;gap:6px;margin-left:16px;cursor:pointer;">
        <input type="checkbox" name="remove_code_acces" value="1"> Supprimer la protection
      </label>
    </div>
    <?php endif; ?>
    <input type="text" name="new_code_acces" class="form-control-custom"
           placeholder="Ex : FMK2026 (affiché en clair ici, stocké hashé en base)"
           autocomplete="off" style="font-family:monospace;">
    <small style="color:var(--muted);font-size:.77rem;">
      Ce code sera communiqué aux étudiants par l'administration après inscription.
    </small>
  </div>
  <div class="form-field-custom">
    <label>Indice affiché à l'étudiant</label>
    <input type="text" name="code_acces_hint" class="form-control-custom"
           value="<?= htmlspecialchars($cours['code_acces_hint'] ?? '') ?>"
           placeholder="Ex : 4 chiffres, communiqué par email">
  </div>

  <!-- URL Vidéo -->
  <div class="form-field-custom" id="urlVideoRow" style="<?= ($cours['type_fichier'] ?? '') === 'video' ? '' : 'display:none' ?>">
    <label><i class="fab fa-youtube me-1 text-danger"></i>URL Vidéo (YouTube / externe)</label>
    <input type="url" name="url_video" class="form-control-custom" value="<?= htmlspecialchars($cours['url_video'] ?? '') ?>" placeholder="https://www.youtube.com/watch?v=...">
  </div>

  <!-- Fichier upload -->
  <div class="form-field-custom" id="fileUploadRow" style="<?= ($cours['type_fichier'] ?? '') === 'video' ? 'display:none' : '' ?>">
    <label><i class="fas fa-upload me-1" style="color:var(--accent)"></i>Fichier du cours
      <small style="color:var(--muted);font-weight:400;"> (PDF, Word, PPT, vidéo — jusqu'à 5 Go)</small>
    </label>
    <?php if (!empty($cours['fichier_path'])): ?>
    <div class="alert-famako alert-info mb-2" style="padding:8px 14px;font-size:.82rem;">
      <i class="fas fa-file"></i>
      <span>Fichier actuel : <strong><?= htmlspecialchars($cours['fichier_nom'] ?: $cours['fichier_path']) ?></strong></span>
    </div>
    <?php endif; ?>
    <label class="file-upload-zone">
      <input type="file" name="fichier" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.avi,.mov,.mkv,.webm">
      <i class="fas fa-cloud-upload-alt d-block" style="font-size:2rem;color:var(--accent);margin-bottom:8px;"></i>
      <p class="upload-filename mb-0">Cliquez ou glissez le fichier ici</p>
      <p style="font-size:.75rem;color:var(--muted);margin-top:4px;">Vidéo jusqu'à 5 Go · PDF/Word jusqu'à 100 Mo</p>
    </label>
  </div>

  <div class="d-flex gap-3 mt-4">
    <button type="submit" class="btn-accent"><i class="fas fa-save me-2"></i><?= $editId ? 'Mettre à jour' : 'Enregistrer le cours' ?></button>
    <a href="index.php" class="btn-outline-navy">Annuler</a>
  </div>
</form>
</div>

<script>
document.getElementById('typeSelect')?.addEventListener('change', function() {
  const isVideo = this.value === 'video';
  const urlVideoRow = document.getElementById('urlVideoRow');
  const fileUploadRow = document.getElementById('fileUploadRow');
  if (urlVideoRow) urlVideoRow.style.display = isVideo ? 'block' : 'none';
  if (fileUploadRow) fileUploadRow.style.display = isVideo ? 'none' : 'block';
});
</script>

