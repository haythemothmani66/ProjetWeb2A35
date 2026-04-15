<?php
$host = "localhost";
$dbname = "databaseedumatch";
$username = "root";
$password = "";

function getDBConnection() {
    global $host, $dbname, $username, $password;
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8",
            $username,
            $password
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;

    } catch (PDOException $e) {
        die("CONNECTION ERROR: " . $e->getMessage());
    }
}

// Test connection
// $conn = getDBConnection();
// echo "CONNECTED SUCCESSFULLY";
?>
?>