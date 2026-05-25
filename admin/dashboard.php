<?php
$page_title = "Tableau de bord";
require_once __DIR__ . '/includes/admin_layout.php';
$pdo = getPDO();
$stats = [
  'cours'         => $pdo->query("SELECT COUNT(*) FROM cours")->fetchColumn(),
  'inscriptions'  => $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn(),
  'en_attente'    => $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE statut='en_attente'")->fetchColumn(),
  'biblio'        => $pdo->query("SELECT COUNT(*) FROM bibliotheque")->fetchColumn(),
  'users'         => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
  'disciplines'   => $pdo->query("SELECT COUNT(*) FROM disciplines WHERE actif=1")->fetchColumn(),
  'td'            => $pdo->query("SELECT COUNT(*) FROM td")->fetchColumn(),
  'td_actif'      => $pdo->query("SELECT COUNT(*) FROM td WHERE actif=1")->fetchColumn(),
];
$recent_inscriptions = $pdo->query("SELECT i.*, d.nom_fr as disc FROM inscriptions i LEFT JOIN disciplines d ON i.discipline_id=d.id ORDER BY i.created_at DESC LIMIT 8")->fetchAll();
$recent_cours = $pdo->query("SELECT c.*, d.nom_fr as disc FROM cours c LEFT JOIN disciplines d ON c.discipline_id=d.id ORDER BY c.created_at DESC LIMIT 5")->fetchAll();
// 5 derniers TD
$recent_tds = $pdo->query("SELECT t.*, d.nom_fr AS disc FROM td t LEFT JOIN disciplines d ON d.id = t.discipline_id ORDER BY t.created_at DESC LIMIT 5")->fetchAll();
$statColors = ['en_attente'=>'warning','accepte'=>'success','refuse'=>'danger','en_cours'=>'info'];
?>

<!-- ── Stat Cards ──────────────────────────────────────────────────────────── -->
<div class="row g-4 mb-4">
  <?php foreach([
    ['fa-play-circle',    'bg-navy',      'Cours',        'cours',        '#0a2342','#c9a84c'],
    ['fa-user-graduate',  'bg-accent',    'Inscriptions', 'inscriptions', '#c9a84c','#0a2342'],
    ['fa-clock',          'bg-warning',   'En attente',   'en_attente',   '#c9820a','#fff'],
    ['fa-book-open',      'bg-info',      'Bibliothèque', 'biblio',       '#1a5f8a','#fff'],
    ['fa-users',          'bg-success',   'Utilisateurs', 'users',        '#1a7f5a','#fff'],
    ['fa-graduation-cap', 'bg-secondary', 'Disciplines',  'disciplines',  '#495057','#fff'],
    ['fa-file-alt',       'bg-navy',      'TD (total)',   'td',           '#0a2342','#c9a84c'],
    ['fa-check-circle',   'bg-success',   'TD actifs',    'td_actif',     '#1a7f5a','#fff'],
  ] as [$ic,$bg,$label,$key,$bg2,$fg]): ?>
  <div class="col-6 col-md-4 col-xl-3">
    <div class="admin-stat-card <?= $key === 'td' || $key === 'td_actif' ? 'td-stat-card' : '' ?>"
         <?= in_array($key, ['td','td_actif']) ? "onclick=\"window.location='".BASE_URL."/admin/td/index.php'\" style=\"cursor:pointer\"" : '' ?>>
      <div class="admin-stat-icon" style="background:<?= $bg2 ?>;color:<?= $fg ?>"><i class="fas <?= $ic ?>"></i></div>
      <div>
        <div class="admin-stat-num"><?= $stats[$key] ?></div>
        <div class="admin-stat-lbl"><?= $label ?></div>
      </div>
      <?php if (in_array($key, ['td','td_actif'])): ?>
        <div class="td-badge-link"><i class="fas fa-arrow-right"></i></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<style>
