<?php
/**
 * admin/inscriptions/index.php
 * Gestion des inscriptions — visualisation & validation des documents
 */
$page_title = "Gestion des inscriptions";
require_once __DIR__ . '/../includes/admin_layout.php';
$pdo = getPDO();

// ----------------------------------------------------------------
//  Suppression
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_inscr'])) {
    $id = (int)$_POST['inscr_id'];
    $row = $pdo->prepare("SELECT photo_path, cv_path, diplome_path, lettre_path FROM inscriptions WHERE id=?");
    $row->execute([$id]);
    $files = $row->fetch();
    if ($files) {
        $uploadDir = __DIR__ . '/../../uploads/inscriptions/';
        foreach (['photo_path','cv_path','diplome_path','lettre_path'] as $f) {
            if (!empty($files[$f])) {
                $fp = $uploadDir . $files[$f];
                if (file_exists($fp)) @unlink($fp);
            }
        }
    }
    $pdo->prepare("DELETE FROM inscriptions WHERE id=?")->execute([$id]);
    header('Location: index.php?deleted=1'); exit;
}

// ----------------------------------------------------------------
//  Changement de statut
// ----------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_statut'])) {
    $stmt = $pdo->prepare("
        UPDATE inscriptions
        SET statut=?, notes=?, user_id_traiteur=?, updated_at=NOW()
        WHERE id=?
    ");
    $stmt->execute([
        $_POST['statut'],
        $_POST['notes'],
        $_SESSION['user_id'],
        (int)$_POST['inscr_id']
    ]);
    header('Location: index.php?updated=1'); exit;
}

