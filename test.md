# 📋 FaMaKo — Documentation Technique des Applications

> **Faculté Maïngo Ködörö** · WAMP64 · PHP 8+ · MySQL  
> Dernière mise à jour : mai 2026

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Structure des dossiers](#2-structure-des-dossiers)
3. [Application 1 — Site Principal FaMaKo](#3-application-1--site-principal-famako)
4. [Application 2 — Bibliothèque Numérique](#4-application-2--bibliothèque-numérique)
5. [Module TD — Travaux Dirigés](#5-module-td--travaux-dirigés)
6. [Sécurité & Authentification](#6-sécurité--authentification)
7. [Base de données](#7-base-de-données)
8. [Fichiers de configuration](#8-fichiers-de-configuration)
9. [Checklist de déploiement](#9-checklist-de-déploiement)
10. [Erreurs courantes & solutions](#10-erreurs-courantes--solutions)

---

## 1. Vue d'ensemble

| Application | URL locale | Rôles | Login |
|---|---|---|---|
| Site principal | `http://localhost/famako1` | `admin` | `/admin/login.php` |
| Admin bibliothèque | `http://localhost/bibliotheque/admin/` | `admin`, `bibliothecaire` | `/bibliotheque/login.php` |
| Espace lecteur | `http://localhost/bibliotheque/dashboard.php` | `lecteur` | `/bibliotheque/login.php` |
| Espace étudiant TD | `http://localhost/famako1/pages/td.php` | Public (clé d'accès) | — |

---

## 2. Structure des dossiers

```
wamp64/www/
├── famako1/                        ← Site principal
│   ├── admin/
│   │   ├── includes/
│   │   │   └── admin_layout.php   ← Layout + vérif session admin
│   │   ├── cours/
│   │   ├── inscriptions/
│   │   ├── td/
│   │   │   └── index.php          ← Gestion des TD
│   │   ├── users/
│   │   ├── dashboard.php
│   │   ├── login.php
│   │   └── logout.php
│   ├── assets/
│   │   ├── css/main.css
│   │   ├── js/main.js
│   │   └── uploads/
│   ├── config/
│   │   └── database.php           ← PDO + constantes
│   ├── includes/
│   │   ├── functions.php          ← Toutes les fonctions globales
│   │   ├── header.php
│   │   └── footer.php
│   ├── pages/
│   │   └── td.php                 ← Accès étudiant (clé)
│   ├── sql/
│   │   └── famako.sql
│   └── index.php
│
└── bibliotheque/                   ← Bibliothèque numérique
    ├── admin/
    │   ├── dashboard.php
    │   ├── documents.php
    │   ├── categories.php
    │   └── users.php
    ├── includes/
    │   ├── functions.php
    │   ├── header.php
    │   ├── sidebar.php
    │   └── topbar.php
    ├── config/
    │   └── database.php
    ├── uploads/
    │   ├── documents/
    │   ├── avatars/
    │   └── covers/
    ├── login.php
    ├── logout.php
    ├── dashboard.php
    └── index.php
```

---

## 3. Application 1 — Site Principal FaMaKo

### 3.1 Accès public

| Page | Fichier | Description |
|---|---|---|
| Accueil | `index.php` | Présentation de la faculté |
| Présentation | `pages/presentation.php` | Histoire, mission |
| Cours | `pages/cours.php` | Liste des cours disponibles |
| Inscription | `pages/inscription.php` | Formulaire d'inscription étudiant |
| Frais | `pages/frais.php` | Grille des frais |
| Contact | `pages/contact.php` | Formulaire de contact |
| TD Étudiant | `pages/td.php` | Accès TD par clé d'accès |

### 3.2 Espace Administration

**Login :** `http://localhost/famako1/admin/login.php`  
**Table :** `users` (colonne `role = 'admin'`, `is_active = 1`)

| Page | Fichier | Fonction |
|---|---|---|
| Tableau de bord | `admin/dashboard.php` | Stats globales, accès rapides |
| Cours | `admin/cours/index.php` | CRUD cours |
| Ajouter cours | `admin/cours/ajouter.php` | Upload PDF/vidéo |
| Inscriptions | `admin/inscriptions/index.php` | Gérer les dossiers |
| Travaux Dirigés | `admin/td/index.php` | CRUD TD + clés d'accès |
| Utilisateurs | `admin/users/index.php` | Gestion des comptes admin |

### 3.3 Authentification admin (session)

```php
// Variables de session créées lors du login
$_SESSION['user_id']   = $user['id'];
$_SESSION['user']      = $user;          // tableau complet
$_SESSION['user_role'] = $user['role'];  // 'admin'
```

**Vérification dans chaque page protégée :**

```php
// Méthode 1 — via admin_layout.php (recommandé)
require_once __DIR__ . '/../../includes/admin_layout.php';
// Le layout vérifie isLoggedIn() + rôle admin automatiquement

// Méthode 2 — via check.php (nouveau)
require_once __DIR__ . '/../../check.php';
checkAdmin();
```

---

## 4. Application 2 — Bibliothèque Numérique

### 4.1 Accès public

| Page | Description |
|---|---|
| `login.php` | Page de connexion |
| `index.php` | Redirige selon session |
| `search.php` | Recherche documents |

### 4.2 Espace Lecteur

**Conditions :** connecté + `is_active = 1` (tout rôle)

| Page | Description |
|---|---|
| `dashboard.php` | Tableau de bord personnel |
| `upload.php` | Déposer un document |
| `favorites.php` | Mes favoris |
| `profile.php` | Mon profil |
| `notifications.php` | Mes notifications |

**Vérification :**
```php
require_once '../includes/functions.php';
requireLogin(); // redirige vers /bibliotheque/login.php si non connecté
```

### 4.3 Espace Admin / Bibliothécaire

**Conditions :** `role IN ('admin', 'bibliothecaire')` + `is_active = 1`

| Page | Description |
|---|---|
| `admin/dashboard.php` | Stats globales |
| `admin/documents.php` | Valider / supprimer documents |
| `admin/categories.php` | Gérer les catégories |
| `admin/users.php` | Gérer les comptes |

**Vérification :**
```php
require_once '../includes/functions.php';
requireRole('admin', 'bibliothecaire');
// OU avec check.php :
require_once '../check.php';
checkBibAdmin();
```

### 4.4 Authentification bibliothèque (session)

```php
// Variables de session créées lors du login
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_role'] = $user['role'];  // 'lecteur' | 'bibliothecaire' | 'admin'
```

---

## 5. Module TD — Travaux Dirigés

### 5.1 Table SQL

```sql
CREATE TABLE td (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  numero        VARCHAR(10)  NOT NULL,         -- ex: 001
  nom           VARCHAR(255) NOT NULL,          -- ex: Algorithmique – TP 3
  description   TEXT,
  niveau        VARCHAR(100),                   -- ex: L2, M1
  discipline_id INT,
  fichier_path  VARCHAR(500),                   -- relatif à la racine
  cle_acces     VARCHAR(64)  NOT NULL,          -- ex: ABCD-EFGH-1234
  actif         TINYINT(1)   DEFAULT 1,
  created_at    DATETIME     DEFAULT NOW(),
  updated_at    DATETIME     DEFAULT NOW() ON UPDATE NOW(),
  created_by    INT,
  FOREIGN KEY (discipline_id) REFERENCES disciplines(id)
);
```

### 5.2 Fichiers

| Fichier | Rôle | Accès |
|---|---|---|
| `admin/td/index.php` | CRUD complet | Admin connecté |
| `pages/td.php` | Affichage étudiant | Public (clé d'accès) |

### 5.3 Format de la clé d'accès

```
XXXX-XXXX-XXXX
Ex : ABCD-1234-EFGH
```

Générée automatiquement ou saisie manuellement. Le bouton **🔄 Générer** crée une clé aléatoire en JavaScript.

### 5.4 Flux étudiant

```
Étudiant → pages/td.php → saisit la clé → BD vérifie (actif=1) → affiche le TD + lien téléchargement
```

### 5.5 Upload fichiers

- Dossier : `uploads/td/`  
- Extensions autorisées : `pdf, doc, docx, ppt, pptx, zip, rar, txt, odt`  
- Taille max : **50 Mo**  
- Nom généré : `YYYYMMDD_HHIISS_<random>.ext`

---

## 6. Sécurité & Authentification

### 6.1 Fichier `check.php`

Garde universel à inclure en tête des pages protégées.

**Emplacement recommandé :**
- `famako1/check.php` pour le site principal
- `bibliotheque/check.php` pour la bibliothèque

**Fonctions disponibles :**

| Fonction | Usage | Redirige vers |
|---|---|---|
| `checkAdmin()` | Pages admin site FaMaKo | `/admin/login.php` |
| `checkBibUser()` | Pages lecteur bibliothèque | `/login.php` |
| `checkBibAdmin()` | Pages admin bibliothèque | `/login.php` ou `/dashboard.php` |

**Exemple d'utilisation :**

```php
<?php
// En haut de admin/td/index.php
require_once __DIR__ . '/../../check.php';
checkAdmin(); // bloque si non admin

// En haut de bibliotheque/admin/dashboard.php
require_once __DIR__ . '/../check.php';
checkBibAdmin(); // bloque si non admin/biblio

// En haut de bibliotheque/dashboard.php
require_once __DIR__ . '/check.php';
checkBibUser(); // bloque si non connecté
```

### 6.2 Expiration de session

La constante `SESSION_TTL` (défaut : 7200 secondes = 2h) contrôle l'expiration par inactivité.

```php
// Pour modifier la durée dans config/database.php ou en tête de page :
define('SESSION_TTL', 3600); // 1 heure
```

### 6.3 Protection CSRF

Disponible dans `functions.php` :

```php
// Dans le formulaire HTML :
<?= csrfField() ?>

// En tête du traitement POST :
verifyCsrf();
```

### 6.4 Headers de sécurité (.htaccess)

```apache
# Déjà présent dans famako1/.htaccess
Options -Indexes
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
```

---

## 7. Base de données

### 7.1 Site principal — Tables principales

| Table | Description |
|---|---|
| `users` | Comptes admin (role = 'admin') |
| `disciplines` | Disciplines (colonne `nom_fr`) |
| `cours` | Cours disponibles |
| `inscriptions` | Dossiers d'inscription étudiants |
| `bibliotheque` | Documents bibliothèque liés au site |
| `td` | Travaux Dirigés |
| `matricule_sequences` | Séquences pour génération matricules |

### 7.2 Bibliothèque — Tables principales

| Table | Description |
|---|---|
| `users` | Comptes lecteurs/admin/bibliothécaires |
| `bib_documents` | Documents uploadés |
| `bib_categories` | Catégories de documents |
| `bib_notifications` | Notifications utilisateurs |

### 7.3 Connexion

**Site principal** — `famako1/config/database.php` :
```php
getPDO()  // retourne une instance PDO
```

**Bibliothèque** — `bibliotheque/config/database.php` :
```php
getDB()   // retourne une instance PDO
```

> ⚠️ Les deux applications utilisent des fonctions différentes (`getPDO` vs `getDB`). Ne pas les mélanger.

---

## 8. Fichiers de configuration

### 8.1 `famako1/config/database.php`

Constantes définies :
- `BASE_URL` — URL racine du site
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
- `UPLOAD_DIR` — chemin absolu uploads
- `MAX_FILE_SIZE`
- `APP_NAME`

### 8.2 `bibliotheque/config/database.php`

Constantes définies :
- `APP_URL` — URL racine bibliothèque
- `APP_NAME`, `APP_SUBTITLE`
- `BIB_UPLOAD_PATH`
- `BIB_MAX_UPLOAD`
- `BIB_ALLOWED_EXT`
- `BIB_ITEMS_PER_PAGE`

---

## 9. Checklist de déploiement

### ✅ Site principal FaMaKo

- [ ] Importer `sql/famako.sql` dans MySQL
- [ ] Configurer `config/database.php` (host, user, pass, dbname)
- [ ] Vérifier `BASE_URL` dans `config/database.php`
- [ ] Créer le dossier `uploads/td/` avec droits en écriture
- [ ] Copier `check.php` à la racine de `famako1/`
- [ ] Tester login admin : `admin/login.php`
- [ ] Vérifier que `admin/td/index.php` s'affiche sans erreur

### ✅ Bibliothèque numérique

- [ ] Configurer `config/database.php`
- [ ] Vérifier `APP_URL` dans `config/database.php`
- [ ] Créer les dossiers `uploads/documents/`, `uploads/avatars/`, `uploads/covers/`
- [ ] Copier `check.php` à la racine de `bibliotheque/`
- [ ] Tester login : `login.php`
- [ ] Tester accès admin : `admin/dashboard.php`

### ✅ Sécurité

- [ ] `check.php` présent dans les deux racines
- [ ] Sessions PHP configurées (durée, cookie httponly)
- [ ] `.htaccess` `Options -Indexes` actif
- [ ] Dossier `uploads/` protégé contre l'exécution PHP

---

## 10. Erreurs courantes & solutions

| Erreur | Cause | Solution |
|---|---|---|
| `Cannot redeclare redirect()` | `redirect()` dans `td/index.php` ET `functions.php` | Utiliser `tdRedirect()` dans `td/index.php` |
| `Cannot redeclare uploadFile()` | Doublon entre `td/index.php` et `functions.php` | Supprimer la déclaration dans `td/index.php` |
| `Column not found: 'nom'` | Table `disciplines` utilise `nom_fr` | Remplacer `d.nom` par `d.nom_fr` |
| `Failed to open stream: database.php` | Mauvais chemin relatif | Compter les niveaux : `admin/td/` = `../../config/` |
| `Undefined variable $pdo in admin_layout.php` | `$GLOBALS['pdo']` avant init | Remplacer par `getPDO()` |
| `Session expirée` | `SESSION_TTL` dépassé | Redéfinir `SESSION_TTL` ou se reconnecter |
| Page blanche après login | Erreur PHP masquée | Activer `display_errors` temporairement |
| Upload échoue | Dossier `uploads/td/` absent | Créer le dossier + `chmod 755` |

---

## Contacts & Références

- **Framework CSS :** Bootstrap 5.3  
- **Icônes :** Font Awesome 6.5  
- **Police :** Playfair Display + DM Sans (Google Fonts)  
- **Serveur local :** WAMP64 · PHP 8.x · MySQL 8.x  
- **Chemin racine :** `C:\wamp64\www\`
