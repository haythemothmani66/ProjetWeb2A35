-- =====================================================
-- Migration : Ajout des tables du module Partenariat
-- Base de donnees : edumatch
-- Date : 2026-05-07
-- =====================================================

-- Table des partenaires
CREATE TABLE IF NOT EXISTS `partenaires` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NULL,
    `organization_name` VARCHAR(255) NOT NULL,
    `partner_type` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `telephone` VARCHAR(50) NOT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT NULL,
    `domain` VARCHAR(255) DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
    `view_count` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `auth_key_hash` CHAR(64) DEFAULT NULL,
    `embedding_vector` TEXT DEFAULT NULL,
    UNIQUE KEY `uniq_partenaires_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des contrats (lies aux partenaires)
CREATE TABLE IF NOT EXISTS `contrats` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NULL,
    `contract_ref` VARCHAR(64) DEFAULT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `type_contrat` VARCHAR(100) NOT NULL,
    `statut` VARCHAR(50) NOT NULL,
    `date_debut` DATE DEFAULT NULL,
    `date_fin` DATE DEFAULT NULL,
    `date_signature` DATE DEFAULT NULL,
    `renouvellement_auto` VARCHAR(10) DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `pdf_file_name` VARCHAR(255) DEFAULT NULL,
    `auth_key_hash` CHAR(64) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_contrats_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des offres d'emploi
CREATE TABLE IF NOT EXISTS `offre_emploi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `competences_requises` TEXT DEFAULT NULL,
    `lieu` VARCHAR(100) NOT NULL,
    `type_contrat` VARCHAR(50) NOT NULL,
    `salaire_min` DECIMAL(10,2) DEFAULT NULL,
    `salaire_max` DECIMAL(10,2) DEFAULT NULL,
    `date_creation` DATE DEFAULT NULL,
    `date_limite` DATE NOT NULL,
    `statut` VARCHAR(20) DEFAULT 'ouverte'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des candidatures (FK vers user.id)
CREATE TABLE IF NOT EXISTS `candidature` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `lettre_motivation` TEXT DEFAULT NULL,
    `cv_url` VARCHAR(255) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `statut` VARCHAR(20) DEFAULT 'en_attente',
    `date_candidature` DATE DEFAULT NULL,
    `date_reponse` DATE DEFAULT NULL,
    `etudiant_id` INT NOT NULL,
    `offre_id` INT NOT NULL,
    KEY `etudiant_id` (`etudiant_id`),
    KEY `offre_id` (`offre_id`),
    CONSTRAINT `candidature_fk_user` FOREIGN KEY (`etudiant_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    CONSTRAINT `candidature_fk_offre` FOREIGN KEY (`offre_id`) REFERENCES `offre_emploi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
