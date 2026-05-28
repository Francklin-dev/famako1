-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 27 mai 2026 à 18:54
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `famako_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `actualites`
--

DROP TABLE IF EXISTS `actualites`;
CREATE TABLE IF NOT EXISTS `actualites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre_fr` varchar(400) NOT NULL,
  `titre_en` varchar(400) DEFAULT NULL,
  `contenu_fr` text,
  `contenu_en` text,
  `image_path` varchar(1000) DEFAULT NULL,
  `publie` tinyint(1) DEFAULT '1',
  `user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque`
--

DROP TABLE IF EXISTS `bibliotheque`;
CREATE TABLE IF NOT EXISTS `bibliotheque` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(300) NOT NULL,
  `auteur` varchar(300) DEFAULT NULL,
  `description` text,
  `categorie` enum('these','article','manuel','rapport','cours','memoire','autre') DEFAULT 'autre',
  `fichier_path` varchar(1000) DEFAULT NULL,
  `fichier_nom` varchar(500) DEFAULT NULL,
  `fichier_taille` bigint DEFAULT NULL,
  `couverture_path` varchar(1000) DEFAULT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `annee_pub` year DEFAULT NULL,
  `langue` varchar(30) DEFAULT 'fr',
  `mots_cles` varchar(1000) DEFAULT NULL,
  `vues` int DEFAULT '0',
  `telechargements` int DEFAULT '0',
  `statut` enum('publie','archive','brouillon') DEFAULT 'publie',
  `user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_categorie` (`categorie`),
  KEY `idx_annee` (`annee_pub`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

DROP TABLE IF EXISTS `cours`;
CREATE TABLE IF NOT EXISTS `cours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `discipline_id` int DEFAULT NULL,
  `type_fichier` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'pdf',
  `fichier_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichier_nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichier_taille` int DEFAULT NULL,
  `url_video` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annee_cours` year DEFAULT NULL,
  `mois_cours` tinyint DEFAULT NULL,
  `code_acces` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code_acces_hint` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `vues` int DEFAULT '0',
  `telechargements` int DEFAULT '0',
  `actif` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id`, `titre`, `description`, `discipline_id`, `type_fichier`, `fichier_path`, `fichier_nom`, `fichier_taille`, `url_video`, `annee_cours`, `mois_cours`, `code_acces`, `code_acces_hint`, `user_id`, `vues`, `telechargements`, `actif`, `created_at`, `updated_at`) VALUES
(1, 'Philo', 'bjr', 5, 'video', 'cours_6a100e74181d1.mp4', 'GMT20260518-170549_Recording_1686x720.mp4', 245501010, '', '2026', 6, '$2y$10$46gCsgRve.Kx.osOxa7QbeoZa8tHZW/62OhfRlIbfVezYMJUwqfMu', '0809', 1, 0, 2, 1, '2026-05-22 09:06:12', '2026-05-22 09:08:22');

-- --------------------------------------------------------

--
-- Structure de la table `disciplines`
--

DROP TABLE IF EXISTS `disciplines`;
CREATE TABLE IF NOT EXISTS `disciplines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_fr` varchar(150) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ordre` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `disciplines`
--

INSERT INTO `disciplines` (`id`, `nom_fr`, `actif`, `created_at`, `ordre`) VALUES
(1, 'Droit privé', 1, '2026-05-20 19:46:50', 0),
(2, 'Droit public', 1, '2026-05-20 19:46:50', 0),
(3, 'Sciences économiques', 1, '2026-05-20 19:46:50', 0),
(4, 'Sciences politiques', 1, '2026-05-20 19:46:50', 0),
(5, 'Administration publique', 1, '2026-05-20 19:46:50', 0),
(6, 'Sociologie', 1, '2026-05-20 19:46:50', 0),
(7, 'Anthropologie', 1, '2026-05-20 19:46:50', 0),
(8, 'Histoire', 1, '2026-05-20 19:46:50', 0),
(9, 'Philosophie', 1, '2026-05-20 19:46:50', 0),
(10, 'Psychologie', 1, '2026-05-20 19:46:50', 0),
(11, 'Pédagogie', 1, '2026-05-20 19:46:50', 0),
(12, 'Didactique', 1, '2026-05-20 19:46:50', 0),
(13, 'Évaluation', 1, '2026-05-20 19:46:50', 0);

