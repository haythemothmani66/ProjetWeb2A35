<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Statistiques des Événements</h2>
    <a href="<?php echo BASE_URL; ?>/AdminEvenement/index" class="btn btn-secondary">Retour a la liste</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><strong>Total:</strong> <?php echo (int) ($data['stats']['total'] ?? 0); ?></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><strong>Planifies:</strong> <?php echo (int) ($data['stats']['planifie'] ?? 0); ?></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><strong>En cours:</strong> <?php echo (int) ($data['stats']['en_cours'] ?? 0); ?></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><strong>Termines/Annules:</strong> <?php echo (int) (($data['stats']['termine'] ?? 0) + ($data['stats']['annule'] ?? 0)); ?></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Diagramme circulaire (repartition par statut)</div>
            <div class="card-body"><canvas id="eventsPieChart" height="260"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Diagramme en courbe (evolution des creations)</div>
            <div class="card-body"><canvas id="eventsLineChart" height="260"></canvas></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pieCtx = document.getElementById('eventsPieChart');
    var lineCtx = document.getElementById('eventsLineChart');
    if (!pieCtx || !lineCtx || typeof Chart === 'undefined') {
        return;
    }

    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Planifie', 'En cours', 'Termine', 'Annule'],
            datasets: [{
                data: [
                    <?php echo (int) ($data['stats']['planifie'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['en_cours'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['termine'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['annule'] ?? 0); ?>
                ],
                backgroundColor: ['#0d6efd', '#198754', '#6f42c1', '#dc3545']
            }]
        }
    });

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($data['evolutionLabels'] ?? []); ?>,
            datasets: [{
                label: 'Nombre d\'evenements',
                data: <?php echo json_encode($data['evolutionData'] ?? []); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.2)',
                fill: true,
                tension: 0.25
            }]
        }
    });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
