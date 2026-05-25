<?php
$page_title = "Contact";
require_once __DIR__ . '/../config/database.php';
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$message) $error = 'Veuillez remplir tous les champs obligatoires.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Email invalide.';
    else $success = 'Message envoyé avec succès ! Nous vous répondrons sous 48h.';
}
require_once __DIR__ . '/../includes/header.php';
?>
<main class="py-5" style="background:var(--light-bg)">
<div class="container">
 
    <div class="col-lg-6">
      <div class="famako-card p-4">
        <h4 style="color:var(--navy);border-bottom:2px solid var(--accent);padding-bottom:10px;margin-bottom:20px;"><i class="fas fa-paper-plane me-2" style="color:var(--accent)"></i><span data-lang="fr">Envoyer un message</span><span data-lang="en">Send a message</span></h4>
        <?php if ($success): ?>
        <div class="alert-famako alert-success mb-3"><i class="fas fa-check-circle"></i><div><?= $success ?></div></div>
        <?php elseif ($error): ?>
        <div class="alert-famako alert-danger mb-3"><i class="fas fa-exclamation-circle"></i><div><?= $error ?></div></div>
        <?php endif; ?>
        <form method="POST">
          <div class="form-field-custom"><label><span data-lang="fr">Nom complet</span><span data-lang="en">Full name</span> <span class="req">*</span></label><input type="text" name="name" class="form-control-custom" value="<?= htmlspecialchars($_POST['name']??'') ?>" required></div>
          <div class="form-field-custom"><label>Email <span class="req">*</span></label><input type="email" name="email" class="form-control-custom" value="<?= htmlspecialchars($_POST['email']??'') ?>" required></div>
          <div class="form-field-custom"><label><span data-lang="fr">Objet</span><span data-lang="en">Subject</span></label><input type="text" name="subject" class="form-control-custom" value="<?= htmlspecialchars($_POST['subject']??'') ?>"></div>
          <div class="form-field-custom"><label><span data-lang="fr">Message</span><span data-lang="en">Message</span> <span class="req">*</span></label><textarea name="message" class="form-control-custom" rows="5" required><?= htmlspecialchars($_POST['message']??'') ?></textarea></div>
          <button type="submit" class="btn-accent w-100 justify-content-center" style="font-family:'DM Sans',sans-serif;font-size:.95rem;padding:13px;border-radius:var(--radius);"><i class="fas fa-paper-plane me-2"></i><span data-lang="fr">Envoyer</span><span data-lang="en">Send</span></button>
        </form>
      </div>
    </div>
  </div>
</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
