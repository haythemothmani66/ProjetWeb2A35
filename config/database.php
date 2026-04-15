<?php

// PDO Database Connection
// Using native PHP with PDO

try {
    // Database credentials
    $host = 'localhost';
    $db = 'bdd_edumatch';
    $user = 'root';          // XAMPP default
    $pass = '';              // XAMPP default (empty)
    
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
