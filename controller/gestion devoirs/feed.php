<?php
require_once __DIR__ . '/../../config/database.php';
$conn = getDBConnection();

// Query devoirs
$devoirs = $conn->query("SELECT * FROM devoirs ORDER BY id_devoir DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Query corrections
$corrections = $conn->query("SELECT * FROM corrections ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

include 'feed.html';
?>

<!-- Dynamic Feed Section -->
<section class="dynamic-feed">
    <div class="container">
        <h2>Derniers Devoirs Soumis</h2>
        <?php foreach ($devoirs as $devoir): ?>
            <div class="devoir-item">
                <h3><?php echo htmlspecialchars($devoir['titre']); ?></h3>
                <p><?php echo htmlspecialchars($devoir['description']); ?></p>
                <p>Niveau: <?php echo htmlspecialchars($devoir['niveau_difficulte']); ?> | Date: <?php echo $devoir['date_soumission']; ?></p>
                <?php if ($devoir['fichier']): ?>
                    <a href="/eduleb/uploads/<?php echo htmlspecialchars($devoir['fichier']); ?>" download>Télécharger</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <h2>Dernières Corrections</h2>
        <?php foreach ($corrections as $correction): ?>
            <div class="correction-item">
                <p><?php echo htmlspecialchars($correction['commentaire']); ?></p>
                <p>Type: <?php echo htmlspecialchars($correction['typefeedback']); ?> | Note: <?php echo $correction['note']; ?>/20</p>
                <p>Compétences: <?php echo htmlspecialchars($correction['competences']); ?> | Date: <?php echo $correction['datecorrection']; ?></p>
                <?php if ($correction['fichier']): ?>
                    <a href="/eduleb/uploads/<?php echo htmlspecialchars($correction['fichier']); ?>" download>Télécharger</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>