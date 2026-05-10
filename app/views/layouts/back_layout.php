<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Module Événement</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Jost:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
</head>
<body class="template-admin-body">
    <nav class="navbar navbar-expand-lg navbar-dark template-admin-navbar shadow-sm">
        <div class="container-fluid px-3">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?php echo BASE_URL; ?>/AdminEvenement/index">
                <i class="fas fa-calendar-check"></i> Admin Événements
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminCategorie/index"><i class="fas fa-tags me-1 d-none d-lg-inline"></i> Catégories</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminEvenement/index"><i class="fas fa-calendar-alt me-1 d-none d-lg-inline"></i> Événements</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/AdminParticipation/index"><i class="fas fa-users me-1 d-none d-lg-inline"></i> Participants</a></li>
                    <li class="nav-item"><a class="nav-link btn-admin-site rounded-pill px-3 mt-2 mt-lg-0 ms-lg-2" href="<?php echo BASE_URL; ?>/Home/index"><i class="fas fa-external-link-alt me-1"></i> Voir le site</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4 template-admin-content">
        <?php echo $content; ?>
    </div>

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteConfirmMessage" class="mb-0 text-muted">Voulez-vous confirmer la suppression ?</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger rounded-pill" id="deleteConfirmYesBtn">Oui</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/validation.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var confirmModalEl = document.getElementById('deleteConfirmModal');
        var confirmMessageEl = document.getElementById('deleteConfirmMessage');
        var confirmYesBtn = document.getElementById('deleteConfirmYesBtn');
        var pendingDeleteUrl = null;

        if (confirmModalEl && confirmMessageEl && confirmYesBtn) {
            var deleteModal = new bootstrap.Modal(confirmModalEl);
            document.querySelectorAll('.js-confirm-delete').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    pendingDeleteUrl = link.getAttribute('href');
                    confirmMessageEl.textContent = link.getAttribute('data-confirm-message') || 'Voulez-vous confirmer la suppression ?';
                    deleteModal.show();
                });
            });

            confirmYesBtn.addEventListener('click', function () {
                if (pendingDeleteUrl) {
                    window.location.href = pendingDeleteUrl;
                }
            });
        }

        document.querySelectorAll('.js-export-pdf').forEach(function (button) {
            button.addEventListener('click', function () {
                var tableId = button.getAttribute('data-table-id');
                var title = button.getAttribute('data-title') || 'Export PDF';
                var table = document.getElementById(tableId);
                if (!table || !window.jspdf || !window.jspdf.jsPDF) {
                    return;
                }

                var headers = [];
                table.querySelectorAll('thead th').forEach(function (th) {
                    headers.push(th.innerText.trim());
                });

                var rows = [];
                table.querySelectorAll('tbody tr').forEach(function (tr) {
                    if (tr.classList.contains('participants-row') && tr.classList.contains('d-none')) {
                        return;
                    }
                    var cells = [];
                    tr.querySelectorAll('td').forEach(function (td) {
                        cells.push(td.innerText.replace(/\s+/g, ' ').trim());
                    });
                    if (cells.length > 0) {
                        rows.push(cells);
                    }
                });

                var doc = new window.jspdf.jsPDF();
                doc.setFontSize(14);
                doc.text(title, 14, 16);
                doc.autoTable({
                    head: [headers],
                    body: rows,
                    startY: 22,
                    styles: { fontSize: 9, cellPadding: 2 }
                });
                doc.save(title.toLowerCase().replace(/[^a-z0-9]+/g, '_') + '.pdf');
            });
        });
    });
    </script>
</body>
</html>
