-- ============================================================
-- Migration module Offre d'emploi
-- Base : edumatch
-- Adapte les tables `offreemploi`/`candidature` du dump bdd_edumatch
-- vers nos noms standard snake_case + ajout FK user(id)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing (vide de toute facon)
DROP TABLE IF EXISTS `candidature`;
DROP TABLE IF EXISTS `offre_emploi`;

-- ============================================================
-- Table offre_emploi (enrichie)
-- ============================================================
CREATE TABLE `offre_emploi` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `competences_requises` TEXT DEFAULT NULL,
  `lieu` VARCHAR(100) NOT NULL,
  `type_contrat` VARCHAR(50) NOT NULL,
  `salaire_min` DECIMAL(10,2) DEFAULT NULL,
  `salaire_max` DECIMAL(10,2) DEFAULT NULL,
  `date_creation` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `date_limite` DATETIME NOT NULL,
  `statut` VARCHAR(20) DEFAULT 'ouverte',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table candidature (enrichie : CV, IA matching, FK user)
-- ============================================================
CREATE TABLE `candidature` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user` INT(11) DEFAULT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `lettre_motivation` TEXT DEFAULT NULL,
  `cv_external_url` VARCHAR(255) DEFAULT NULL,
  `cv_file_path` VARCHAR(255) DEFAULT NULL,
  `cv_original_name` VARCHAR(255) DEFAULT NULL,
  `cv_mime` VARCHAR(100) DEFAULT NULL,
  `cv_size` INT(11) DEFAULT NULL,
  `cv_source` VARCHAR(10) DEFAULT NULL,
  `match_score` DECIMAL(5,2) DEFAULT NULL,
  `match_details` LONGTEXT DEFAULT NULL,
  `match_provider` VARCHAR(40) DEFAULT NULL,
  `match_model` VARCHAR(120) DEFAULT NULL,
  `match_generated_at` DATETIME DEFAULT NULL,
  `statut` VARCHAR(20) DEFAULT 'en_attente',
  `date_candidature` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `date_reponse` DATETIME DEFAULT NULL,
  `offre_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_candidature_offre` (`offre_id`),
  KEY `idx_candidature_user` (`id_user`),
  CONSTRAINT `fk_candidature_offre` FOREIGN KEY (`offre_id`) REFERENCES `offre_emploi`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_candidature_user` FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
