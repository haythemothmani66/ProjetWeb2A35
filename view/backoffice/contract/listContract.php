<?php
declare(strict_types=1);
require dirname(__DIR__) . '/layout/header.php';
$contracts = $contracts ?? [];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>



<div class="card-body bg-white border-bottom p-3">
    <div class="row g-3 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by reference, company, type...">
            </div>
        </div>
        <div class="col-md-3">
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="actif">Actif</option>
                <option value="expire">Expiré</option>
                <option value="suspendu">Suspendu</option>
            </select>
        </div>
        <div class="col-md-2">
            <select id="sortSelect" class="form-select">
                <option value="number">Sort by #</option>
                <option value="reference">Sort by Reference</option>
                <option value="company">Sort by Company</option>
                <option value="type">Sort by Type</option>
                <option value="status">Sort by Status</option>
            </select>
        </div>
        <div class="col-md-2">
            <button id="resetFiltersBtn" class="btn btn-outline-secondary w-100">
                <i class="ti ti-refresh"></i> Reset
            </button>
        </div>
    </div>
</div>

<!-- SECTION STATISTIQUES CONTRATS -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti ti-chart-pie me-2"></i> Contracts by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="contractStatusPieChart" style="max-height: 300px; width: 100%;"></canvas>
                <div id="contractStatusLegend" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti ti-chart-line me-2"></i> Contracts Evolution</h5>
            </div>
            <div class="card-body">
                <canvas id="contractTrendLineChart" style="max-height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Cartes de résumé pour les contrats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-success bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-success" id="activeContractsCount">0</h3>
                <p class="text-muted mb-0">Actif</p>
                <i class="ti ti-file-check text-success fs-4"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-danger bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-danger" id="expiredContractsCount">0</h3>
                <p class="text-muted mb-0">Expiré</p>
                <i class="ti ti-calendar-off text-danger fs-4"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-warning bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-warning" id="suspendedContractsCount">0</h3>
                <p class="text-muted mb-0">Suspendu</p>
                <i class="ti ti-pause-circle text-warning fs-4"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-primary" id="totalContractsCount">0</h3>
                <p class="text-muted mb-0">Total Contrats</p>
                <i class="ti ti-files text-primary fs-4"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Contracts</h2>
        <div class="d-flex gap-2">
    <button id="exportPdfBtn" class="btn btn-danger btn-sm">
        <i class="ti ti-file-pdf"></i> Export PDF
    </button>
    <a class="btn btn-outline-secondary btn-sm" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'verification'])); ?>">Verification</a>
    <a class="btn btn-dark btn-sm" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'add'])); ?>">Add Contract</a>
