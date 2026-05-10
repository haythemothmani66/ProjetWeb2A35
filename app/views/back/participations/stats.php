<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Statistiques des Participants</h2>
    <a href="<?php echo BASE_URL; ?>/AdminParticipation/index" class="btn btn-secondary">Retour a la liste</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-2"><div class="card"><div class="card-body"><strong>Total:</strong> <?php echo (int) ($data['stats']['total'] ?? 0); ?></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><strong>Inscrits:</strong> <?php echo (int) ($data['stats']['inscrit'] ?? 0); ?></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><strong>Confirmes:</strong> <?php echo (int) ($data['stats']['confirme'] ?? 0); ?></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><strong>Presents:</strong> <?php echo (int) ($data['stats']['present'] ?? 0); ?></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><strong>Absents:</strong> <?php echo (int) ($data['stats']['absent'] ?? 0); ?></div></div></div>
    <div class="col-md-2"><div class="card"><div class="card-body"><strong>Annules:</strong> <?php echo (int) ($data['stats']['annule'] ?? 0); ?></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Diagramme circulaire (repartition par statut)</div>
            <div class="card-body"><canvas id="participantsPieChart" height="260"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Diagramme en courbe (evolution des inscriptions)</div>
            <div class="card-body"><canvas id="participantsLineChart" height="260"></canvas></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pieCtx = document.getElementById('participantsPieChart');
    var lineCtx = document.getElementById('participantsLineChart');
    if (!pieCtx || !lineCtx || typeof Chart === 'undefined') {
        return;
    }

    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Inscrit', 'Confirme', 'Present', 'Absent', 'Annule'],
            datasets: [{
                data: [
                    <?php echo (int) ($data['stats']['inscrit'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['confirme'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['present'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['absent'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['annule'] ?? 0); ?>
                ],
                backgroundColor: ['#0d6efd', '#20c997', '#198754', '#ffc107', '#dc3545']
            }]
        }
    });

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($data['evolutionLabels'] ?? []); ?>,
            datasets: [{
                label: 'Nombre d\'inscriptions',
                data: <?php echo json_encode($data['evolutionData'] ?? []); ?>,
                borderColor: '#6610f2',
                backgroundColor: 'rgba(102,16,242,0.2)',
                fill: true,
                tension: 0.25
            }]
        }
    });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
