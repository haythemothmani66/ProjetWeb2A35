<?php
declare(strict_types=1);
require dirname(__DIR__) . '/layout/header.php';
$partners = $partners ?? [];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- SECTION STATISTIQUES -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti ti-chart-pie me-2"></i> Partners by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusPieChart" style="max-height: 300px; width: 100%;"></canvas>
                <div id="statusLegend" class="mt-3 text-center"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti ti-chart-line me-2"></i> Partners Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="trendLineChart" style="max-height: 300px; width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Cartes de résumé -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-success bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-success" id="approvedCount">0</h3>
                <p class="text-muted mb-0">Approved</p>
                <i class="ti ti-check-circle text-success fs-4"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-warning bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-warning" id="pendingCount">0</h3>
                <p class="text-muted mb-0">Pending</p>
                <i class="ti ti-clock text-warning fs-4"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-danger bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-danger" id="rejectedCount">0</h3>
                <p class="text-muted mb-0">Rejected</p>
                <i class="ti ti-x-circle text-danger fs-4"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 bg-primary bg-opacity-10">
            <div class="card-body text-center">
                <h3 class="mb-0 text-primary" id="totalPartnersCount">0</h3>
                <p class="text-muted mb-0">Total Partners</p>
                <i class="ti ti-users text-primary fs-4"></i>
            </div>
        </div>
    </div>
</div>

<div class="card-body bg-white border-bottom p-3">
    <div class="row g-3 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by organization, type, email...">
            </div>
        </div>
        <div class="col-md-3">
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
            <select id="sortSelect" class="form-select">
                <option value="number">Sort by #</option>
                <option value="organization">Sort by Organization</option>
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


<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h2 class="h5 mb-0">Partner Requests</h2>
    <div class="d-flex gap-2">
        <button id="exportPdfBtn" class="btn btn-danger btn-sm">
            <i class="ti ti-file-pdf"></i> Export PDF
        </button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'verification'])); ?>">Verification</a>
        <a class="btn btn-dark btn-sm" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'add'])); ?>">Add Partenaire</a>
    </div>
