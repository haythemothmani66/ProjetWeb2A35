<?php

class Database {
    private $host = "localhost";
    private $db_name = "event_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $serverConn = new PDO(
                "mysql:host=" . $this->host . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $serverConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $serverConn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeSchema();
        } catch (PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }

        return $this->conn;
    }

    private function initializeSchema() {
        $sql = "
        CREATE TABLE IF NOT EXISTS categories (
            id_categorie INT AUTO_INCREMENT PRIMARY KEY,
            nom_categorie VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            couleur VARCHAR(7) NOT NULL,
            statut ENUM('actif', 'inactif') DEFAULT 'actif',
            date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
            CONSTRAINT fk_evenement_categorie
                FOREIGN KEY (id_categorie) REFERENCES categories(id_categorie)
                ON UPDATE CASCADE ON DELETE RESTRICT
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
            note INT NULL,
            CONSTRAINT fk_participation_evenement
                FOREIGN KEY (id_evenement) REFERENCES evenements(id_evenement)
                ON UPDATE CASCADE ON DELETE CASCADE
        );
        ";

        $this->conn->exec($sql);
    }
}