</div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="contractsTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Document</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($contracts)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No contracts found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($contracts as $index => $contract): ?>
                    <?php
                    $status = (string)($contract['statut'] ?? 'Actif');
                    $statusClass = 'bg-success-subtle text-success-emphasis';
                    if (in_array($status, ['Expire', 'Expiré'], true)) {
                        $statusClass = 'bg-danger-subtle text-danger-emphasis';
                    } elseif ($status === 'Suspendu') {
                        $statusClass = 'bg-warning-subtle text-warning-emphasis';
                    }
                    ?>
                    <tr>
                        <td><?= (int)$index + 1; ?></td>
                        <td><?= h((string)($contract['contract_ref'] ?? '')); ?></td>
                        <td><?= h((string)($contract['company_name'] ?? '')); ?></td>
                        <td><?= h((string)($contract['type_contrat'] ?? '')); ?></td>
                        <td>
                            <div class="small text-muted">Start: <?= h((string)($contract['date_debut'] ?? '-')); ?></div>
                            <div class="small text-muted">End: <?= h((string)($contract['date_fin'] ?? '-')); ?></div>
                        </td>
                        <td><span class="badge <?= h($statusClass); ?>"><?= h($status); ?></span></td>
                        <td>
                            <?php if (!empty($contract['pdf_file_name'])): ?>
                                <a target="_blank" rel="noopener" href="<?= h(assetUrl((string)$contract['pdf_file_name'])); ?>">Open PDF</a>
                            <?php else: ?>
                                <span class="text-muted">No file</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= h(appUrl(['controller' => 'contract', 'action' => 'edit', 'id' => (int)$contract['id']])); ?>">Edit</a>
                            <form action="<?= h(appUrl(['controller' => 'contract', 'action' => 'delete', 'id' => (int)$contract['id']])); ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this contract?');">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('exportPdfBtn')?.addEventListener('click', function() {
    const { jsPDF } = window.jspdf;

    const rows = [];

    document.querySelectorAll('#contractsTable tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        // 8 colonnes : # | Reference | Company | Type | Dates | Status | Document | Actions
        if (cells.length >= 7 && !cells[0]?.innerText.includes('No contracts')) {
            const dateDivs = cells[4]?.querySelectorAll('.small');
            rows.push({
                reference: cells[1]?.innerText.trim() || '',                              // ← index 1
                company:   cells[2]?.innerText.trim() || '',                              // ← index 2
                type:      cells[3]?.innerText.trim() || '',                              // ← index 3
                startDate: dateDivs?.[0]?.innerText.replace('Start:', '').trim() || '-', // ← index 4, div[0]
                endDate:   dateDivs?.[1]?.innerText.replace('End:', '').trim()   || '-', // ← index 4, div[1]
                status:    cells[5]?.querySelector('span')?.innerText.trim() || ''       // ← index 5, badge
            });
        }
    });

    if (rows.length === 0) { alert('No data to export!'); return; }

    const pdf = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
    const primaryColor = [255, 127, 80];
    let yPos = 25;

    // ── En-tête ──
    pdf.setFontSize(20);
    pdf.setTextColor(...primaryColor);
    pdf.text('EduMatch Contracts Report', 15, yPos);
    yPos += 10;
    pdf.setFontSize(9);
    pdf.setTextColor(100, 100, 100);
    pdf.text(`Generated: ${new Date().toLocaleString()}`, 15, yPos);
    yPos += 6;
    pdf.text(`Total Contracts: ${rows.length}`, 15, yPos);
    yPos += 6;
    pdf.setDrawColor(200, 200, 200);
    pdf.line(15, yPos, 280, yPos);
    yPos += 10;

    // ── Configuration colonnes ──
    const headers   = ['Reference', 'Company', 'Type', 'Start Date', 'End Date', 'Status'];
    const colWidths = [40, 60, 45, 35, 35, 30]; // total = 245 mm (paysage)
    const maxChars  = [20, 28, 22, 16, 16, 12];

    const statusColors = {
        'actif':    [40, 167, 69],
        'expire':   [220, 53, 69],
        'expiré':   [220, 53, 69],
        'suspendu': [200, 150, 0]
    };

    const statusLabels = {
        'actif':    'Actif',
        'expire':   'Expiré',
        'expiré':   'Expiré',
        'suspendu': 'Suspendu'
    };

    // ── Helper : en-tête du tableau ──
    function drawHeader() {
        pdf.setFillColor(...primaryColor);
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'bold');
        let xPos = 15;
        for (let i = 0; i < headers.length; i++) {
            pdf.rect(xPos, yPos, colWidths[i], 9, 'F');
            pdf.text(headers[i], xPos + 2, yPos + 6);
            xPos += colWidths[i];
        }
        yPos += 11;
    }

    drawHeader();

    // ── Lignes de données ──
    pdf.setFontSize(8);
    pdf.setFont('helvetica', 'normal');

    for (let i = 0; i < rows.length; i++) {
        if (yPos > 180) {
            pdf.addPage();
            yPos = 20;
            drawHeader();
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
        }

        // Fond alterné
        if (i % 2 === 0) {
            pdf.setFillColor(245, 245, 245);
            pdf.rect(15, yPos - 2, 245, 8, 'F');
        }

        const values = [
            rows[i].reference,
            rows[i].company,
            rows[i].type,
            rows[i].startDate,
            rows[i].endDate,
            rows[i].status
        ];

        let xPos = 15;
        for (let col = 0; col < headers.length; col++) {
            let val = values[col] || '';
            if (val.length > maxChars[col]) val = val.substring(0, maxChars[col] - 2) + '..';

            if (col === 5) {
                // Colonne Status : couleur selon valeur
                const key = val.toLowerCase();
                const color = statusColors[key] || [108, 117, 125];
                pdf.setTextColor(...color);
                val = statusLabels[key] || val;
            } else {
                pdf.setTextColor(0, 0, 0);
            }

            pdf.text(val, xPos + 2, yPos + 4);
            xPos += colWidths[col]; // ✅ position calculée dynamiquement
        }

        yPos += 9;
    }

    // ── Pied de page ──
    const pageCount = pdf.getNumberOfPages();
    for (let p = 1; p <= pageCount; p++) {
        pdf.setPage(p);
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        pdf.text(`Page ${p} of ${pageCount}`, 280, 195, { align: 'right' });
        pdf.text('EduMatch Platform - Contract Management', 15, 195);
    }

    const d = new Date();
    const fileName = `contracts_${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}.pdf`;
    pdf.save(fileName);
});
</script>