</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="partnersTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Organization</th>
                    <th>Type</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            
            <tbody>
            <?php if (empty($partners)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No partner requests found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($partners as $index => $partner): ?>
                    <?php
                    $status = strtolower((string)($partner['status'] ?? 'pending'));
                    $statusClass = 'bg-warning-subtle text-warning-emphasis';
                    if ($status === 'approved') {
                        $statusClass = 'bg-success-subtle text-success-emphasis';
                    } elseif ($status === 'rejected') {
                        $statusClass = 'bg-danger-subtle text-danger-emphasis';
                    }
                    ?>
                    <tr>
                        <td><?= (int)$index + 1; ?></td>
                        <td><?= h((string)($partner['organization_name'] ?? '')); ?></td>
                        <td><?= h((string)($partner['partner_type'] ?? '')); ?></td>
                        <td><?= h((string)($partner['email'] ?? '')); ?></td>
                        <td><?= h((string)($partner['telephone'] ?? '')); ?></td>
                        <td><span class="badge <?= h($statusClass); ?>"><?= h(ucfirst($status)); ?></span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'edit', 'id' => (int)$partner['id']])); ?>">Edit</a>
                            <form action="<?= h(appUrl(['controller' => 'partenaire', 'action' => 'delete', 'id' => (int)$partner['id']])); ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this partner request?');">
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
    document.querySelectorAll('.table tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        // 7 colonnes : # | Org | Type | Email | Tel | Status | Actions
        if (cells.length >= 6 && !cells[0]?.innerText.includes('No partner')) {
            rows.push({
                organization: cells[1]?.innerText.trim() || '',   // ← index 1
                type:         cells[2]?.innerText.trim() || '',   // ← index 2
                email:        cells[3]?.innerText.trim() || '',   // ← index 3
                telephone:    cells[4]?.innerText.trim() || '',   // ← index 4
                // Le status est dans un <span class="badge"> → on lit son texte
                status:       cells[5]?.querySelector('span')?.innerText.trim().toLowerCase() || ''  // ← index 5
            });
        }
    });

    if (rows.length === 0) { alert('No data to export!'); return; }

    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const primaryColor = [255, 127, 80];
    let yPos = 25;

    // ── En-tête ──
    pdf.setFontSize(20);
    pdf.setTextColor(...primaryColor);
    pdf.text('EduMatch Partners Report', 15, yPos);
    yPos += 10;
    pdf.setFontSize(9);
    pdf.setTextColor(100, 100, 100);
    pdf.text(`Generated: ${new Date().toLocaleString()}`, 15, yPos);
    yPos += 6;
    pdf.text(`Total Partners: ${rows.length}`, 15, yPos);
    yPos += 6;
    pdf.setDrawColor(200, 200, 200);
    pdf.line(15, yPos, 200, yPos);
    yPos += 10;

    // ── Configuration colonnes ──
    const headers   = ['Organization', 'Type', 'Email', 'Phone', 'Status'];
    const colWidths = [45, 25, 65, 25, 25];
    const maxChars  = [20, 12, 28, 14, 12];

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

    const statusColors = {
        approved: [40, 167, 69],
        pending:  [200, 150, 0],
        rejected: [220, 53, 69]
    };

    for (let i = 0; i < rows.length; i++) {
        if (yPos > 275) {
            pdf.addPage();
            yPos = 20;
            drawHeader();
            pdf.setFontSize(8);
            pdf.setFont('helvetica', 'normal');
        }

        // Fond alterné
        if (i % 2 === 0) {
            pdf.setFillColor(245, 245, 245);
            pdf.rect(15, yPos - 2, 185, 8, 'F');
        }

        const values = [
            rows[i].organization,
            rows[i].type,
            rows[i].email,
            rows[i].telephone,
            rows[i].status
        ];

        let xPos = 15;
        for (let col = 0; col < headers.length; col++) {
            let val = values[col] || '';
            if (val.length > maxChars[col]) val = val.substring(0, maxChars[col] - 2) + '..';

            if (col === 4) {
                // Colonne Status : couleur selon valeur
                const color = statusColors[val.toLowerCase()] || [108, 117, 125];
                pdf.setTextColor(...color);
                val = val.charAt(0).toUpperCase() + val.slice(1);
            } else {
                pdf.setTextColor(0, 0, 0);
            }

            pdf.text(val, xPos + 2, yPos + 4);
            xPos += colWidths[col];
        }

        yPos += 8;
    }

    // ── Pied de page ──
    const pageCount = pdf.getNumberOfPages();
    for (let p = 1; p <= pageCount; p++) {
        pdf.setPage(p);
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        pdf.text(`Page ${p} of ${pageCount}`, 195, 290, { align: 'right' });
        pdf.text('EduMatch Platform - Partner Management', 15, 290);
    }

    const d = new Date();
    const fileName = `partners_${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}.pdf`;
    pdf.save(fileName);
});
</script>

