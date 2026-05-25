<?php
// admin/inscriptions/voir.php - Voir une inscription
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/header.php';

$pdo = getPDO();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

// Récupérer l'inscription (table inscriptions, pas documents)
$stmt = $pdo->prepare("
    SELECT i.*, d.nom_fr AS discipline
    FROM inscriptions i
    LEFT JOIN disciplines d ON i.discipline_id = d.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$inscription = $stmt->fetch();

if (!$inscription) {
    echo "<div class='alert alert-danger'>Inscription non trouvée.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$page_title = "Détails de l'inscription - " . htmlspecialchars($inscription['nom'] . ' ' . $inscription['prenom']);
?>

<div class="page-header">
    <h1>Détails de l'inscription</h1>
    <div class="breadcrumb">Administration / Inscriptions / Détails</div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Informations personnelles</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Matricule :</strong> <?= htmlspecialchars($inscription['matricule'] ?? '—') ?></p>
                <p><strong>Nom :</strong> <?= htmlspecialchars($inscription['nom']) ?></p>
                <p><strong>Prénom :</strong> <?= htmlspecialchars($inscription['prenom']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($inscription['email']) ?></p>
                <p><strong>Contact :</strong> <?= htmlspecialchars($inscription['contact'] ?? '—') ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Pays :</strong> <?= htmlspecialchars($inscription['pays'] ?? '—') ?></p>
                <p><strong>Date de naissance :</strong> <?= date('d/m/Y', strtotime($inscription['date_naissance'])) ?></p>
                <p><strong>Discipline :</strong> <?= htmlspecialchars($inscription['discipline'] ?? '—') ?></p>
                <p><strong>Statut :</strong> 
                    <span class="badge bg-<?= $inscription['statut'] == 'accepte' ? 'success' : ($inscription['statut'] == 'refuse' ? 'danger' : 'warning') ?>">
                        <?= ucfirst($inscription['statut']) ?>
                    </span>
                </p>
                <p><strong>Date d'inscription :</strong> <?= date('d/m/Y H:i', strtotime($inscription['created_at'])) ?></p>
            </div>
        </div>
    </div>
</div>

<?php if ($inscription['notes']): ?>
<div class="card mt-3">
    <div class="card-header">
        <h3>Notes internes</h3>
    </div>
    <div class="card-body">
        <p><?= nl2br(htmlspecialchars($inscription['notes'])) ?></p>
    </div>
</div>
<?php endif; ?>

<div class="mt-3">
    <a href="index.php" class="btn btn-secondary">← Retour à la liste</a>
    <a href="modifier.php?id=<?= $id ?>" class="btn btn-primary">✏️ Modifier</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>