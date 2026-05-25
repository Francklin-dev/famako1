<?php
// admin/users/index.php
$page_title = "Utilisateurs";
require_once __DIR__ . '/../includes/admin_layout.php';
if (currentUser()['role'] !== 'admin') { echo '<div class="alert-famako alert-danger m-4"><i class="fas fa-ban"></i> Accès réservé aux administrateurs.</div>'; exit; }
$pdo = getPDO();

$error = $success = '';

// Suppression
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id !== (int)$_SESSION['user_id']) { $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]); $success='Utilisateur supprimé.'; }
    else $error = 'Impossible de supprimer votre propre compte.';
}

// Ajout / Modification
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $uid      = (int)($_POST['uid']??0);
    $username = trim($_POST['username']??'');
    $email    = trim($_POST['email']??'');
    $fullname = trim($_POST['full_name']??'');
    $role     = $_POST['role']??'gestionnaire';
    $pass     = trim($_POST['password']??'');
    if (!$username||!$email) { $error='Nom d\'utilisateur et email obligatoires.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $error='Email invalide.'; }
    else {
        if ($uid) {
            if ($pass) { $pdo->prepare("UPDATE users SET username=?,email=?,full_name=?,role=?,password_hash=? WHERE id=?")->execute([$username,$email,$fullname,$role,password_hash($pass,PASSWORD_DEFAULT),$uid]); }
            else       { $pdo->prepare("UPDATE users SET username=?,email=?,full_name=?,role=? WHERE id=?")->execute([$username,$email,$fullname,$role,$uid]); }
            $success='Utilisateur mis à jour.';
        } else {
            if (!$pass) { $error='Le mot de passe est obligatoire pour un nouvel utilisateur.'; }
            else {
                try {
                    $pdo->prepare("INSERT INTO users (username,email,full_name,role,password_hash) VALUES (?,?,?,?,?)")->execute([$username,$email,$fullname,$role,password_hash($pass,PASSWORD_DEFAULT)]);
                    $success='Utilisateur créé avec succès.';
                } catch (\PDOException $e) { $error='Email ou nom d\'utilisateur déjà utilisé.'; }
            }
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY role, created_at DESC")->fetchAll();
$edit  = null;
if (isset($_GET['edit'])) { $s=$pdo->prepare("SELECT * FROM users WHERE id=?"); $s->execute([(int)$_GET['edit']]); $edit=$s->fetch(); }
?>

<?php if($success): ?><div class="alert-famako alert-success mb-3"><i class="fas fa-check-circle"></i><?=htmlspecialchars($success)?></div><?php endif; ?>
<?php if($error):   ?><div class="alert-famako alert-danger  mb-3"><i class="fas fa-exclamation-circle"></i><?=htmlspecialchars($error)?></div><?php endif; ?>

<div class="row g-4">
  <!-- FORMULAIRE -->
  <div class="col-lg-4">
    <div class="famako-card p-4">
      <h4 style="font-family:'Playfair Display',serif;color:var(--navy);margin-bottom:18px;font-size:1.1rem;">
        <i class="fas fa-<?=$edit?'user-edit':'user-plus'?> me-2" style="color:var(--accent)"></i>
        <?=$edit?'Modifier utilisateur':'Nouvel utilisateur'?>
      </h4>
      <form method="POST">
        <?php if($edit): ?><input type="hidden" name="uid" value="<?=$edit['id']?>"><?php endif; ?>
        <div class="form-field-custom"><label>Nom d'utilisateur <span class="req">*</span></label><input type="text" name="username" class="form-control-custom" value="<?=htmlspecialchars($edit['username']??'')?>" required></div>
        <div class="form-field-custom"><label>Nom complet</label><input type="text" name="full_name" class="form-control-custom" value="<?=htmlspecialchars($edit['full_name']??'')?>"></div>
        <div class="form-field-custom"><label>Email <span class="req">*</span></label><input type="email" name="email" class="form-control-custom" value="<?=htmlspecialchars($edit['email']??'')?>" required></div>
        <div class="form-field-custom"><label>Rôle</label>
          <select name="role" class="form-control-custom">
            <option value="gestionnaire" <?=($edit['role']??'')==='gestionnaire'?'selected':''?>>Gestionnaire</option>
            <option value="bibliothecaire" <?=($edit['role']??'')==='bibliothecaire'?'selected':''?>>Bibliothécaire</option>
            <option value="admin" <?=($edit['role']??'')==='admin'?'selected':''?>>Administrateur</option>
          </select>
        </div>
        <div class="form-field-custom"><label>Mot de passe <?=$edit?'<small style="color:var(--muted)">(laisser vide = inchangé)</small>':' <span class="req">*</span>'?></label><input type="password" name="password" class="form-control-custom" placeholder="••••••••"></div>
        <div class="d-flex gap-2">
          <button type="submit" class="btn-accent flex-1"><i class="fas fa-save me-1"></i><?=$edit?'Mettre à jour':'Créer'?></button>
          <?php if($edit): ?><a href="index.php" class="btn-outline-navy" style="padding:10px 14px;">Annuler</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- LISTE -->
  <div class="col-lg-8">
    <div class="famako-card">
      <div class="card-header-custom">
        <span style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--navy);"><i class="fas fa-users me-2" style="color:var(--accent)"></i>Utilisateurs (<?=count($users)?>)</span>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Dernière connexion</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($users as $u): $roleBadge=['admin'=>'badge-pdf','gestionnaire'=>'badge-word','bibliothecaire'=>'badge-video']; ?>
            <tr>
              <td><strong style="font-size:.88rem;color:var(--navy);"><?=htmlspecialchars($u['full_name']?:$u['username'])?></strong><br><small style="color:var(--muted);">@<?=htmlspecialchars($u['username'])?></small></td>
              <td style="font-size:.82rem;"><?=htmlspecialchars($u['email'])?></td>
              <td><span class="badge-type <?=$roleBadge[$u['role']]??'badge-autre'?>"><?=ucfirst($u['role'])?></span></td>
              <td style="font-size:.8rem;"><?=$u['last_login']?date('d/m/Y H:i',strtotime($u['last_login'])):'—'?></td>
              <td><div class="d-flex gap-1">
                <a href="?edit=<?=$u['id']?>" class="btn-primary-custom" style="padding:5px 10px;font-size:.75rem;border-radius:6px;"><i class="fas fa-edit"></i></a>
                <?php if ($u['id']!==$_SESSION['user_id']): ?>
                <a href="?del=<?=$u['id']?>" class="btn-outline-navy" style="padding:5px 10px;font-size:.75rem;border-radius:6px;color:var(--danger);border-color:var(--danger);" onclick="return confirm('Supprimer cet utilisateur ?')"><i class="fas fa-trash-alt"></i></a>
                <?php endif; ?>
              </div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</div><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="<?= BASE_URL ?>/assets/js/main.js"></script></body></html>