-- --------------------------------------------------------

--
-- Structure de la table `historique`
--

DROP TABLE IF EXISTS `historique`;
CREATE TABLE IF NOT EXISTS `historique` (
  `id` int NOT NULL AUTO_INCREMENT,
  `annee` year NOT NULL,
  `titre_fr` varchar(400) NOT NULL,
  `titre_en` varchar(400) DEFAULT NULL,
  `contenu_fr` text,
  `contenu_en` text,
  `image_path` varchar(1000) DEFAULT NULL,
  `ordre` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historique`
--

INSERT INTO `historique` (`id`, `annee`, `titre_fr`, `titre_en`, `contenu_fr`, `contenu_en`, `image_path`, `ordre`) VALUES
(1, '2018', 'Fondation de la Faculté', 'Faculty Foundation', 'La Faculté Maïngo Ködörö est fondée à Bangui avec le soutien de l\'Université de Bangui et de la communauté Baha\'ie locale. Elle vise à former des cadres spécialisés en Sciences de l\'Éducation.', 'Maïngo Ködörö Faculty is founded in Bangui with the support of the University of Bangui and the local Baha\'i community, aiming to train specialized executives in Educational Sciences.', NULL, 1),
(2, '2019', 'Lancement du programme DSPR', 'Launch of DSPR Program', 'Ouverture du Diplôme Supérieur de Préparation à la Recherche (DSPR), première étape obligatoire vers le Doctorat. Les premières promotions accueillent 45 étudiants issus de toute la RCA.', 'Opening of the Higher Diploma in Research Preparation (DSPR), the mandatory first step towards PhD. The first cohorts welcome 45 students from across CAR.', NULL, 2),
(3, '2021', 'Accréditation et partenariats', 'Accreditation and Partnerships', 'La Faculté obtient son accréditation officielle et signe des accords de coopération avec plusieurs universités africaines. Le programme doctoral est officiellement lancé.', 'The Faculty obtains its official accreditation and signs cooperation agreements with several African universities. The doctoral program is officially launched.', NULL, 3),
(4, '2023', 'Expansion des disciplines', 'Expansion of Disciplines', 'Extension à 13 disciplines couvrant l\'ensemble des Sciences de l\'Éducation. Inauguration de la bibliothèque numérique et de la salle multimédia.', 'Expansion to 13 disciplines covering all Educational Sciences. Inauguration of the digital library and multimedia room.', NULL, 4),
(5, '2026', 'Ouverture des inscriptions 2026', 'Opening of 2026 Registrations', 'Nouvelle campagne d\'inscriptions avec un dispositif en ligne renforcé. La Faculté accueille désormais des étudiants de toute l\'Afrique centrale.', 'New registration campaign with enhanced online system. The Faculty now welcomes students from all of Central Africa.', NULL, 5),
(6, '2018', 'Fondation de la Faculté', 'Faculty Foundation', 'La Faculté Maïngo Ködörö est fondée à Bangui avec le soutien de l\'Université de Bangui et de la communauté Baha\'ie locale. Elle vise à former des cadres spécialisés en Sciences de l\'Éducation.', 'Maïngo Ködörö Faculty is founded in Bangui with the support of the University of Bangui and the local Baha\'i community, aiming to train specialized executives in Educational Sciences.', NULL, 1),
(7, '2019', 'Lancement du programme DSPR', 'Launch of DSPR Program', 'Ouverture du Diplôme Supérieur de Préparation à la Recherche (DSPR), première étape obligatoire vers le Doctorat. Les premières promotions accueillent 45 étudiants issus de toute la RCA.', 'Opening of the Higher Diploma in Research Preparation (DSPR), the mandatory first step towards PhD. The first cohorts welcome 45 students from across CAR.', NULL, 2),
(8, '2021', 'Accréditation et partenariats', 'Accreditation and Partnerships', 'La Faculté obtient son accréditation officielle et signe des accords de coopération avec plusieurs universités africaines. Le programme doctoral est officiellement lancé.', 'The Faculty obtains its official accreditation and signs cooperation agreements with several African universities. The doctoral program is officially launched.', NULL, 3),
(9, '2023', 'Expansion des disciplines', 'Expansion of Disciplines', 'Extension à 13 disciplines couvrant l\'ensemble des Sciences de l\'Éducation. Inauguration de la bibliothèque numérique et de la salle multimédia.', 'Expansion to 13 disciplines covering all Educational Sciences. Inauguration of the digital library and multimedia room.', NULL, 4),
(10, '2026', 'Ouverture des inscriptions 2026', 'Opening of 2026 Registrations', 'Nouvelle campagne d\'inscriptions avec un dispositif en ligne renforcé. La Faculté accueille désormais des étudiants de toute l\'Afrique centrale.', 'New registration campaign with enhanced online system. The Faculty now welcomes students from all of Central Africa.', NULL, 5);

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(180) NOT NULL,
  `contact` varchar(30) DEFAULT NULL,
  `adresse` text,
  `pays` varchar(100) DEFAULT NULL,
  `date_naissance` date NOT NULL,
  `discipline_id` int NOT NULL,
  `matricule` varchar(10) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `cv_path` varchar(255) DEFAULT NULL,
  `diplome_path` varchar(255) DEFAULT NULL,
  `lettre_path` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','en_cours','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `notes` text,
  `user_id_traiteur` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_matricule` (`matricule`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_statut` (`statut`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_inscr_discipline` (`discipline_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `inscriptions`
--

INSERT INTO `inscriptions` (`id`, `nom`, `prenom`, `email`, `contact`, `adresse`, `pays`, `date_naissance`, `discipline_id`, `matricule`, `photo_path`, `cv_path`, `diplome_path`, `lettre_path`, `statut`, `notes`, `user_id_traiteur`, `created_at`, `updated_at`) VALUES
(1, 'JD', 'paul', 'vous@example.com', '+236 72090872', 'Bangui,RCA', 'République centrafricaine', '2010-05-06', 2, '2605100001', 'photo_6a100ff887c5a.png', 'cv_6a100ff887e5b.pdf', 'diplome_6a100ff887fc3.pdf', 'lettre_6a100ff888190.pdf', 'en_attente', NULL, NULL, '2026-05-22 09:12:40', '2026-05-22 09:12:40');

-- --------------------------------------------------------

--
-- Structure de la table `matricule_sequences`
--

DROP TABLE IF EXISTS `matricule_sequences`;
CREATE TABLE IF NOT EXISTS `matricule_sequences` (
  `prefix` char(6) NOT NULL,
  `last_seq` smallint UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `matricule_sequences`
--

INSERT INTO `matricule_sequences` (`prefix`, `last_seq`, `updated_at`) VALUES
('260101', 0, '2026-05-20 19:46:50'),
('260102', 0, '2026-05-20 19:46:50'),
('260103', 0, '2026-05-20 19:46:50'),
('260104', 0, '2026-05-20 19:46:50'),
('260105', 0, '2026-05-20 19:46:50'),
('260106', 0, '2026-05-20 19:46:50'),
('260107', 0, '2026-05-20 19:46:50'),
('260108', 0, '2026-05-20 19:46:50'),
('260109', 0, '2026-05-20 19:46:50'),
('260110', 0, '2026-05-20 19:46:50'),
('260111', 0, '2026-05-20 19:46:50'),
('260112', 0, '2026-05-20 19:46:50'),
('260510', 1, '2026-05-22 09:12:40');

-- --------------------------------------------------------

--
-- Structure de la table `td`
--

DROP TABLE IF EXISTS `td`;
CREATE TABLE IF NOT EXISTS `td` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `niveau` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipline_id` int DEFAULT NULL,
  `fichier_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cle_acces` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actif` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discipline_id` (`discipline_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `role` enum('admin','gestionnaire','bibliothecaire') DEFAULT 'gestionnaire',
  `avatar` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `avatar`, `is_active`, `created_at`, `last_login`) VALUES
(1, 'admin', 'admin@famako.edu', '$2y$12$hs9DJlyV.BMXJ1uICvXHYeElHoElsAWWQeXKSOdy1lOeWl7BFBY3W', 'Administrateur FaMaKo', 'admin', NULL, 1, '2026-05-13 08:55:42', '2026-05-26 10:47:34'),
(2, 'gestionnaire', 'gestion@famako.edu', '$2y$12$BN9qaMjWk7r5G8Q0N0Mcwe71y4GAcR2S2/ExJ5Jktz6R37niES22e', 'Gestionnaire', 'gestionnaire', NULL, 1, '2026-05-13 08:55:42', NULL),
(3, 'biblio', 'biblio@famako.edu', '$2y$12$oJOAAC3CgdQpAyCMOwLB6.A.Zx/shuBwKIEO7QZTgTTz76.Ky9JfS', 'Bibliothécaire', 'bibliothecaire', NULL, 1, '2026-05-13 08:55:42', NULL);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_inscriptions_admin`
-- (Voir ci-dessous la vue réelle)
--
DROP VIEW IF EXISTS `v_inscriptions_admin`;
CREATE TABLE IF NOT EXISTS `v_inscriptions_admin` (
`id` int
,`matricule` varchar(10)
,`nom` varchar(100)
,`prenom` varchar(100)
,`email` varchar(180)
,`contact` varchar(30)
,`pays` varchar(100)
,`date_naissance` date
,`discipline` varchar(150)
,`statut` enum('en_attente','en_cours','accepte','refuse')
,`notes` text
,`photo_path` varchar(255)
,`cv_path` varchar(255)
,`diplome_path` varchar(255)
,`lettre_path` varchar(255)
,`created_at` datetime
,`updated_at` datetime
,`annee_inscription` varchar(4)
,`mois_naissance` varchar(2)
,`annee_naissance_abbr` varchar(4)
,`numero_sequentiel` bigint unsigned
);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `bibliotheque`
--
ALTER TABLE `bibliotheque` ADD FULLTEXT KEY `idx_search` (`titre`,`auteur`,`description`,`mots_cles`);

-- --------------------------------------------------------

--
-- Structure de la vue `v_inscriptions_admin`
--
DROP TABLE IF EXISTS `v_inscriptions_admin`;

DROP VIEW IF EXISTS `v_inscriptions_admin`;
CREATE OR REPLACE VIEW `v_inscriptions_admin`  AS SELECT `i`.`id` AS `id`, `i`.`matricule` AS `matricule`, `i`.`nom` AS `nom`, `i`.`prenom` AS `prenom`, `i`.`email` AS `email`, `i`.`contact` AS `contact`, `i`.`pays` AS `pays`, `i`.`date_naissance` AS `date_naissance`, `d`.`nom_fr` AS `discipline`, `i`.`statut` AS `statut`, `i`.`notes` AS `notes`, `i`.`photo_path` AS `photo_path`, `i`.`cv_path` AS `cv_path`, `i`.`diplome_path` AS `diplome_path`, `i`.`lettre_path` AS `lettre_path`, `i`.`created_at` AS `created_at`, `i`.`updated_at` AS `updated_at`, concat('20',substr(`i`.`matricule`,1,2)) AS `annee_inscription`, substr(`i`.`matricule`,3,2) AS `mois_naissance`, concat('20',substr(`i`.`matricule`,5,2)) AS `annee_naissance_abbr`, cast(substr(`i`.`matricule`,7,4) as unsigned) AS `numero_sequentiel` FROM (`inscriptions` `i` left join `disciplines` `d` on((`d`.`id` = `i`.`discipline_id`))) ;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `actualites`
--
ALTER TABLE `actualites`
  ADD CONSTRAINT `actualites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `bibliotheque`
--
ALTER TABLE `bibliotheque`
  ADD CONSTRAINT `bibliotheque_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `fk_inscr_discipline` FOREIGN KEY (`discipline_id`) REFERENCES `disciplines` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
