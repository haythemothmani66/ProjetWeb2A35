CREATE DATABASE IF NOT EXISTS event_db;
USE event_db;

CREATE TABLE IF NOT EXISTS categories (
    id_categorie INT AUTO_INCREMENT PRIMARY KEY,
    nom_categorie VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    couleur VARCHAR(7) NOT NULL,
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (couleur REGEXP '^#[A-Fa-f0-9]{6}$')
);

CREATE TABLE IF NOT EXISTS evenements (
    id_evenement INT AUTO_INCREMENT PRIMARY KEY,
    id_categorie INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type_evenement ENUM('en ligne', 'présentiel', 'hybride') NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    partenariat ENUM('oui', 'non') DEFAULT 'non',
    capacite_max INT NOT NULL,
    nb_places_disponibles INT NOT NULL,
    lieu VARCHAR(255),
    lien_acces VARCHAR(255),
    organisateur VARCHAR(100),
    statut ENUM('planifié', 'en cours', 'terminé', 'annulé') DEFAULT 'planifié',
    image_evenement VARCHAR(255),
    FOREIGN KEY (id_categorie) REFERENCES categories(id_categorie),
    CHECK (capacite_max > 0),
    CHECK (nb_places_disponibles >= 0),
    CHECK (nb_places_disponibles <= capacite_max),
    CHECK (date_fin >= date_debut)
);

CREATE TABLE IF NOT EXISTS participations (
    id_participation INT AUTO_INCREMENT PRIMARY KEY,
    id_evenement INT NOT NULL,
    id_user INT,
    nom_participant VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telephone VARCHAR(20),
    statut_participation ENUM('inscrit', 'confirmé', 'présent', 'absent', 'annulé') DEFAULT 'inscrit',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    mode_participation ENUM('en ligne', 'présentiel') NOT NULL,
    feedback TEXT,
    note INT CHECK (note BETWEEN 0 AND 5),
    FOREIGN KEY (id_evenement) REFERENCES evenements(id_evenement) ON DELETE CASCADE,
    UNIQUE KEY unique_participation (id_evenement, email)
);
