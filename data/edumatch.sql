-- ============================================================
-- EduMatch — Schema SQL
-- Base de données : edumatch
-- ============================================================

CREATE DATABASE IF NOT EXISTS edumatch
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE edumatch;

-- ------------------------------------------------------------
-- Table : user
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user (
    id            INT          NOT NULL AUTO_INCREMENT,
    nom           VARCHAR(100) NOT NULL,
    prenom        VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password      VARCHAR(255) NOT NULL,
    telephone     VARCHAR(20)  DEFAULT NULL,
    role          ENUM('admin','encadrant','etudiant') NOT NULL DEFAULT 'etudiant',
    statut        TINYINT(1)   NOT NULL DEFAULT 1,
    photo         VARCHAR(255) NOT NULL DEFAULT 'default.png',
    token_verif   VARCHAR(64)  DEFAULT NULL,
    reset_code    VARCHAR(6)   DEFAULT NULL,
    reset_expires DATETIME     DEFAULT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table : profil — relation 1-1 avec user
-- bio_text : optionnel (NULL autorisé)
-- niveau   : optionnel — rempli si role = etudiant
-- specialite : optionnel — rempli si role = encadrant
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS profil (
    id_profil   INT  NOT NULL AUTO_INCREMENT,
    user_id     INT  NOT NULL,
    bio_text    TEXT     DEFAULT NULL COMMENT 'Optionnel',
    niveau      VARCHAR(100) DEFAULT NULL COMMENT 'Etudiant uniquement',
    specialite  VARCHAR(100) DEFAULT NULL COMMENT 'Encadrant uniquement',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_profil),
    UNIQUE KEY uq_user_id (user_id),
    CONSTRAINT fk_profil_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Données initiales — Admin par défaut
-- Email    : admin@edumatch.tn
-- Password : Admin123!
-- ------------------------------------------------------------
INSERT INTO user (nom, prenom, email, password, role, statut, photo, token_verif, created_at)
VALUES (
    'Admin',
    'EduMatch',
    'admin@edumatch.tn',
    '$2y$10$G9X6/QiU/4I6dpUiI40By.OP7PdA72EfqEluCtD.By27SfxqzPTEO',
    'admin',
    1,
    'default.png',
    NULL,
    NOW()
);

INSERT INTO profil (user_id, bio_text, created_at)
VALUES (LAST_INSERT_ID(), NULL, NOW());
