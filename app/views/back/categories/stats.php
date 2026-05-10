<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Statistiques des Catégories</h2>
    <a href="<?php echo BASE_URL; ?>/AdminCategorie/index" class="btn btn-secondary">Retour a la liste</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><strong>Total:</strong> <?php echo (int) ($data['stats']['total'] ?? 0); ?></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><strong>Actives:</strong> <?php echo (int) ($data['stats']['actif'] ?? 0); ?></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><strong>Inactives:</strong> <?php echo (int) ($data['stats']['inactif'] ?? 0); ?></div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Diagramme circulaire (repartition des statuts)</div>
            <div class="card-body"><canvas id="categoriesPieChart" height="260"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">Diagramme en courbe (evolution des creations)</div>
            <div class="card-body"><canvas id="categoriesLineChart" height="260"></canvas></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var pieCtx = document.getElementById('categoriesPieChart');
    var lineCtx = document.getElementById('categoriesLineChart');
    if (!pieCtx || !lineCtx || typeof Chart === 'undefined') {
        return;
    }

    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: ['Actif', 'Inactif'],
            datasets: [{
                data: [
                    <?php echo (int) ($data['stats']['actif'] ?? 0); ?>,
                    <?php echo (int) ($data['stats']['inactif'] ?? 0); ?>
                ],
                backgroundColor: ['#198754', '#dc3545']
            }]
        }
    });

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($data['evolutionLabels'] ?? []); ?>,
            datasets: [{
                label: 'Nombre de categories',
                data: <?php echo json_encode($data['evolutionData'] ?? []); ?>,
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.2)',
                fill: true,
                tension: 0.25
            }]
        }
    });
});
</script>

<?php $content = ob_get_clean(); include __DIR__ . '/../../layouts/back_layout.php'; ?>