<script>
// FONCTIONS DE RECHERCHE ET TRI
(function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortSelect = document.getElementById('sortSelect');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const tableBody = document.querySelector('#contractsTable tbody');
    
    if (!tableBody) return;
    
    // Stocker les données originales
    let originalRows = [];
    
    // Sauvegarder les lignes originales
    function saveOriginalRows() {
        originalRows = [];
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.cells.length >= 7 && !row.cells[0]?.innerText.includes('No contracts')) {
                const dateDivs = row.cells[4]?.querySelectorAll('.small');
                originalRows.push({
                    element: row,
                    reference: row.cells[1]?.innerText.trim() || '',
                    company: row.cells[2]?.innerText.trim() || '',
                    type: row.cells[3]?.innerText.trim() || '',
                    startDate: dateDivs?.[0]?.innerText.replace('Start:', '').trim() || '',
                    endDate: dateDivs?.[1]?.innerText.replace('End:', '').trim() || '',
                    status: row.cells[5]?.innerText.trim().toLowerCase() || ''
                });
            }
        });
    }
    
    // Filtrer les lignes
    function filterRows() {
        const searchTerm = searchInput?.value.toLowerCase() || '';
        const statusValue = statusFilter?.value.toLowerCase() || '';
        
        const filtered = originalRows.filter(row => {
            // Recherche
            const matchSearch = searchTerm === '' || 
                row.reference.toLowerCase().includes(searchTerm) ||
                row.company.toLowerCase().includes(searchTerm) ||
                row.type.toLowerCase().includes(searchTerm);
            
            // Filtre statut
            const matchStatus = statusValue === '' || row.status === statusValue;
            
            return matchSearch && matchStatus;
        });
        
        return filtered;
    }
    
    // Trier les lignes
    function sortRows(rows, sortBy) {
        const sorted = [...rows];
        
        switch(sortBy) {
            case 'reference':
                sorted.sort((a, b) => a.reference.localeCompare(b.reference));
                break;
            case 'company':
                sorted.sort((a, b) => a.company.localeCompare(b.company));
                break;
            case 'type':
                sorted.sort((a, b) => a.type.localeCompare(b.type));
                break;
            case 'status':
                sorted.sort((a, b) => a.status.localeCompare(b.status));
                break;
            case 'number':
            default:
                // Garder l'ordre original
                sorted.sort((a, b) => {
                    const indexA = originalRows.findIndex(r => r.element === a.element);
                    const indexB = originalRows.findIndex(r => r.element === b.element);
                    return indexA - indexB;
                });
                break;
        }
        return sorted;
    }
    
    // Mettre à jour l'affichage
    function updateDisplay() {
        const filtered = filterRows();
        const sortBy = sortSelect?.value || 'number';
        const sorted = sortRows(filtered, sortBy);
        
        // Vider le tableau
        while (tableBody.firstChild) {
            tableBody.removeChild(tableBody.firstChild);
        }
        
        if (sorted.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="8" class="text-center text-muted py-4">No contracts found.</td>';
            tableBody.appendChild(emptyRow);
        } else {
            sorted.forEach((row, newIndex) => {
                const newRow = row.element.cloneNode(true);
                // Mettre à jour le numéro
                if (newRow.cells[0]) {
                    newRow.cells[0].innerText = (newIndex + 1).toString();
                }
                tableBody.appendChild(newRow);
            });
        }
        
        // Mettre à jour le compteur
        const totalSpan = document.getElementById('totalCount');
        if (totalSpan) {
            totalSpan.innerText = sorted.length;
        }
    }
    
    // Réinitialiser tous les filtres
    function resetFilters() {
        if (searchInput) searchInput.value = '';
        if (statusFilter) statusFilter.value = '';
        if (sortSelect) sortSelect.value = 'number';
        updateDisplay();
    }
    
    // Initialisation
    saveOriginalRows();
    
    // Ajouter le compteur dans le header
    const cardHeader = document.querySelector('.card-header .h5');
    if (cardHeader && !document.getElementById('totalCount')) {
        const countSpan = document.createElement('span');
        countSpan.id = 'totalCount';
        countSpan.className = 'ms-2 badge bg-secondary';
        countSpan.innerText = originalRows.length;
        cardHeader.appendChild(countSpan);
    }
    
    // Événements
    if (searchInput) searchInput.addEventListener('input', updateDisplay);
    if (statusFilter) statusFilter.addEventListener('change', updateDisplay);
    if (sortSelect) sortSelect.addEventListener('change', updateDisplay);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);
})();
</script>

