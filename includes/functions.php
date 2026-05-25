<?php
// ================================================================
//  FAMAKO — FUNCTIONS GLOBALES  (includes/functions.php)
//  Couvre : site principal + bibliothèque numérique
//  Require : config/database.php  (déjà chargé si besoin)
// ================================================================

if (!defined('DB_NAME')) {
    require_once __DIR__ . '/../config/database.php';
}


// ================================================================
//  1. AUTHENTIFICATION — site principal (table `users`)
// ================================================================

/** L'admin/gestionnaire courant est-il connecté ? */
function isLoggedIn(): bool { return !empty($_SESSION['user_id']); }

/** Données de l'admin connecté (depuis session) */
function currentUser(): array { return $_SESSION['user'] ?? []; }

/** Redirige vers login admin si non connecté */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

/** Déconnexion admin */
function logoutAdmin(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}


// ================================================================
//  2. AUTHENTIFICATION — bibliothèque numérique (table `bib_users`)
// ================================================================

/** Le lecteur de la bibliothèque est-il connecté ? */
function bibIsLoggedIn(): bool { return !empty($_SESSION['bib_user_id']); }

/** Données du lecteur connecté (rechargées depuis DB une seule fois) */
function bibCurrentUser(): ?array {
    if (!bibIsLoggedIn()) return null;
    static $user = null;
    if (!$user) {
        $st = getPDO()->prepare("SELECT * FROM bib_users WHERE id = ? AND is_active = 1");
        $st->execute([$_SESSION['bib_user_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user;
}

/** Redirige vers login bibliothèque si non connecté */
function bibRequireLogin(): void {
    if (!bibIsLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/bibliotheque/login.php');
        exit;
    }
}

/** Contrôle de rôle bibliothèque */
function bibRequireRole(string ...$roles): void {
    bibRequireLogin();
    $u = bibCurrentUser();
    if (!$u || !in_array($u['role'], $roles)) {
        bibSetFlash('Accès non autorisé.', 'danger');
        header('Location: ' . BASE_URL . '/bibliotheque/dashboard.php');
        exit;
    }
}

function bibIsAdmin(): bool  { $u = bibCurrentUser(); return $u && $u['role'] === 'admin'; }
function bibIsBiblio(): bool { $u = bibCurrentUser(); return $u && in_array($u['role'], ['admin', 'bibliothecaire']); }

/** Déconnexion lecteur bibliothèque (sans détruire la session admin) */
function bibLogout(): void {
    unset($_SESSION['bib_user_id'], $_SESSION['bib_flash'], $_SESSION['redirect_after_login']);
}


// ================================================================
//  3. FLASH MESSAGES (deux espaces de nommage distincts)
// ================================================================

// -- Site principal --
function setFlash(string $msg, string $type = 'info'): void {
    $_SESSION['flash'][] = ['msg' => $msg, 'type' => $type];
}
function getFlashes(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// -- Bibliothèque numérique --
function bibSetFlash(string $msg, string $type = 'info'): void {
    $_SESSION['bib_flash'][] = ['msg' => $msg, 'type' => $type];
}
function bibGetFlashes(): array {
    $f = $_SESSION['bib_flash'] ?? [];
    unset($_SESSION['bib_flash']);
    return $f;
}


// ================================================================
//  4. CSRF
// ================================================================

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verifyCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF invalide');
        }
    }
}

/** Champ caché CSRF à insérer dans les formulaires */
function csrfField(): string {
    return '<input type="hidden" name="_csrf" value="' . h(csrfToken()) . '">';
}


// ================================================================
//  5. UPLOAD DE FICHIERS
// ================================================================

/** Vérifie que l'extension est autorisée (bibliothèque numérique) */
function bibAllowedFile(string $filename): bool {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, BIB_ALLOWED_EXT);
}

/**
 * Upload générique vers un sous-dossier.
 * @param array  $file      $_FILES['champ']
 * @param string $destDir   Chemin absolu du dossier cible
 * @param int    $maxSize   Taille max en octets (0 = pas de limite)
 * @param array  $allowExt  Extensions autorisées ([] = toutes)
 * @return string|null      Nom du fichier enregistré, ou null en cas d'erreur
 */
function uploadFile(array $file, string $destDir, int $maxSize = 0, array $allowExt = []): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($maxSize > 0 && $file['size'] > $maxSize) return null;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($allowExt && !in_array($ext, $allowExt)) return null;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $destDir . '/' . $filename)) return null;
    return $filename;
}

/** Upload fichier bibliothèque numérique */
function bibUploadFile(array $file, string $subfolder): ?string {
    return uploadFile($file, BIB_UPLOAD_PATH . $subfolder, BIB_MAX_UPLOAD, BIB_ALLOWED_EXT);
}