.td-stat-card { position:relative; transition:box-shadow .18s, transform .15s; }
.td-stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(10,35,66,.18); }
.td-badge-link {
  position:absolute; top:.75rem; right:.85rem;
  color:var(--accent, #c9a84c); font-size:.8rem; opacity:.7;
}
.td-stat-card:hover .td-badge-link { opacity:1; }
</style>

<!-- ── Main Row ────────────────────────────────────────────────────────────── -->
<div class="row g-4">

  <!-- INSCRIPTIONS RÉCENTES -->
  <div class="col-lg-8">
    <div class="famako-card">
      <div class="card-header-custom">
        <span class="card-title" style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--navy);">
          <i class="fas fa-user-graduate me-2" style="color:var(--accent)"></i>Inscriptions récentes
        </span>
        <a href="<?= BASE_URL ?>/admin/inscriptions/index.php" style="font-size:.8rem;color:var(--accent)">Voir tout →</a>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Candidat</th><th>Discipline</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($recent_inscriptions as $r): ?>
            <tr>
              <td>
                <strong style="color:var(--navy);font-size:.88rem;"><?= htmlspecialchars($r['prenom'].' '.$r['nom']) ?></strong>
                <br><small><?= htmlspecialchars($r['email']) ?></small>
              </td>
              <td style="font-size:.82rem;"><?= htmlspecialchars($r['disc'] ?? '—') ?></td>
              <td style="font-size:.8rem;"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
              <td>
                <span class="badge-type badge-<?= $r['statut']==='accepte'?'video':($r['statut']==='refuse'?'pdf':($r['statut']==='en_cours'?'word':'diapo')) ?>">
                  <?= ucfirst(str_replace('_',' ',$r['statut'])) ?>
                </span>
              </td>
              <td>
                <a href="<?= BASE_URL ?>/admin/inscriptions/voir.php?id=<?= $r['id'] ?>"
                   class="btn-accent" style="padding:4px 10px;font-size:.75rem;border-radius:6px;">
                  <i class="fas fa-eye"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_inscriptions)): ?>
            <tr><td colspan="5" class="text-center py-3" style="color:var(--muted);">Aucune inscription</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- COLONNE DROITE : Cours + TD accès rapide -->
  <div class="col-lg-4 d-flex flex-column gap-4">

    <!-- COURS RÉCENTS -->
    <div class="famako-card">
      <div class="card-header-custom">
        <span style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--navy);">
          <i class="fas fa-play-circle me-2" style="color:var(--accent)"></i>Cours récents
        </span>
        <a href="<?= BASE_URL ?>/admin/cours/index.php" style="font-size:.8rem;color:var(--accent)">Voir tout →</a>
      </div>
      <div class="card-body-custom">
        <?php if (empty($recent_cours)): ?>
          <p class="text-center text-muted py-3">Aucun cours</p>
        <?php else: ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($recent_cours as $c):
            $icons = ['pdf'=>'fa-file-pdf','word'=>'fa-file-word','video'=>'fa-video','diapo'=>'fa-file-powerpoint','autre'=>'fa-file'];
          ?>
          <div class="d-flex gap-3 align-items-start">
            <div class="cours-icon icon-<?= $c['type_fichier'] ?>" style="width:38px;height:38px;font-size:1rem;flex-shrink:0;border-radius:8px;">
              <i class="fas <?= $icons[$c['type_fichier']] ?? 'fa-file' ?>"></i>
            </div>
            <div class="flex-1" style="min-width:0;">
              <div style="font-size:.85rem;font-weight:600;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($c['titre']) ?>
              </div>
              <div style="font-size:.75rem;color:var(--muted);">
                <?= htmlspecialchars($c['disc'] ?? '—') ?> · <?= $c['annee_cours'] ?? '' ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/admin/cours/ajouter.php"
           class="btn-accent w-100 justify-content-center mt-3" style="font-size:.84rem;padding:10px;">
          <i class="fas fa-plus me-2"></i>Ajouter un cours
        </a>
      </div>
    </div>

    <!-- ── TRAVAUX DIRIGÉS – accès rapide ────────────────────────────── -->
    <div class="famako-card" style="border-top:3px solid var(--navy,#0a2342);">
      <div class="card-header-custom">
        <span style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--navy);">
          <i class="fas fa-file-alt me-2" style="color:var(--accent)"></i>Travaux Dirigés
        </span>
        <a href="<?= BASE_URL ?>/admin/td/index.php" style="font-size:.8rem;color:var(--accent)">
          Gérer tout →
        </a>
      </div>
      <div class="card-body-custom">

        <!-- Mini stats TD -->
        <div class="d-flex gap-2 mb-3">
          <div style="flex:1;background:rgba(10,35,66,.06);border-radius:10px;padding:.6rem .8rem;text-align:center;">
            <div style="font-size:1.3rem;font-weight:800;color:var(--navy,#0a2342);font-family:'Playfair Display',serif;">
              <?= $stats['td'] ?>
            </div>
            <div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Total</div>
          </div>
          <div style="flex:1;background:rgba(52,168,83,.08);border-radius:10px;padding:.6rem .8rem;text-align:center;">
            <div style="font-size:1.3rem;font-weight:800;color:#1a7f5a;font-family:'Playfair Display',serif;">
              <?= $stats['td_actif'] ?>
            </div>
            <div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Actifs</div>
          </div>
          <div style="flex:1;background:rgba(201,168,76,.08);border-radius:10px;padding:.6rem .8rem;text-align:center;">
            <div style="font-size:1.3rem;font-weight:800;color:#c9820a;font-family:'Playfair Display',serif;">
              <?= $stats['td'] - $stats['td_actif'] ?>
            </div>
            <div style="font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;">Inactifs</div>
          </div>
        </div>

        <!-- Liste 5 derniers TD -->
        <?php if (empty($recent_tds)): ?>
          <p class="text-center text-muted py-2" style="font-size:.85rem;">Aucun TD enregistré</p>
        <?php else: ?>
        <div class="d-flex flex-column gap-2 mb-3">
          <?php foreach ($recent_tds as $td): ?>
          <div class="d-flex align-items-center gap-2"
               style="padding:.5rem .6rem;border-radius:8px;background:rgba(0,0,0,.03);transition:background .15s;"
               onmouseover="this.style.background='rgba(10,35,66,.06)'"
               onmouseout="this.style.background='rgba(0,0,0,.03)'">
            <span style="
              background:<?= $td['actif'] ? 'var(--navy,#0a2342)' : '#adb5bd' ?>;
              color:<?= $td['actif'] ? '#c9a84c' : '#fff' ?>;
              font-size:.62rem;font-weight:800;
              padding:.18rem .45rem;border-radius:5px;
              font-family:'Playfair Display',serif;
              white-space:nowrap;flex-shrink:0;">
              <?= htmlspecialchars($td['numero']) ?>
            </span>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.82rem;font-weight:600;color:var(--navy,#0a2342);
                          white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($td['nom']) ?>
              </div>
              <?php if ($td['niveau']): ?>
              <div style="font-size:.7rem;color:var(--muted);">
                <?= htmlspecialchars($td['niveau']) ?>
                <?= $td['disc'] ? ' · ' . htmlspecialchars($td['disc']) : '' ?>
              </div>
              <?php endif; ?>
            </div>
            <a href="<?= BASE_URL ?>/admin/td/index.php?action=view&id=<?= $td['id'] ?>"
               style="color:var(--accent);font-size:.8rem;flex-shrink:0;opacity:.8;"
               title="Voir le TD">
              <i class="fas fa-eye"></i>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Boutons d'action -->
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/admin/td/index.php?action=add"
             class="btn-accent justify-content-center"
             style="flex:1;font-size:.8rem;padding:8px 10px;text-align:center;border-radius:8px;">
            <i class="fas fa-plus me-1"></i> Nouveau TD
          </a>
          <a href="<?= BASE_URL ?>/admin/td/index.php"
             style="flex:1;font-size:.8rem;padding:8px 10px;text-align:center;border-radius:8px;
                    background:rgba(10,35,66,.07);color:var(--navy,#0a2342);font-weight:600;
                    text-decoration:none;transition:background .15s;"
             onmouseover="this.style.background='rgba(10,35,66,.14)'"
             onmouseout="this.style.background='rgba(10,35,66,.07)'">
            <i class="fas fa-list me-1"></i> Tous les TD
          </a>
        </div>

      </div>
    </div>
    <!-- ── /Travaux Dirigés ───────────────────────────────────────────── -->

  </div>
</div>

</div><!-- /admin-content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>