// ----------------------------------------------------------------
//  Filtres + pagination
// ----------------------------------------------------------------
$statut  = $_GET['statut']  ?? '';
$search  = trim($_GET['q']  ?? '');
$disc    = (int)($_GET['disc'] ?? 0);
$annee   = trim($_GET['annee'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 15;
$offset  = ($page - 1) * $limit;

$where = []; $params = [];
if ($statut) { $where[] = 'i.statut=?'; $params[] = $statut; }
if ($search) {
    $where[] = '(i.nom LIKE ? OR i.prenom LIKE ? OR i.email LIKE ? OR i.matricule LIKE ?)';
    $params  = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
if ($disc)  { $where[] = 'i.discipline_id=?'; $params[] = $disc; }
if ($annee) { $where[] = 'i.matricule LIKE ?'; $params[] = $annee . '%'; }

$ws = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM inscriptions i $ws");
$total->execute($params);
$totalCount = $total->fetchColumn();
$pages = max(1, (int)ceil($totalCount / $limit));

$stmt = $pdo->prepare("
    SELECT i.*, d.nom_fr AS disc
    FROM inscriptions i
    LEFT JOIN disciplines d ON i.discipline_id = d.id
    $ws
    ORDER BY i.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$list = $stmt->fetchAll();

$counts = $pdo->query("SELECT statut, COUNT(*) FROM inscriptions GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$disciplines = $pdo->query("SELECT id, nom_fr FROM disciplines WHERE actif=1 ORDER BY nom_fr")->fetchAll();

$seqStats = [];
try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'matricule_sequences'");
    if ($checkTable->rowCount() > 0) {
        $seqStats = $pdo->query("SELECT prefix, last_seq FROM matricule_sequences ORDER BY prefix DESC")->fetchAll();
    }
} catch (PDOException $e) {
    $seqStats = [];
}

$statusBadge = ['en_attente'=>'badge-diapo','accepte'=>'badge-video','refuse'=>'badge-pdf','en_cours'=>'badge-word'];
$statusLabel = ['en_attente'=>'En attente','accepte'=>'Accepté','refuse'=>'Refusé','en_cours'=>'En cours'];

function decomposeMatricule(string $m): array {
    if (strlen($m) < 10) return [];
    return ['aa'=>substr($m,0,2),'mm'=>substr($m,2,2),'an'=>substr($m,4,2),'nnnn'=>substr($m,6,4)];
}
$mois_abr = ['01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Jun',
             '07'=>'Jul','08'=>'Aoû','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'];

function getDocType(string $path): string {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) return 'image';
    if ($ext === 'pdf') return 'pdf';
    if (in_array($ext, ['doc','docx'])) return 'word';
    return 'other';
}
?>

<!-- CONTENU PRINCIPAL -->
<div style="padding: 24px;">

<?php if (isset($_GET['updated'])): ?>
<div class="alert-famako alert-success mb-3"><i class="fas fa-check-circle"></i> Statut mis à jour.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="alert-famako alert-success mb-3" style="border-left-color:#dc2626;">
  <i class="fas fa-trash-alt" style="color:#dc2626;"></i> Inscription supprimée avec succès.
</div>
<?php endif; ?>

<!-- EN-TÊTE -->
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
  <div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--navy);font-size:1.4rem;margin:0;">
      Inscriptions <span style="color:var(--muted);font-size:.9rem;font-family:'DM Sans',sans-serif;">(<?= $totalCount ?>)</span>
    </h2>
    <p style="font-size:.78rem;color:var(--muted);margin:4px 0 0;">Format matricule :
      <code style="background:var(--light-bg);padding:1px 6px;border-radius:4px;font-size:.8rem;">AA·MM·AN·NNNN</code>
    </p>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php foreach ($statusLabel as $k => $v): ?>
    <a href="?statut=<?= $k ?>" class="badge-type <?= $statusBadge[$k] ?>"
       style="padding:6px 12px;cursor:pointer;text-decoration:none;<?= $statut===$k?'box-shadow:0 0 0 2px var(--navy);':'' ?>">
      <?= $v ?> <span style="opacity:.7;">(<?= $counts[$k] ?? 0 ?>)</span>
    </a>
    <?php endforeach; ?>
    <?php if ($statut || $search || $disc || $annee): ?>
    <a href="index.php" class="btn-outline-navy" style="padding:6px 12px;font-size:.78rem;">
      <i class="fas fa-times me-1"></i>Réinitialiser
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- STATS SÉQUENTIELS -->
<?php if ($seqStats): ?>
<div class="famako-card mb-4 p-3">
  <p style="font-size:.78rem;font-weight:700;color:var(--navy);margin:0 0 8px;">
    <i class="fas fa-hashtag me-1" style="color:var(--accent)"></i>Compteurs séquentiels actifs
  </p>
  <div class="d-flex gap-2 flex-wrap">
    <?php foreach ($seqStats as $s):
        $aa = substr($s['prefix'],0,2); $mm = substr($s['prefix'],2,2); $an = substr($s['prefix'],4,2);
    ?>
    <div style="background:var(--light-bg);border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:.75rem;">
      <span style="font-weight:700;color:var(--navy);">20<?= $aa ?> · <?= $mois_abr[$mm]??$mm ?> · '<?= $an ?></span>
      <span style="color:var(--muted);margin-left:6px;">→</span>
      <span style="color:var(--accent);font-weight:700;margin-left:4px;"><?= str_pad($s['last_seq'],4,'0',STR_PAD_LEFT) ?></span>
      <span style="color:var(--muted);font-size:.7rem;"> / 9999</span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- RECHERCHE -->
<div class="famako-card p-3 mb-4">
  <form method="GET" class="d-flex gap-3 align-items-end flex-wrap">
    <div>
      <label style="font-size:.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:4px;">Recherche</label>
      <input type="text" name="q" class="form-control-custom" value="<?= htmlspecialchars($search) ?>" placeholder="Nom, prénom, email, matricule…" style="width:240px;">
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:4px;">Discipline</label>
      <select name="disc" class="form-control-custom" style="width:180px;">
        <option value="">Toutes</option>
        <?php foreach ($disciplines as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $disc===$d['id']?'selected':'' ?>><?= htmlspecialchars($d['nom_fr']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label style="font-size:.8rem;font-weight:600;color:var(--navy);display:block;margin-bottom:4px;">Année inscr.</label>
      <input type="text" name="annee" class="form-control-custom" value="<?= htmlspecialchars($annee) ?>" placeholder="ex: 26" maxlength="2" style="width:80px;">
    </div>
    <?php if ($statut): ?><input type="hidden" name="statut" value="<?= htmlspecialchars($statut) ?>"><?php endif; ?>
    <button type="submit" class="btn-accent" style="padding:10px 18px;"><i class="fas fa-search me-1"></i>Filtrer</button>
  </form>
</div>

<!-- TABLEAU -->
<div class="famako-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Matricule</th>
          <th>Candidat</th>
          <th>Email / Contact</th>
          <th>Discipline</th>
          <th>Date dépôt</th>
          <th>Statut</th>
          <th style="min-width:130px;">Documents</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $r):
            $dc = decomposeMatricule($r['matricule'] ?? '');

            $docsDef = [
                ['key'=>'photo',   'path'=>$r['photo_path'],   'label'=>"Photo d'identité", 'icon'=>'fa-camera',      'color'=>'var(--accent)'],
                ['key'=>'cv',      'path'=>$r['cv_path'],       'label'=>'CV',               'icon'=>'fa-file-alt',    'color'=>'var(--info)'],
                ['key'=>'diplome', 'path'=>$r['diplome_path'],  'label'=>'Diplôme',          'icon'=>'fa-certificate', 'color'=>'var(--success)'],
                ['key'=>'lettre',  'path'=>$r['lettre_path'],   'label'=>'Lettre de motiv.', 'icon'=>'fa-pen',         'color'=>'var(--warning)'],
            ];
            $docs_dispo = array_filter($docsDef, fn($d) => !empty($d['path']));
            $docs_dispo = array_values($docs_dispo);
            $nbDocs = count($docs_dispo);

            $docsJson = json_encode(array_map(fn($d) => [
                'label' => $d['label'],
                'icon'  => $d['icon'],
                'color' => $d['color'],
                'path'  => $d['path'],
                'type'  => getDocType($d['path']),
                'url'   => BASE_URL . '/uploads/inscriptions/' . rawurlencode($d['path']),
            ], $docs_dispo), JSON_HEX_QUOT | JSON_HEX_APOS);
        ?>
        <tr>
          <!-- MATRICULE -->
          <td>
            <div style="font-family:'Playfair Display',serif;font-size:.95rem;font-weight:900;color:var(--navy);letter-spacing:.06em;">
              <?= htmlspecialchars($r['matricule'] ?? '—') ?>
            </div>
            <?php if ($dc): ?>
            <div style="display:flex;gap:4px;margin-top:4px;flex-wrap:wrap;">
              <?php foreach ([
                ['20'.$dc['aa'],'Insc.','var(--accent)'],
                [$mois_abr[$dc['mm']]??$dc['mm'],'M.n.','var(--info)'],
                ["'".$dc['an'],'An.n.','var(--success)'],
                ['#'.$dc['nnnn'],'Seq.','var(--warning)'],
              ] as [$v,$l,$c]): ?>
              <span title="<?= $l ?>" style="font-size:.6rem;background:var(--light-bg);border-radius:4px;padding:1px 5px;color:<?= $c ?>;font-weight:700;"><?= $v ?></span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </td>

          <!-- CANDIDAT -->
          <td>
            <strong style="font-size:.88rem;color:var(--navy);"><?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?></strong><br>
            <small style="color:var(--muted);">
              <?= htmlspecialchars($r['pays'] ?? '') ?>
              <?php if ($r['date_naissance']): ?> · né le <?= date('d/m/Y', strtotime($r['date_naissance'])) ?><?php endif; ?>
            </small>
          </td>

          <td style="font-size:.8rem;">
            <?= htmlspecialchars($r['email']) ?>
            <?php if ($r['contact']): ?><br><span style="color:var(--muted);"><?= htmlspecialchars($r['contact']) ?></span><?php endif; ?>
          </td>

          <td style="font-size:.82rem;"><?= htmlspecialchars($r['disc'] ?? '—') ?></td>

          <td style="font-size:.78rem;white-space:nowrap;color:var(--muted);">
            <?= date('d/m/Y', strtotime($r['created_at'])) ?><br><?= date('H:i', strtotime($r['created_at'])) ?>
          </td>

          <td>
            <span class="badge-type <?= $statusBadge[$r['statut']] ?? '' ?>"><?= $statusLabel[$r['statut']] ?? $r['statut'] ?></span>
          </td>

          <!-- DOCUMENTS -->
          <td>
            <?php if ($nbDocs > 0): ?>
            <button
              onclick='openDocs(<?= $r["id"] ?>, <?= json_encode($r["prenom"]." ".$r["nom"]) ?>, <?= $docsJson ?>)'
              class="docs-btn"
              style="display:inline-flex;align-items:center;gap:7px;background:var(--navy);color:#fff;border:none;border-radius:9px;padding:7px 13px;font-size:.76rem;cursor:pointer;font-family:inherit;font-weight:600;transition:all .2s;white-space:nowrap;">
              <i class="fas fa-folder-open"></i>
              Voir <?= $nbDocs ?> doc<?= $nbDocs>1?'s':'' ?>
              <span style="display:flex;gap:3px;opacity:.7;">
                <?php foreach ($docs_dispo as $d): ?>
                <i class="fas <?= $d['icon'] ?>" style="font-size:.65rem;color:<?= $d['color'] ?>;"></i>
                <?php endforeach; ?>
              </span>
            </button>
            <?php else: ?>
            <span style="font-size:.75rem;color:var(--muted);font-style:italic;"><i class="fas fa-minus me-1"></i>Aucun fichier</span>
            <?php endif; ?>
          </td>

          <!-- ACTIONS -->
          <td>
            <div style="position:relative;display:inline-block;">
              <button
                onclick="toggleActionMenu(event, <?= $r['id'] ?>)"
                class="btn-accent"
                style="padding:5px 12px;font-size:.75rem;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-cog"></i> Actions <i class="fas fa-chevron-down" style="font-size:.6rem;"></i>
              </button>
              <div id="actionMenu_<?= $r['id'] ?>" style="display:none;position:absolute;right:0;top:calc(100% + 6px);background:#fff;border:1px solid #dde3ee;border-radius:12px;box-shadow:0 10px 30px rgba(10,35,66,.18);z-index:500;min-width:185px;overflow:hidden;">
                <div style="padding:6px;">
                  <!-- Modifier statut -->
                  <button
                    onclick="closeAllMenus(); openModal(
                      <?= $r['id'] ?>,
                      <?= json_encode($r['prenom'].' '.$r['nom']) ?>,
                      <?= json_encode($r['matricule'] ?? '') ?>,
                      <?= json_encode($r['statut']) ?>,
                      <?= json_encode($r['notes'] ?? '') ?>
                    )"
                    style="display:flex;align-items:center;gap:10px;width:100%;background:transparent;border:none;padding:9px 12px;border-radius:8px;cursor:pointer;font-family:inherit;font-size:.8rem;font-weight:600;color:var(--navy);text-align:left;transition:background .15s;">
                    <span style="width:28px;height:28px;background:#eef2ff;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                      <i class="fas fa-edit" style="color:var(--accent);font-size:.75rem;"></i>
                    </span>
                    Modifier le statut
                  </button>
                  <div style="height:1px;background:#f0f4fa;margin:3px 6px;"></div>
                  <!-- Supprimer -->
                  <button
                    onclick="closeAllMenus(); openDeleteModal(
                      <?= $r['id'] ?>,
                      <?= json_encode($r['prenom'].' '.$r['nom']) ?>,
                      <?= json_encode($r['matricule'] ?? '') ?>
                    )"
                    style="display:flex;align-items:center;gap:10px;width:100%;background:transparent;border:none;padding:9px 12px;border-radius:8px;cursor:pointer;font-family:inherit;font-size:.8rem;font-weight:600;color:#dc2626;text-align:left;transition:background .15s;">
                    <span style="width:28px;height:28px;background:#fff1f1;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                      <i class="fas fa-trash-alt" style="color:#dc2626;font-size:.75rem;"></i>
                    </span>
                    Supprimer
                  </button>
                </div>
              </div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($list)): ?>
        <tr><td colspan="8" class="text-center py-4" style="color:var(--muted);">Aucune inscription trouvée</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($pages > 1):
    $qs = http_build_query(array_filter(['statut'=>$statut,'q'=>$search,'disc'=>$disc,'annee'=>$annee]));
  ?>
  <div class="famako-pagination p-3">
    <?php if ($page > 1): ?><a href="?page=<?=$page-1?>&<?=$qs?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for ($i=max(1,$page-2);$i<=min($pages,$page+2);$i++): ?>
    <a href="?page=<?=$i?>&<?=$qs?>" class="<?=$i===$page?'current':''?>"><?=$i?></a>
    <?php endfor; ?>
    <?php if ($page < $pages): ?><a href="?page=<?=$page+1?>&<?=$qs?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