/** Upload fichier site principal (inscriptions, cours…) */
function siteUploadFile(array $file, string $subfolder, int $maxSize = 0, array $ext = []): ?string {
    return uploadFile($file, UPLOAD_DIR . $subfolder, $maxSize ?: MAX_FILE_SIZE, $ext);
}


// ================================================================
//  6. NOTIFICATIONS (bibliothèque numérique)
// ================================================================

function bibCreateNotification(int $userId, string $type, string $title, string $message, string $link = ''): void {
    getPDO()->prepare("INSERT INTO bib_notifications (user_id, type, title, message, link) VALUES (?,?,?,?,?)")
            ->execute([$userId, $type, $title, $message, $link]);
}

function bibUnreadNotifCount(): int {
    if (!bibIsLoggedIn()) return 0;
    return (int) getPDO()
        ->query("SELECT COUNT(*) FROM bib_notifications WHERE user_id = " . (int)$_SESSION['bib_user_id'] . " AND is_read = 0")
        ->fetchColumn();
}


// ================================================================
//  7. PAGINATION
// ================================================================

/**
 * Pagine n'importe quelle requête SELECT.
 * @param string $query   Requête sans LIMIT/OFFSET
 * @param array  $params  Paramètres PDO
 * @param int    $page    Page courante (≥1)
 * @param int    $perPage Éléments par page
 * @return array  {items, total, pages, page, per_page}
 */
function paginate(string $query, array $params, int $page, int $perPage = BIB_ITEMS_PER_PAGE): array {
    $db    = getPDO();
    $total = (int) $db->prepare("SELECT COUNT(*) FROM ($query) AS _cnt")
                      ->execute($params) ? $db->prepare("SELECT COUNT(*) FROM ($query) AS _cnt")->execute($params) : 0;

    // Exécution propre en deux étapes
    $stCount = $db->prepare("SELECT COUNT(*) FROM ($query) AS _cnt");
    $stCount->execute($params);
    $total   = (int)$stCount->fetchColumn();

    $pages   = max(1, (int)ceil($total / $perPage));
    $page    = max(1, min($page, $pages));
    $offset  = ($page - 1) * $perPage;

    $stData  = $db->prepare($query . " LIMIT $perPage OFFSET $offset");
    $stData->execute($params);

    return [
        'items'    => $stData->fetchAll(),
        'total'    => $total,
        'pages'    => $pages,
        'page'     => $page,
        'per_page' => $perPage,
    ];
}

/**
 * Génère le HTML de la pagination Bootstrap.
 * @param array  $pagination  Résultat de paginate()
 * @param string $baseUrl     URL de base (sans ?page=)
 * @param array  $extraParams Paramètres GET supplémentaires à conserver
 */
function renderPagination(array $pagination, string $baseUrl, array $extraParams = []): string {
    if ($pagination['pages'] <= 1) return '';
    $qs = $extraParams ? '&' . http_build_query($extraParams) : '';
    $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $pagination['pages']; $i++) {
        $active = $i === $pagination['page'] ? ' active' : '';
        $html  .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"{$baseUrl}?page={$i}{$qs}\">$i</a></li>";
    }
    return $html . '</ul></nav>';
}


// ================================================================
//  8. SLUG & DOCUMENTS (bibliothèque numérique)
// ================================================================

function generateSlug(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $map  = ['àáâãäå'=>'a','èéêë'=>'e','ìíîï'=>'i','òóôõö'=>'o','ùúûü'=>'u','ç'=>'c','ñ'=>'n'];
    foreach ($map as $chars => $rep) {
        $text = preg_replace('/[' . $chars . ']/u', $rep, $text);
    }
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text ?: 'doc-' . uniqid();
}

function bibUniqueSlug(string $title): string {
    $db   = getPDO();
    $base = generateSlug($title);
    $slug = $base;
    $i    = 1;
    while ($db->prepare("SELECT id FROM bib_documents WHERE slug = ?")->execute([$slug]) &&
           $db->prepare("SELECT id FROM bib_documents WHERE slug = ?")->execute([$slug]) ? 
           (function() use ($db, $slug) { $st = $db->prepare("SELECT id FROM bib_documents WHERE slug = ?"); $st->execute([$slug]); return $st->fetch(); })() : false) {
        $slug = $base . '-' . $i++;
    }
    // Version propre
    do {
        $st = $db->prepare("SELECT id FROM bib_documents WHERE slug = ?");
        $st->execute([$slug]);
        if ($st->fetch()) { $slug = $base . '-' . $i++; } else { break; }
    } while (true);
    return $slug;
}

