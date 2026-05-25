<?php
/**
 * pages/cours.php
 * Liste des cours avec protection par code d'accès étudiant.
 * Les cours sans code_acces sont libres ; les autres nécessitent
 * un code créé par l'administration avant tout téléchargement.
 */
$page_title = "Cours";

// CORRECTION DU CHEMIN - Remonter d'un niveau
require_once __DIR__ . '/../config/database.php';
$pdo = getPDO();

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Vérification / octroi d'accès à un cours protégé ──────────────
$accessMsg  = '';
$accessType = '';  // 'success' | 'danger'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $coursId  = (int)($_POST['cours_id'] ?? 0);
    $codePost = trim($_POST['code_acces'] ?? '');

    if ($coursId && $codePost) {
        $row = $pdo->prepare("SELECT code_acces FROM cours WHERE id=? AND actif=1");
        $row->execute([$coursId]);
        $c = $row->fetch();

        if ($c && $c['code_acces'] && password_verify($codePost, $c['code_acces'])) {
            // Stocker l'accès en session (8 h)
            $key = 'cours_acces_' . $coursId;
            $_SESSION[$key] = time() + 8 * 3600;

            // Persister en base (optionnel, pour les stats)
            try {
                $pdo->prepare("
                    INSERT INTO cours_acces_session (cours_id, session_id, ip, expires_at)
                    VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 8 HOUR))
                    ON DUPLICATE KEY UPDATE granted_at=NOW(), expires_at=DATE_ADD(NOW(), INTERVAL 8 HOUR)
                ")->execute([$coursId, session_id(), sanitize($_SERVER['REMOTE_ADDR'] ?? ''), null]);
            } catch (Exception $e) {
                // Table peut ne pas exister, ignorer l'erreur
            }

            $accessMsg  = 'Accès accordé. Vous pouvez consulter ce cours.';
            $accessType = 'success';
        } else {
            $accessMsg  = 'Code incorrect. Contactez l\'administration.';
            $accessType = 'danger';
        }
    }
}

// ── Filtres ────────────────────────────────────────────────────────
$type   = $_GET['type']   ?? '';
$disc   = (int)($_GET['disc']   ?? 0);
$annee  = (int)($_GET['annee']  ?? 0);
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 12;
$offset = ($page - 1) * $limit;