</div><!-- fin contenu principal -->

<!-- ================================================================
     MODAL VISIONNEUR DE DOCUMENTS
================================================================ -->
<div id="docsModal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(5,15,35,.75);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:18px;box-shadow:0 25px 60px rgba(10,35,66,.35);width:94vw;max-width:960px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;">

    <!-- En-tête modal docs -->
    <div style="padding:16px 20px;border-bottom:1px solid #eef2f9;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:var(--navy,#0a2342);">
      <div>
        <p style="margin:0;font-size:.7rem;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.08em;">Documents de</p>
        <h3 id="docsModalName" style="margin:2px 0 0;font-size:1rem;font-weight:700;color:#fff;font-family:'Playfair Display',serif;"></h3>
      </div>
      <button onclick="closeDocsModal()" style="background:rgba(255,255,255,.12);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1.1rem;display:flex;align-items:center;justify-content:center;transition:background .2s;" onmouseover="this.style.background='rgba(255,255,255,.22)'" onmouseout="this.style.background='rgba(255,255,255,.12)'">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Onglets documents -->
    <div id="docsTabs" style="display:flex;gap:0;border-bottom:1px solid #eef2f9;flex-shrink:0;overflow-x:auto;background:#f8faff;"></div>

    <!-- Zone de prévisualisation -->
    <div id="docsPreview" style="flex:1;overflow:auto;display:flex;align-items:center;justify-content:center;background:#f0f4fa;min-height:350px;padding:20px;"></div>

    <!-- Pied de modal docs -->
    <div style="padding:12px 20px;border-top:1px solid #eef2f9;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;background:#fff;">
      <span id="docsCurrentLabel" style="font-size:.8rem;color:var(--muted);"></span>
      <a id="docsDownloadLink" href="#" download target="_blank"
        style="display:inline-flex;align-items:center;gap:8px;background:var(--navy,#0a2342);color:#fff;padding:8px 18px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;transition:opacity .2s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <i class="fas fa-download"></i> Télécharger
      </a>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL CHANGEMENT DE STATUT
