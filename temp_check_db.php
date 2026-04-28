<?php
require_once __DIR__ . '/config/database.php';
$result = $conn->query('SELECT id, organization_name, email, telephone, partner_type, status, created_at FROM partenaires ORDER BY id DESC LIMIT 5');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo implode(' | ', $row) . PHP_EOL;
    }
} else {
    echo 'ERROR: ' . $conn->error . PHP_EOL;
}