$where  = ['c.actif = 1'];
$params = [];
if ($type)   { $where[] = 'c.type_fichier = ?'; $params[] = $type; }
if ($disc)   { $where[] = 'c.discipline_id = ?'; $params[] = $disc; }
if ($annee)  { $where[] = 'c.annee_cours = ?'; $params[] = $annee; }
if ($search) { $where[] = '(c.titre LIKE ? OR c.description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
$whereStr = 'WHERE ' . implode(' AND ', $where);

$total = $pdo->prepare("SELECT COUNT(*) FROM cours c $whereStr");
$total->execute($params);
$totalCount = (int)$total->fetchColumn();
$pages = (int)ceil($totalCount / $limit);

$stmt = $pdo->prepare("
  SELECT c.*, d.nom_fr AS discipline_nom
  FROM cours c
  LEFT JOIN disciplines d ON c.discipline_id = d.id
  $whereStr
  ORDER BY c.annee_cours DESC, c.mois_cours DESC, c.created_at DESC
  LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$coursList = $stmt->fetchAll();

$disciplines = $pdo->query("SELECT id, nom_fr FROM disciplines WHERE actif=1 ORDER BY ordre")->fetchAll();
$annees      = $pdo->query("SELECT DISTINCT annee_cours FROM cours WHERE actif=1 AND annee_cours IS NOT NULL ORDER BY annee_cours DESC")->fetchAll(PDO::FETCH_COLUMN);

$typeIcons  = ['pdf'=>'fa-file-pdf','word'=>'fa-file-word','video'=>'fa-video','diapo'=>'fa-file-powerpoint','autre'=>'fa-file'];
$typeLabels = ['pdf'=>'PDF','word'=>'Word','video'=>'Vidéo','diapo'=>'Diaporama','autre'=>'Autre'];

/** Vérifie si la session donne accès à un cours protégé */
function hasCoursAccess(int $id): bool {
    $key = 'cours_acces_' . $id;
    return isset($_SESSION[$key]) && $_SESSION[$key] > time();
}

require_once __DIR__ . '/../includes/header.php';
?>
<main class="py-5">
<div class="container">
  <h1 class="page-title">
    <span data-lang="fr">Cours &amp; Ressources</span>
    <span data-lang="en">Courses &amp; Resources</span>
  </h1>

  <?php if ($accessMsg): ?>
  <div class="alert alert-<?= $accessType === 'success' ? 'success' : 'danger' ?> d-flex align-items-center gap-2 mb-4" role="alert">
    <i class="fas fa-<?= $accessType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
    <?= sanitize($accessMsg) ?>
  </div>
  <?php endif; ?>

  <!-- FILTRES -->
  <div class="cours-filter">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label fw-semibold" style="font-size:.83rem;color:var(--navy)">
          <i class="fas fa-search me-1" style="color:var(--accent)"></i>
          <span data-lang="fr">Rechercher</span><span data-lang="en">Search</span>
        </label>
        <input type="text" name="q" class="form-control-custom" value="<?= htmlspecialchars($search) ?>" placeholder="Titre du cours…">
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label fw-semibold" style="font-size:.83rem;color:var(--navy)">
          <span data-lang="fr">Type</span><span data-lang="en">Type</span>
        </label>
        <select name="type" class="form-control-custom">
          <option value="">Tous</option>
          <?php foreach ($typeLabels as $k => $v): ?>
          <option value="<?= $k ?>" <?= $type===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label fw-semibold" style="font-size:.83rem;color:var(--navy)">
          <span data-lang="fr">Discipline</span><span data-lang="en">Discipline</span>
        </label>
        <select name="disc" class="form-control-custom">
          <option value="0">Toutes</option>
          <?php foreach ($disciplines as $d): ?>
          <option value="<?= $d['id'] ?>" <?= $disc==$d['id']?'selected':'' ?>><?= htmlspecialchars($d['nom_fr']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label fw-semibold" style="font-size:.83rem;color:var(--navy)">Année</label>
        <select name="annee" class="form-control-custom">
          <option value="0">Toutes</option>
          <?php foreach ($annees as $a): ?>
          <option value="<?= $a ?>" <?= $annee==$a?'selected':'' ?>><?= $a ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-1 d-flex gap-2">
        <button type="submit" class="btn-accent w-100"><i class="fas fa-search"></i></button>
        <?php if ($type||$disc||$annee||$search): ?>
        <a href="cours.php" class="btn-outline-navy px-3"><i class="fas fa-times"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- RÉSULTATS -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <p class="mb-0" style="font-size:.85rem;color:var(--muted);">
      <?= $totalCount ?> cours trouvé<?= $totalCount>1?'s':'' ?>
    </p>
  </div>

  <?php if (empty($coursList)): ?>
  <div class="text-center py-5" style="color:var(--muted)">
    <i class="fas fa-folder-open fa-3x mb-3 d-block" style="opacity:.3"></i>
    <p>Aucun cours disponible pour ces critères.</p>
    <a href="cours.php" class="btn-accent mt-2">Voir tous les cours</a>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($coursList as $c):
      $icon       = $typeIcons[$c['type_fichier']] ?? 'fa-file';
      $iconClass  = 'icon-' . $c['type_fichier'];
      $badge      = 'badge-' . $c['type_fichier'];
      $moisNoms   = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
      $dateCours  = ($c['mois_cours'] ? $moisNoms[(int)$c['mois_cours']] . ' ' : '') . ($c['annee_cours'] ?? '');
      $isProtected = !empty($c['code_acces']);
      $isUnlocked  = !$isProtected || hasCoursAccess((int)$c['id']);
    ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="cours-card">
        <div class="p-4">
          <div class="d-flex gap-3 mb-3">
            <div class="cours-icon <?= $iconClass ?>">
              <i class="fas <?= $icon ?>"></i>
            </div>
            <div class="flex-1">
              <span class="badge-type <?= $badge ?>"><?= $typeLabels[$c['type_fichier']] ?? $c['type_fichier'] ?></span>
              <?php if ($c['discipline_nom']): ?>
              <div style="font-size:.72rem;color:var(--accent);font-weight:600;margin-top:4px;"><?= htmlspecialchars($c['discipline_nom']) ?></div>
              <?php endif; ?>
              <?php if ($isProtected): ?>
              <div style="font-size:.7rem;margin-top:4px;">
                <?php if ($isUnlocked): ?>
                  <span style="color:#198754"><i class="fas fa-lock-open me-1"></i>Accès accordé</span>
                <?php else: ?>
                  <span style="color:var(--accent)"><i class="fas fa-lock me-1"></i>Code requis</span>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </div>
          <h5 style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--navy);margin-bottom:8px;line-height:1.4;">
            <?= htmlspecialchars($c['titre']) ?>
          </h5>
          <?php if ($c['description']): ?>
          <p style="font-size:.82rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:12px;">
            <?= htmlspecialchars($c['description']) ?>
          </p>
          <?php endif; ?>
          <div class="cours-meta">
            <?php if ($dateCours): ?><span><i class="fas fa-calendar-alt"></i> <?= $dateCours ?></span><?php endif; ?>
            <span><i class="fas fa-eye"></i> <?= $c['vues'] ?? 0 ?></span>
            <span><i class="fas fa-download"></i> <?= $c['telechargements'] ?? 0 ?></span>
            <?php if (($c['fichier_taille'] ?? 0) > 0): ?>
            <span><i class="fas fa-hdd"></i>
              <?php
              $s = $c['fichier_taille'];
              if ($s >= 1073741824) echo round($s/1073741824,1).' Go';
              elseif ($s >= 1048576) echo round($s/1048576,1).' Mo';
              else echo round($s/1024,1).' Ko';
              ?>
            </span>
            <?php endif; ?>
          </div>
        </div>

        <!-- ZONE D'ACTION -->
        <div class="border-top p-3" style="background:var(--light-bg)">
          <?php if ($isProtected && !$isUnlocked): ?>
            <!-- Formulaire de saisie du code -->
            <form method="POST" class="d-flex gap-2 align-items-center">
              <input type="hidden" name="cours_id" value="<?= $c['id'] ?>">
              <input type="hidden" name="verify_code" value="1">
              <input type="password" name="code_acces" class="form-control-custom flex-1"
                     placeholder="<?= ($c['code_acces_hint'] ?? '') ? htmlspecialchars($c['code_acces_hint']) : 'Code d\'accès…' ?>"
                     style="font-size:.8rem;padding:7px 12px;" required autocomplete="off">
              <button type="submit" class="btn-accent" style="font-size:.8rem;padding:8px 14px;white-space:nowrap;">
                <i class="fas fa-key me-1"></i>Déverrouiller
              </button>
            </form>
            <div style="font-size:.72rem;color:var(--muted);margin-top:6px;">
              <i class="fas fa-info-circle me-1"></i>Code fourni par l'administration après inscription.
            </div>
          <?php elseif ($c['type_fichier'] === 'video' && !empty($c['url_video'])): ?>
            <a href="<?= htmlspecialchars($c['url_video']) ?>" target="_blank"
               class="btn-accent flex-1 d-flex justify-content-center" style="font-size:.8rem;padding:8px 14px;">
              <i class="fas fa-play me-1"></i>
              <span data-lang="fr">Regarder</span><span data-lang="en">Watch</span>
            </a>
          <?php elseif (!empty($c['fichier_path'])): ?>
            <a href="<?= BASE_URL ?>/pages/download.php?id=<?= $c['id'] ?>&type=cours"
               class="btn-accent d-flex justify-content-center" style="font-size:.8rem;padding:8px 14px;">
              <i class="fas fa-download me-1"></i>
              <span data-lang="fr">Télécharger</span><span data-lang="en">Download</span>
            </a>
          <?php else: ?>
            <span style="font-size:.78rem;color:var(--muted);padding:8px;">
              <i class="fas fa-clock me-1"></i>Bientôt disponible
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- PAGINATION -->
  <?php if ($pages > 1): ?>
  <nav class="famako-pagination mt-4">
    <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&type=<?= urlencode($type) ?>&disc=<?= $disc ?>&annee=<?= $annee ?>&q=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
    <a href="?page=<?= $i ?>&type=<?= urlencode($type) ?>&disc=<?= $disc ?>&annee=<?= $annee ?>&q=<?= urlencode($search) ?>" class="<?= $i===$page?'current':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $pages): ?><a href="?page=<?= $page+1 ?>&type=<?= urlencode($type) ?>&disc=<?= $disc ?>&annee=<?= $annee ?>&q=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
  </nav>
  <?php endif; ?>
  <?php endif; ?>
</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>