================================================================ -->
<div id="statusModal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(5,15,35,.65);backdrop-filter:blur(3px);align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:18px;box-shadow:0 25px 60px rgba(10,35,66,.3);width:94vw;max-width:480px;overflow:hidden;">
    <!-- En-tête -->
    <div style="padding:18px 22px;border-bottom:1px solid #eef2f9;display:flex;align-items:center;justify-content:space-between;background:var(--navy,#0a2342);">
      <div>
        <p style="margin:0;font-size:.68rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;">Modification du statut</p>
        <h3 id="statusModalName" style="margin:3px 0 0;font-size:.95rem;font-weight:700;color:#fff;font-family:'Playfair Display',serif;"></h3>
        <p id="statusModalMatricule" style="margin:2px 0 0;font-size:.72rem;color:rgba(255,255,255,.55);"></p>
      </div>
      <button onclick="closeModal()" style="background:rgba(255,255,255,.12);border:none;color:#fff;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;" onmouseover="this.style.background='rgba(255,255,255,.22)'" onmouseout="this.style.background='rgba(255,255,255,.12)'">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <!-- Corps du formulaire -->
    <form method="POST" style="padding:22px;">
      <input type="hidden" name="change_statut" value="1">
      <input type="hidden" name="inscr_id" id="statusModalId">

      <div style="margin-bottom:18px;">
        <label style="font-size:.8rem;font-weight:700;color:var(--navy);display:block;margin-bottom:8px;">
          <i class="fas fa-tag me-1" style="color:var(--accent);"></i>Nouveau statut
        </label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;" id="statusOptions">
          <?php foreach ($statusLabel as $k => $v): ?>
          <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:2px solid #e8edf5;border-radius:10px;cursor:pointer;transition:all .15s;font-size:.82rem;font-weight:600;">
            <input type="radio" name="statut" value="<?= $k ?>" style="accent-color:var(--navy);" onchange="highlightStatus(this)">
            <span><?= $v ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="margin-bottom:20px;">
        <label style="font-size:.8rem;font-weight:700;color:var(--navy);display:block;margin-bottom:6px;">
          <i class="fas fa-sticky-note me-1" style="color:var(--accent);"></i>Notes internes
        </label>
        <textarea name="notes" id="statusModalNotes" rows="4"
          style="width:100%;border:1.5px solid #dde3ee;border-radius:10px;padding:10px 14px;font-family:inherit;font-size:.83rem;color:var(--navy);resize:vertical;outline:none;transition:border-color .2s;box-sizing:border-box;"
          placeholder="Observations, motif de décision…"
          onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='#dde3ee'"></textarea>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="closeModal()"
          style="padding:10px 20px;border:1.5px solid #dde3ee;background:transparent;border-radius:9px;font-family:inherit;font-size:.83rem;font-weight:600;color:var(--muted);cursor:pointer;transition:all .15s;"
          onmouseover="this.style.borderColor='var(--navy)';this.style.color='var(--navy)'"
          onmouseout="this.style.borderColor='#dde3ee';this.style.color='var(--muted)'">
          Annuler
        </button>
        <button type="submit"
          style="padding:10px 24px;background:var(--navy,#0a2342);color:#fff;border:none;border-radius:9px;font-family:inherit;font-size:.83rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:opacity .2s;"
          onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
          <i class="fas fa-save"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ================================================================
     MODAL SUPPRESSION
