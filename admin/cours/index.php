<?php
// admin/cours/index.php
$page_title = "Gestion des cours";
require_once __DIR__ . '/../includes/admin_layout.php';
$pdo = getPDO();
$search = trim($_GET['q'] ?? '');
$type   = $_GET['type'] ?? '';
$page   = max(1,(int)($_GET['page']??1));
$limit  = 15; $offset = ($page-1)*$limit;

$where = []; $params = [];
if ($search) { $where[] = '(c.titre LIKE ? OR c.description LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($type)   { $where[] = 'c.type_fichier = ?'; $params[] = $type; }
$ws = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM cours c $ws"); $total->execute($params);
$totalCount = $total->fetchColumn(); $pages = ceil($totalCount/$limit);

$stmt = $pdo->prepare("SELECT c.*, d.nom_fr as disc FROM cours c LEFT JOIN disciplines d ON c.discipline_id=d.id $ws ORDER BY c.created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params); $coursList = $stmt->fetchAll();
$typeLabels = ['pdf'=>'PDF','word'=>'Word','video'=>'Vidéo','diapo'=>'Diaporama','autre'=>'Autre'];
?>
<!-- DELETE -->
<?php if (isset($_GET['del'])):
  $pdo->prepare("UPDATE cours SET actif=0 WHERE id=?")->execute([(int)$_GET['del']]);
  header('Location: index.php?deleted=1'); exit;
endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
  <h2 style="font-family:'Playfair Display',serif;color:var(--navy);font-size:1.4rem;margin:0;">Gestion des cours (<?= $totalCount ?>)</h2>
  <a href="<?= BASE_URL ?>/admin/cours/ajouter.php" class="btn-accent"><i class="fas fa-plus me-2"></i>Ajouter un cours</a>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert-famako alert-success mb-3"><i class="fas fa-check-circle"></i>Cours archivé.</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert-famako alert-success mb-3"><i class="fas fa-check-circle"></i>Cours enregistré avec succès.</div><?php endif; ?>

<!-- Filtres -->
<div class="famako-card p-3 mb-4">
  <form method="GET" class="d-flex gap-3 flex-wrap align-items-end">
    <div><label style="font-size:.82rem;font-weight:600;color:var(--navy);display:block;margin-bottom:4px;">Recherche</label><input type="text" name="q" class="form-control-custom" value="<?= htmlspecialchars($search) ?>" placeholder="Titre…" style="width:220px;"></div>
    <div><label style="font-size:.82rem;font-weight:600;color:var(--navy);display:block;margin-bottom:4px;">Type</label>
    <select name="type" class="form-control-custom" style="width:140px;"><option value="">Tous</option><?php foreach($typeLabels as $k=>$v): ?><option value="<?=$k?>" <?=$type===$k?'selected':''?>><?=$v?></option><?php endforeach;?></select></div>
    <button type="submit" class="btn-accent" style="padding:10px 18px;">Filtrer</button>
    <?php if($search||$type): ?><a href="index.php" class="btn-outline-navy" style="padding:10px 16px;">Effacer</a><?php endif; ?>
  </form>
</div>

<div class="famako-card">
  <div class="table-responsive">
    <table class="data-table">
      <thead><tr><th>Titre</th><th>Type</th><th>Discipline</th><th>Date</th><th>Vues</th><th>Actif</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($coursList as $c): ?>
        <tr>
          <td style="max-width:280px;"><strong style="font-size:.88rem;color:var(--navy);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($c['titre']) ?></strong></td>
          <td><span class="badge-type badge-<?= $c['type_fichier'] ?>"><?= $typeLabels[$c['type_fichier']] ?? $c['type_fichier'] ?></span></td>
          <td style="font-size:.82rem;"><?= htmlspecialchars($c['disc'] ?? '—') ?></td>
          <td style="font-size:.8rem;white-space:nowrap;"><?= $c['annee_cours'] ?? '—' ?></td>
          <td style="font-size:.82rem;"><?= $c['vues'] ?></td>
          <td><span class="badge-type <?= $c['actif']?'badge-video':'badge-archive' ?>"><?= $c['actif']?'Oui':'Non' ?></span></td>
          <td>
            <div class="d-flex gap-1">
              <a href="modifier.php?id=<?= $c['id'] ?>" class="btn-primary-custom" style="padding:5px 10px;font-size:.75rem;border-radius:6px;"><i class="fas fa-edit"></i></a>
              <a href="index.php?del=<?= $c['id'] ?>" class="btn-outline-navy" style="padding:5px 10px;font-size:.75rem;border-radius:6px;color:var(--danger);border-color:var(--danger);" onclick="return confirm('Archiver ce cours ?')"><i class="fas fa-trash-alt"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($coursList)): ?><tr><td colspan="7" class="text-center py-4" style="color:var(--muted);">Aucun cours trouvé</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($pages > 1): ?>
  <div class="famako-pagination p-3">
    <?php if ($page>1): ?><a href="?page=<?=$page-1?>&q=<?=urlencode($search)?>&type=<?=urlencode($type)?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
    <?php for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++): ?><a href="?page=<?=$i?>&q=<?=urlencode($search)?>&type=<?=urlencode($type)?>" class="<?=$i===$page?'current':''?>"><?=$i?></a><?php endfor; ?>
    <?php if ($page<$pages): ?><a href="?page=<?=$page+1?>&q=<?=urlencode($search)?>&type=<?=urlencode($type)?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="<?= BASE_URL ?>/assets/js/main.js"></script></body></html>
