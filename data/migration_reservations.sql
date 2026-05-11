-- ============================================================
-- Migration : Module Reservation de seances avec encadrants
-- Base : edumatch
-- ============================================================

-- Table disponibilites : creneaux ou l'encadrant est disponible
CREATE TABLE IF NOT EXISTS `disponibilites` (
  `id_disponibilite` INT(11) NOT NULL AUTO_INCREMENT,
  `id_encadrant` INT(11) NOT NULL,
  `jour_semaine` ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `heure_debut` TIME NOT NULL,
  `heure_fin` TIME NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 1,
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_disponibilite`),
  KEY `idx_disponibilites_encadrant` (`id_encadrant`),
  CONSTRAINT `fk_disponibilite_encadrant` FOREIGN KEY (`id_encadrant`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table reservations
CREATE TABLE IF NOT EXISTS `reservations` (
  `id_reservation` INT(11) NOT NULL AUTO_INCREMENT,
  `id_etudiant` INT(11) NOT NULL,
  `id_encadrant` INT(11) NOT NULL,
  `date_reservation` DATE NOT NULL,
  `heure_debut` TIME NOT NULL,
  `heure_fin` TIME NOT NULL,
  `matiere` VARCHAR(100) NOT NULL,
  `sujet` TEXT,
  `mode` ENUM('en_ligne','presentiel') NOT NULL DEFAULT 'en_ligne',
  `statut` ENUM('en_attente','acceptee','refusee','annulee','terminee') NOT NULL DEFAULT 'en_attente',
  `notes_etudiant` TEXT NULL,
  `notes_encadrant` TEXT NULL,
  `token_action` CHAR(64) NOT NULL,
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_reponse` TIMESTAMP NULL,
  PRIMARY KEY (`id_reservation`),
  UNIQUE KEY `uniq_token` (`token_action`),
  UNIQUE KEY `uniq_creneau` (`id_encadrant`,`date_reservation`,`heure_debut`),
  KEY `idx_reservations_etudiant` (`id_etudiant`),
  KEY `idx_reservations_encadrant` (`id_encadrant`),
  KEY `idx_reservations_date` (`date_reservation`),
  CONSTRAINT `fk_reservation_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `user`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reservation_encadrant` FOREIGN KEY (`id_encadrant`) REFERENCES `user`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
