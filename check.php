<?php
/**
 * ============================================================
 *  check.php — Garde de sécurité universel FaMaKo
 * ============================================================
 *  À inclure EN TÊTE de chaque page protégée.
 *  Gère trois espaces d'authentification distincts :
 *
 *   1. ADMIN SITE PRINCIPAL  → table `users`, rôle `admin`
 *      Usage : require_once check.php;  checkAdmin();
 *
 *   2. LECTEUR BIBLIOTHÈQUE  → table `users` (bib), rôle `lecteur/admin/bibliothecaire`
 *      Usage : require_once check.php;  checkBibUser();
 *
 *   3. ADMIN BIBLIOTHÈQUE    → table `users`, rôle `admin` ou `bibliothecaire`
 *      Usage : require_once check.php;  checkBibAdmin();
 *
 *  Chaque fonction redirige automatiquement vers la bonne page
 *  de connexion si la session est absente ou invalide.
 * ============================================================
 */

// ── Démarrage de session (idempotent) ─────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Constantes de redirection (sécurité de secours si BASE_URL non défini) ───
if (!defined('BASE_URL'))    define('BASE_URL',    'http://localhost/famako1');
if (!defined('APP_URL'))     define('APP_URL',     'http://localhost/bibliotheque');
if (!defined('SESSION_TTL')) define('SESSION_TTL', 7200); // 2 heures d'inactivité max

// ─────────────────────────────────────────────────────────────────────────────
//  UTILITAIRE : expiration de session par inactivité
// ─────────────────────────────────────────────────────────────────────────────
function _checkSessionTTL(string $tsKey, string $redirectUrl): void {
    $now = time();
    if (isset($_SESSION[$tsKey]) && ($now - $_SESSION[$tsKey]) > SESSION_TTL) {
        // Session expirée : on la détruit proprement
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        session_start();
        $_SESSION['flash_error'] = 'Votre session a expiré. Veuillez vous reconnecter.';
        header('Location: ' . $redirectUrl);
        exit;
    }
    // Rafraîchir l'horodatage d'activité
    $_SESSION[$tsKey] = $now;
}

// ─────────────────────────────────────────────────────────────────────────────
//  1. ADMIN SITE PRINCIPAL
//     Vérifie : $_SESSION['user_id'] + rôle 'admin' dans la table `users`
// ─────────────────────────────────────────────────────────────────────────────
function checkAdmin(): void {
    $loginUrl = BASE_URL . '/admin/login.php';

    // Pas de session
    if (empty($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $loginUrl);
        exit;
    }

    // Expiration par inactivité
    _checkSessionTTL('admin_last_activity', $loginUrl);

    // Vérification du rôle en base (anti-élévation de privilège)
    try {
        require_once _famako_db_path();
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT role, is_active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['role'] !== 'admin' || !$user['is_active']) {
            _destroyAndRedirect($loginUrl, 'Accès refusé : compte non autorisé.');
        }
    } catch (Exception $e) {
        _destroyAndRedirect($loginUrl, 'Erreur de vérification. Reconnectez-vous.');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  2. LECTEUR BIBLIOTHÈQUE
//     Vérifie : $_SESSION['user_id'] (bib) + compte actif
//     Rôles acceptés : lecteur, bibliothecaire, admin
// ─────────────────────────────────────────────────────────────────────────────
function checkBibUser(): void {
    $loginUrl = APP_URL . '/login.php';

    if (empty($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $loginUrl);
        exit;
    }

    _checkSessionTTL('bib_last_activity', $loginUrl);

    try {
        require_once _bib_db_path();
        $db   = getDB();
        $stmt = $db->prepare("SELECT role, is_active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['is_active']) {
            _destroyAndRedirect($loginUrl, 'Compte désactivé. Contactez l\'administrateur.');
        }
    } catch (Exception $e) {
        _destroyAndRedirect($loginUrl, 'Erreur de vérification. Reconnectez-vous.');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  3. ADMIN / BIBLIOTHÉCAIRE BIBLIOTHÈQUE
//     Vérifie : $_SESSION['user_id'] + rôle admin ou bibliothecaire
// ─────────────────────────────────────────────────────────────────────────────
function checkBibAdmin(): void {
    $loginUrl    = APP_URL . '/login.php';
    $dashUrl     = APP_URL . '/dashboard.php';
    $allowedRoles = ['admin', 'bibliothecaire'];

    if (empty($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . $loginUrl);
        exit;
    }

    _checkSessionTTL('bib_last_activity', $loginUrl);

    try {
        require_once _bib_db_path();
        $db   = getDB();
        $stmt = $db->prepare("SELECT role, is_active FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['is_active']) {
            _destroyAndRedirect($loginUrl, 'Compte désactivé.');
        }

        if (!in_array($user['role'], $allowedRoles)) {
            // Connecté mais pas le bon rôle → rediriger vers dashboard lecteur
            $_SESSION['flash_error'] = 'Accès réservé aux administrateurs et bibliothécaires.';
            header('Location: ' . $dashUrl);
            exit;
        }
    } catch (Exception $e) {
        _destroyAndRedirect($loginUrl, 'Erreur de vérification. Reconnectez-vous.');
    }
}

// ─────────────────────────────────────────────────────────────────────────────
//  UTILITAIRES INTERNES
// ─────────────────────────────────────────────────────────────────────────────

/** Détruit la session et redirige avec message d'erreur */
function _destroyAndRedirect(string $url, string $msg): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();
    $_SESSION['flash_error'] = $msg;
    header('Location: ' . $url);
    exit;
}

/** Résout le chemin vers database.php du site principal */
function _famako_db_path(): string {
    // Depuis famako1/admin/*/check.php → famako1/config/database.php
    $candidates = [
        __DIR__ . '/../../config/database.php',   // admin/includes/ ou admin/td/
        __DIR__ . '/../config/database.php',       // admin/
        __DIR__ . '/config/database.php',          // racine
    ];
    foreach ($candidates as $p) {
        if (file_exists($p)) return $p;
    }
    // Chemin absolu de secours (WAMP)
    return 'C:/wamp64/www/famako1/config/database.php';
}

/** Résout le chemin vers config/database.php de la bibliothèque */
function _bib_db_path(): string {
    $candidates = [
        __DIR__ . '/../config/database.php',
        __DIR__ . '/../../config/database.php',
        __DIR__ . '/config/database.php',
    ];
    foreach ($candidates as $p) {
        if (file_exists($p)) return $p;
    }
    return 'C:/wamp64/www/bibliotheque/config/database.php';
}
