<?php
/**
 * pages/download.php
 * Téléchargement sécurisé des cours (vérifie le code d'accès si protégé)
 * et des documents de la bibliothèque.
 */
require_once __DIR__ . '/../config/database.php';
$pdo = getPDO();

$id   = (int)($_GET['id']   ?? 0);
$type = $_GET['type'] ?? '';

if (!$id || !in_array($type, ['cours', 'biblio'])) {
    http_response_code(400); die('Requête invalide.');
}

if ($type === 'cours') {
    $stmt = $pdo->prepare("SELECT * FROM cours WHERE id = ? AND actif = 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !$row['fichier_path']) { http_response_code(404); die('Fichier introuvable.'); }

    // ── Vérification du code d'accès ────────────────────────────────
    if (!empty($row['code_acces'])) {
        $key = 'cours_acces_' . $id;
        $ok  = isset($_SESSION[$key]) && $_SESSION[$key] > time();

        if (!$ok) {
            // Renvoyer sur la page des cours avec message
            header('Location: ' . BASE_URL . '/pages/cours.php?acces_requis=' . $id);
            exit;
        }
    }

    $filePath = UPLOAD_DIR . 'cours/' . basename($row['fichier_path']);
    $fileName = $row['fichier_nom'] ?: basename($row['fichier_path']);
    $pdo->prepare("UPDATE cours SET telechargements = telechargements + 1 WHERE id = ?")->execute([$id]);

} else {
    $stmt = $pdo->prepare("SELECT * FROM bibliotheque WHERE id = ? AND statut = 'publie'");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row || !$row['fichier_path']) { http_response_code(404); die('Fichier introuvable.'); }
    $filePath = UPLOAD_DIR . 'bibliotheque/' . basename($row['fichier_path']);
    $fileName = $row['fichier_nom'] ?: basename($row['fichier_path']);
    $pdo->prepare("UPDATE bibliotheque SET telechargements = telechargements + 1 WHERE id = ?")->execute([$id]);
}

if (!file_exists($filePath)) { http_response_code(404); die('Fichier introuvable sur le serveur.'); }

$mime = mime_content_type($filePath) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');
readfile($filePath);
exit;
