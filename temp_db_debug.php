<?php
require_once __DIR__ . '/config/database.php';

$tables = $conn->query("SHOW TABLES");
if ($tables) {
    echo "Tables in db: \n";
    while ($row = $tables->fetch_row()) {
        echo $row[0] . "\n";
    }
}

$result = $conn->query('SELECT COUNT(*) AS count FROM partenaires');
if ($result) {
    $row = $result->fetch_assoc();
    echo "partenaires count: " . $row['count'] . "\n";
} else {
    echo 'ERROR COUNT: ' . $conn->error . "\n";
}