================================================================ -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;z-index:9000;background:rgba(5,15,35,.65);backdrop-filter:blur(3px);align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:18px;box-shadow:0 25px 60px rgba(10,35,66,.3);width:94vw;max-width:420px;overflow:hidden;">
    <!-- En-tête -->
    <div style="padding:18px 22px;border-bottom:1px solid #fde8e8;background:#fff1f1;display:flex;align-items:center;justify-content:space-between;">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="width:40px;height:40px;background:#fde8e8;border-radius:50%;display:flex;align-items:center;justify-content:center;">
          <i class="fas fa-exclamation-triangle" style="color:#dc2626;font-size:1rem;"></i>
        </div>
        <div>
          <h3 style="margin:0;font-size:.95rem;font-weight:700;color:#dc2626;">Confirmer la suppression</h3>
          <p style="margin:2px 0 0;font-size:.72rem;color:#9ca3af;">Cette action est irréversible</p>
        </div>
      </div>
      <button onclick="closeDeleteModal()" style="background:#fde8e8;border:none;color:#dc2626;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <!-- Corps -->
    <div style="padding:22px;">
      <p style="font-size:.88rem;color:var(--navy);margin:0 0 6px;">
        Vous êtes sur le point de supprimer l'inscription de :
      </p>
      <div style="background:#f8faff;border:1px solid #dde3ee;border-radius:10px;padding:12px 16px;margin-bottom:18px;">
        <p id="deleteModalName" style="margin:0;font-size:.95rem;font-weight:700;color:var(--navy);"></p>
        <p style="margin:3px 0 0;font-size:.78rem;color:var(--muted);">
          Matricule : <span id="deleteModalMatricule" style="font-weight:600;"></span>
        </p>
      </div>
      <p style="font-size:.8rem;color:#dc2626;margin:0 0 20px;background:#fff1f1;padding:10px 14px;border-radius:8px;border-left:3px solid #dc2626;">
        <i class="fas fa-exclamation-circle me-1"></i>
        Tous les fichiers (photo, CV, diplôme, lettre) seront définitivement supprimés.
      </p>
      <form method="POST" style="display:flex;gap:10px;justify-content:flex-end;">
        <input type="hidden" name="delete_inscr" value="1">
        <input type="hidden" name="inscr_id" id="deleteModalId">
        <button type="button" onclick="closeDeleteModal()"
          style="padding:10px 20px;border:1.5px solid #dde3ee;background:transparent;border-radius:9px;font-family:inherit;font-size:.83rem;font-weight:600;color:var(--muted);cursor:pointer;transition:all .15s;"
          onmouseover="this.style.borderColor='var(--navy)';this.style.color='var(--navy)'"
          onmouseout="this.style.borderColor='#dde3ee';this.style.color='var(--muted)'">
          Annuler
        </button>
        <button type="submit"
          style="padding:10px 24px;background:#dc2626;color:#fff;border:none;border-radius:9px;font-family:inherit;font-size:.83rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:opacity .2s;"
          onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
          <i class="fas fa-trash-alt"></i> Supprimer définitivement
        </button>
      </form>
    </div>
  </div>
