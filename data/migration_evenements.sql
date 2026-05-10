-- Migration tables module Evenements
-- Adapter pour utiliser FK vers user(id) au lieu de hardcode id_user=1
-- Base : edumatch

-- ============================================================
-- Table categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id_categorie` INT AUTO_INCREMENT PRIMARY KEY,
  `nom_categorie` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `couleur` VARCHAR(7) NOT NULL,
  `statut` ENUM('actif', 'inactif') DEFAULT 'actif',
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table evenements
-- ============================================================
CREATE TABLE IF NOT EXISTS `evenements` (
  `id_evenement` INT AUTO_INCREMENT PRIMARY KEY,
  `id_categorie` INT NOT NULL,
  `titre` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `type_evenement` ENUM('en ligne', 'présentiel', 'hybride') NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `heure_debut` TIME NOT NULL,
  `heure_fin` TIME NOT NULL,
  `partenariat` ENUM('oui', 'non') DEFAULT 'non',
  `capacite_max` INT NOT NULL,
  `nb_places_disponibles` INT NOT NULL,
  `lieu` VARCHAR(255),
  `lien_acces` VARCHAR(255),
  `organisateur` VARCHAR(100),
  `statut` ENUM('planifié', 'en cours', 'terminé', 'annulé') DEFAULT 'planifié',
  `image_evenement` VARCHAR(255),
  CONSTRAINT `fk_evenement_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categories`(`id_categorie`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table participations
-- ============================================================
CREATE TABLE IF NOT EXISTS `participations` (
  `id_participation` INT AUTO_INCREMENT PRIMARY KEY,
  `id_evenement` INT NOT NULL,
  `id_user` INT NULL,
  `nom_participant` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `telephone` VARCHAR(20),
  `statut_participation` ENUM('inscrit', 'confirmé', 'présent', 'absent', 'annulé') DEFAULT 'inscrit',
  `date_inscription` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `mode_participation` ENUM('en ligne', 'présentiel') NOT NULL,
  `feedback` TEXT,
  `note` INT NULL,
  CONSTRAINT `fk_participation_evenement` FOREIGN KEY (`id_evenement`) REFERENCES `evenements`(`id_evenement`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_participation_user` FOREIGN KEY (`id_user`) REFERENCES `user`(`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Donnees de demonstration
-- ============================================================

INSERT IGNORE INTO `categories` (`id_categorie`, `nom_categorie`, `description`, `couleur`, `statut`) VALUES
(1, 'Conférence', 'Conférences éducatives et professionnelles', '#3b82f6', 'actif'),
(2, 'Atelier', 'Ateliers pratiques et formations', '#10b981', 'actif'),
(3, 'Hackathon', 'Compétitions de programmation et innovation', '#f59e0b', 'actif'),
(4, 'Séminaire', 'Séminaires et présentations académiques', '#8b5cf6', 'actif'),
(5, 'Métiers avancés', 'Présentation des métiers technologiques avancés', '#ef4444', 'actif');
