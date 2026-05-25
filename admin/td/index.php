<?php
/**
 * admin/td/index.php
 * Gestion des TD – s'intègre dans le layout admin existant
 */

// ── Chemins ───────────────────────────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../uploads/td/');
define('UPLOAD_URL', '../uploads/td/');

// ── Auth : session admin existante ────────────────────────────────────────────
require_once __DIR__ . '/../includes/admin_layout.php';
require_once __DIR__ . '/../../config/database.php';
$pdo = getPDO();

// ── Helpers ───────────────────────────────────────────────────────────────────
// redirect() définie dans includes/functions.php
// Helper flash local (format simple {msg,type} pour ce module)
function tdGetFlash(): ?array {
    if (isset($_SESSION['td_flash'])) { $f = $_SESSION['td_flash']; unset($_SESSION['td_flash']); return $f; }
    return null;
}
function tdRedirect(string $msg = '', string $type = 'success'): void {
    if ($msg) $_SESSION['td_flash'] = ['msg' => $msg, 'type' => $type];
    header('Location: ' . $_SERVER['PHP_SELF']); exit;
}
function genKey(string $numero): string {
    return strtoupper(substr(md5($numero . microtime()), 0, 4))
         . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 4))
         . '-' . strtoupper(substr(md5(rand()), 0, 4));
}
// uploadFile() définie dans includes/functions.php

// ── Disciplines ───────────────────────────────────────────────────────────────
$disciplines = $pdo->query("SELECT id, nom_fr AS nom FROM disciplines ORDER BY nom_fr")->fetchAll(PDO::FETCH_ASSOC);

// ── Actions ───────────────────────────────────────────────────────────────────
$action  = $_POST['action'] ?? $_GET['action'] ?? 'list';
$id_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// DELETE
if ($action === 'delete' && $id_edit) {
    $row = $pdo->query("SELECT fichier_path FROM td WHERE id = $id_edit")->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['fichier_path']) { $f = UPLOAD_DIR . basename($row['fichier_path']); if (file_exists($f)) unlink($f); }
    $pdo->prepare("DELETE FROM td WHERE id = ?")->execute([$id_edit]);
    tdRedirect("TD supprimé avec succès.");
}

// TOGGLE
if ($action === 'toggle' && $id_edit) {
    $pdo->prepare("UPDATE td SET actif = 1 - actif WHERE id = ?")->execute([$id_edit]);
    tdRedirect("Statut mis à jour.");
}

// SAVE
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $numero      = trim($_POST['numero']      ?? '');
    $nom         = trim($_POST['nom']         ?? '');
    $description = trim($_POST['description'] ?? '');
    $niveau      = trim($_POST['niveau']      ?? '');
    $discipline  = (int)($_POST['discipline_id'] ?? 0) ?: null;
    $cle_acces   = trim($_POST['cle_acces']   ?? '') ?: genKey($numero);
    $actif       = isset($_POST['actif']) ? 1 : 0;

    $fichier_path = null;
    if (!empty($_FILES['fichier']['name'])) {
        $fname = uploadFile($_FILES['fichier'], UPLOAD_DIR);
        if ($fname) $fichier_path = 'uploads/td/' . $fname;
        else tdRedirect("Fichier invalide (format ou taille).", 'error');
    }

    if ($id) {
        $old = $pdo->query("SELECT fichier_path FROM td WHERE id = $id")->fetch();
        $fp  = $fichier_path ?? ($old['fichier_path'] ?? null);
        if ($fichier_path && $old['fichier_path']) { $f = UPLOAD_DIR . basename($old['fichier_path']); if (file_exists($f)) unlink($f); }
        $pdo->prepare("UPDATE td SET numero=:n,nom=:nom,description=:d,niveau=:niv,discipline_id=:disc,fichier_path=:fp,cle_acces=:cle,actif=:a WHERE id=:id")
            ->execute([':n'=>$numero,':nom'=>$nom,':d'=>$description,':niv'=>$niveau,':disc'=>$discipline,':fp'=>$fp,':cle'=>$cle_acces,':a'=>$actif,':id'=>$id]);
        tdRedirect("TD modifié avec succès.");
    } else {
        $pdo->prepare("INSERT INTO td (numero,nom,description,niveau,discipline_id,fichier_path,cle_acces,actif,created_by) VALUES (:n,:nom,:d,:niv,:disc,:fp,:cle,:a,1)")
            ->execute([':n'=>$numero,':nom'=>$nom,':d'=>$description,':niv'=>$niveau,':disc'=>$discipline,':fp'=>$fichier_path,':cle'=>$cle_acces,':a'=>$actif]);
        tdRedirect("TD ajouté avec succès.");
    }
}