</div>

<!-- ================================================================
     FERMETURE layout
================================================================ -->
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ----------------------------------------------------------------
   SIDEBAR TOGGLE (mobile)
---------------------------------------------------------------- */
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
  document.getElementById('adminSidebar').classList.toggle('open');
});

/* ----------------------------------------------------------------
   MENU DÉROULANT ACTIONS
---------------------------------------------------------------- */
function toggleActionMenu(e, id) {
  e.stopPropagation();
  const alreadyOpen = document.getElementById('actionMenu_' + id).style.display === 'block';
  closeAllMenus();
  if (!alreadyOpen) {
    document.getElementById('actionMenu_' + id).style.display = 'block';
  }
}

function closeAllMenus() {
  document.querySelectorAll('[id^="actionMenu_"]').forEach(m => m.style.display = 'none');
}

document.addEventListener('click', closeAllMenus);


/* ================================================================
   MODAL VISIONNEUR DE DOCUMENTS
================================================================ */
let currentDocs = [];
let currentDocIndex = 0;

function openDocs(id, name, docs) {
  currentDocs = docs;
  currentDocIndex = 0;

  document.getElementById('docsModalName').textContent = name;

  // Construire les onglets
  const tabsEl = document.getElementById('docsTabs');
  tabsEl.innerHTML = '';
  docs.forEach(function(doc, i) {
    const btn = document.createElement('button');
    btn.style.cssText = 'display:inline-flex;align-items:center;gap:7px;padding:11px 18px;border:none;background:transparent;cursor:pointer;font-family:inherit;font-size:.78rem;font-weight:600;color:#6b7a99;border-bottom:2px solid transparent;transition:all .18s;white-space:nowrap;';
    btn.innerHTML = '<i class="fas ' + doc.icon + '" style="color:' + doc.color + ';font-size:.8rem;"></i>' + doc.label;
    btn.id = 'docTab_' + i;
    btn.onclick = function() { showDoc(i); };
    tabsEl.appendChild(btn);
  });

  document.getElementById('docsModal').style.display = 'flex';
  showDoc(0);
}