<script>
// FONCTIONS DE RECHERCHE ET TRI POUR LES PARTENAIRES
(function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortSelect = document.getElementById('sortSelect');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const tableBody = document.querySelector('#partnersTable tbody');
    
    if (!tableBody) return;
    
    // Stocker les données originales
    let originalRows = [];
    
    // Sauvegarder les lignes originales
    function saveOriginalRows() {
        originalRows = [];
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 6 && !cells[0]?.innerText.includes('No partner')) {
                originalRows.push({
                    element: row,
                    number: cells[0]?.innerText.trim() || '',
                    organization: cells[1]?.innerText.trim() || '',
                    type: cells[2]?.innerText.trim() || '',
                    email: cells[3]?.innerText.trim() || '',
                    telephone: cells[4]?.innerText.trim() || '',
                    status: cells[5]?.innerText.trim().toLowerCase() || ''
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
                row.organization.toLowerCase().includes(searchTerm) ||
                row.type.toLowerCase().includes(searchTerm) ||
                row.email.toLowerCase().includes(searchTerm);
            
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
            case 'organization':
                sorted.sort((a, b) => a.organization.localeCompare(b.organization));
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
            emptyRow.innerHTML = '<td colspan="7" class="text-center text-muted py-4">No partner requests found.匹配';
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
// STATISTIQUES ET GRAPHIQUES
(function() {
    let statusChart = null;
    let trendChart = null;
    
    // Fonction pour récupérer les statistiques depuis le tableau
    function getStatsFromTable() {
        const rows = [];
        document.querySelectorAll('#partnersTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 6 && !cells[0]?.innerText.includes('No partner')) {
                const statusCell = cells[5]?.innerText.trim().toLowerCase() || '';
                let status = 'pending';
                if (statusCell.includes('approved')) status = 'approved';
                else if (statusCell.includes('rejected')) status = 'rejected';
                else if (statusCell.includes('pending')) status = 'pending';
                
                // Récupérer la date de création si disponible (sinon utiliser date actuelle)
                let createdAt = new Date();
                rows.push({ status, createdAt });
            }
        });
        return rows;
    }
    
    // Calculer les statistiques
    function calculateStats(partners) {
        const stats = {
            approved: 0,
            pending: 0,
            rejected: 0,
            total: partners.length,
            monthlyData: {}
        };
        
        partners.forEach(partner => {
            stats[partner.status]++;
            
            // Pour la courbe - regroupement par mois
            const month = partner.createdAt.toLocaleString('default', { month: 'short', year: 'numeric' });
            if (!stats.monthlyData[month]) {
                stats.monthlyData[month] = { approved: 0, pending: 0, rejected: 0, total: 0 };
            }
            stats.monthlyData[month][partner.status]++;
            stats.monthlyData[month].total++;
        });
        
        return stats;
    }
    
    // Mettre à jour les cartes de statistiques
    function updateStatsCards(stats) {
        document.getElementById('approvedCount').innerText = stats.approved;
        document.getElementById('pendingCount').innerText = stats.pending;
        document.getElementById('rejectedCount').innerText = stats.rejected;
        document.getElementById('totalPartnersCount').innerText = stats.total;
    }
    
    // Créer ou mettre à jour le graphique circulaire
    function updatePieChart(stats) {
        const ctx = document.getElementById('statusPieChart')?.getContext('2d');
        if (!ctx) return;
        
        if (statusChart) {
            statusChart.destroy();
        }
        
        statusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [stats.approved, stats.pending, stats.rejected],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
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
                                const total = stats.approved + stats.pending + stats.rejected;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Créer ou mettre à jour le graphique en courbe
    function updateTrendChart(stats) {
        const ctx = document.getElementById('trendLineChart')?.getContext('2d');
        if (!ctx) return;
        
        // Trier les mois chronologiquement
        const sortedMonths = Object.keys(stats.monthlyData).sort((a, b) => {
            return new Date(a) - new Date(b);
        });
        
        const approvedData = sortedMonths.map(month => stats.monthlyData[month]?.approved || 0);
        const pendingData = sortedMonths.map(month => stats.monthlyData[month]?.pending || 0);
        const rejectedData = sortedMonths.map(month => stats.monthlyData[month]?.rejected || 0);
        
        if (trendChart) {
            trendChart.destroy();
        }
        
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: sortedMonths,
                datasets: [
                    {
                        label: 'Approved',
                        data: approvedData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Pending',
                        data: pendingData,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Rejected',
                        data: rejectedData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
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
                        title: { display: true, text: 'Number of Partners' },
                        ticks: { stepSize: 1 }
                    },
                    x: { title: { display: true, text: 'Month' } }
                }
            }
        });
    }
    
    // Rafraîchir toutes les statistiques
    function refreshStats() {
        const partners = getStatsFromTable();
        const stats = calculateStats(partners);
        updateStatsCards(stats);
        updatePieChart(stats);
        updateTrendChart(stats);
    }
    
    // Observer les changements du tableau (pour mettre à jour les stats)
    function observeTableChanges() {
        const observer = new MutationObserver(function(mutations) {
            refreshStats();
        });
        
        const tableBody = document.querySelector('#partnersTable tbody');
        if (tableBody) {
            observer.observe(tableBody, { childList: true, subtree: true });
        }
    }
    
    // Initialiser les stats
    setTimeout(() => {
        refreshStats();
        observeTableChanges();
    }, 500);
})();
</script>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