<script>
// STATISTIQUES ET GRAPHIQUES POUR LES CONTRATS
(function() {
    let statusChart = null;
    let trendChart = null;
    
    // Fonction pour récupérer les statistiques depuis le tableau des contrats
    function getContractsStatsFromTable() {
        const rows = [];
        document.querySelectorAll('#contractsTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 7 && !cells[0]?.innerText.includes('No contracts')) {
                let status = cells[5]?.innerText.trim().toLowerCase() || '';
                // Normaliser les statuts
                if (status.includes('actif')) status = 'actif';
                else if (status.includes('expir')) status = 'expire';
                else if (status.includes('suspendu')) status = 'suspendu';
                
                // Récupérer la date de début pour la courbe
                let startDate = '-';
                const dateDivs = cells[4]?.querySelectorAll('.small');
                if (dateDivs && dateDivs[0]) {
                    startDate = dateDivs[0]?.innerText.replace('Start:', '').trim() || '-';
                }
                
                rows.push({ status, startDate });
            }
        });
        return rows;
    }
    
    // Calculer les statistiques des contrats
    function calculateContractsStats(contracts) {
        const stats = {
            actif: 0,
            expire: 0,
            suspendu: 0,
            total: contracts.length,
            monthlyData: {}
        };
        
        contracts.forEach(contract => {
            stats[contract.status]++;
            
            // Pour la courbe - regroupement par mois (basé sur la date de début)
            if (contract.startDate && contract.startDate !== '-') {
                let date = new Date(contract.startDate);
                if (!isNaN(date.getTime())) {
                    const month = date.toLocaleString('default', { month: 'short', year: 'numeric' });
                    if (!stats.monthlyData[month]) {
                        stats.monthlyData[month] = { actif: 0, expire: 0, suspendu: 0, total: 0 };
                    }
                    stats.monthlyData[month][contract.status]++;
                    stats.monthlyData[month].total++;
                }
            }
        });
        
        return stats;
    }
    
    // Mettre à jour les cartes de statistiques des contrats
    function updateContractsStatsCards(stats) {
        const activeEl = document.getElementById('activeContractsCount');
        const expiredEl = document.getElementById('expiredContractsCount');
        const suspendedEl = document.getElementById('suspendedContractsCount');
        const totalEl = document.getElementById('totalContractsCount');
        
        if (activeEl) activeEl.innerText = stats.actif;
        if (expiredEl) expiredEl.innerText = stats.expire;
        if (suspendedEl) suspendedEl.innerText = stats.suspendu;
        if (totalEl) totalEl.innerText = stats.total;
    }
    
    // Créer ou mettre à jour le graphique circulaire des contrats
    function updateContractsPieChart(stats) {
        const ctx = document.getElementById('contractStatusPieChart')?.getContext('2d');
        if (!ctx) return;
        
        if (statusChart) {
            statusChart.destroy();
        }
        
        statusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Actif', 'Expiré', 'Suspendu'],
                datasets: [{
                    data: [stats.actif, stats.expire, stats.suspendu],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 12 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = stats.actif + stats.expire + stats.suspendu;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Créer ou mettre à jour le graphique en courbe des contrats
    function updateContractsTrendChart(stats) {
        const ctx = document.getElementById('contractTrendLineChart')?.getContext('2d');
        if (!ctx) return;
        
        // Trier les mois chronologiquement
        const sortedMonths = Object.keys(stats.monthlyData).sort((a, b) => {
            return new Date(a) - new Date(b);
        });
        
        const actifData = sortedMonths.map(month => stats.monthlyData[month]?.actif || 0);
        const expireData = sortedMonths.map(month => stats.monthlyData[month]?.expire || 0);
        const suspenduData = sortedMonths.map(month => stats.monthlyData[month]?.suspendu || 0);
        
        if (trendChart) {
            trendChart.destroy();
        }
        
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: sortedMonths,
                datasets: [
                    {
                        label: 'Actif',
                        data: actifData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Expiré',
                        data: expireData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Suspendu',
                        data: suspenduData,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Number of Contracts' },
                        ticks: { stepSize: 1 }
                    },
                    x: { title: { display: true, text: 'Month' } }
                }
            }
        });
    }
    
    // Rafraîchir toutes les statistiques des contrats
    function refreshContractsStats() {
        const contracts = getContractsStatsFromTable();
        const stats = calculateContractsStats(contracts);
        updateContractsStatsCards(stats);
        updateContractsPieChart(stats);
        updateContractsTrendChart(stats);
    }
    
    // Observer les changements du tableau des contrats
    function observeContractsTableChanges() {
        const observer = new MutationObserver(function(mutations) {
            refreshContractsStats();
        });
        
        const tableBody = document.querySelector('#contractsTable tbody');
        if (tableBody) {
            observer.observe(tableBody, { childList: true, subtree: true });
        }
    }
    
    // Initialiser les stats des contrats
    setTimeout(() => {
        refreshContractsStats();
        observeContractsTableChanges();
    }, 500);
})();
</script>

<style>
    #searchInput:focus, #statusFilter:focus, #sortSelect:focus {
        border-color: #ff7f50;
        box-shadow: 0 0 0 0.2rem rgba(255, 127, 80, 0.25);
    }
    .table-responsive {
        min-height: 300px;
    }

</style>

            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
