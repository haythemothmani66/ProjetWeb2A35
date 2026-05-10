-- Migration table devoirs et correction (module gestion_de_devoirs)
-- Adapter pour utiliser FK vers user(id) au lieu de users(id_user)
-- Base : edumatch

-- ============================================================
-- Table devoirs
-- ============================================================
CREATE TABLE IF NOT EXISTS `devoirs` (
  `id_devoir` INT(11) NOT NULL AUTO_INCREMENT,
  `titre` VARCHAR(150) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `fichier` VARCHAR(255) DEFAULT NULL,
  `date_soumission` DATE DEFAULT NULL,
  `niveau_difficulte` VARCHAR(50) DEFAULT NULL,
  `type_erreur_predominant` VARCHAR(50) DEFAULT NULL,
  `temps_estime_resolution` INT(11) DEFAULT NULL,
  `progression_eleve` INT(11) DEFAULT NULL,
  `mots_cles` VARCHAR(255) DEFAULT NULL,
  `urgence` VARCHAR(50) DEFAULT NULL,
  `id_eleve` INT(11) DEFAULT NULL,
  `sentiment` VARCHAR(20) DEFAULT 'neutre',
  `sentiment_score` FLOAT DEFAULT 0.5,
  `alerte_urgence` TINYINT(1) DEFAULT 0,
  `date_analyse` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_devoir`),
  KEY `idx_devoirs_id_eleve` (`id_eleve`),
  CONSTRAINT `fk_devoirs_user` FOREIGN KEY (`id_eleve`) REFERENCES `user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table correction
-- ============================================================
CREATE TABLE IF NOT EXISTS `correction` (
  `id_correction` INT(11) NOT NULL AUTO_INCREMENT,
  `commentaire` TEXT DEFAULT NULL,
  `fichier_corrige` VARCHAR(255) DEFAULT NULL,
  `date_correction` DATE DEFAULT NULL,
  `type_feedback` VARCHAR(50) DEFAULT NULL,
  `note_estimee` INT(11) DEFAULT NULL,
  `competences_evaluees` VARCHAR(255) DEFAULT NULL,
  `nombre_iterations` INT(11) DEFAULT NULL,
  `suggestions_personnalisees` TEXT DEFAULT NULL,
  `ressources_recommandees` TEXT DEFAULT NULL,
  `rapidite_correction` INT(11) DEFAULT NULL,
  `ton_feedback` VARCHAR(50) DEFAULT NULL,
  `id_devoir` INT(11) DEFAULT NULL,
  `id_encadrant` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id_correction`),
  KEY `idx_correction_id_devoir` (`id_devoir`),
  KEY `idx_correction_id_encadrant` (`id_encadrant`),
  CONSTRAINT `fk_correction_devoir` FOREIGN KEY (`id_devoir`) REFERENCES `devoirs`(`id_devoir`) ON DELETE CASCADE,
  CONSTRAINT `fk_correction_encadrant` FOREIGN KEY (`id_encadrant`) REFERENCES `user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