function bibPdfPageCount(string $path): int {
    if (!file_exists($path)) return 0;
    $content = file_get_contents($path);
    preg_match_all('/\/Page\b/', $content, $m);
    return count($m[0]);
}


// ================================================================
//  9. MATRICULE (inscriptions site principal)
// ================================================================

/**
 * Génère le prochain matricule unique.
 * Format : AAMMJJNNNN  (AA=année, MM=mois naissance, JJ=jour naissance, NNNN=séquence)
 */
function generateMatricule(\DateTime $dateNaissance): string {
    $db     = getPDO();
    $prefix = $dateNaissance->format('ymd');    // ex: 100513
    $db->beginTransaction();
    try {
        $st = $db->prepare("INSERT INTO matricule_sequences (prefix, last_seq) VALUES (?, 1)
                            ON DUPLICATE KEY UPDATE last_seq = last_seq + 1");
        $st->execute([$prefix]);
        $st2 = $db->prepare("SELECT last_seq FROM matricule_sequences WHERE prefix = ?");
        $st2->execute([$prefix]);
        $seq = (int)$st2->fetchColumn();
        $db->commit();
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    } catch (\Throwable $e) {
        $db->rollBack();
        throw $e;
    }
}


// ================================================================
//  10. EMAIL
// ================================================================

function sendEmail(string $to, string $subject, string $html): bool {
    $headers  = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . APP_NAME . " <noreply@famako.edu>\r\n";
    return @mail($to, $subject, $html, $headers);
}


// ================================================================
//  11. HELPERS GÉNÉRAUX
// ================================================================

/** Échappe pour HTML */
function h(?string $s): string { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
function e(?string $s): string { return h($s); }

/** Redirection HTTP propre */
function redirect(string $url): never { header("Location: $url"); exit; }

/** IP du client */
function getClientIP(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
}

/** Formate une taille en octets */
function formatSize(int $bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 1) . ' Go';
    if ($bytes >= 1048576)    return round($bytes / 1048576, 1)    . ' Mo';
    if ($bytes >= 1024)       return round($bytes / 1024, 1)       . ' Ko';
    return $bytes . ' o';
}

/** "il y a X minutes" */
function timeAgo(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60)      return 'à l\'instant';
    if ($diff < 3600)    return floor($diff / 60) . ' min';
    if ($diff < 86400)   return floor($diff / 3600) . 'h';
    if ($diff < 2592000) return floor($diff / 86400) . 'j';
    return date('d/m/Y', strtotime($date));
}

/** Génère un token aléatoire sécurisé */
function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/** Vérifie si une chaîne est un email valide */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Tronque un texte avec ellipsis */
function truncate(string $text, int $maxLen = 120, string $suffix = '…'): string {
    if (mb_strlen($text) <= $maxLen) return $text;
    return mb_substr($text, 0, $maxLen - mb_strlen($suffix)) . $suffix;
}


// ================================================================
//  12. STATISTIQUES RAPIDES (dashboard admin)
// ================================================================

/** Compte les lignes d'une table avec filtre optionnel */
function countRows(string $table, string $where = '', array $params = []): int {
    $db  = getPDO();
    $sql = "SELECT COUNT(*) FROM `$table`" . ($where ? " WHERE $where" : '');
    $st  = $db->prepare($sql);
    $st->execute($params);
    return (int)$st->fetchColumn();
}

/** Stats du site principal */
function getSiteStats(): array {
    return [
        'inscriptions_total'     => countRows('inscriptions'),
        'inscriptions_en_attente'=> countRows('inscriptions', 'statut = ?', ['en_attente']),
        'inscriptions_acceptees' => countRows('inscriptions', 'statut = ?', ['accepte']),
        'cours_total'            => countRows('cours'),
        'bibliotheque_total'     => countRows('bibliotheque'),
        'disciplines_total'      => countRows('disciplines'),
        'users_total'            => countRows('users'),
    ];
}

/** Stats de la bibliothèque numérique */
function getBibStats(): array {
    return [
        'documents_total'    => countRows('bib_documents'),
        'documents_publies'  => countRows('bib_documents', 'status = ?', ['publie']),
        'documents_attente'  => countRows('bib_documents', 'status = ?', ['en_attente']),
        'categories_total'   => countRows('bib_categories'),
        'lecteurs_total'     => countRows('bib_users'),
        'lecteurs_actifs'    => countRows('bib_users', 'is_active = 1'),
        'telechargements'    => (int)(getPDO()->query("SELECT COALESCE(SUM(downloads_count),0) FROM bib_documents")->fetchColumn()),
        'vues'               => (int)(getPDO()->query("SELECT COALESCE(SUM(views_count),0) FROM bib_documents")->fetchColumn()),
    ];
}