// LIST
$search = trim($_GET['q'] ?? '');
$where  = $search ? "WHERE (t.numero LIKE :q OR t.nom LIKE :q OR t.niveau LIKE :q)" : '';
$params = $search ? [':q' => "%$search%"] : [];
$stmt   = $pdo->prepare("SELECT t.*, d.nom_fr AS discipline_nom FROM td t LEFT JOIN disciplines d ON d.id = t.discipline_id $where ORDER BY t.created_at DESC");
$stmt->execute($params);
$tds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// EDIT / VIEW
$edit_td = null;
if (in_array($action, ['edit','view']) && $id_edit)
    $edit_td = $pdo->query("SELECT * FROM td WHERE id = $id_edit")->fetch(PDO::FETCH_ASSOC);

$flash = tdGetFlash();
$page_title = "Gestion des TD";
?>
<style>
/* ── TD MODULE – styles additionnels ───────────────────────────────────────── */
.td-flash{border-radius:10px;padding:.75rem 1.1rem;display:flex;align-items:center;gap:.7rem;font-size:.88rem;margin-bottom:1.5rem;}
.td-flash.ok{background:rgba(26,127,90,.1);border:1px solid rgba(26,127,90,.3);color:#1a7f5a;}
.td-flash.err{background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.3);color:#dc3545;}

.td-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:2rem;}
.td-stat{background:#fff;border:1px solid #e9ecef;border-radius:12px;padding:1.1rem 1.3rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.04);}
.td-stat .num{font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;margin-bottom:.15rem;}
.td-stat .lbl{font-size:.7rem;color:#6c757d;text-transform:uppercase;letter-spacing:.06em;}
.td-stat.s-total .num{color:var(--navy,#0a2342);}
.td-stat.s-actif  .num{color:#1a7f5a;}
.td-stat.s-off    .num{color:#6c757d;}

.td-ph{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;}
.td-ph h2{font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--navy,#0a2342);margin:0;}
.btn-add{background:var(--navy,#0a2342);color:#c9a84c;border:none;border-radius:10px;padding:.55rem 1.2rem;font-weight:700;font-size:.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;text-decoration:none;}
.btn-add:hover{opacity:.88;color:#c9a84c;}

.td-search{display:flex;gap:.6rem;margin-bottom:1.5rem;}
.td-search input{flex:1;border:1.5px solid #dee2e6;border-radius:10px;padding:.6rem 1rem;font-size:.9rem;outline:none;transition:border-color .2s;}
.td-search input:focus{border-color:var(--navy,#0a2342);}
.td-search .btn-s{background:var(--navy,#0a2342);color:#c9a84c;border:none;border-radius:10px;padding:.6rem 1.3rem;font-weight:600;font-size:.85rem;cursor:pointer;}

.td-wrap{background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05);}
.td-tbl{width:100%;border-collapse:collapse;font-size:.85rem;}
.td-tbl thead tr{background:#f8f9fa;}
.td-tbl thead th{padding:.7rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#6c757d;border-bottom:1px solid #dee2e6;white-space:nowrap;}
.td-tbl tbody tr{border-bottom:1px solid #f1f3f5;transition:background .1s;}
.td-tbl tbody tr:last-child{border-bottom:none;}
.td-tbl tbody tr:hover{background:#fafbfc;}
.td-tbl td{padding:.75rem 1rem;vertical-align:middle;}

.tdn{font-family:'Playfair Display',serif;font-size:.78rem;font-weight:700;background:var(--navy,#0a2342);color:#c9a84c;padding:.28rem .6rem;border-radius:6px;white-space:nowrap;}
.tdn-name{font-weight:600;color:var(--navy,#0a2342);font-size:.88rem;}
.tdn-sub{font-size:.75rem;color:#6c757d;margin-top:.1rem;}
.s-on{display:inline-flex;align-items:center;gap:.3rem;background:rgba(26,127,90,.1);color:#1a7f5a;border-radius:5px;padding:.2rem .55rem;font-size:.72rem;font-weight:600;}
.s-off{display:inline-flex;align-items:center;gap:.3rem;background:#f1f3f5;color:#6c757d;border-radius:5px;padding:.2rem .55rem;font-size:.72rem;font-weight:600;}
.cle{font-size:.73rem;font-family:monospace;letter-spacing:.04em;background:#f1f3f5;color:#495057;border:1px solid #dee2e6;padding:.22rem .55rem;border-radius:6px;cursor:pointer;user-select:all;}
.cle:hover{background:#e9ecef;}
.tda{display:flex;gap:.35rem;}
.btd{border:none;border-radius:7px;padding:.35rem .7rem;font-size:.78rem;cursor:pointer;font-weight:600;transition:all .15s;}
.btd-v{background:#f1f3f5;color:var(--navy,#0a2342);}   .btd-v:hover{background:#e2e6ea;}
.btd-e{background:rgba(10,35,66,.08);color:var(--navy,#0a2342);} .btd-e:hover{background:rgba(10,35,66,.16);}
.btd-t{background:rgba(26,127,90,.1);color:#1a7f5a;}    .btd-t:hover{background:rgba(26,127,90,.2);}
.btd-d{background:rgba(220,53,69,.08);color:#dc3545;}   .btd-d:hover{background:rgba(220,53,69,.18);}
.td-empty{text-align:center;padding:3.5rem 2rem;color:#6c757d;}
.td-empty .ico{font-size:3rem;display:block;margin-bottom:.8rem;}

/* MODAL */
.td-ov{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(3px);z-index:1050;display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem;overflow-y:auto;}
.td-mdl{background:#fff;border-radius:18px;width:100%;max-width:620px;box-shadow:0 24px 64px rgba(0,0,0,.18);animation:mIn .25s ease;}
@keyframes mIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
.md-hd{display:flex;align-items:center;justify-content:space-between;padding:1.3rem 1.7rem;border-bottom:1px solid #f1f3f5;}
.md-hd h3{font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--navy,#0a2342);margin:0;}
.md-x{background:#f1f3f5;border:none;border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6c757d;font-size:1rem;}
.md-x:hover{background:#e2e6ea;}
.md-bd{padding:1.7rem;}
.md-ft{padding:1.1rem 1.7rem;border-top:1px solid #f1f3f5;display:flex;justify-content:flex-end;gap:.6rem;}
.r2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.fg{margin-bottom:1.1rem;}
.fg label{display:block;font-size:.72rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#6c757d;margin-bottom:.4rem;}
.fg input[type=text],.fg select,.fg textarea,.fg input[type=file]{width:100%;border:1.5px solid #dee2e6;border-radius:9px;padding:.65rem .9rem;font-size:.9rem;outline:none;transition:border-color .2s;font-family:'DM Sans',sans-serif;}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--navy,#0a2342);}
.fg textarea{resize:vertical;min-height:85px;line-height:1.6;}
.kw{display:flex;gap:.5rem;}
.kw input{flex:1;}
.kbtn{background:#f1f3f5;border:1.5px solid #dee2e6;border-radius:9px;padding:.65rem .9rem;font-size:.78rem;font-weight:600;cursor:pointer;white-space:nowrap;color:#495057;}
.kbtn:hover{background:#e2e6ea;}
.tgw{display:flex;align-items:center;gap:.7rem;margin-top:.2rem;}
.tg{position:relative;width:40px;height:22px;}
.tg input{opacity:0;width:0;height:0;}
.tg .sl{position:absolute;inset:0;background:#dee2e6;border-radius:99px;transition:.2s;cursor:pointer;}
.tg .sl::before{content:'';position:absolute;width:16px;height:16px;background:#fff;border-radius:50%;left:3px;top:3px;transition:.2s;}
.tg input:checked+.sl{background:#1a7f5a;}
.tg input:checked+.sl::before{transform:translateX(18px);}
.vs{background:#f8f9fa;border-radius:10px;padding:1rem 1.2rem;margin-bottom:.8rem;}
.vs h5{font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#6c757d;margin:0 0 .7rem;}
.mg{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
.mk{font-size:.73rem;color:#6c757d;}
.mv{font-size:.88rem;font-weight:600;color:var(--navy,#0a2342);margin-top:.1rem;}
.btn-c{background:#f1f3f5;color:#495057;border:none;border-radius:9px;padding:.6rem 1.2rem;font-weight:600;font-size:.85rem;cursor:pointer;}
.btn-c:hover{background:#e2e6ea;}
.btn-sv{background:var(--navy,#0a2342);color:#c9a84c;border:none;border-radius:9px;padding:.6rem 1.2rem;font-weight:700;font-size:.85rem;cursor:pointer;}
.btn-sv:hover{opacity:.88;}
.td-toast{position:fixed;bottom:1.5rem;right:1.5rem;background:#1a7f5a;color:#fff;padding:.6rem 1.1rem;border-radius:10px;font-size:.82rem;font-weight:700;z-index:2000;display:none;}
@media(max-width:576px){.r2{grid-template-columns:1fr;}.mg{grid-template-columns:1fr;}.td-ph{flex-direction:column;}}
</style>

<?php if ($flash): ?>
<div class="td-flash <?= $flash['type']==='success' ? 'ok' : 'err' ?>">
    <?= $flash['type']==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<?php
    $total  = count($tds);
    $actifs = count(array_filter($tds, fn($t) => $t['actif']));
?>
<div class="td-stats">
    <div class="td-stat s-total"><div class="num"><?= $total ?></div><div class="lbl">Total TD</div></div>
    <div class="td-stat s-actif"><div class="num"><?= $actifs ?></div><div class="lbl">Actifs</div></div>
    <div class="td-stat s-off"><div class="num"><?= $total - $actifs ?></div><div class="lbl">Désactivés</div></div>
</div>

<div class="td-ph">
    <h2><i class="fas fa-file-alt me-2" style="color:var(--accent,#c9a84c)"></i>Travaux Dirigés</h2>
    <a href="?action=add" class="btn-add"><i class="fas fa-plus"></i> Nouveau TD</a>
</div>

<form class="td-search" method="GET">
    <input type="text" name="q" placeholder="🔍  Rechercher par numéro, nom, niveau…" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn-s">Rechercher</button>
    <?php if ($search): ?>
        <a href="?" style="align-self:center;font-size:.82rem;color:#6c757d;text-decoration:none;">✕ effacer</a>
    <?php endif; ?>
</form>

<div class="td-wrap">
    <table class="td-tbl">
        <thead>
            <tr>
                <th>N°</th><th>Nom du TD</th><th>Niveau</th>
                <th>Discipline</th><th>Clé d'accès</th><th>Statut</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($tds)): ?>
            <tr><td colspan="7">
                <div class="td-empty">
                    <span class="ico">📂</span>
                    <p><?= $search ? "Aucun TD trouvé pour « ".htmlspecialchars($search)." »" : "Aucun TD enregistré." ?></p>
                    <a href="?action=add" class="btn-add" style="display:inline-flex;margin-top:.5rem;"><i class="fas fa-plus"></i> Premier TD</a>
                </div>
            </td></tr>
        <?php else: ?>
            <?php foreach ($tds as $td): ?>
            <tr>
                <td><span class="tdn"><?= htmlspecialchars($td['numero']) ?></span></td>
                <td>
                    <div class="tdn-name"><?= htmlspecialchars($td['nom']) ?></div>
                    <?php if ($td['description']): ?>
                    <div class="tdn-sub"><?= mb_strimwidth(htmlspecialchars($td['description']), 0, 55, '…') ?></div>
                    <?php endif; ?>
                </td>
                <td style="font-size:.83rem;"><?= htmlspecialchars($td['niveau'] ?: '—') ?></td>
                <td style="font-size:.83rem;"><?= htmlspecialchars($td['discipline_nom'] ?? '—') ?></td>
                <td><code class="cle" onclick="copyKey(this)" title="Copier"><?= htmlspecialchars($td['cle_acces']) ?></code></td>
                <td>
                    <?php if ($td['actif']): ?>
                        <span class="s-on">● Actif</span>
                    <?php else: ?>
                        <span class="s-off">○ Inactif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="tda">
                        <a href="?action=view&id=<?= $td['id'] ?>"><button class="btd btd-v" title="Voir">👁</button></a>
                        <a href="?action=edit&id=<?= $td['id'] ?>"><button class="btd btd-e" title="Modifier">✏️</button></a>
                        <a href="?action=toggle&id=<?= $td['id'] ?>" onclick="return confirm('Changer le statut ?')">
                            <button class="btd btd-t" title="Toggle"><?= $td['actif'] ? '⏸' : '▶' ?></button>
                        </a>
                        <a href="?action=delete&id=<?= $td['id'] ?>" onclick="return confirm('Supprimer définitivement ce TD ?')">
                            <button class="btd btd-d" title="Supprimer">🗑</button>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php /* ═══ MODAL AJOUT / ÉDITION ═══════════════════════════════════════════ */ ?>
<?php if (in_array($action, ['add','edit'])): ?>
<div class="td-ov">
    <div class="td-mdl">
        <div class="md-hd">
            <h3><?= $action === 'edit' ? '✏️ Modifier le TD' : '➕ Nouveau TD' ?></h3>
            <button class="md-x" onclick="window.location='?'">✕</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id"     value="<?= (int)($edit_td['id'] ?? 0) ?>">
            <div class="md-bd">
                <div class="r2">
                    <div class="fg">
                        <label>Numéro *</label>
                        <input type="text" name="numero" placeholder="ex : 001" required value="<?= htmlspecialchars($edit_td['numero'] ?? '') ?>">
                    </div>
                    <div class="fg">
                        <label>Niveau</label>
                        <input type="text" name="niveau" placeholder="ex : L2, M1…" value="<?= htmlspecialchars($edit_td['niveau'] ?? '') ?>">
                    </div>
                </div>
                <div class="fg">
                    <label>Nom du TD *</label>
                    <input type="text" name="nom" placeholder="ex : Algorithmique – TP 3" required value="<?= htmlspecialchars($edit_td['nom'] ?? '') ?>">
                </div>
                <div class="fg">
                    <label>Discipline</label>
                    <select name="discipline_id">
                        <option value="">— Aucune —</option>
                        <?php foreach ($disciplines as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($edit_td['discipline_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg">
                    <label>Description</label>
                    <textarea name="description" placeholder="Objectifs, consignes…"><?= htmlspecialchars($edit_td['description'] ?? '') ?></textarea>
                </div>
                <div class="fg">
                    <label>Fichier <?= $action==='edit' ? '(vide = conserver)' : '' ?></label>
                    <input type="file" name="fichier" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,.rar,.txt,.odt">
                    <?php if (!empty($edit_td['fichier_path'])): ?>
                        <small style="color:#6c757d;font-size:.75rem;display:block;margin-top:.3rem">
                            Actuel : <?= htmlspecialchars(basename($edit_td['fichier_path'])) ?>
                        </small>
                    <?php endif; ?>
                </div>
                <div class="fg">
                    <label>Clé d'accès</label>
                    <div class="kw">
                        <input type="text" name="cle_acces" id="cle_acces" placeholder="Auto-générée si vide" value="<?= htmlspecialchars($edit_td['cle_acces'] ?? '') ?>">
                        <button type="button" class="kbtn" onclick="genKey()">🔄 Générer</button>
                    </div>
                </div>
                <div class="fg">
                    <label>Statut</label>
                    <div class="tgw">
                        <label class="tg">
                            <input type="checkbox" name="actif" value="1" <?= ($edit_td['actif'] ?? 1) ? 'checked' : '' ?>>
                            <span class="sl"></span>
                        </label>
                        <span style="font-size:.88rem;color:#6c757d">TD actif (visible par les étudiants)</span>
                    </div>
                </div>
            </div>
            <div class="md-ft">
                <button type="button" class="btn-c" onclick="window.location='?'">Annuler</button>
                <button type="submit" class="btn-sv">
                    <?= $action === 'edit' ? '💾 Enregistrer' : '➕ Créer le TD' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php /* ═══ MODAL VISUALISATION ═══════════════════════════════════════════ */ ?>
<?php if ($action === 'view' && $edit_td): ?>
<div class="td-ov">
    <div class="td-mdl">
        <div class="md-hd">
            <h3>👁 Détails du TD</h3>
            <button class="md-x" onclick="window.location='?'">✕</button>
        </div>
        <div class="md-bd">
            <div class="vs">
                <h5>Informations générales</h5>
                <div class="mg">
                    <div><div class="mk">Numéro</div><div class="mv"><span class="tdn"><?= htmlspecialchars($edit_td['numero']) ?></span></div></div>
                    <div><div class="mk">Statut</div><div class="mv">
                        <?php if ($edit_td['actif']): ?><span class="s-on">● Actif</span><?php else: ?><span class="s-off">○ Inactif</span><?php endif; ?>
                    </div></div>
                    <div><div class="mk">Nom</div><div class="mv"><?= htmlspecialchars($edit_td['nom']) ?></div></div>
                    <div><div class="mk">Niveau</div><div class="mv"><?= htmlspecialchars($edit_td['niveau'] ?: '—') ?></div></div>
                </div>
            </div>
            <?php if ($edit_td['description']): ?>
            <div class="vs">
                <h5>Description</h5>
                <div style="font-size:.88rem;line-height:1.7;color:#495057"><?= nl2br(htmlspecialchars($edit_td['description'])) ?></div>
            </div>
            <?php endif; ?>
            <div class="vs">
                <h5>Clé d'accès étudiant</h5>
                <code class="cle" onclick="copyKey(this)"><?= htmlspecialchars($edit_td['cle_acces']) ?></code>
                <small style="color:#6c757d;font-size:.72rem;margin-left:.5rem">cliquer pour copier</small>
            </div>
            <div class="vs">
                <h5>Fichier joint</h5>
                <?php if ($edit_td['fichier_path']): ?>
                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($edit_td['fichier_path']) ?>" target="_blank"
                       style="font-size:.88rem;color:var(--navy,#0a2342);font-weight:600;">
                        📄 <?= htmlspecialchars(basename($edit_td['fichier_path'])) ?> ↗
                    </a>
                <?php else: ?>
                    <span style="color:#6c757d;font-size:.85rem">Aucun fichier joint.</span>
                <?php endif; ?>
            </div>
            <div style="font-size:.75rem;color:#adb5bd;margin-top:.3rem">
                Créé le <?= date('d/m/Y H:i', strtotime($edit_td['created_at'])) ?>
                <?php if ($edit_td['updated_at'] !== $edit_td['created_at']): ?>
                    · Modifié le <?= date('d/m/Y H:i', strtotime($edit_td['updated_at'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="md-ft">
            <button class="btn-c" onclick="window.location='?'">Fermer</button>
            <a href="?action=edit&id=<?= $edit_td['id'] ?>"><button class="btn-sv">✏️ Modifier</button></a>
        </div>
    </div>
</div>
<?php endif; ?>

<div id="td-toast" class="td-toast">✅ Clé copiée !</div>

<script>
function copyKey(el) {
    navigator.clipboard.writeText(el.textContent.trim()).then(() => {
        const t = document.getElementById('td-toast');
        t.style.display = 'block';
        setTimeout(() => t.style.display = 'none', 2000);
    });
}
function genKey() {
    const c = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    const s = () => Array.from({length:4}, () => c[Math.floor(Math.random()*c.length)]).join('');
    document.getElementById('cle_acces').value = s()+'-'+s()+'-'+s();
}
document.querySelectorAll('.td-ov').forEach(o =>
    o.addEventListener('click', e => { if (e.target === o) window.location = '?'; })
);
</script>