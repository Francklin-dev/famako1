<?php
/**
 * pages/td.php
 * Page ÉTUDIANT - Accès aux TD par clé d'accès
 * Séparé de l'interface admin (td/index.php)
 */

$page_title = "Travaux Dirigés";
require_once __DIR__ . '/../config/database.php';
$pdo = getPDO();

$td        = null;
$error     = '';
$success   = false;
$submitted = false;

// ─── Traitement du formulaire ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted  = true;
    $cle_saisie = trim($_POST['cle_acces'] ?? '');

    if ($cle_saisie === '') {
        $error = "Veuillez saisir votre clé d'accès.";
    } else {
        $stmt = $pdo->prepare("
            SELECT t.*, d.nom AS discipline_nom
            FROM   td t
            LEFT JOIN disciplines d ON d.id = t.discipline_id
            WHERE  t.cle_acces = :cle
              AND  t.actif = 1
            LIMIT  1
        ");
        $stmt->execute([':cle' => $cle_saisie]);
        $td = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$td) {
            $error = "Clé d'accès incorrecte ou TD désactivé.";
        } else {
            $success = true;
        }
    }
}

// ─── Extension → icône ───────────────────────────────────────────────────────
function getFileIcon(string $path): string {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return match($ext) {
        'pdf'              => '📄',
        'doc', 'docx'      => '📝',
        'ppt', 'pptx'      => '📊',
        'zip', 'rar'       => '🗜️',
        default            => '📎',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Variables ─────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #0b0e14;
            --bg2:       #131720;
            --surface:   #1a1f2e;
            --border:    #252c3e;
            --accent:    #4f8cff;
            --accent2:   #a78bfa;
            --green:     #34d399;
            --red:       #f87171;
            --text:      #e8eaf0;
            --muted:     #6b7280;
            --font-head: 'Syne', sans-serif;
            --font-body: 'DM Sans', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow-x: hidden;
        }

        /* ── Fond animé ─────────────────────────────────────────────── */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(79,140,255,.10) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(167,139,250,.08) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── Grid pattern ───────────────────────────────────────────── */
        body::after {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: .25;
            pointer-events: none;
            z-index: 0;
        }

        .wrap {
            position: relative; z-index: 1;
            width: 100%; max-width: 600px;
        }

        /* ── Header ─────────────────────────────────────────────────── */
        .site-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .site-header .badge {
            display: inline-flex; align-items: center; gap: .45rem;
            background: rgba(79,140,255,.12);
            border: 1px solid rgba(79,140,255,.25);
            color: var(--accent);
            font-family: var(--font-head);
            font-size: .72rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase;
            padding: .35rem .9rem; border-radius: 99px;
            margin-bottom: 1.2rem;
        }
        .site-header h1 {
            font-family: var(--font-head);
            font-size: clamp(2rem, 6vw, 3rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -.03em;
        }
        .site-header h1 span {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .site-header p {
            margin-top: .8rem;
            color: var(--muted);
            font-size: .95rem;
            font-weight: 300;
        }

        /* ── Card ────────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 24px 64px rgba(0,0,0,.45);
        }

        /* ── Form ────────────────────────────────────────────────────── */
        .form-label {
            display: block;
            font-size: .78rem; font-weight: 600;
            letter-spacing: .08em; text-transform: uppercase;
            color: var(--muted);
            margin-bottom: .55rem;
        }

        .input-wrap {
            position: relative;
            margin-bottom: 1.4rem;
        }
        .input-wrap .icon {
            position: absolute; left: 1rem; top: 50%;
            transform: translateY(-50%);
            font-size: 1.1rem; pointer-events: none;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            background: var(--bg2);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-family: var(--font-body);
            font-size: 1rem;
            padding: .85rem 1rem .85rem 3rem;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            letter-spacing: .05em;
        }
        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79,140,255,.15);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, #6366f1 100%);
            border: none; border-radius: 12px;
            color: #fff;
            font-family: var(--font-head);
            font-size: 1rem; font-weight: 700;
            letter-spacing: .04em;
            padding: .9rem;
            cursor: pointer;
            transition: transform .15s, box-shadow .2s, opacity .2s;
            box-shadow: 0 4px 24px rgba(79,140,255,.35);
        }
        .btn-primary:hover  { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(79,140,255,.45); }
        .btn-primary:active { transform: translateY(0); opacity: .9; }

        /* ── Alertes ─────────────────────────────────────────────────── */
        .alert {
            border-radius: 12px; padding: .9rem 1.1rem;
            font-size: .9rem; margin-bottom: 1.4rem;
            display: flex; align-items: flex-start; gap: .7rem;
        }
        .alert-error {
            background: rgba(248,113,113,.1);
            border: 1px solid rgba(248,113,113,.25);
            color: var(--red);
        }
        .alert-success {
            background: rgba(52,211,153,.1);
            border: 1px solid rgba(52,211,153,.25);
            color: var(--green);
        }

        /* ── TD Result ───────────────────────────────────────────────── */
        .td-result { margin-top: 2rem; }

        .td-header {
            display: flex; align-items: flex-start; gap: 1.2rem;
            margin-bottom: 1.8rem;
        }
        .td-number {
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            color: #fff;
            font-family: var(--font-head);
            font-size: .75rem; font-weight: 700; letter-spacing: .1em;
            padding: .4rem .8rem; border-radius: 8px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .td-meta h2 {
            font-family: var(--font-head);
            font-size: 1.4rem; font-weight: 700;
            line-height: 1.25; margin-bottom: .35rem;
        }
        .td-meta .chips {
            display: flex; flex-wrap: wrap; gap: .4rem;
        }
        .chip {
            background: rgba(255,255,255,.06);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: .72rem; color: var(--muted);
            padding: .25rem .6rem;
        }

        .td-description {
            background: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.1rem 1.3rem;
            font-size: .9rem; color: var(--muted);
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .divider {
            border: none; border-top: 1px solid var(--border);
            margin: 1.5rem 0;
        }

        .download-block {
            background: var(--bg2);
            border: 1.5px solid var(--border);
            border-radius: 14px;
            padding: 1.2rem 1.4rem;
            display: flex; align-items: center; gap: 1rem;
            transition: border-color .2s, transform .15s;
            text-decoration: none;
        }
        .download-block:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
        }
        .download-icon {
            font-size: 2rem; flex-shrink: 0;
            width: 48px; height: 48px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(79,140,255,.1);
            border-radius: 10px;
        }
        .download-info { flex: 1; }
        .download-info strong {
            display: block; font-size: .9rem; color: var(--text);
            font-weight: 600; margin-bottom: .2rem;
        }
        .download-info span {
            font-size: .78rem; color: var(--muted);
        }
        .download-arrow {
            color: var(--accent); font-size: 1.3rem; flex-shrink: 0;
        }

        .no-file {
            text-align: center; padding: 1.5rem;
            color: var(--muted); font-size: .88rem;
            border: 1px dashed var(--border); border-radius: 12px;
        }

        /* ── Footer ──────────────────────────────────────────────────── */
        footer {
            margin-top: 3rem; text-align: center;
            color: var(--muted); font-size: .8rem;
        }
        footer a { color: var(--accent); text-decoration: none; }

        /* ── Animations ──────────────────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .card { animation: fadeUp .5s ease both; }
        .td-result { animation: fadeUp .4s .15s ease both; }
    </style>
</head>
<body>

<div class="wrap">

    <!-- ── En-tête ───────────────────────────────────────────────────────── -->
    <header class="site-header">
        <div class="badge">🎓 Espace Étudiant</div>
        <h1>Travaux <span>Dirigés</span></h1>
        <p>Entrez votre clé d'accès pour récupérer votre TD</p>
    </header>

    <!-- ── Formulaire / Résultat ─────────────────────────────────────────── -->
    <div class="card">

        <?php if ($error): ?>
            <div class="alert alert-error">
                <span>⚠️</span>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <!-- Formulaire de saisie clé -->
        <form method="POST" action="">
            <label class="form-label" for="cle_acces">Clé d'accès au TD</label>
            <div class="input-wrap">
                <span class="icon">🔑</span>
                <input
                    type="text"
                    id="cle_acces"
                    name="cle_acces"
                    placeholder="ex : TD2024-ALGO-001"
                    autocomplete="off"
                    autocapitalize="off"
                    spellcheck="false"
                    value="<?= htmlspecialchars($_POST['cle_acces'] ?? '') ?>"
                    required
                >
            </div>
            <button type="submit" class="btn-primary">Accéder au TD →</button>
        </form>

        <?php else: ?>
        <!-- Succès : affichage du TD -->
        <div class="alert alert-success">
            <span>✅</span>
            <div>TD trouvé ! Voici votre document.</div>
        </div>

        <div class="td-result">
            <div class="td-header">
                <div class="td-number">N° <?= htmlspecialchars($td['numero']) ?></div>
                <div class="td-meta">
                    <h2><?= htmlspecialchars($td['nom']) ?></h2>
                    <div class="chips">
                        <?php if ($td['niveau']): ?>
                            <span class="chip">🎓 <?= htmlspecialchars($td['niveau']) ?></span>
                        <?php endif; ?>
                        <?php if ($td['discipline_nom']): ?>
                            <span class="chip">📚 <?= htmlspecialchars($td['discipline_nom']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($td['description']): ?>
            <div class="td-description">
                <?= nl2br(htmlspecialchars($td['description'])) ?>
            </div>
            <?php endif; ?>

            <hr class="divider">

            <p style="font-size:.8rem;color:var(--muted);margin-bottom:.9rem;text-transform:uppercase;letter-spacing:.08em;font-weight:600;">
                Fichier du TD
            </p>

            <?php if ($td['fichier_path']): ?>
                <?php
                    $filename = basename($td['fichier_path']);
                    $icon     = getFileIcon($td['fichier_path']);
                    $ext      = strtoupper(pathinfo($td['fichier_path'], PATHINFO_EXTENSION));
                ?>
                <a
                    href="<?= htmlspecialchars('../' . ltrim($td['fichier_path'], '/')) ?>"
                    class="download-block"
                    download
                    target="_blank"
                >
                    <div class="download-icon"><?= $icon ?></div>
                    <div class="download-info">
                        <strong><?= htmlspecialchars($filename) ?></strong>
                        <span>Cliquez pour télécharger · <?= $ext ?></span>
                    </div>
                    <div class="download-arrow">↓</div>
                </a>
            <?php else: ?>
                <div class="no-file">
                    📭 Aucun fichier joint pour ce TD.
                </div>
            <?php endif; ?>

            <div style="margin-top:1.8rem;text-align:center;">
                <a href="?" style="color:var(--muted);font-size:.85rem;text-decoration:none;">
                    ← Saisir une autre clé
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer>
        Espace étudiant · <a href="mailto:fmaingokodoro@gmail.com">fmaingokodoro@gmail.com</a>
    </footer>
</div>

</body>
</html>
