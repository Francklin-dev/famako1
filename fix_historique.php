<?php
require_once __DIR__ . '/config/database.php';
$pdo = getPDO();

// Vide la table historique
$pdo->exec("DELETE FROM historique");

// Insère les bonnes données
$pdo->exec("INSERT INTO historique (annee, titre_fr, titre_en, contenu_fr, contenu_en) VALUES
(2018, 'Fondation de la Faculté', 'Faculty Foundation', 'Création de la Faculté Maïngo Ködörö à Bangui, République Centrafricaine, sous l\'inspiration Baha\'ie, avec pour mission de former des cadres en Sciences de l\'Éducation.', 'Creation of Maïngo Ködörö Faculty in Bangui, Central African Republic, with Baha\'i inspiration, with the mission to train Educational Sciences executives.'),
(2026, 'Lancement du programme DSPR', 'DSPR Program Launch', 'Ouverture du Diplôme Supérieur de Préparation à la Recherche (DSPR), première étape obligatoire vers le Doctorat. Les premières promotions accueillent 45 étudiants issus de toute la RCA.', 'Opening of the Superior Diploma in Research Preparation (DSPR), the mandatory first step toward the PhD. The first cohorts welcome 45 students from across the CAR.')
");

echo '✅ Historique mis à jour !';
?>