function showDoc(index) {
  currentDocIndex = index;
  const doc = currentDocs[index];

  // Mettre à jour les onglets actifs
  currentDocs.forEach(function(_, i) {
    const tab = document.getElementById('docTab_' + i);
    if (!tab) return;
    if (i === index) {
      tab.style.color = 'var(--navy, #0a2342)';
      tab.style.borderBottomColor = 'var(--accent, #2563eb)';
      tab.style.background = '#fff';
    } else {
      tab.style.color = '#6b7a99';
      tab.style.borderBottomColor = 'transparent';
      tab.style.background = 'transparent';
    }
  });

  // Mettre à jour le lien de téléchargement
  const dlLink = document.getElementById('docsDownloadLink');
  dlLink.href = doc.url;
  dlLink.download = doc.path;
  document.getElementById('docsCurrentLabel').textContent = doc.label + '  ·  ' + doc.path;

  // Afficher le contenu
  const preview = document.getElementById('docsPreview');
  preview.innerHTML = '';

  if (doc.type === 'image') {
    // ---- Prévisualisation image ----
    const wrapper = document.createElement('div');
    wrapper.style.cssText = 'text-align:center;max-width:100%;';

    const loader = document.createElement('div');
    loader.style.cssText = 'font-size:.8rem;color:#9ca3af;padding:40px;';
    loader.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Chargement…';
    wrapper.appendChild(loader);

    const img = document.createElement('img');
    img.style.cssText = 'max-width:100%;max-height:65vh;border-radius:10px;box-shadow:0 6px 24px rgba(10,35,66,.18);display:none;';
    img.onload = function() {
      loader.style.display = 'none';
      img.style.display = 'block';
    };
    img.onerror = function() {
      loader.innerHTML = '<i class="fas fa-image me-2" style="color:#9ca3af;"></i>Image non disponible — vérifiez le chemin du fichier.';
    };
    img.src = doc.url;
    wrapper.appendChild(img);
    preview.appendChild(wrapper);

  } else if (doc.type === 'pdf') {
    // ---- Prévisualisation PDF via iframe ----
    const wrapper = document.createElement('div');
    wrapper.style.cssText = 'width:100%;height:65vh;display:flex;flex-direction:column;';

    // Fallback si l'iframe ne charge pas
    const fallback = document.createElement('div');
    fallback.style.cssText = 'display:none;text-align:center;padding:40px;';
    fallback.innerHTML =
      '<i class="fas fa-file-pdf" style="font-size:3rem;color:#dc2626;display:block;margin-bottom:12px;"></i>' +
      '<p style="font-size:.85rem;color:#6b7a99;margin:0 0 14px;">La prévisualisation inline n\'est pas disponible dans ce navigateur.</p>' +
      '<a href="' + doc.url + '" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:var(--navy,#0a2342);color:#fff;padding:10px 20px;border-radius:8px;font-size:.83rem;font-weight:600;text-decoration:none;">' +
      '<i class="fas fa-external-link-alt"></i> Ouvrir dans un nouvel onglet</a>';

    const iframe = document.createElement('iframe');
    iframe.src = doc.url + '#toolbar=1&navpanes=0&scrollbar=1&view=FitH';
    iframe.style.cssText = 'width:100%;flex:1;border:none;border-radius:10px;background:#fff;';
    iframe.onerror = function() {
      iframe.style.display = 'none';
      fallback.style.display = 'block';
    };

    // Bouton ouvrir dans un onglet
    const openBtn = document.createElement('div');
    openBtn.style.cssText = 'padding:8px 0 0;text-align:right;';
    openBtn.innerHTML = '<a href="' + doc.url + '" target="_blank" style="font-size:.75rem;color:var(--accent,#2563eb);text-decoration:none;"><i class="fas fa-external-link-alt me-1"></i>Ouvrir dans un onglet</a>';

    wrapper.appendChild(iframe);
    wrapper.appendChild(fallback);
    wrapper.appendChild(openBtn);
    preview.appendChild(wrapper);

  } else if (doc.type === 'word') {
    // ---- Fichier Word : pas de preview native, proposer téléchargement ----
    const el = document.createElement('div');
    el.style.cssText = 'text-align:center;padding:50px 30px;';
    el.innerHTML =
      '<div style="width:72px;height:72px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">' +
      '<i class="fas fa-file-word" style="font-size:2rem;color:#2563eb;"></i></div>' +
      '<p style="font-size:.9rem;font-weight:700;color:var(--navy,#0a2342);margin:0 0 6px;">' + doc.label + '</p>' +
      '<p style="font-size:.8rem;color:#6b7a99;margin:0 0 22px;">Les fichiers Word ne peuvent pas être prévisualisés directement.<br>Téléchargez le fichier ou ouvrez-le dans Google Docs.</p>' +
      '<div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">' +
      '<a href="' + doc.url + '" download style="display:inline-flex;align-items:center;gap:8px;background:var(--navy,#0a2342);color:#fff;padding:10px 20px;border-radius:8px;font-size:.82rem;font-weight:600;text-decoration:none;">' +
      '<i class="fas fa-download"></i> Télécharger</a>' +
      '<a href="https://docs.google.com/viewer?url=' + encodeURIComponent(doc.url) + '" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#16a34a;color:#fff;padding:10px 20px;border-radius:8px;font-size:.82rem;font-weight:600;text-decoration:none;">' +
      '<i class="fas fa-eye"></i> Voir via Google Docs</a>' +
      '</div>';
    preview.appendChild(el);

  } else {
    // ---- Fichier autre ----
    const el = document.createElement('div');
    el.style.cssText = 'text-align:center;padding:50px 30px;';
    el.innerHTML =
      '<div style="width:72px;height:72px;background:#f0f4fa;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">' +
      '<i class="fas fa-file" style="font-size:2rem;color:#6b7a99;"></i></div>' +
      '<p style="font-size:.9rem;font-weight:700;color:var(--navy,#0a2342);margin:0 0 6px;">' + doc.label + '</p>' +
      '<p style="font-size:.8rem;color:#6b7a99;margin:0 0 22px;">Prévisualisation non disponible pour ce type de fichier.</p>' +
      '<a href="' + doc.url + '" download style="display:inline-flex;align-items:center;gap:8px;background:var(--navy,#0a2342);color:#fff;padding:10px 20px;border-radius:8px;font-size:.82rem;font-weight:600;text-decoration:none;">' +
      '<i class="fas fa-download"></i> Télécharger</a>';
    preview.appendChild(el);
  }
}

