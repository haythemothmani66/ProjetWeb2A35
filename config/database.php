<?php

// PDO Database Connection
// Using native PHP with PDO

try {
    // Database credentials from environment with XAMPP-safe fallbacks
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = (int) ($_ENV['DB_PORT'] ?? 3306);
    $db = $_ENV['DB_NAME'] ?? 'bdd_edumatch';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASSWORD'] ?? '';
    
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
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
