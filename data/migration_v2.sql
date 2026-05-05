-- ============================================================
-- EduMatch — Migration V2 : Metiers avances
-- Etat connecte, historique connexion, verification etudiant,
-- champs profil etudiant enrichi, geocoding, parametres
-- ============================================================

USE edumatch;

-- ------------------------------------------------------------
-- 1. Table user : ajouter role partenariat + etat connecte + verification_student
-- ------------------------------------------------------------
ALTER TABLE user
    MODIFY COLUMN role ENUM('admin','encadrant','etudiant','partenariat') NOT NULL DEFAULT 'etudiant',
    ADD COLUMN etat ENUM('online','offline') NOT NULL DEFAULT 'offline' AFTER statut,
    ADD COLUMN verification_student TINYINT(1) NOT NULL DEFAULT 0 AFTER etat;

-- ------------------------------------------------------------
-- 2. Table profil : ajouter champs etudiant + geocoding
-- ------------------------------------------------------------
ALTER TABLE profil
    ADD COLUMN classe VARCHAR(100) DEFAULT NULL COMMENT 'Etudiant: classe (ex: 2A35)',
    ADD COLUMN email_universitaire VARCHAR(150) DEFAULT NULL COMMENT 'Etudiant: email universitaire',
    ADD COLUMN card_image VARCHAR(255) DEFAULT NULL COMMENT 'Etudiant: image carte etudiant',
    ADD COLUMN adresse VARCHAR(255) DEFAULT NULL COMMENT 'Adresse',
    ADD COLUMN etablissement_ecole VARCHAR(150) DEFAULT NULL COMMENT 'Etudiant: etablissement/ecole',
    ADD COLUMN identifiant_card VARCHAR(100) DEFAULT NULL COMMENT 'Etudiant: identifiant carte',
    ADD COLUMN annee_universitaire VARCHAR(50) DEFAULT NULL COMMENT 'Etudiant: annee universitaire (ex: 2025-2026)';

-- ------------------------------------------------------------
-- 3. Table connexion_history : historique de connexions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS connexion_history (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    connected_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    disconnected_at DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    INDEX idx_connected_at (connected_at),
    CONSTRAINT fk_connexion_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. Table parametres : config globale parametrable
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS parametres (
    id INT NOT NULL AUTO_INCREMENT,
    cle VARCHAR(100) NOT NULL,
    valeur VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cle (cle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parametre par defaut : nombre de jours trial pour etudiant non verifie
INSERT INTO parametres (cle, valeur, description) VALUES
('expiration_verification_jours', '7', 'Nombre de jours apres inscription pendant lesquels un etudiant non verifie peut se connecter (trial period)');