function closeDocsModal() {
  document.getElementById('docsModal').style.display = 'none';
  document.getElementById('docsPreview').innerHTML = '';
  currentDocs = [];
}

// Navigation clavier dans le visionneur (flèches gauche/droite)
document.addEventListener('keydown', function(e) {
  const modal = document.getElementById('docsModal');
  if (modal.style.display !== 'flex') return;
  if (e.key === 'ArrowRight' && currentDocIndex < currentDocs.length - 1) showDoc(currentDocIndex + 1);
  if (e.key === 'ArrowLeft'  && currentDocIndex > 0) showDoc(currentDocIndex - 1);
  if (e.key === 'Escape') closeDocsModal();
});

// Clic en dehors du modal → fermeture
document.getElementById('docsModal').addEventListener('click', function(e) {
  if (e.target === this) closeDocsModal();
});


/* ================================================================
   MODAL STATUT
================================================================ */
function openModal(id, name, matricule, statut, notes) {
  document.getElementById('statusModalId').value   = id;
  document.getElementById('statusModalName').textContent      = name;
  document.getElementById('statusModalMatricule').textContent = matricule ? 'Matricule : ' + matricule : '';
  document.getElementById('statusModalNotes').value = notes || '';

  // Cocher le bon radio et le mettre en évidence
  document.querySelectorAll('#statusOptions input[type=radio]').forEach(function(radio) {
    radio.checked = (radio.value === statut);
    const label = radio.closest('label');
    if (label) {
      label.style.borderColor = radio.checked ? 'var(--navy, #0a2342)' : '#e8edf5';
      label.style.background  = radio.checked ? '#f0f4ff' : 'transparent';
    }
  });

  document.getElementById('statusModal').style.display = 'flex';
}

function closeModal() {
  document.getElementById('statusModal').style.display = 'none';
}

function highlightStatus(radio) {
  document.querySelectorAll('#statusOptions label').forEach(function(label) {
    const r = label.querySelector('input');
    label.style.borderColor = (r && r === radio) ? 'var(--navy, #0a2342)' : '#e8edf5';
    label.style.background  = (r && r === radio) ? '#f0f4ff' : 'transparent';
  });
}

document.getElementById('statusModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Touche Echap
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeModal();
    closeDeleteModal();
    closeDocsModal();
  }
});


/* ================================================================
   MODAL SUPPRESSION
================================================================ */
function openDeleteModal(id, name, matricule) {
  document.getElementById('deleteModalId').value               = id;
  document.getElementById('deleteModalName').textContent       = name;
  document.getElementById('deleteModalMatricule').textContent  = matricule || '—';
  document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
}

document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) closeDeleteModal();
});
</script>

</body>
</html>